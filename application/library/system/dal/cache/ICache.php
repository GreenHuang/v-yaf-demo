<?php

namespace system\dal\cache;

/**
 * 缓存接口
 * @package system\dal\cache\ICache
 * @author vergil<vergil@vip.163.com>
 */
interface ICache{

	/**
	 * 增加一项值(当且仅当存储空间不存在相同键时才保存)
	 * @param string $key 
	 * @param mixed $value	到期时间或保存时长 默认为 0,这个值可以是一个Unix时间戳(自1970年1月1日起的秒数), 或者从现在起的时间差. 对于后一种情况, 时间差秒数不能超过60 * 60 * 24 * 30(30天的秒数), 如果过期时间超过这个值, 服务端会将其作为Unix时间戳与现在时间进行比较.
如果过期值为0(默认), 此项永不过期(但是它可能会因为为了给其他项分配空间而被删除)
	 * @param int $expiration
	 */
    public function add($key, $value, $expiration = 0);
    
     
    /**
     * 减小数值元素的值
     * @param string $key
     * @param int $offset 减小的值
     * @return int			成功时返回元素新的值， 或者在失败时返回 FALSE。
     */
    public function decrement($key, $offset = 1);
    
    /**
     * 递增一个元素的值
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function increment($key, $offset = 1);
    
    /**
     * 删除一项值
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function delete($key, $time = 0);
    
    /**
     * 让所有缓存失效
     * @param int $delay
     * @return bool
     */
    public function flush($delay = 0);
    
    /**
     * 获取某项值
     * @param string $key
     * @param callback $cacheDb
     * @param float $casToken
     * @return mixed
     */
    public function get($key, $cacheDb = null, &$casToken = null);
    
    /**
     *	获取多个数
     * @param array $keys
     * @param array $casToken
     * @param int $flags
     * @return array|false
     */
    public function getMulti(array $keys, &$arrCasToken = null, $flags = null);
    
     /**
     * 增加缓存(无论存储空间是否存在相同键，都保存)
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function set($key, $value, $expiration = 0);
    
    /**
     * 一次性增加多项缓存
     * @param array $items as array(key=>value,key1=>value1)
     * @param int $expiration
     * @return bool
     */
    public function setMulti(array $items, $expiration = 0);
    
}