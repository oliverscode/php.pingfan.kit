<?php

/**JSON扩展*/
class JSON
{
    public static function encode($data)
    {
        // 中文不转义
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function decode($data)
    {
        return json_decode($data, true);
    }
}