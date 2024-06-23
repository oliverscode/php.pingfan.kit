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

    /**获取请求头中的参数*/
    public static function header(string $key, $default = ''): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? $default;
    }

    /**获取cookie中的参数*/
    public static function cookie(string $key, $default = ''): string
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**获取request中的参数*/
    public static function string(string $key, $default = ''): string
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**获取request中的参数, 并转成整型*/
    public static function int(string $key, $default = 0): int
    {
        return intval(self::string($key, $default));
    }

    /**获取request中的参数, 并转成浮点型*/
    public static function float(string $key, $default = 0.0): float
    {
        return floatval(self::string($key, $default));
    }

    /**获取文件上传*/
    public static function file(string $key, string $savePath): bool
    {
        return move_uploaded_file($_FILES[$key]['tmp_name'], $savePath);
    }


    /**获取本次请求流*/
    public static function input()
    {
        return file_get_contents('php://input');
    }
}