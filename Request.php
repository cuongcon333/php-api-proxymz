<?php
class Request
{
    public const GET = "GET";
    public const POST = "POST";
    public const PUT = "PUT";
    public const DELETE = "DELETE";
    public const PATCH = "PATCH";
    public $url = null;
    public $path;
    public $method = "GET";
    public $accessToken = null;
    public $parse = false;
    public $object = null;
    public $headers = null;
    public $user_agent = null;
    public function __construct($url = "")
    {
        $this->url = $url;
    }
    public function setAccessToken($token = null)
    {
        $this->accessToken = $token;
        $this->headers['Authorization'] = 'Bearer ' . $token;
        return $this;
    }
    public function setURL($path = "")
    {
        $this->path = $path;
        return $this;
    }
    public function setMethod($method = "GET")
    {
        $this->method = $method;
        return $this;
    }
    public function setParseJSON($parse = true)
    {
        $this->parse = $parse;
        return $this;
    }
    public function setObject($object = null)
    {
        $this->object = $object;
        return $this;
    }
    public function setHeaders($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }
    public function setUserAgent($user_agent = null)
    {
        $this->user_agent = $user_agent;
        return $this;
    }
    public function execute()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $this->path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if ($this->object !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_object($this->object) ? $this->object : (is_array($this->object) ? http_build_query($this->object) : $this->object));
        }
        if ($this->headers !== null && is_array($this->headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        if ($this->user_agent !== null) curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($this->parse == true) return json_decode($response);
        return $response;
    }
}
