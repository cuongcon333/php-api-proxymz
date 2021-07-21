<?php

class ProxyMZ
{
    const BASE_API = "https://api.proxymz.com/api";
    private $http;
    private $accessToken = null;
    public function __construct($accessToken)
    {
        if (!$accessToken)
            exit("Vui lòng nhập mã Access Token tài khoản");
        $this->http = new Request(self::BASE_API);
    }

    /**
     * Lây thông tin tài khoản
     * @method GET
     * @param accessToken
     */
    public function me()
    {
        $response = $this->http
            ->setURL("/me")
            ->setMethod(Request::GET)
            ->setToken($this->accessToken)
            ->setParseJSON()
            ->execute();
        if (isset($response->username)) {
            // Lấy thông tin thành công truy cập vào đây để xem response -> https://proxymz.com/developer/api
        } else {
            // Lấy thông tin không thành công
        }
    }
    /**
     * Tạo đơn hàng Proxy Private/Shared và thanh toan toán đơn hàng trong 1 bước
     * @method POST
     * @param accessToken
     * @param country quốc gia theo ký hiệu ISO: ví dụ VN
     * @param serviceType Loại dịch vụ chỉ cho phép: IPv4, IPv6, IPv4Shared
     * @param period thời hạn chỉ định mua proxy được phép [3|7|14|30|60|90|180|365]
     * @param quantity số lượng proxy muốn mua, tối thiểu là 1 tối đa là 1000 proxy
     */
    public function createInCheckout($country = "VN", $serviceType = "IPv6", $period = 3, $quantity = 1, $connectMethod = "HTTP")
    {
        $response = $this->http
            ->setURL("/orders/proxies")
            ->setObject(json_encode([
                'country' => $country,
                'serviceType' => $serviceType,
                'period' => $period,
                'quantity' => $quantity
            ]))
            ->setMethod(Request::POST)
            ->setToken($this->accessToken)
            ->setParseJSON()
            ->execute();
        if ($response->success == true) {
            $ReferenceID = $response->data->ReferenceID; // Sử dụng mã này để thanh toán đơn hàng hoặc TransactionID, HashID, _id, ReferenceID (Đểu có thể sử dụng cho việc thanh toán)
            /**
             * Tiêp tục tới bước thanh toán đơn hàng
             * @param connectMethod phương thức kết nối [HTTP, SOCKS]
             */
            $checkout = $this->http
                ->setURL("/orders/proxies/$ReferenceID/checkout")
                ->setObject(json_encode([
                    'connectMethod' => $connectMethod
                ]))
                ->setMethod(Request::POST)
                ->setToken($this->accessToken)
                ->setParseJSON()
                ->execute();
            if ($checkout->success) {
                // Thanh toán đơn hàng thành công
                // $checkout->data // Hiền thị danh sách proxy đã mua thành công
                // Lưu ý hãy lưu $checkout->data[$i]->ServiceCode lại để sử dụng cho việc gia hạn
            } else {
                // Thanh toán đơn hàng không thành công
            }
            // Tạo đơn hàng thành công
        } else {
            // Tạo đơn hàng thất bại
        }
    }
    /**
     * Tạo đơn gia hạn và thanh toán đơn hàng gia hạn
     * @method POST
     * @param accessToken
     * @param serviceList array danh sách ServiceCode được định nghĩa ["ABCDC","HICAS","HEHE]
     * @param period thời hạn chỉ định mua proxy được phép [3|7|14|30|60|90|180|365]
     */
    public function renewalInCheckout($serviceList = ["ABCDC", "HIEC"], $period = 3)
    {
        $response = $this->http
            ->setURL("/proxy/renewal")
            ->setObject(json_encode([
                'serviceList' => $serviceList,
                'period' => $period,
            ]))
            ->setMethod(Request::POST)
            ->setToken($this->accessToken)
            ->setParseJSON()
            ->execute();
        if ($response->success == true) {
            $ReferenceID = $response->data->ReferenceID; // Sử dụng mã này để thanh toán đơn hàng hoặc TransactionID, HashID, _id, ReferenceID (Đểu có thể sử dụng cho việc thanh toán)
            /**
             * Tiêp tục tới bước thanh toán đơn hàng gia hạn
             */
            $checkout = $this->http
                ->setURL("/orders/proxies/$ReferenceID/renewal")
                ->setMethod(Request::POST)
                ->setToken($this->accessToken)
                ->setParseJSON()
                ->execute();
            if ($checkout->success) {
                // Thanh toán đơn hàng gia hạn thành công
            } else {
                // Thanh toán đơn hàng gia hạn không thành công
            }
        } else {
            // Tạo đơn hàng gia hạn thất bại
        }
    }
    /**
     * Sử dụng mã giảm giá cho đơn hàng
     * @method PUT
     * @param couponName mã giảm giá
     */
    public function applyCoupon($ReferenceID = null, $couponName = "")
    {
        $response = $this->http
            ->setURL("/orders/proxies/$ReferenceID")
            ->setObject(json_encode([
                'couponName' => $couponName
            ]))
            ->setMethod(Request::PUT)
            ->setToken($this->accessToken)
            ->setParseJSON()
            ->execute();
        if ($response->success == true) {
            // Sử dụng mã giảm giá cho đơn hàng thành công
        } else {
            // Sử dụng mã giảm giá cho đơn hàng không thành công
        }
    }
    /**
     * Bật tự động gia hạn hoặc tắt tự động gia hạn
     * @method POST
     * @param serviceList array danh sách ServiceCode được định nghĩa ["ABCDC","HICAS","HEHE]
     * @param type ON=Bật tự động gia hạn; OFF=Tắt tự động gia hạn;
     */
    public function setAutoRenewal($serviceList = ["ABCDC", "HIEC"], $type = "ON")
    {
        $response = $this->http
            ->setURL("/proxy/autorenewal")
            ->setObject(json_encode([
                'serviceList' => $serviceList,
                'type' => $type,
            ]))
            ->setMethod(Request::POST)
            ->setToken($this->accessToken)
            ->setParseJSON()
            ->execute();
        if ($response->success == true) {
            if ($type == "ON") {
                // Bật tự động gia hạn thành công
            } else {
                // Tắt tự động gia hạn thành công
            }
        } else {
            // Báo lỗi trong qua trình bật tự động hạn hoặc tắt tự động gia hạn
        }
    }
    /**
     * Huỷ proxy theo proxy đã chọn
     * @method POST
     * @param serviceList array danh sách ServiceCode được định nghĩa ["ABCDC","HICAS","HEHE] để sử dụng xoá
     */
    public function destroyProxy($serviceList = ["ABCDC", "HIEC"])
    {
        $delete = $this->http
            ->setURL("/proxy/delete")
            ->setObject(json_encode([
                'serviceList' => $serviceList,
            ]))
            ->setMethod(Request::POST)
            ->setToken($this->accessToken)
            ->setParseJSON()
            ->execute();
        if ($delete->success == true) {
            // Xoá proxy thành công
            // $delete->data // danh sách proxy bị xoá
        } else {
            // Báo lỗi trong qua trình bật tự động hạn hoặc tắt tự động gia hạn
        }
    }
}
