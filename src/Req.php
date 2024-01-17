<?php

/**获取请求的数据*/
class Req
{
    /**获取get请求中的参数*/
    public static function get(string $key, $default = ''): string
    {
        return $_GET[$key] ?? $default;
    }

    /**获取post请求中的参数*/
    public static function post(string $key, $default = ''): string
    {
        return $_POST[$key] ?? $default;
    }

    /**获取session中的参数*/
    public static function session(string $key, $default = ''): string
    {
        return $_SESSION[$key] ?? $default;
    }

    /**获取cookie中的参数*/
    public static function cookie(string $key, $default = ''): string
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**获取request中的参数*/
    public static function request(string $key, $default = ''): string
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**获取本次请求流*/
    public static function input()
    {
        return file_get_contents('php://input');
    }
}