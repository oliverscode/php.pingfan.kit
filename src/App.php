<?php

class App
{
    public static function Run($opt = [])
    {
        $option = [
            'defaultController' => 'home',
            'defaultAction' => 'index',
            'debug' => false,
            'errLevel' => E_ERROR,
        ];
        if (is_array($opt)) {
            $option = array_merge($option, $opt);
        }

        error_reporting($option['errLevel']); // 设置错误级别

        // 提取路径中的控制器和方法
        $url = new Str($_SERVER['REQUEST_URI']);

        $path = $url->match('/^\/[\w\/]+(?=\?|$)/');



        if ($url == '/' && strlen($path) == 0) {
            $path = $option['defaultController'] . '/' . $option['defaultAction'];
            $path = new Str($path);
        }

        // 分割控制器和方法
        $arr = $path->split('/');
        if (count($arr) == 2) {
            $controller = $arr[0];
            $action = $arr[1];
        }

        $root = dirname($_SERVER['SCRIPT_FILENAME']);


        // 当前脚本的物理目录
        $fileHide = Uri::combine($root, $path . '.php');
        $fileApi = Uri::combine($root, $controller . '.php');
        $file = Uri::combine($root, $url);

//        echo "fileHide:$fileHide\n";
//        echo "fileApi:$fileApi\n";
//        echo "file:$file\n";
//        die;

        try {

            if ($option['debug'] && file_exists($file) && strpos($file, '.php') !== false) {
                self::runFile($file);
            } elseif (file_exists($fileHide)) {
                self::runFile($fileHide);
            } elseif (file_exists($fileApi)) {
                self::runFile($fileApi);
                self::runApi($controller, $action);
            } else {
                Uri::notfound();
            }

        } catch (Exception $exception) {

            if ($option['debug']) {
                throw $exception;
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
        include $file;
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

/*
nginx配置
location / {
   if (!-e $request_filename) {
       rewrite  ^(.*)$  index?s=/$1  last;
   }
}
location ~ ^/(\.user.ini|\.htaccess|\.git|\.svn|\.project|LICENSE|README.md)
{
    return 404;
}

# 处理php文件
location ~ \.php {
    include fastcgi_params;
    fastcgi_pass   127.0.0.1:9000;
    fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    fastcgi_param  PATH_INFO  $fastcgi_path_info;
    fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
}
*/