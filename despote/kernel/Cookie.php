<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * COOKIE 操作类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Exception;

class Cookie extends Service
{
    // 是否开启安全模式
    protected $safe;
    // 安全模式中加密的密钥
    protected $key;

    public function init()
    {
        if ($this->safe && empty($this->key)) {
            throw new Exception("开启了安全模式但是未能传入 key", 500);
        }
    }

    /**
     * 添加 cookie，如果存在则不添加
     * @param String  $key    cookie 的键名
     * @param Mixed   $value  cookie 的键值
     * @param Integer $expire cookie 的过期时间
     */
    public function add($key, $value, $expire = 0)
    {
        $this->has($key) || $this->set($key, $value, $expire);
    }

    /**
     * 设置 cookie，如果存在则直接覆盖
     * @param String  $key    cookie 的键名
     * @param Mixed   $value  cookie 的键值
     * @param Integer $expire cookie 的过期时间
     */
    public function set($key, $value, $expire = 0)
    {
        if ($this->safe) {
            // 只能加密字符串，所以先序列化
            $value = serialize($value);
            // 加密字符串
            $value = Despote::encrypt()->encode($value, $this->key, $expire);
        }
        // 设置 cookie
        $expire += time();
        setcookie($key, $value, $expire, '/');
    }

    /**
     * 获取 cookie 的值
     * @param  String $key      cookie 的键名
     * @param  String $default  cookie 不存在时返回的值，默认为 null
     * @return Mixed            cookie 的键值
     */
    public function get($key, $default = null)
    {
        // 如果没有设置 cookie 则返回默认值
        if (!isset($_COOKIE[$key])) {
            return $default;
        }

        // 获取 cookie 中存放的值
        $value = $_COOKIE[$key];
        // 开启安全模式则先解密
        if ($this->safe) {
            $value = Despote::encrypt()->decode($value, $this->key);
            $value = unserialize($value);
        }

        // 返回结果
        return $value;
    }

    /**
     * 删除 cookie
     * @param  String $key cookie 的键名
     */
    public function del($key)
    {
        setcookie($key, '', time() - 1, '/');
    }

    /**
     * 判断 cookie 是否存在
     * @param  String  $key cookie 的键名
     * @return Boolean      cookie 是否存在
     */
    public function has($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * 清空 cookie 数组
     */
    public function flush()
    {
        $_COOKIE = [];
    }
}
