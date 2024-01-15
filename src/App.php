<?php

class App
{
    public static function Run($opt = [])
    {
        $option = [
            'defaultController' => 'home',
            'defaultAction' => 'index',
            'debug' => false,
        ];
        if (is_array($opt)) {
            $option = array_merge($option, $opt);
        }

        error_reporting(E_ERROR); // 设置错误级别


        // 提取路径中的控制器和方法
        $url = new Str($_SERVER['REQUEST_URI']);

        $path = $url->match('/^\/[\w\/]+(?=\?|$)/');
        if (strlen($path) == 0) {
            $path = $option['defaultController'] . '/' . $option['defaultAction'];
        }
        // 分割控制器和方法
        $arr = $path->split('/');
        $controller = $arr[0];
        $action = $arr[1];

        $root = dirname($_SERVER['SCRIPT_FILENAME']);

        // 当前脚本的物理目录
        $file = Uri::combine($root, $path . '.php');
        $fileApi = Uri::combine($root, $controller . '.php');

        try {
            // 以文件形式执行
            if (file_exists($file)) {
                self::runFile($file);
            } elseif (file_exists($fileApi)) {
                self::runFile($fileApi);
                self::runApi($controller, $action);
            } else {
                Uri::notfound();
            }
            return;
        } catch (Exception $exception) {
            if ($option['debug']) {
                echo $exception->getMessage();
            } else {
                $log = new Log("error");
                $log->error($exception->getMessage());
            }
            Uri::error();
        }


    }

    private static function runFile($file)
    {
        header('Content-Type: text/html; charset=utf-8'); // 设置编码
        require_once $file;
    }

    private static function runApi($controller, $action)
    {

        $class = new $controller();

        if (method_exists($controller, $action)) {
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
        } else {
            Uri::notfound();
        }

    }
}