<?php
// 允许短标签
ini_set('short_open_tag', 'On');

// 屏蔽警告
// error_reporting(E_ERROR);


// $req = new Req();
// $res = new Res();
// $session = new Session();
// $auth = new Auth();
// $cache = new FileCache();
// $log = new Log();
// $url = Path::getUrl();
// $ip = $req->ip();
// $log->debug("访问页面: $url, Ip: $ip");


// $db = new Orm("sqlsrv:server=your_server_name;database=your_database_name;TrustServerCertificate=1", "your_username", "your_password");
// $db = new Orm("mysql:host=your_server_name;dbname=your_database_name", "your_username", "your_password");
// $db = new Orm("sqlite:your_database_name");

/**字符串扩展类*/
class Str
{
    public $Source;

    public function __construct(string $str)
    {
        if (!extension_loaded('mbstring')) {
            throw new Exception('mbstring extension not loaded');
        }

        // 如果不是字符串就报错
        if (gettype($str) != 'string') {
            throw new Exception('str is not string');
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
        // 正则提取数字 [-+]?[0-9]+(\.[0-9]+)?
        $num = $this->match('/[-+]?[0-9]+(\.[0-9]+)?/');
        return (float)$num;
    }

    /**转成数字类型*/
    public function toInt(): int
    {
        // 正则提取数字 [-+]?[0-9]+
        $num = $this->match('/[-+]?[0-9]+/');
        return (int)$num;
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

    /**替换字符串*/
    public function replace($oldValues, string $replacement): Str
    {
        // 如果是数组就分别替换
        if (gettype($oldValues) == 'array') {
            $str = $this->Source;
            foreach ($oldValues as $oldValue) {
                $str = str_replace($oldValue, $replacement, $str);
            }
            return new Str($str);
        }
        // 如果是字符串就直接替换
        return new Str(str_replace($oldValues, $replacement, $this->Source));

    }

    /**
     * 支持分割字符串数组，同时排除空
     * @param string ...$separator 支持数组或者字符串
     * @return array
     */
    public function split(string ...$separator): array
    {
        $result = [];
        $str = $this->Source;

        // 如果没有提供分隔符，直接返回包含原始字符串的数组
        if (empty($separator)) {
            return [$str];
        }

        // 将所有分隔符替换为第一个分隔符
        foreach ($separator as $sep) {
            $str = str_replace($sep, $separator[0], $str);
        }

        // 分割字符串并排除空字符串
        $result = explode($separator[0], $str);
        $result = array_filter($result, function ($item) {
            return $item !== '';
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

    /**向字符串左边追加*/
    public function padLeft($total, $char = ' '): Str
    {
        return new Str(str_pad($this->Source, $total, $char, STR_PAD_LEFT));
    }

    /**向字符串右边追加*/
    public function padRight($total, $char = ' '): Str
    {
        return new Str(str_pad($this->Source, $total, $char, STR_PAD_RIGHT));
    }

    public static function join(array $arr, string $separator): Str
    {
        return new Str(implode($separator, $arr));
    }

    public function __toString()
    {
        return $this->Source;
    }
}

/**请求扩展*/
class Req
{
    public function __construct()
    {
        // 判断是否开启session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**获取get请求中的参数*/
    public function get(string $key, $default = ''): string
    {
        return $_GET[$key] ?? $default;
    }

    /**获取post请求中的参数*/
    public function post(string $key, $default = ''): string
    {
        return $_POST[$key] ?? $default;
    }

    /**获取请求头中的参数*/
    public function header(string $key, $default = ''): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? $default;
    }

    /**获取cookie中的参数*/
    public function cookie(string $key, $default = ''): string
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**获取request中的参数*/
    public function string(string $key, $default = ''): string
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**获取request中的参数, 并转成整型*/
    public function int(string $key, $default = 0): int
    {
        return intval(self::string($key, $default));
    }

    /**获取request中的参数, 并转成浮点型*/
    public function float(string $key, $default = 0.0): float
    {
        return floatval(self::string($key, $default));
    }

    /**获取文件上传*/
    public function file(string $key, string $savePath): bool
    {
        return move_uploaded_file($_FILES[$key]['tmp_name'], $savePath);
    }

    /**获取本次请求流*/
    public function input()
    {
        return file_get_contents('php://input');
    }

    /**获取请求的Ip地址*/
    public function ip(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        return $ip;
    }
}

/**响应扩展*/
class Res
{
    /**设置cookie*/
    public function setCookie(string $key, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }


    /**设置header*/
    public function setHeader(string $key, string $value)
    {
        header("$key: $value");
    }

    /**设置状态码*/
    public function setStatus(int $code)
    {
        http_response_code($code);
    }

    /**跳转url*/
    public function redirect(string $url)
    {
        header("Location: $url");
        die;
    }

    /**返回上一个页面*/
    public function return($count = 1)
    {
        // 返回上一个页面
        die("<script>history.go(-$count);</script>");
    }

    /**返回404错误*/
    public function notfound()
    {
        // 设置404状态码
        http_response_code(404);
        die;
    }

    /**返回500错误*/
    public function error()
    {
        http_response_code(500);
        die;
    }
}

/**session扩展*/
class Session
{
    /**设置session过期时间, 默认30分钟*/
    public function __construct($expire = 1800)
    {
        // 判断是否开启session
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params($expire);
            session_name('SESSION_ID');
            session_start();
        }
    }

    /**设置session*/
    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**获取session*/
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**删除session*/
    public function delete(string $key)
    {
        unset($_SESSION[$key]);
    }

    /**清空session*/
    public function clear()
    {
        session_destroy();
    }

    /**是否存在session*/
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**获取session id*/
    public function getSessionId(): string
    {
        return session_id();
    }
}

/**模仿C#的Linq对数组扩展*/
class Linq implements ArrayAccess
{
    public $Source;

    public function __construct(array $source)
    {
        // 判断是否是数组
        if (gettype($source) != 'array') {
            throw new Exception('source is not array');
        }
        // 遍历数组
//        foreach ($source as $item){
//            // 添加到Source
//            $this->Source[] = $item;
//        }

        $this->Source = $source;
    }

    /**过滤数组*/
    public function where(callable $func): Linq
    {
        $result = [];
        foreach ($this->Source as $item) {
            if ($func($item)) {
                $result[] = $item;
            }
        }
        return new Linq($result);
    }

    /**过滤数组*/
    public function ifWhere(bool $if, callable $func): Linq
    {
        $result = [];
        if ($if) {
            foreach ($this->Source as $item) {
                if ($func($item)) {
                    $result[] = $item;
                }
            }
        } else {
            $result = $this->Source;
        }
        return new Linq($result);
    }

    /**映射数组*/
    public function select(callable $func): Linq
    {
        $result = [];
        foreach ($this->Source as $item) {
            $result[] = $func($item);
        }
        return new Linq($result);
    }

    /**获取第一个元素*/
    public function first()
    {
        return $this->Source[0] ?? null;
    }

    /**获取最后一个元素*/
    public function last()
    {
        return $this->Source[count($this->Source) - 1] ?? null;
    }

    /**跳过数组中的前N个元素*/
    public function skip(int $count): Linq
    {
        return new Linq(array_slice($this->Source, $count));
    }

    /**从数组中获取前N个元素*/
    public function take(int $count): Linq
    {
        return new Linq(array_slice($this->Source, 0, $count));
    }

    /**判断是否包含某个字符串, 同时忽略大小写*/
    public function has($obj): bool
    {
        foreach ($this->Source as $item) {
            if (($item) == ($obj)) {
                return true;
            }
        }
        return false;
    }


    /**是否包含*/
    public function any(callable $func): bool
    {
        foreach ($this->Source as $item) {
            if ($func($item)) {
                return true;
            }
        }
        return false;
    }

    /**全部包含*/
    public function all(callable $func): bool
    {
        foreach ($this->Source as $item) {
            if (!$func($item)) {
                return false;
            }
        }
        return true;
    }


    /**获取数组长度*/
    public function count(): int
    {
        return count($this->Source);
    }

    /**转成数组*/
    public function toArray(): array
    {
        return $this->Source;
    }

    /**重新整理数组索引*/
    public function format(): Linq
    {
        $this->Source = array_values($this->Source);
        return $this;
    }

    /**求和*/
    public function sum(callable $func = null): float
    {
        $result = 0.0;
        foreach ($this->Source as $item) {
            if ($func == null)
                $result += floatval($item);
            else
                $result += $func($item);
        }

        return $result;
    }

    /**求平均值*/
    public
    function avg(callable $func = null): float
    {
        $result = 0.0;
        $count = 0;
        foreach ($this->Source as $item) {
            if ($func == null)
                $result += floatval($item);
            else
                $result += $func($item);

            $count++;
        }
        return $result / $count;
    }

    /**求最大值*/
    public
    function max(callable $func = null): float
    {
        $result = PHP_INT_MIN;
        foreach ($this->Source as $item) {
            if ($func == null)
                $result = max($result, floatval($item));
            else
                $result = max($result, $func($item));
        }
        return $result;
    }

    /**求最小值*/
    public
    function min(callable $func = null): float
    {
        $result = PHP_INT_MAX;
        foreach ($this->Source as $item) {
            if ($func == null)
                $result = min($result, floatval($item));
            else
                $result = min($result, $func($item));
        }
        return $result;
    }

    /**从小到大排序*/
    public
    function orderByAes(callable $func): Linq
    {
        $result = $this->Source;
        usort($result, function ($a, $b) use ($func) {
            return $func($a) > $func($b);
        });
        return new Linq($result);

    }

    /**从大到小排序*/
    public
    function orderByDesc(callable $func): Linq
    {
        $result = $this->Source;
        usort($result, function ($a, $b) use ($func) {
            return $func($a) < $func($b);
        });
        return new Linq($result);
    }

    /**分组*/
    public
    function groupBy(callable $func): array
    {
        $result = [];
        foreach ($this->Source as $item) {
            $key = $func($item);
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result[$key][] = $item;
        }
        return $result;
    }

    /**连接*/
    public
    function join(Linq $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector): Linq
    {
        $result = [];
        foreach ($this->Source as $outer) {
            foreach ($inner->Source as $innerItem) {
                if ($outerKeySelector($outer) == $innerKeySelector($innerItem)) {
                    $result[] = $resultSelector($outer, $innerItem);
                }
            }
        }
        return new Linq($result);
    }

    /**去重*/
    public
    function distinct(): Linq
    {
        return new Linq(array_unique($this->Source));
    }

    /**合并
     * @throws Exception 参数必须是数组或者是Linq类型
     */
    public
    function concat($list): Linq
    {
        if ($list instanceof Linq)
            return new Linq(array_merge($this->Source, $list->Source));
        // 如果是数组
        else if (is_array($list))
            return new Linq(array_merge($this->Source, $list));
        throw new Exception("参数必须是Linq或者数组");
    }

    /**分页*/
    public
    function page(int $pageIndex, int $pageSize): Linq
    {
        return new Linq(array_slice($this->Source, ($pageIndex - 1) * $pageSize, $pageSize));
    }

    /**聚合*/
    public
    function aggregate(callable $func)
    {
        $result = $this->Source[0];
        for ($i = 1; $i < count($this->Source); $i++) {
            $result = $func($result, $this->Source[$i]);
        }
        return $result;
    }

    /**去掉空元素*/
    public
    function whereNotNull(): Linq
    {
        return new Linq(array_filter($this->Source, function ($item) {
            return $item != null;
        }));
    }

    /**乱序*/
    public
    function shuffle(): Linq
    {
        $result = $this->Source;
        shuffle($result);
        return new Linq($result);
    }

    /**克隆本数组*/
    public
    function clone(): Linq
    {
        return new Linq($this->Source);
    }

    /**遍历*/
    public function each(callable $func)
    {
        foreach ($this->Source as $item) {
            $func($item);
        }
    }

    /**遍历, 第二个参数为下标*/
    public function eachWithIndex(callable $func)
    {
        foreach ($this->Source as $index => $item) {
            $func($item, $index);
        }
    }

    public function push($item): Linq
    {
        $this->Source[] = $item;
        return $this;
    }

    /**支持魔术方法*/
    public function __get($name)
    {
        if (isset($this->Source[$name])) {
            return $this->Source[$name];
        } else {
            return null;
        }
    }

    /**支持魔术方法*/
    public function __set($name, $value)
    {
        $this->Source[$name] = $value;
    }

    public function __toString(): string
    {
        // 模拟数组返回
        return json_encode($this->Source, JSON_UNESCAPED_UNICODE);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->Source[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->Source[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->Source[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->Source[$offset]);
    }
}

/**
 * 简单的数据库封装
 * new Orm("sqlsrv:server=your_server_name;database=your_database_name", "your_username", "your_password");
 * new Orm("mysql:host=your_server_name;dbname=your_database_name", "your_username", "your_password");
 * new Orm("sqlite:your_database_name");
 *
 */
class Orm
{

    public $pdo;
    protected $dbType;

    public function __construct(string $dbString, $user = '', $pwd = '', $options = array())
    {

        // 通过连接字符串判断数据库类型
        $this->dbType = explode(':', $dbString)[0];


        if ($this->dbType == 'sqlite') {
            if (!extension_loaded('pdo_sqlite'))
                throw new Exception('pdo_sqlite extension is not loaded');
            $this->pdo = new PDO($dbString, $user, $pwd, $options);
            $this->pdo->exec('PRAGMA journal_mode = WAL');
            $this->pdo->exec('PRAGMA cache_size = 20000');

        } else if ($this->dbType == 'sqlsrv') {
            if (!extension_loaded('pdo_sqlsrv'))
                throw new Exception('pdo_sqlsrv extension is not loaded');
            $this->pdo = new PDO($dbString, $user, $pwd, $options);
            $this->pdo->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, true);

        } else if ($this->dbType == 'mysql') {
            if (!extension_loaded('pdo_mysql'))
                throw new Exception('pdo_mysql extension is not loaded');
            $this->pdo = new PDO($dbString, $user, $pwd, $options);
            $this->pdo->exec('SET NAMES utf8');
        }

        // 设置错误处理方式为异常
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }


    public function query(string $sql, $params = array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute($sql, $params = array(), $correctRow = -1): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->rowCount();
        if ($correctRow != -1 && $row != $correctRow) {
            throw new Exception("execute error, affected rows: $row, correct rows: $correctRow");
        }
        return $row;
    }

    public function transaction(callable $func)
    {
        $this->pdo->beginTransaction();
        try {
            $func();
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function insert($table, $data)
    {
        $fields = [];
        $values = [];
        $pdo_parameters = [];
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = ':' . $field;
            $pdo_parameters[$field] = $value;
        }
        $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
        return $this->execute($sql, $pdo_parameters, 1);
    }

    public function update($table, $data, $condition)
    {
        $fields = [];
        $pdo_parameters = [];
        foreach ($data as $field => $value) {
            $fields[] = $field . '=:' . $field;
            $pdo_parameters[$field] = $value;
        }
        $where = '';
        if (is_string($condition)) {
            $where = $condition;
        } else if (is_array($condition)) {
            $fields = [];
            foreach ($condition as $field => $value) {
                $fields[] = $field . '=:' . $field;
                $pdo_parameters[$field] = $value;
            }
            $where = implode(" AND ", $fields);
        }
        $sql = "UPDATE $table SET " . implode(',', $fields) . ' WHERE ' . $where;
        return $this->execute($sql, $pdo_parameters, 1);
    }

    public function delete($table, $condition)
    {
        $fields = [];
        $pdo_parameters = [];
        $where = '';
        if (is_string($condition)) {
            $where = $condition;
        } else if (is_array($condition)) {
            foreach ($condition as $field => $value) {
                $fields[] = $field . '=:' . $field;
                $pdo_parameters[$field] = $value;
            }
            $where = implode(" AND ", $fields);
        }
        $sql = "DELETE FROM $table WHERE " . $where;
        return $this->execute($sql, $pdo_parameters, 1);
    }

    public function insertOrUpdate($table, $data, $condition)
    {
        $count = $this->select($table, 'count(*)', $condition)[0][0];
        if ($count > 0) {
            return $this->update($table, $data, $condition);
        } else {
            return $this->insert($table, $data);
        }
    }

    public function select($table, $columns = '*', $condition = '', $order = 'Id', $page = 1, $pageSize = 10000, &$totalPage = null)
    {
        // 判断是否是sqlserver
        if ($this->dbType == 'sqlsrv') {
            $sql = "SELECT ";
            if (is_string($columns)) {
                $sql .= $columns;
            } else {
                $sql .= implode(',', $columns);
            }
            $sql .= " FROM $table";
            $fields = [];
            $pdo_parameters = [];
            $where = '';
            if (is_string($condition)) {
                $where = $condition;
            } else if (is_array($condition)) {
                foreach ($condition as $field => $value) {
                    $fields[] = $field . '=:' . $field;
                    $pdo_parameters[$field] = $value;
                }
                $where = implode(" AND ", $fields);
            }
            if (!empty($where)) {
                $sql .= ' WHERE ' . $where;
            }

            if (!empty($order)) {
                $sql .= ' ORDER BY ' . $order;
            }
            if ($page > 0 && $pageSize > 0) {
                $sql .= ' OFFSET ' . ($page - 1) * $pageSize . ' ROWS FETCH NEXT ' . $pageSize . ' ROWS ONLY';
            }
            $result = $this->query($sql, $pdo_parameters);
            if ($page > 0 && $pageSize > 0) {
                if (isset($totalPage)) {
                    $count = $this->select($table, 'count(*)', $condition)[0][0];
                    $totalPage = ceil($count * 1.0 / $pageSize);

                }
            }
            return $result;
        } else {

            $sql = "SELECT ";
            if (is_string($columns)) {
                $sql .= $columns;
            } else {
                $sql .= implode(',', $columns);
            }
            $sql .= " FROM $table";
            $fields = [];
            $pdo_parameters = [];
            $where = '';
            if (is_string($condition)) {
                $where = $condition;
            } else if (is_array($condition)) {
                foreach ($condition as $field => $value) {
                    $fields[] = $field . '=:' . $field;
                    $pdo_parameters[$field] = $value;
                }
                $where = implode(" AND ", $fields);
            }
            if (!empty($where)) {
                $sql .= ' WHERE ' . $where;
            }

            if (!empty($order)) {
                $sql .= ' ORDER BY ' . $order;
            }
            if ($page > 0 && $pageSize > 0) {
                $sql .= ' LIMIT ' . ($page - 1) * $pageSize . ',' . $pageSize;
            }
            $result = $this->query($sql, $pdo_parameters);
            if ($page > 0 && $pageSize > 0) {
                if (isset($totalPage)) {
                    $count = $this->select($table, 'count(*)', $condition)[0][0];
                    $totalPage = ceil($count * 1.0 / $pageSize);

                }
            }
            return $result;
        }
    }


}

/**openai 对接, 默认使用gpt-3.5-turbo模型, 还可以选择gpt-4-turbo*/
class Ai
{
    public $model = '';
    public $apiKey;
    public $maxChatLength = 10;
    public $systemMessage = '请用简单的语言回答,不要解释,更不要客套话,仅仅准确的回答问题';

    public function __construct($key, $model = 'gpt-3.5-turbo')
    {
        $this->apiKey = $key;
        $this->model = $model;
    }


    public function talk($message)
    {
        $sendMessage = ['role' => 'user', 'content' => $message];

        // 构造发送的消息
        $messages = [
            ['role' => 'system', 'content' => $this->systemMessage]
        ];

        $history = [];
        // 判断历史是否超过最大长度, 因为是我一条, 系统一条, 所以是2倍, 然后只取最后的部分
        if (count($this->history) > $this->maxChatLength * 2) {
            $history = array_slice($this->history, count($this->history) - $this->maxChatLength * 2);
        } else {
            $history = $this->history;
        }

        if (!empty($this->history)) {
            $messages = array_merge($messages, $history);
        }

        $messages[] = $sendMessage;

        $data = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        // 发送请求
        $http = new Http();
        $response = $http->post($this->apiUrl, $data, true, ['Authorization' => 'Bearer ' . $this->apiKey]);
        $result = json_decode($response['body'], true);

        // 获取对话内容
        $resMessage = $result['choices'][0]['message'];


        // 添加到对话历史
        $this->history[] = $sendMessage;
        $this->history[] = $resMessage;

        // 返回对话内容
        return $resMessage['content'];


    }

    private $history = []; // 对话历史
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';


}

/**断点辅助, 每次调用都是会自增*/
class Bp
{
    /**如果相等为true*/
    public static function if($count): bool
    {
        self::$count++;
        if ($count == self::$count) {
            return true;
        }
        return false;
    }

    /**小于为true*/
    public static function lt($count): bool
    {
        self::$count++;
        if (self::$count < $count) {
            return true;
        }
        return false;
    }

    /**大于为true*/
    public static function gt($count): bool
    {
        self::$count++;
        if (self::$count > $count) {
            return true;
        }
        return false;
    }


    /**重置*/
    public static function reset()
    {
        self::$count = 0;
    }

    private static $count = 0;

}

/**路径封装*/
class Path
{
    /**连接目录, 结尾不包含/*/
    public static function combine(string ...$paths): string
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
    public static function combineFromServerRoot(string ...$paths): string
    {
        $root = self::getServerRoot();
        return self::combine($root, ...$paths);
    }

    /**获取当前网址的完整根域名, 如果不是默认端口, 则包含端口, 结尾不包含/*/
    public static function getFullHost(): string
    {
        $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host";
    }

    /**获取项目根目录, 结尾不包含/*/
    public static function getServerRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**获取除了根域名后面的路径, 以/开头*/
    public static function getUrl(): string
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**获取当前执行的脚本名, 仅仅是脚本名*/
    public static function getScript(): string
    {
        return basename($_SERVER['SCRIPT_NAME']);
    }

    /**遍历这个目录下面的所有子目录和文件, 默认遍历当前目录*/
    public static function getFiles(string $dir = null): array
    {
        if ($dir == null) {
            $dir = self::getServerRoot();
        }
        return self::glob_recursive($dir . '/*');
    }

    private static function glob_recursive($pattern, $flags = 0)
    {
        // 搜索匹配当前模式的文件
        $files = glob($pattern, $flags);
        // 也搜索匹配当前模式的目录中的文件
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::glob_recursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}

/**curl封装, 同时支持cookie管理*/
class Http
{
    private $timeout;
    private $retryCount;
    private $cookieJar;

    public function __construct($timeout = 30, $retryCount = 3)
    {
        // 判断是否存在curl库
        if (!extension_loaded('curl')) {
            throw new Exception('curl extension not loaded');
        }

        $this->timeout = $timeout;
        $this->retryCount = $retryCount;
        $this->cookieJar = [];
    }

    public function get($url, $params = [], $customHeaders = []): array
    {
        $queryString = http_build_query($params);
        $url = "{$url}?{$queryString}";
        return $this->request('GET', $url, [], false, $customHeaders);
    }

    public function post($url, $params = [], $asJson = false, $customHeaders = []): array
    {
        return $this->request('POST', $url, $params, $asJson, $customHeaders);
    }

    private function request($method, $url, $params, $asJson, $customHeaders = []): array
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => true  // 添加这一行
        ];
        $headers = [];

        $cookieString = http_build_query($this->cookieJar, '', '; ');
        if (!empty($cookieString)) {
            $options[CURLOPT_COOKIE] = $cookieString;
        }


        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $asJson ? json_encode($params, JSON_UNESCAPED_UNICODE) : http_build_query($params);
            $headers = $asJson ? ['Content-Type' => 'application/json'] : ['Content-Type' => 'application/x-www-form-urlencoded'];
            $headers = array_merge($headers, $customHeaders);

        }


        if (!empty($headers)) {
            // $options[CURLOPT_HTTPHEADER] = $headers;
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($v, $k) {
                return $k . ': ' . $v;
            }, array_values($headers), array_keys($headers)));
        }

        curl_setopt_array($ch, $options);


        $retry = $this->retryCount;
        $response = null;
        while ($retry--) {
            $response = curl_exec($ch);
            if (curl_errno($ch) == 0) {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headerString = substr($response, 0, $header_size);
                $body = substr($response, $header_size);

                $headers = $this->parseHeaders($headerString);

                $this->updateCookies(curl_getinfo($ch, CURLINFO_COOKIELIST));
                curl_close($ch);

                return ['headers' => $headers, 'body' => $body];
            }
        }

        curl_close($ch);
        return ['headers' => [], 'body' => ''];
    }

    private function parseHeaders($headerString): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerString);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }

    private function updateCookies($cookieList)
    {
        foreach ($cookieList as $cookieString) {
            list($domain, $flag, $path, $secure, $expiration, $name, $value) = explode("\t", $cookieString);
            $this->cookieJar[$name] = $value;
        }
    }
}

/**Mime类型*/
class Mime
{

    private static $types = [
        'html' => 'text/html;charset=UTF-8',
        'htm' => 'text/html;charset=UTF-8',
        'shtml' => 'text/html;charset=UTF-8',
        'css' => 'text/css;charset=UTF-8',
        'xml' => 'text/xml;charset=UTF-8',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/javascript;charset=UTF-8',
        'atom' => 'application/atom+xml',
        'rss' => 'application/rss+xml',
        'mml' => 'text/mathml',
        'txt' => 'text/plain',
        'jad' => 'text/vnd.sun.j2me.app-descriptor',
        'wml' => 'text/vnd.wap.wml',
        'htc' => 'text/x-component',
        'avif' => 'image/avif',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'wbmp' => 'image/vnd.wap.wbmp',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'jng' => 'image/x-jng',
        'bmp' => 'image/x-ms-bmp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'jar' => 'application/java-archive',
        'war' => 'application/java-archive',
        'ear' => 'application/java-archive',
        'json' => 'application/json',
        'hqx' => 'application/mac-binhex40',
        'doc' => 'application/msword',
        'pdf' => 'application/pdf',
        'ps' => 'application/postscript',
        'eps' => 'application/postscript',
        'ai' => 'application/postscript',
        'rtf' => 'application/rtf',
        'm3u8' => 'application/vnd.apple.mpegurl',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        'xls' => 'application/vnd.ms-excel',
        'eot' => 'application/vnd.ms-fontobject',
        'ppt' => 'application/vnd.ms-powerpoint',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wasm' => 'application/wasm',
        '7z' => 'application/x-7z-compressed',
        'cco' => 'application/x-cocoa',
        'jardiff' => 'application/x-java-archive-diff',
        'jnlp' => 'application/x-java-jnlp-file',
        'run' => 'application/x-makeself',
        'pl' => 'application/x-perl',
        'pm' => 'application/x-perl',
        'prc' => 'application/x-pilot',
        'pdb' => 'application/x-pilot',
        'rar' => 'application/x-rar-compressed',
        'rpm' => 'application/x-redhat-package-manager',
        'sea' => 'application/x-sea',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'tcl' => 'application/x-tcl',
        'tk' => 'application/x-tcl',
        'der' => 'application/x-x509-ca-cert',
        'pem' => 'application/x-x509-ca-cert',
        'crt' => 'application/x-x509-ca-cert',
        'xpi' => 'application/x-xpinstall',
        'xhtml' => 'application/xhtml+xml',
        'xspf' => 'application/xspf+xml',
        'zip' => 'application/zip',
        'bin' => 'application/octet-stream',
        'exe' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'deb' => 'application/octet-stream',
        'dmg' => 'application/octet-stream',
        'iso' => 'application/octet-stream',
        'img' => 'application/octet-stream',
        'msi' => 'application/octet-stream',
        'msp' => 'application/octet-stream',
        'msm' => 'application/octet-stream',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'kar' => 'audio/midi',
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/x-m4a',
        'ra' => 'audio/x-realaudio',
        '3gpp' => 'video/3gpp',
        '3gp' => 'video/3gpp',
        'ts' => 'video/mp2t',
        'mp4' => 'video/mp4',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'webm' => 'video/webm',
        'flv' => 'video/x-flv',
        'm4v' => 'video/x-m4v',
        'mng' => 'video/x-mng',
        'asx' => 'video/x-ms-asf',
        'asf' => 'video/x-ms-asf',
        'wmv' => 'video/x-ms-wmv',
        'avi' => 'video/x-msvideo',
    ];

    /**返回扩展名的mime类型, 前面不要加小数点*/
    public static function get(string $key)
    {
        $result = self::$types[$key];
        if (isset($result))
            return $result;
        else {
            return 'application/octet-stream';
        }
    }

    public static function set(string $key, string $value)
    {
        self::$types[$key] = $value;
    }
}

/**文件缓存*/
class FileCache
{
    protected $cachePath;// 缓存路径

    public function __construct()
    {
        $path = sys_get_temp_dir() . '/pingfan_kit_runtime_cache/' . md5($_SERVER['DOCUMENT_ROOT']);
        $this->cachePath = $path;
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    /**
     * 设置缓存
     * @param string $key
     * @param $data
     * @param int $expire 单位秒, 默认缓存时间1小时
     * @return void
     */
    public function set(string $key, $data, int $expire = 3600)
    {
        $cacheFile = $this->getCacheFile($key);
        $content = serialize([
            'expire' => time() + $expire,
            'data' => $data
        ]);
        file_put_contents($cacheFile, $content);
    }

    /**
     * 增加缓存时间
     * @param string $key
     * @param int $expire 单位秒
     * @return void
     */
    public function touch(string $key, int $expire = 1)
    {
        $data = $this->get($key);
        if ($data !== null) {
            $this->set($key, $data, $expire);
        }
    }

    /**
     * 获取缓存
     * @param string $key
     * @param null $default 默认值
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        $cacheFile = $this->getCacheFile($key);
        if (!file_exists($cacheFile)) {
            return $default;
        }
        $content = file_get_contents($cacheFile);
        $cacheData = unserialize($content);
        if (time() >= $cacheData['expire']) {
            $this->delete($key);
            return $default;
        }
        return $cacheData['data'];
    }

    /**判断缓存是否存在*/
    public function has(string $key): bool
    {
        $cacheFile = $this->getCacheFile($key);
        if (!file_exists($cacheFile)) {
            return false;
        }
        $content = file_get_contents($cacheFile);
        $cacheData = unserialize($content);
        if (time() > $cacheData['expire']) {
            $this->delete($key);
            return false;
        }
        return true;
    }

    /**获取缓存, 如果不存在则设置缓存*/
    public function getOrSet(string $key, callable $callback, int $expire = 3600)
    {
        $cacheFile = $this->getCacheFile($key);
        if (!file_exists($cacheFile)) {
            $data = call_user_func($callback);
            $this->set($key, $data, $expire);
            return $data;
        }
        $content = file_get_contents($cacheFile);
        $cacheData = unserialize($content);
        if (time() > $cacheData['expire']) {
            $data = call_user_func($callback);
            $this->set($key, $data, $expire);
            return $data;
        }
        return $cacheData['data'];
    }


    /**删除缓存*/
    public function delete(string $key)
    {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**清空缓存*/
    public function clear()
    {
        $files = glob($this->cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }


    /**获取缓存文件的路径*/
    protected function getCacheFile(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.tmp';
    }
}

/**单线程执行*/
class Lock
{
    private $filename;

    public function __construct($key = null)
    {
        if (!$key) {
            $key = Path::getServerRoot();
        }
        $this->filename = sys_get_temp_dir() . '/' . md5($key) . '.lock';
    }

    public function __destruct()
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }


    public function run(callable $callback, array $params = [])
    {
        // 打开文件，如果文件不存在则创建
        $fileHandle = fopen($this->filename, 'a+');
        if (!$fileHandle) {
            throw new Exception("无法打开文件: $this->filename");
        }

        // 尝试获取锁
        if (!flock($fileHandle, LOCK_EX)) {
            fclose($fileHandle);
            throw new Exception("无法获取文件锁: $this->filename");
        }

        // 执行回调函数
        try {
            $result = call_user_func_array($callback, $params);
        } catch (Exception $e) {
            // 释放锁并关闭文件句柄
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            throw $e;
        } finally {
            // 释放锁并关闭文件句柄
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
        }

        return $result;
    }
}

/**JSON扩展*/
class Json
{
    /** 将数据转成JSON字符串, 中文不进行转义 */
    public static function encode($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /** 将数据转成JSON字符串, 中文不进行转义, 同时带有缩进 */
    public static function encodePretty($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /** 将JSON字符串转成数组 */
    public static function decode(string $data): array
    {
        return json_decode($data, true);
    }
}

/**日志类*/
class Log
{
    private $fileName;

    public function __construct($fileName = 'app')
    {
        $this->fileName = $fileName;
    }

    private function log($message, $level)
    {
        // 检查$message的类型，如果是数组或对象则转换为JSON字符串
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        $timestamp = date('Y-m-d H:i:s');
        $localFileName = Path::combineFromServerRoot('log', date('Y-m-d') . ' ' . $this->fileName . '.log');
        $logMessage = "[$timestamp] [$level] $message\n";

        // 判断目录是否存在, 不存在则创建
        $dir = dirname($localFileName);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($localFileName, $logMessage, FILE_APPEND);

    }

    public function debug($message)
    {
        $this->log($message, 'DBG');
    }

    public function success($message)
    {
        $this->log($message, 'SUC');
    }

    public function info($message)
    {
        $this->log($message, 'INF');
    }

    public function warn($message)
    {
        $this->log($message, 'WAF');
    }

    public function error($message)
    {
        $this->log($message, 'ERR');
    }

    public function fatal($message)
    {
        $this->log($message, 'FAL');
    }

}

/**日期时间处理*/
class Dt
{
    public $timestamp;

    /**默认为当前时间*/
    public function __construct($time = 0)
    {
        // 如果是整数就是时间戳
        if (is_int($time)) {
            // 如果是0就是当前时间
            if ($time == 0) {
                $this->timestamp = time();
                return;
            }

            $this->timestamp = $time;
            return;
        }
        // 如果是字符串就是日期时间, 转成时间戳
        if (is_string($time)) {
            $this->timestamp = strtotime($time);
            return;
        }

        // 如果是Dt对象
        if ($time instanceof Dt) {
            $this->timestamp = $time->timestamp;
            return;
        }

        // 如果是其他类型, 抛出异常
        throw new Exception('invalid time type');

    }


    /**加秒*/
    public function addSecond(int $second): Dt
    {
        return new Dt($this->timestamp + $second);
    }

    /**加分钟*/
    public function addMinute(int $minute): Dt
    {
        return new Dt($this->timestamp + $minute * 60);
    }

    /**加小时*/
    public function addHour(int $hour): Dt
    {
        return new Dt($this->timestamp + $hour * 3600);
    }

    /**加天*/
    public function addDay(int $day): Dt
    {
        return new Dt($this->timestamp + $day * 24 * 3600);
    }

    /**加月*/
    public function addMonth(int $month): Dt
    {
        $year = date('Y', $this->timestamp);
        $month = date('m', $this->timestamp) + $month;
        $day = date('d', $this->timestamp);
        return new Dt(strtotime("$year-$month-$day"));
    }

    /**加年*/
    public function addYear(int $year): Dt
    {
        $year = date('Y', $this->timestamp) + $year;
        $month = date('m', $this->timestamp);
        $day = date('d', $this->timestamp);
        return new Dt(strtotime("$year-$month-$day"));
    }

    /**转成字符串*/
    public function toDateTimeString(): string
    {
        // 格式为 Y/m/d H:i:s
        return date('Y/m/d H:i:s', $this->timestamp);
    }

    /**转成字符串*/
    public function toDateString(): string
    {
        // 格式为 Y/m/d
        return date('Y/m/d', $this->timestamp);
    }

    /**转成字符串*/
    public function toTimeString(): string
    {
        // 格式为 H:i:s
        return date('H:i:s', $this->timestamp);
    }

    public function __toString(): string
    {
        return $this->toDateTimeString();
    }

    /**当前时间*/
    public static function now(): Dt
    {
        return new Dt(time());
    }

    /**当前时间戳*/
    public static function timeStamp(): int
    {
        return time();
    }

    /**当前世界时间戳*/
    public static function nowUtc(): int
    {
        return strtotime(gmdate('Y-m-d H:i:s'));
    }

}

/**不区分大写*/
class DirFile
{

    public function __construct($dir = null)
    {
        if ($dir == null) {
            $dir = Path::getServerRoot();
        }
        $this->dir = $dir;
        $this->cache = new FileCache();

        // 遍历所有文件
        $this->files = $this->scan();

    }

    /**获取不区分大小写的文件名*/
    public function getFileName($fileName)
    {
        $fileName = strtolower($fileName);
        foreach ($this->files as $file) {
            if (strtolower($file) == $fileName) {
                return $file;
            }
        }
        return $fileName;
    }


    private function scan()
    {
        return $this->cache->getOrSet($this->dir, function () {
            $list = $this->glob_recursive($this->dir . '/*');
            // 遍历list
            $result = [];
            foreach ($list as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                // 如果是文件夹, 则跳过
                if (is_dir($item)) {
                    continue;
                }
                $fileName = $item;
                $result[] = $fileName;
            }
            return $result;
        }, 30);
    }

    private function glob_recursive($pattern, $flags = 0)
    {
        // 搜索匹配当前模式的文件
        $files = glob($pattern, $flags);

        // 也搜索匹配当前模式的目录中的文件
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->glob_recursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }


    private $files;
    private $dir;
    private $cache;
}

/**Ini配置类*/
class IniConfig
{
    private $config = [];
    private $file = 'app.ini';

    public function __construct()
    {
        $this->config = parse_ini_file($this->file, true);
    }

    // 析构时保存配置
    public function __destruct()
    {
        $this->save();
    }

    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
            // 如果是字符串且有换行符, 则转义
            if (is_string($value) && strpos($value, "\\n") !== false) {
                $value = str_replace("\\n", "\n", $value);
            }
        }
        return $value;
    }

    public function set($key, $value)
    {
        // 如果有换行符, 则转义
        if (is_string($value) && strpos($value, "\n") !== false) {
            $value = str_replace("\n", "\\n", $value);
        }

        $keys = explode('.', $key);
        $config = &$this->config;
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        $config = $value;
    }

    public function save()
    {
        $content = $this->arrayToIni($this->config);
        file_put_contents($this->file, $content);
    }

    private function arrayToIni($array)
    {
        $content = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $content .= "[$key]\n";
                $content .= $this->arrayToIni($value);
            } else {
                $content .= "$key = $value\n";
            }
        }
        return $content;
    }
}


/**一般用于限制访问次数*/
class Limit
{
    /*判断在当前页面,一定时间内是否访问超过指定次数, 时间最大不能超过60秒*/
    public static function On(int $count = 10, int $time = 3): bool
    {
        $key = 'limit_on';

        // 添加进session
        $_SESSION[$key][] = time();

        // 遍历数组, 删除session超过60秒的值
        foreach ($_SESSION[$key] as $index => $item) {
            if (time() - $item > 60) {
                unset($_SESSION[$key][$index]);
            }
        }

        // 判断指定时间内是否超过限制
        $visitCount = 0;
        foreach ($_SESSION[$key] as $item) {
            if (time() - $item < $time) {
                $visitCount++;
                if ($visitCount > $count) {
                    return true;
                }
            }
        }
        return false;
    }
}

/**一般用于登录权限验证*/
class Auth
{


    public function __construct($authPage = "/auth.php")
    {
        // 判断全局是否有$session变量
        global $session;
        if (!isset($session)) {
            throw new Exception("全局变量session未定义");
        }
        $this->isLogin($authPage);
    }

    /**登录成功调用*/
    public function login($auth)
    {
        global $session;
        $session->set('auth', $auth);
    }

    /**获取登录验证的信息*/
    public function auth()
    {
        global $session;
        return $session->get('auth');
    }

    /**清空登录信息*/
    public function clear()
    {
        global $session;
        $session->delete('auth');
    }

    private function isLogin($authPage = '/auth.php')
    {
        // 获取当前脚本名称
        $currentPage = Path::getUrl();
        // 如果是登录页面就不做处理
        if ($currentPage == $authPage) {
            return;
        }

        global $session;
        $auth = $session->get('auth');
        if ($auth == null) {
            header("Location: $authPage?b=$currentPage");
            die;
        }
    }


}

///**入口类*/
//class App
//{
//    private $option = [
//        'debug' => false,
//        'errLevel' => E_ERROR,
//        'index' => '/index.php',
//        'static' => [],
//        'self' => 'app.php',
//        // 禁止访问的文件
//        'deny' => [
//            'app.php',
//            'pingfan.kit.php',
//            '.ini',
//            '.db',
//            '.log',
//            '.sql',
//            '.bak',
//            'inc',
//            'config',
//            'runtime',
//            'vendor',
//            'htaccess',
//            '.git',
//            '.svn',
//            '.env',
//            'composer',
//            '.project',
//            'LICENSE',
//            'README',
//
//
//        ]
//    ];
//
//    public function __construct($opt = [])
//    {
//        // 允许短标签
//        ini_set('short_open_tag', 'On');
//
//        if (is_array($opt) && count($opt) > 0) {
//            $this->option = array_merge($this->option, $opt);
//        }
//        error_reporting($this->option['errLevel']); // 设置错误级别
//
//        // 默认首页
//        $request_url = $_SERVER['REQUEST_URI'];
//        if ($request_url == '/') {
//            $request_url = $this->option['index'];
//        }
//
//
//        // 判断是否是禁止访问的文件
//        foreach (