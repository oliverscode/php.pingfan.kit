<?php

class Str
{
    public string $Value;


    public function __construct(string $str)
    {
        // 如果不是字符串就报错
        if (gettype($str) != 'string') {
            $str = (string)$str;
        }
        $this->Value = $str;
    }

    /**字符串长度*/
    public function getLength(): int
    {
        return (int)mb_strlen($this->Value);
    }

    /**转成数字类型*/
    public function toFloat(): float
    {
        return (float)$this->Value;
    }

    /**转成数字类型*/
    public function toInt(): int
    {
        return (int)$this->Value;
    }

    /**隐藏中间几位*/
    public function hide(int $startIndex, int $count, $char = '*')
    {
        if ($startIndex < 0) {
            $startIndex = 0;
        }
        if ($startIndex >= $this->getLength()) {
            return $this->Value;
        }
        if ($count <= 0) {
            return $this->Value;
        }
        if ($startIndex + $count > $this->getLength()) {
            $count = $this->getLength() - $startIndex;
        }

        $str = $this->Value;
        for ($i = 0; $i < $count; $i++) {
            $str[$startIndex + $i] = $char;
        }
        return new Str($str);
    }


    /**是否匹配一个字符串*/
    public function isMatch(string $pattern): bool
    {
        return preg_match($pattern, $this->Value) === 1;
    }

    /**正则匹配第一个字符串*/
    public function match(string $pattern): Str
    {
        preg_match($pattern, $this->Value, $match);
        $str = $match[0] ?? '';
        return new Str($str);
    }

    /**正则匹配所有字符串*/
    public function matches(string $pattern): array
    {
        preg_match_all("$pattern", $this->Value, $matches);
        return $matches[0];
    }

    /**正则替换字符串*/
    public function replace(string $pattern, string $replacement): Str
    {
        return new Str(preg_replace($pattern, $replacement, $this->Value));
    }

    /**支持分割字符串数组, 同时排除空*/
    public function split($separator): array
    {
        if (is_array($separator)) {
            $pattern = '/' . implode('|', array_map('preg_quote', $separator)) . '/';
        } else {
            $pattern = '/' . preg_quote($separator, '/') . '/';
        }
        return preg_split($pattern, $this->Value, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**是否为null或者空*/
    public function isEmpty(): bool
    {
        return $this->getLength() == 0;
    }

    /**寻找字符串, 找不到返回-1*/
    public function indexOf($str, $ignoreCase = false): int
    {
        $index = 0;
        if ($ignoreCase) {
            $index = mb_stripos($this->Value, $str);
        } else {
            $index = mb_strpos($this->Value, $str);
        }
        return $index === false ? -1 : $index;
    }

    /**是否包含另外一个字符串*/
    public function contains($str, $ignoreCase = false): bool
    {
        // 判断str是否Str类型
        if (gettype($str) == 'object' && get_class($str) == 'Str') {
            $str = $str->Value;
        }
        return $this->indexOf($str, $ignoreCase) >= 0;
    }

    /**截取字符串*/
    public function substring(int $startIndex, int $length = null): Str
    {
        if ($length == null) {
            $length = $this->getLength() - $startIndex;
        }
        return new Str(mb_substr($this->Value, $startIndex, $length));
    }

    /**转成大写*/
    public function toUpper(): Str
    {
        return new Str(mb_strtoupper($this->Value));
    }

    /**转成小写*/
    public function toLower(): Str
    {
        return new Str(mb_strtolower($this->Value));
    }

    public function __toString()
    {
        return $this->Value;
    }
}