<?php

class Lock
{
    public static function run(string $key, callable $callback)
    {
        $filename = sys_get_temp_dir() . '/' . md5($key) . '.lock';

        // 打开文件，如果文件不存在则创建
        $fileHandle = fopen($filename, 'a+');
        if (!$fileHandle) {
            throw new Exception("无法打开文件: $filename");
        }

        // 尝试获取锁
        if (!flock($fileHandle, LOCK_EX)) {
            fclose($fileHandle);
            throw new Exception("无法获取文件锁: $filename");
        }

        // 执行回调函数
        try {
            $result = call_user_func($callback, $fileHandle);
        } catch (Exception $e) {
            // 释放锁并关闭文件句柄
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            throw $e;
        }

        // 释放锁并关闭文件句柄
        flock($fileHandle, LOCK_UN);
        fclose($fileHandle);

        // 删除临时文件
        unlink($filename);

        return $result;
    }
}