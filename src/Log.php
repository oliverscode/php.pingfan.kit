<?php
/**日志类*/
class Log
{
    private $fileName;

    /**
     */
    public function __construct($fileName = 'app')
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $logDir = $root . '/log';
        if (!is_dir($logDir)) {
            mkdir($logDir);
        }

        $this->fileName = $fileName;
    }

    private function log($message, $level)
    {

        // 检查$message的类型，如果是数组或对象则转换为JSON字符串
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        $timestamp = date('Y-m-d H:i:s');
        $localFileName = Uri::combineFromServerRoot('log', date('Y-m-d'), $this->fileName . '.log');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;

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
