<?php

class Str
{
    public $Source;

    public function __construct(string $str)
    {
        if (!extension_loaded('mbstring')) {
            die('mbstring extension not loaded');
        }

        // 如果不是字符串就报错
        if (gettype($str) != 'string') {
            $str = (string)$str;
        }

        $this->Source = $str;
    }

    /**字符串长度*/
    public function getLength(): int
    {
        return (int)mb_strlen($this->Source);
    }

    /**转成数字类型*/
    public function toFloat(): float
    {
        return (float)$this->Source;
    }

    /**转成数字类型*/
    public function toInt(): int
    {
        return (int)$this->Source;
    }

    /**隐藏中间几位*/
    public function hide(int $startIndex, int $count, $char = '*')
    {
        if ($startIndex < 0) {
            $startIndex = 0;
        }
        if ($startIndex >= $this->getLength()) {
            return $this->Source;
        }
        if ($count <= 0) {
            return $this->Source;
        }
        if ($startIndex + $count > $this->getLength()) {
            $count = $this->getLength() - $startIndex;
        }

        $str = $this->Source;
        for ($i = 0; $i < $count; $i++) {
            $str[$startIndex + $i] = $char;
        }
        return new Str($str);
    }


    /**是否匹配一个字符串*/
    public function isMatch(string $pattern): bool
    {
        return preg_match($pattern, $this->Source) === 1;
    }

    /**正则匹配第一个字符串*/
    public function match(string $pattern): Str
    {
        preg_match($pattern, $this->Source, $match);
        $length = count($match);
        $str = $match[$length - 1] ?? '';
        return new Str($str);
    }

    /**正则匹配所有字符串*/
    public function matches(string $pattern): array
    {
        preg_match_all("$pattern", $this->Source, $matches);
        return $matches[0];
    }

    /**正则替换字符串*/
    public function replace(Str $pattern, Str $replacement): Str
    {
        return new Str(preg_replace($pattern, $replacement, $this->Source));
    }

    /** 支持分割字符串数组, 同时排除空
     * @param array ...$separator 支持数组或者字符串
     * @return array
     */
    public function split(array ...$separator): array
    {
        $result = [];
        $str = $this->Source;
        foreach ($separator as $sep) {
            if (gettype($sep) == 'string') {
                $str = str_replace($sep, $separator[0], $str);
            } else {
                foreach ($sep as $s) {
                    $str = str_replace($s, $separator[0], $str);
                }
            }
        }
        $result = explode((string)$separator[0], $str);
        $result = array_filter($result, function ($item) {
            return $item != '';
        });
        return $result;
    }

    /**是否为null或者空*/
    public function isEmpty(): bool
    {
        return $this->getLength() == 0;
    }

    /**获取文本中间*/
    public function between(string $start, string $end, bool $ignoreCase = false): Str
    {
        $startIndex = $this->indexOf($start);
        if ($startIndex < 0) {
            return new Str('');
        }
        $startIndex += mb_strlen($start);
        $endIndex = $this->indexOf($end, $ignoreCase);
        if ($endIndex < 0) {
            return new Str('');
        }
        return $this->subString($startIndex, $endIndex - $startIndex);
    }

    /**寻找字符串, 找不到返回-1*/
    public function indexOf($str, $ignoreCase = false): int
    {
        $index = 0;
        if ($ignoreCase) {
            $index = mb_stripos($this->Source, $str);
        } else {
            $index = mb_strpos($this->Source, $str);
        }
        return $index === false ? -1 : $index;
    }

    /**是否包含另外一个字符串*/
    public function contains($str, $ignoreCase = false): bool
    {
        // 判断str是否Str类型
        if (gettype($str) == 'object' && get_class($str) == 'Str') {
            $str = $str->Source;
        }
        return $this->indexOf($str, $ignoreCase) >= 0;
    }

    /**截取字符串*/
    public function subString(int $startIndex, int $length = null): Str
    {
        if ($length == null) {
            $length = $this->getLength() - $startIndex;
        }
        return new Str(mb_substr($this->Source, $startIndex, $length));
    }

    /**转成大写*/
    public function toUpper(): Str
    {
        return new Str(mb_strtoupper($this->Source));
    }

    /**转成小写*/
    public function toLower(): Str
    {
        return new Str(mb_strtolower($this->Source));
    }


    public function __toString()
    {
        return $this->Source;
    }
}