<?php
namespace system\dal\base;
/**
 * 数据库访问对象接口
 * @package system\dal\base\IDao.php
 * @author vergil<vergil@vip.163.com>
 */
interface IDao {

	/**
	 * 解析查询过虑器的内容，返回该数据源可识别的查询对象
	 * @param \system\dal\base\QueryFilter $queryFilter
	 */
	public static function parseQueryFilter(QueryFilter $queryFilter);

	/**
	 * 查找总记录数
	 * @param \system\dal\base\QueryFilter $queryFilter
	 */
	public static function count($queryFilter);

	/**
	 * 插入数据
	 * @param array $document
	 * @param array $options
	 */
	public static function insert(array $document, array $options);
	
	/**
	 * 更新数据
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $object
	 * @param array $options
	 */
	public static function update(QueryFilter $queryFilter, array $object, array $options);
	
	/**
	 * 保存数据（集合insert和update操作）
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $object
	 * @param array $options
	 */
	public static function save(QueryFilter $queryFilter, array $object, array $options);
	
	/**
	 * 字段自增
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $object
	 * @param array $options
	 */
	public static function increase(QueryFilter $queryFilter, array $object, array $options);
	
	/**
	 * 字段自减
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $object
	 * @param array $options
	 */
	public static function reduce(QueryFilter $queryFilter, array $object, array $options);
	
	/**
	 * 查询单条记录
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $fields
	 */
	public static function fetchOne(QueryFilter $queryFilter,array $fields);
	
	/**
	 * 查询所有记录
	 * @param type $queryFilter
	 * @param array $fields
	 * @param array $sort
	 */
	public static function fetchAll($queryFilter, array $fields, array $sort);
	
	/**
	 * 查询分页记录
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $fields
	 * @param array $sortArray
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function fetchPage($queryFilter, array $fields, array $sortArray, $pageIndex, $pageSize);
	
	/**
	 * 删除记录
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $options
	 */
	public static function remove(QueryFilter $queryFilter, array $options);
	
}