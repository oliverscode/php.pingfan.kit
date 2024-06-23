<?php
require_once 'Http.php';
require_once 'Str.php';

class IpEx
{
    /**获取IP物理地址*/
    public static function getIpLocation($ip, $lang = 'zh-CN')
    {
        $http = new Http(30000, 5);
        $url = "http://ip-api.com/json/{$ip}";
        $html = $http->get($url, ['lang' => $lang])['body'];
        $html = trim($html);
        $html = new Str($html);
        $country = $html->match('/"country":"(.+?)"/');
        $regionName = $html->match('/"regionName":"(.+?)"/');
        $city = $html->match('/"city":"(.+?)"/');

        if (!empty($country) && !empty($regionName) && !empty($city)) {

            if ($country === $regionName && $regionName === $city) {
                return "{$country}";
            }

            if ($country === $regionName) {
                return "{$country}{$city}";
            }
            if ($country === $city) {
                return "{$country}{$regionName}";
            }
            if ($regionName === $city) {
                return "{$country}{$regionName}";
            }

            return "{$country}{$regionName}{$city}";
        }
        return null;
    }
}