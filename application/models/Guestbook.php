<?php

/**
 * 留言板Model
 * @package GuestbookModel
 * @author vergil<vergil@vip.163.com>
 */
class GuestbookModel extends BaseModel {
	
	/**
	 * 数据源结构$_SCHEMA_
	 * 当前数据源所对应的数据库表的简单定义
	 * @var array
	 */
	protected static $_SCHEMA_ = array(
		 //默认数据库连接名
		'dbname' => 'yaftest',
		 //是否需自动将array格式的数据转为json
		'codeArray' => true,
		 //数据表名
		'schemaName' => 'guestbook',
		/**
		 * 字段列表
		 * '字段名称' => array(
		 *		'type' => 字段类型(pk:主键; isAllowNull:是否允许空; array:数组,自动以json格式保存)
		 *		'grnerator' => 主键生成策略(assigned:上层程序指定; increment:底层DAO自动生成; identity:数据库自动生成)
		 *		'isAllowNull' => 是否允许空值(boolean)
		 *		'defaultValue' => 默认值
		 * )
		 */
		'columnNameList' => array(
			//grnerator=主键生成策略,assigned=>上层程序指定,increment=>底层DAO自动生成,identity=>数据库自动生成
			'id' => array('type' => 'pk', 'isAllowNull' => false, 'grnerator' => 'identity'),//主键
			'username' => array('type' => 'string', 'isAllowNull' => false),//用户名
			'content' => array('type' => 'string', 'isAllowNull' => false),//内容
			'email' => array('type' => 'string', 'isAllowNull' => true),//email
			'ip' => array('type' => 'int', 'isAllowNull' => true),//用户ip
			'create_time' => array('type' => 'int', 'isAllowNull' => false),//创建时间
			'json' => array('type' => 'array', 'isAllowNull' => true),
		),
	);
}