<?php
class App
{
    private static $option = [
        'debug' => false,
        'errLevel' => E_ERROR,
        'index' => '/index.php',
        'static' => [],
        'self' => 'app.php',
    ];

    public static function Run($opt = [])
    {
        if (is_array($opt)) {
            self::$option = array_merge(self::$option, $opt);
        }
        error_reporting(self::$option['errLevel']); // 设置错误级别

        // 默认首页
        $url = $_SERVER['SCRIPT_NAME'];
        if ($url == '/') {
            $url = self::$option['index'];
        }
        // 取扩展名
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        if ($ext == '') {
            $ext = 'php';
            $url .= '.php';
        } elseif ($ext == 'php' && !self::$option['debug']) {
            Uri::notfound();
            die;
        }

        // 如果是php文件
        if ($ext == 'php') {
            $file = Uri::combineFromServerRoot($url);
            if (file_exists($file)) {
                self::runFile($file);
                die;
            } else {
                Uri::notfound();
            }
        } else {
            // 静态文件
            foreach (self::$option['static'] as $staticDir) {
                $file = Uri::combineFromServerRoot($staticDir, $url);
                if (file_exists($file)) {
                    $mime = Mime::get($ext);
                    header("Content-type: $mime");
                    readfile($file);
                    die;
                }
            }
        }
        Uri::notfound();
    }

    private static function runFile($file)
    {
        // 获取$file文件名
        $name = basename($file);
        if (strcasecmp($name, self::$option['self']) == 0) {
            Uri::notfound();
            return;
        }
        header('Content-Type: text/html; charset=utf-8'); // 设置编码
        try {
            include $file;
        } catch (Exception $exception) {
            if (self::$option['debug'] === true) {
                throw $exception;
            } else {
                $log = new Log("error");
                $logMessage = '异常消息: ' . $exception->getMessage() . ' 在文件 ' . $exception->getFile() . ' 第 ' . $exception->getLine() . ' 行';
                $log->error($logMessage);
            }
            Uri::error();
        }
    }

    private static function runApi($controller, $action)
    {
        // 不存在这个类
        if (!class_exists($controller) || !method_exists($controller, $action))
            return;

        $class = new $controller();
        // 创建反射方法
        $reflectionMethod = new ReflectionMethod($controller, $action);
        $params = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $name = $parameter->getName();
            $params[] = $_REQUEST[$name] ?? null;
        }
        // 调用方法
        $result = $reflectionMethod->invoke($class, ...$params);
        echo $result;
    }
}

