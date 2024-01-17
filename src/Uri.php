<?php

/**常见url管理*/
class Uri
{

    /**获取当前网址的完整根域名, 如果不是默认端口, 则包含端口, 结尾包含/*/
    public static function getHost(): string
    {
        $protocol = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host/";
    }

    /**获取项目根目录, 结尾包含/*/
    public static function getServerRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/';
    }

    /**连接目录, 结尾不包含/*/
    public static function combine(...$paths): string
    {
        $dirs = [];
        foreach ($paths as $path) {
            $str = new Str($path);
            foreach ($str->split('\\', '/') as $p) {
                $dirs[] = $p;
            }
        }

        $result = '';
        foreach ($dirs as $path) {
            $result .= $path . DIRECTORY_SEPARATOR;
        }
        $result = rtrim($result, DIRECTORY_SEPARATOR);


        // 是否是linux系统
        $isLinux = DIRECTORY_SEPARATOR === '/';
        if ($isLinux) {
            $result = '/' . $result;
        }
        return $result;
    }

    /**从服务器根目录连接一系列目录, 结尾不包含/*/
    public static function combineFromServerRoot(...$paths): string
    {
        $root = self::getServerRoot();
        return self::combine($root, ...$paths);
    }

    /**跳转url header("Location: $url");*/
    public static function redirect($url)
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
