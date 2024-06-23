<?php

/**目录拼接*/
class Path
{
    /**连接目录, 结尾不包含/*/
    public static function combine(...$paths): string
    {
        $dirs = [];
        foreach ($paths as $path) {
            $path = str_replace('\\', '/', $path);
            $parts = explode('/', $path);
            foreach ($parts as $p) {
                if ($p !== '') {
                    $dirs[] = $p;
                }
            }
        }

        $result = '';
        foreach ($dirs as $path) {
            $result .= $path . DIRECTORY_SEPARATOR;
        }
        $result = rtrim($result, DIRECTORY_SEPARATOR);
        // 是否是 Linux 系统
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

    /**获取当前网址的完整根域名, 如果不是默认端口, 则包含端口, 结尾包含/*/
    public static function getFullHost(): string
    {
        $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host/";
    }

    /**获取项目根目录, 结尾包含/*/
    public static function getServerRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/';
    }
}