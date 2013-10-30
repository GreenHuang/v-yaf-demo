<?php

namespace system\dal\cache;

use system\dal\cache\ICache;

/**
 * 由于windows下暂无memcached扩展，所以暂用此类代替其简单的功能
 * memcache与memcached在存储数据时，所设置的标识位不同，所以不能共用
 * memcached 标识位 0表明字段串,1表明int,2表明double,4表明序列化过的数据
 * memcache 标识位 1表明经过序列化，但未经过压缩，2表明压缩而未序列化，3表明压缩并且序列化，0表明未经过压缩和序列化
 * (本地测试时用)
 * @package system\dal\cacheMemcachedForWin
 * @author vergil<vergil@vip.163.com>
 */
final class MemcachedForWin implements ICache {

	private static $_memcache = null;
	
	private static $_prefix;

	public function __construct() {
		self::$_prefix = \yaf_Registry::get('config')->cache->memcached->keyPrefix;
		self::$_memcache = new \Memcache();
	}

	public function addServer($host, $port, $weight) {
		self::$_memcache->addServer($host, $port, false, $weight);
		//超过2K文本时以0.2比例进行压缩
		self::$_memcache->setCompressThreshold(2000, 0.2);
	}

	/**
	 * 增加一项值(当且仅当存储空间不存在相同键时才保存)
	 * @param string $key 
	 * @param mixed $value	到期时间或保存时长 默认为 0,这个值可以是一个Unix时间戳(自1970年1月1日起的秒数), 或者从现在起的时间差. 对于后一种情况, 时间差秒数不能超过60 * 60 * 24 * 30(30天的秒数), 如果过期时间超过这个值, 服务端会将其作为Unix时间戳与现在时间进行比较.
	  如果过期值为0(默认), 此项永不过期(但是它可能会因为为了给其他项分配空间而被删除)
	 * @param int $expiration
	 */
	public function add($key, $value, $expiration = 0) {
		return self::$_memcache->add(self::$_prefix . $key, $value, false, $expiration);
	}

	/**
	 * 减小数值元素的值
	 * @param string $key
	 * @param int $offset 减小的值
	 * @return int			成功时返回元素新的值， 或者在失败时返回 FALSE。
	 */
	public function decrement($key, $offset = 1) {
		return self::$_memcache->decrement(self::$_prefix . $key, $offset);
	}

	/**
	 * 递增一个元素的值
	 * @param string $key
	 * @param int $offset
	 * @return int
	 */
	public function increment($key, $offset = 1) {
		return self::$_memcache->increment(self::$_prefix . $key, $offset);
	}

	/**
	 * 删除一项值
	 * @param string $key
	 * @param int $time
	 * @return boolean
	 */
	public function delete($key, $time = 0) {
		return self::$_memcache->delete(self::$_prefix . $key, $time);
	}

	/**
	 * 让所有缓存失效
	 * @param int $delay
	 * @return boolean
	 */
	public function flush($delay = 0) {
		return self::$_memcache->flush();
	}

	/**
	 * 获取某项值
	 * @param string $key
	 * @param callback $cacheDb
	 * @param float $casToken
	 * @return mixed
	 */
	public function get($key, $cacheDb = null, &$casToken = null) {
		return self::$_memcache->get(self::$_prefix . $key);
	}

	/**
	 * 	获取多个数
	 * @param array $keys
	 * @param array $casToken
	 * @param int $flags
	 * @return array|false
	 */
	public function getMulti(array $keys, &$arrCasToken = null, $flags = null) {
		$result = array();
		foreach ($keys as $key) {
			$result[$key] = $this->get($key);
		}
		return $result;
	}

	/**
	 * 增加缓存(无论存储空间是否存在相同键，都保存)
	 * @param string $key
	 * @param mixed $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function set($key, $value, $expiration = 0) {
		return self::$_memcache->set(self::$_prefix . $key, $value, 0, $expiration);
	}

	/**
	 * 一次性增加多项缓存
	 * @param array $items as array(key=>value,key1=>value1)
	 * @param int $expiration
	 * @return boolean
	 */
	public function setMulti(array $items, $expiration = 0) {
		foreach ($items as $key => $item) {
			$this->set($key, $item, $expiration);
		}
		return true;
	}

}