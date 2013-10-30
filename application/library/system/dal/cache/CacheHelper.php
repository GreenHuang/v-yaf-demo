<?php

namespace system\dal\cache;

/**
 * 缓存帮助类
 * @package system\dal\cache\CacheHelper
 * @author vergil<vergil@vip.163.com>
 */
class CacheHelper {

	private static $_cacheInstance = null;

	/**
	 * 获取缓存实例
	 * @return \system\dal\cache\MemcachedForWin or \Memcached
	 */
	public static function getCacheInstance() {
		if (self::$_cacheInstance == null) {
			//当前使用Memcached
			$config = \yaf_Registry::get('config');

			if (!IS_WINDOWS) {
				self::$_cacheInstance = new \Memcached();
				// 设置 Memcached 所有 key 的前缀
				if (isset($config->cache->memcached->keyPrefix) AND ('' !== $config->cache->memcached->keyPrefix)) {
					self::$_cacheInstance->setOption(\Memcached::OPT_PREFIX_KEY, $config->cache->memcached->keyPrefix);
				}
				foreach ($config->cache->memcached->servers->toArray() as $cfg) {
					self::$_cacheInstance->addServer($cfg['host'], $cfg['port'], $cfg['weight']);
				}
			} else {
				self::$_cacheInstance = new \system\dal\cache\MemcachedForWin();
				foreach ($config->cache->memcached->servers->toArray() as $cfg) {
					self::$_cacheInstance->addServer($cfg['host'], $cfg['port'], true, $cfg['weight']);
				}
			}
		}
		return self::$_cacheInstance;
	}

}