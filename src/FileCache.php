<?php

class FileCache
{
    protected $cachePath;// 缓存路径

    public function __construct()
    {
        $this->cachePath = Uri::combineFromServerRoot('.cache');
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath);
        }
    }

    /**设置缓存*/
    public function set($key, $data, $expire = 3600)
    {
        $cacheFile = $this->getCacheFile($key);
        $content = serialize([
            'expire' => time() + $expire,
            'data' => $data
        ]);
        file_put_contents($cacheFile, $content);
    }

    /**获取缓存*/
    public function get($key)
    {
        $cacheFile = $this->getCacheFile($key);
        if (!file_exists($cacheFile)) {
            return null;
        }
        $content = file_get_contents($cacheFile);
        $cacheData = unserialize($content);
        if (time() > $cacheData['expire']) {
            $this->delete($key);
            return null;
        }
        return $cacheData['data'];
    }

    /**删除缓存*/
    public function delete($key)
    {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**清空缓存*/
    public function clear()
    {
        $files = glob($this->cachePath . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**更新缓存过期时间*/
    public function touch($key, $expire)
    {
        $data = $this->get($key);
        if ($data !== null) {
            $this->set($key, $data, $expire);
        }
    }

    /**获取缓存文件的路径*/
    protected function getCacheFile($key)
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}
