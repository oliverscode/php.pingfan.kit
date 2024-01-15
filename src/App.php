<?php

class App
{
    public static function Run($default = '/home/index')
    {

        error_reporting(E_ERROR); // 设置错误级别


        // 提取路径中的控制器和方法
        $url = $_SERVER['REQUEST_URI'];

        $path = RegexEx::match($url, '\/(\w+)\/(\w+)\/?');
        if (strlen($path) == 0) {
            $path = $default;
        }

        $root = dirname($_SERVER['SCRIPT_FILENAME']);

        // 当前脚本的物理目录
        $file = Uri::combine($root, $path . '.php');


        if (!file_exists($file)) {
            Uri::notfound();
        }

        try {
            header('Content-Type: text/html; charset=utf-8'); // 设置编码
            require_once $file;
        } catch (Exception $e) {
            $log = new Log('error');
            $log->error($e);
            Uri::error();
        }

    }
}