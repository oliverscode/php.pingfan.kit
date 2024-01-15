<?php

/**curl封装, 同时支持cookie管理*/
class Http
{
    private $timeout;
    private $retryCount;
    private $cookieJar;

    public function __construct($timeout = 30, $retryCount = 3)
    {
        // 判断是否存在curl库
        if (!extension_loaded('curl')) {
            die('curl extension not loaded');
        }

        $this->timeout = $timeout;
        $this->retryCount = $retryCount;
        $this->cookieJar = [];
    }

    public function get($url, $params = [], $customHeaders = []): array
    {
        $queryString = http_build_query($params);
        $url = "{$url}?{$queryString}";
        return $this->request('GET', $url, [], false, $customHeaders);
    }

    public function post($url, $params = [], $asJson = false, $customHeaders = []): array
    {
        return $this->request('POST', $url, $params, $asJson, $customHeaders);
    }

    private function request($method, $url, $params, $asJson, $customHeaders = []): array
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => true  // 添加这一行
        ];
        $headers = $customHeaders;

        $cookieString = http_build_query($this->cookieJar, '', '; ');
        if (!empty($cookieString)) {
            $options[CURLOPT_COOKIE] = $cookieString;
        }


        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $asJson ? json_encode($params) : http_build_query($params);
            $headers[] = $asJson ? 'Content-Type: application/json' : 'application/x-www-form-urlencoded';
            $headers = array_merge($headers, $customHeaders);

        }


        if (!empty($headers)) {
            // $options[CURLOPT_HTTPHEADER] = $headers;
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($v, $k) {
                return $k . ': ' . $v;
            }, array_values($headers), array_keys($headers)));
        }

        curl_setopt_array($ch, $options);


        $retry = $this->retryCount;
        $response = null;
        while ($retry--) {
            $response = curl_exec($ch);
            if (curl_errno($ch) == 0) {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headerString = substr($response, 0, $header_size);
                $body = substr($response, $header_size);

                $headers = $this->parseHeaders($headerString);

                $this->updateCookies(curl_getinfo($ch, CURLINFO_COOKIELIST));
                curl_close($ch);

                return ['headers' => $headers, 'body' => $body];
            }
        }

        curl_close($ch);
        return ['headers' => [], 'body' => ''];
    }

    private function parseHeaders($headerString): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerString);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }

    private function updateCookies($cookieList)
    {
        foreach ($cookieList as $cookieString) {
            list($domain, $flag, $path, $secure, $expiration, $name, $value) = explode("\t", $cookieString);
            $this->cookieJar[$name] = $value;
        }
    }
}