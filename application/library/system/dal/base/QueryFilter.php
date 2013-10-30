<?php

namespace system\dal\base;

use system\base\ArgumentHelper;

/**
 * 查询过虑器
 * @package system\dal\base\QueryFilter
 * @author vergil<vergil@vip.163.com>
 */
class QueryFilter {
	
	/**
	 * 升序
	 * @var int
	 */
	const SORT_ASC = 0;

	/**
	 * 降序
	 * @var int
	 */
	const SORT_DESC = 1;

	/**
	 * 等于
	 * @var int
	 */
	const EQ = 2;

	/**
	 * 不等于
	 * @var int
	 */
	const NEQ = 3;

	/**
	 * 大于(>)
	 * @var int
	 */
	const GT = 4;

	/**
	 * 大于等于(>=)
	 * @var int
	 */
	const GTE = 5;

	/**
	 * 小于(<)
	 * @var int
	 */
	const LT = 6;

	/**
	 * 小于等于(<=)
	 * @var int
	 */
	const LTE = 7;

	/**
	 * in 查询<br/>
	 * 条件为array(value1,value2,...)
	 * @var int
	 */
	const IN = 8;

	/**
	 * not in 查询<br/>
	 * 条件为array(value1,value2,...)
	 * @var int
	 */
	const NIN = 9;

	/**
	 * Between 范围查询
	 * 条件为array(minNum,maxNum)
	 * @var int
	 */
	const BT = 10;

	/**
	 * 模糊查询
	 * @var int
	 */
	const LK = 11;

	/**
	 * 查询条件
	 * @var array
	 */
	private $conditions = array();
	
	/**
	 * 排序规则
	 * @var array 
	 */
	private $sorts = array();

	/**
	 * 添加过滤条件，每个字段只允许出现一次（最后一次有效）
	 * @param string $columnName
	 * @param int $condition
	 * @param mixed $value
	 */
	public function addFilter($columnName, $condition, $value) {
		$this->conditions[$columnName] = array('cond' => $condition, 'value' => $value);
	}

	/**
	 * 等于<br/>
	 * $colunmName = $value
	 * @param string $columnName 字段名
	 * @param int $value 值
	 * @return QueryFilter $this
	 */
	public function eq($columnName, $value) {
		ArgumentHelper::isScalar($columnName);
		ArgumentHelper::isScalar($value);
		self::addFilter($columnName, self::EQ, $value);

		return $this;
	}

	/**
	 * 不等于<br/>
	 * $coumnName != $value
	 * @param string $columnName 字段名
	 * @param int $value 值
	 * @return QueryFilter $this
	 */
	public function neq($columnName, $value) {
		ArgumentHelper::isScalar($columnName);
		ArgumentHelper::isScalar($value);
		self::addFilter($columnName, self::NEQ, $value);
		return $this;
	}

	/**
	 * 大于<br/>
	 *  $colnumName &gt $value
	 * @param string $columnName
	 * @param int $value
	 * @return QueryFilter $this
	 */
	public function gt($columnName, $value) {
		ArgumentHelper::isScalar($columnName);
		ArgumentHelper::isScalar($value);
		self::addFilter($columnName, self::GT, $value);
		return $this;
	}

	/**
	 * 大于等于 <br/>
	 * $colunmName &gt= $value 
	 * @param string $columnName
	 * @param int $value
	 * @return QueryFilter $this
	 */
	public function gte($columnName, $value) {
		ArgumentHelper::isScalar($columnName);
		ArgumentHelper::isScalar($value);
		self::addFilter($columnName, self::GTE, $value);
		return $this;
	}

	/**
	 * 小于
	 * <br/>$columnName &lt $value
	 * @param unknown_type $columnName
	 * @param unknown_type $value
	 * @return QueryFilter $this
	 */
	public function lt($columnName, $value) {
		ArgumentHelper::isScalar($columnName);
		ArgumentHelper::isScalar($value);
		self::addFilter($columnName, self::LT, $value);
		return $this;
	}

	/**
	 * 小于等于
	 * <br/> $columnName <= $value
	 * @param string $columnName
	 * @param int $value
	 * @return QueryFilter $this
	 */
	public function lte($columnName, $value) {
		ArgumentHelper::isScalar($columnName);
		ArgumentHelper::isScalar($value);
		self::addFilter($columnName, self::LTE, $value);
		return $this;
	}

	/**
	 * in查询
	 * <br/> columnName in (value1,value2,...)
	 * @param string $columnName
	 * @param array $values array(value1,value2,...)
	 * @return QueryFilter $this
	 */
	public function in($columnName, array $values) {
		ArgumentHelper::isScalar($columnName);
		self::addFilter($columnName, self::IN, $values);
		return $this;
	}

	/**
	 * not in 查询
	 * <br/> columnName not in (value1,value2,...)
	 * @param string $columnName
	 * @param array $values
	 * @return QueryFilter $this
	 */
	public function nin($columnName, array $values) {
		ArgumentHelper::isScalar($columnName);
		self::addFilter($columnName, self::NIN, $values);
		return $this;
	}

	/**
	 * 范围查询
	 * @param string $columnName
	 * @param mixed $min
	 * @param mixed $max
	 * @return QueryFilter $this
	 */
	public function bt($columnName, $min, $max) {
		ArgumentHelper::isScalar($columnName);
		self::addFilter($columnName, self::BT, array($min, $max));
		return $this;
	}

	/**
	 * 模糊查询
	 * @param string $columnName
	 * @param string $str
	 * @return QueryFilter $this
	 */
	public function lk($columnName, $str) {
		ArgumentHelper::isScalar($columnName);
		self::addFilter($columnName, self::LK, $str);
		return $this;
	}

	/**
	 * 获取所有查询条件
	 * @return array
	 */
	public function getConditions() {
		return $this->conditions;
	}

	/**
	 * 重置查询条件
	 */
	public function resetConditions() {
		$this->conditions = array();
	}

	/**
	 * 获取查询条件数组
	 * @return array
	 */
	public function getConditionsArray() {
		$result = array();
		foreach ($this->conditions as $key => $value) {
			$result[$key] = $value['value'];
		}
		return $result;
	}

}