<?php

/**常见url管理*/
class Uri
{
    /**跳转url header("Location: $url");*/
    public static function redirect(string $url)
    {
        header("Location: $url");
        die;
    }

    /**返回上一个页面*/
    public static function return($count = 1)
    {
        // 返回上一个页面
        die("<script>history.go(-$count);</script>");
    }

    /**返回404错误*/
    public static function notfound()
    {
        // 设置404状态码
        http_response_code(404);
        die;
//        header('HTTP/1.1 404 Not Found');
//       die();
    }

    /**返回500错误*/
    public static function error()
    {
        http_response_code(500);
        die;
//        header('HTTP/1.1 500 Internal Server Error');
//        die;
    }
}
