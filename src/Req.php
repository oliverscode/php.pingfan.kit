<?php

/**获取get请求中的参数*/
function get(string $key, $default = ''): string
{
    return $_GET[$key] ?? $default;
}

/**获取post请求中的参数*/

function post(string $key, $default = ''): string
{
    return $_POST[$key] ?? $default;
}

/**获取session中的参数*/

function session(string $key, $default = ''): string
{
    return $_SESSION[$key] ?? $default;
}

/**获取cookie中的参数*/

function cookie(string $key, $default = ''): string
{
    return $_COOKIE[$key] ?? $default;
}

/**获取request中的参数*/

function request(string $key, $default = ''): string
{
    return $_REQUEST[$key] ?? $default;
}

/**获取本次请求流*/

function input()
{
    return file_get_contents('php://input');
}
$b = "ccc";