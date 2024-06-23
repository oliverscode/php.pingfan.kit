<?php

/**JSON扩展*/
class Json
{
    /** 将数据转成JSON字符串, 中文不进行转义 */
    public static function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /** 将JSON字符串转成数组 */
    public static function decode(string $data): array
    {

        return json_decode($data, true);
    }
}