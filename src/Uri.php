<?php

/**常见url管理*/
class Uri
{

    /**
     * 获取当前网址的完整根域名, 如果不是默认端口, 则包含端口, 结尾包含/
     */
    public static function getHost(): string
    {
        $protocol = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host/";
    }

    /**
     * 获取根目录, 结尾包含/
     */
    public static function getServerRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/';
    }


    /**
     * 连接目录, 结尾不包含/
     */
    public static function combine(...$paths): string
    {
        $result = '';
        foreach ($paths as $path) {
            $result .= rtrim($path, '/') . '/';
        }
        return rtrim($result, '/');
    }

    /**
     * 从服务器根目录连接一系列目录, 结尾不包含/
     */
    public static function combineFromServerRoot(...$paths): string
    {
        $root = self::getServerRoot();
        return self::combine($root, ...$paths);
    }

}
