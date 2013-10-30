<?php

namespace system\dal\base;

/**
 * 抽象基础DAO类
 * @package system\dal\base\AbBaseDao
 * @author	vergil<vergil@vip.163.com>
 */
use system\dal\mysql\MysqlBaseDao;

abstract class AbBaseDao extends MysqlBaseDao{
	/**
	 * 获得表结构
	 * @return array
	 */
	public static function _GetMySchema() {
		return static::$_SCHEMA_;
	}
}