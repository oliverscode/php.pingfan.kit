<?php
require_once 'Path.php';

class FileCache
{
    protected $cachePath;// 缓存路径

    public function __construct()
    {
        $this->cachePath = Path::combineFromServerRoot('runtime_file_cache');
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath);
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
            $this->set($key, $data, time() + $expire);
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
        if (time() > $cacheData['expire']) {
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
        $files = glob($this->cachePath . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }


    /**获取缓存文件的路径*/
    protected function getCacheFile(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}
