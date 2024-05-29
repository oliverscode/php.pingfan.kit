<?php

/**常见的数字管理*/
class NumberEx
{
    /**计算2个数大小是否在小于等于一个比例, 默认1％*/
    public static function errorRate($num1, $num2, $errRate = 0.01): bool
    {
        $max = max($num1, $num2);
        $min = min($num1, $num2);
        return ($max - $min) / $min <= $errRate || ($max - $min) / $max <= $errRate;
    }

    /**转成含有B KB MB GB的字符串*/
    public static function toByteSize($size, $digits = 1): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return round($size / pow(1024, $power), $digits) . ' ' . $units[$power];
    }
}
