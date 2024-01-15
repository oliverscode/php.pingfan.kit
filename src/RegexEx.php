<?php

/**正则封装*/
class RegexEx
{
    /**判断输入字符串是否与模式匹配*/
    public static function isMatch(string $input, string $pattern): bool
    {
        return preg_match("/$pattern/", $input) === 1;
    }

    /**用指定的替换字符串替换与模式匹配的所有子串*/
    public static function replace(string $input, string $pattern, string $replacement)
    {
        return preg_replace("/$pattern/", $replacement, $input);
    }

    /**获取与模式匹配的所有子串*/
    public static function matches(string $input, string $pattern): array
    {
        preg_match_all("/$pattern/", $input, $matches);
        return $matches[0];
    }

    /**获取与模式匹配的第一个子串*/
    public static function match(string $input, string $pattern): ?string
    {
        preg_match("/$pattern/", $input, $match);
        return $match[0] ?? null;
    }
}
