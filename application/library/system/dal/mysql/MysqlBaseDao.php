<?php

namespace system\dal\mysql;

use system\dal\base\QueryFilter;

use system\dal\mysql\ValidateHelper;

/**
 * MySQL数据库访问对象
 * @abstract
 * @package system\dal\mysql\MysqlBaseDao
 * @author vergil<vergil@vip.163.com>
 */
abstract class MysqlBaseDao implements \system\dal\base\IDao {

	const _ID = '_id';
	const CUT_COLUMN_NAME = 'cutColumn';

	/**
	 * 主键生成策略-上层程序指定
	 * @var string
	 */
	const PK_GRNERATOR_ASSIGNED = 'assigned';

	/**
	 * 主键生成策略-底层DAO自动生成（32位字符串）
	 * @var string
	 */
	const PK_GRNERATOR_INCREMENT = 'increment';

	/**
	 * 主键生成策略-数据库自动生成
	 * @var string
	 */
	const PK_GRNERATOR_IDENTITY = 'identity';
	
	
	
	const SORT_DESC = 'DESC';
	const SORT_ASC = 'ASC';
	const KEY_SLASHES = '`';
	
	/**
	 * 表前缀
	 * @var string 
	 */
	protected static $_tablePerfix = null;

	/**
	 * 查询操作符
	 * @var array
	 */
	private static $_operator = array(
		QueryFilter::EQ => '=', 
		QueryFilter::NEQ => '!=', 
		QueryFilter::GT => '>', 
		QueryFilter::GTE => '>=', 
		QueryFilter::IN => 'IN',
		QueryFilter::NIN => 'NOT IN', 
		QueryFilter::LT => '<', 
		QueryFilter::LTE => '<=', 
		QueryFilter::LK => ''
	);

	/**
	 * 数据源结构$_SCHEMA_
	 * 当前数据源所对应的数据库表的简单定义
	 * grnerator=主键生成策略,assigned=>上层程序指定,increment=>底层DAO自动生成,identity=>数据库自动生成
	 * @var array
	 */
	protected static $_SCHEMA_ = array(
		'dbname' => 'public', //默认数据库连接名
		'schemaName' => 'user',
		'cutColumn' => 'userId', //分库字段（根据此字段分库）
		'codeArray' => false, //是否需自动将array格式的数据转为
		'columnNameList' => array(
			//grnerator=主键生成策略,assigned=>上层程序指定,increment=>底层DAO自动生成,identity=>数据库自动生成
			'userId' => array('type' => 'pk', 'isAllowNull' => false, 'grnerator' => 'assigned'), // 用户ID
			'times' => array('type' => 'int', 'isAllowNull' => false,), // 用户ID
		),
		'pk' => 'userId',
	);
	
	/**
	 * 获取表前缀
	 * @return string
	 */
	protected static function getTbPerfix() {
		if(static::$_tablePerfix === null) {
			$config = \Yaf_Registry::get("config")->db->mysqls->toArray();
			
			if(isset($config[static::$_SCHEMA_['dbname']]['tablePrefix'])) {
				static::$_tablePerfix = $config[static::$_SCHEMA_['dbname']]['tablePrefix'];
			}
		}
		return static::$_tablePerfix;
	}


	/**
	 * 获取真实数据表名
	 * @return string
	 */
	protected static function getTbName() {
		return static::getTbPerfix() . static::$_SCHEMA_['schemaName'];
	}

	/**
	 * 获取数据表名
	 * @return string
	 */
	protected static function getTbNames() {
		return static::$_SCHEMA_['schemaName'];
	}

	/**
	 * 根据分库规则获取当前记录应该在哪个分库
	 * 默认处理32位16进制的用户ID(腾讯平台传过来的ID)
	 * @param unknown_type $pk
	 */
	protected static function cutValue($pk) {
		return intval(fmod(hexdec($pk), USER_DB_NUM)) + 1;
		/*
		  if(is_string($pk) && mb_strlen($pk) == 32){
		  return hexdec($pk) % USER_DB_NUM + 1;
		  }else{
		  //非正常规则时不分库
		  return hexdec($pk) % USER_DB_NUM + 1;
		  }
		 */
	}

	/**
	 * 获取链接
	 * 如果包含分库字段则
	 * @return PDO or array(PDO)
	 */
	public static function getCurrDbConn($object = null) {
		if (isset(static::$_SCHEMA_[self::CUT_COLUMN_NAME])) {
			//分库
			if (isset($object[static::$_SCHEMA_[static::CUT_COLUMN_NAME]]) && $object !== null && isset($object[static::$_SCHEMA_[static::CUT_COLUMN_NAME]])) {
				if (is_array($object[static::$_SCHEMA_[static::CUT_COLUMN_NAME]])) {
					//IN查询,可能需要同时连接多个库
					$cutValues = array();
					foreach ($object[static::$_SCHEMA_[static::CUT_COLUMN_NAME]] as $v) {
						$cut = static::cutValue($v);
						$cutValues[$cut] = $cut;
					}
					if (count($cutValues) == 1) {
						//只需要连一个库
						$connName = static::$_SCHEMA_['dbname'] . current($cutValues);
						return MysqlConnManager::getConn($connName);
					} else {
						//多个库
						$conns = array();
						foreach ($cutValues as $key => $v) {
							$conns[] = MysqlConnManager::getConn(static::$_SCHEMA_['dbname'] . $key);
						}
						return $conns;
					}
				} else {
					return MysqlConnManager::getConn(static::$_SCHEMA_['dbname'] . static::cutValue($object[static::$_SCHEMA_[static::CUT_COLUMN_NAME]]));
				}
			} else {
				//同时要在多个库查询
				return MysqlConnManager::getCutConns();
			}
		} else {
			return MysqlConnManager::getConn(static::$_SCHEMA_['dbname']);
		}
	}

	/**
	 * 解析查询过虑器的内容，返回该数据源可识别的查询对象
	 * @param QueryFilter $queryFilter
	 * @return string where 后面的内容
	 */
	public static function parseQueryFilter(QueryFilter $queryFilter) {
		$whereStr = '';
		$isFrist = true;
		foreach ($queryFilter->getConditions() as $columnName => $conds) {
			$condType = $conds['cond'];
			$value = $conds['value'];
			//过滤查询条件
			if (is_string($value)) {
				$value = addslashes($value);
			} else if (is_array($value)) {
				foreach ($value as &$v) {
					if (is_string($v)) {
						$v = addslashes($v);
					}
				}
			}

			if (!$isFrist) {
				$whereStr = $whereStr . ' AND ';
			}
			$isFrist = FALSE;
			switch ($condType) {
				case QueryFilter::IN : {
						if (is_array($value)) {
							$tempStr = implode("','", $value);
							$operator = self::$_operator[$condType];
							$whereStr = $whereStr . "{$columnName} {$operator}('{$tempStr}')";
						}
					}break;
				case QueryFilter::NIN : {
						if (is_array($value)) {
							$tempStr = implode("','", $value);
							$operator = self::$_operator[$condType];
							$whereStr = $whereStr . "{$columnName} {$operator}('{$tempStr}')";
						}
					}break;
				case QueryFilter::BT : {
						/* if(is_array($value) && count($value)==2){
						  $result[$columnName] = array(self::$_operator[QueryFilter::GT]=>$value[0],self::$_operator[QueryFilter::LT]=>$value[1]);
						  } */
						if (is_array($value) && count($value) == 2) {
							$whereStr = $whereStr . "{$value[0]} <= `{$columnName}` AND `{$columnName}`<= {$value[1]}";
						}
					}break;
				case QueryFilter::LK : {
						$whereStr = $whereStr . "`{$columnName}` LIKE '%{$value}%'";
					}break;
				default: {
						if (isset(self::$_operator[$condType])) {
							$operator = self::$_operator[$condType];
							$whereStr = $whereStr . "`{$columnName}`{$operator}'{$value}'";
						}
					}
			}
		}
		return $whereStr;
	}

	/**
	 * 查找总记录数
	 * @param QueryFilter $queryFilter
	 */
	public static function count($queryFilter = null) {
		$sql = 'SELECT count(*) counts FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			$sql .= ' WHERE ' . $filter;
		}
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);

		$counts = 0;
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$row = $sth->fetch(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($row)) {
					$counts += $row['counts'];
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			$counts = $result['counts'];
		}

		return $counts;
	}

	/**
	 * 查找总记录数
	 * @param string $columnName
	 * @param object $value
	 */
	public static function countBy($columnName, $value) {
		$queryFilter = new QueryFilter();
		$queryFilter->eq($columnName, $value);
		return static::count($queryFilter);
	}

	/**
	 * 插入数据
	 *
	 * @param array $document
	 * @return string autoId
	 */
	public static function insert(array $document, array $options = array()) {
		$autoId = static::beforeInsert($document);
		$tbname = static::getTbName();
		$columns = array_keys($document);
		$columnList = self::KEY_SLASHES . implode(self::KEY_SLASHES . ',' . self::KEY_SLASHES, $columns) . self::KEY_SLASHES;
		$count = count($columns);
		if ($count > 1) {
			$valueParamsList = str_repeat('?, ', $count - 1) . '?';
		} else {
			$valueParamsList = '?';
		}
		$pdo = static::getCurrDbConn($document);
		$keySlashes = self::KEY_SLASHES;
		$stmt = $pdo->prepare("INSERT INTO {$keySlashes}{$tbname}{$keySlashes}({$columnList}) VALUES({$valueParamsList})");
		$flag = $stmt->execute(array_values($document));
		$stmt->closeCursor();
		unset($columns);
		unset($columnList);
		unset($valueParamsList);
		unset($document);
		if ($autoId === false) {
			//数据库生成ID
			return $pdo->lastInsertId();
		} else {
			//程序生成ID
			return $autoId;
		}
	}

	/**
	 * 插入前处理数据
	 * @param array $object
	 * @return $autoId string or false  返回程序生成的id或false(由数据库自动生成)
	 */
	private static function beforeInsert(&$object) {
		return ValidateHelper::validateByInsert(static::$_SCHEMA_, $object);
	}

	/**
	 * 更新前处理数据
	 * @param type $object
	 */
	private static function beforeUpdate(&$object) {
		ValidateHelper::validateByUpdate(static::$_SCHEMA_, $object);
	}

	/**
	 * 整合insert和update的方法
	 * @param \system\dal\base\QueryFilter $queryFilter
	 * @param array $object
	 */
	public static function save(QueryFilter $queryFilter, array $object, array $options = array()) {
		
	}

	/**
	 * 查找单条记录
	 * @param QueryFilter $queryFilter
	 * @param array $fields 需要查询的字段
	 */
	public static function fetchOne(QueryFilter $queryFilter, array $fields = array()) {
		$filter = static::parseQueryFilter($queryFilter);
		$sql = 'SELECT ' . static::getFieldsStr($fields) . ' FROM ' . static::getTbName();
		if ($filter !== '') {
			$sql.= ' WHERE ' . $filter;
		}
		$sql .= ' LIMIT 1';
		$pdo = static::getCurrDbConn($queryFilter->getConditionsArray());
		if (is_array($pdo)) {
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$result = $sth->fetch(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($result)) {
					static::handleRow($result);
					return $result;
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			if (!empty($result)) {
				static::handleRow($result);
			}
		}
		return $result;
	}

	/**
	 * 查找单条记录
	 * @param string $columnName
	 * @param unknown_type $value
	 * @param array $fields
	 */
	public static function fetchOneBy($columnName, $value, array $fields = array()) {
		$queryFilter = new QueryFilter();
		$queryFilter->eq($columnName, $value);
		return self::fetchOne($queryFilter, $fields);
	}

	private static function getFieldsStr(array $fields = array()) {
		if (empty($fields)) {
			$fields = array_keys(static::$_SCHEMA_['columnNameList']);
		}

		if (count($fields) == 1) {
			$sql = $fields[0];
		} else {
			$sql = '`' . implode('`,`', $fields) . '`';
		}
		return $sql;
	}

	/**
	 * 查询所有记录
	 * @param QueryFilter $queryFilter
	 * @param array $fields
	 * @param array $sort
	 */
	public static function fetchAll($queryFilter = null, array $fields = array(), array $sort = array()) {
		$sql = 'SELECT ' . static::getFieldsStr($fields) . ' FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$sql.= static::getSortStr($sort);
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}
		return $result;
	}

	/**
	 * 根据指定的条件获取记录
	 * @param string $columnName
	 * @param mixed $value
	 * @param array $fields
	 * @param array $sort
	 * @return array
	 */
	public static function fetchAllBy($columnName, $value, array $fields = array(), array $sort = array()) {
		$queryFilter = new QueryFilter();
		$queryFilter->eq($columnName, $value);
		return self::fetchAll($queryFilter, $fields, $sort);
	}

	/**
	 * 获取所有记录
	 * @param array $fields
	 * @param array $sort
	 */
	public static function getAll(array $fields = array(), array $sort = array()) {
		return static::fetchAll(null, $fields, $sort);
	}

	/**
	 * 分页获取记录
	 * 分库时不能准确获取记录会返回(N * pageSize)条记录
	 * @param QueryFilter $queryFilter
	 * @param array $fields
	 * @param array $sortArray
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function fetchPage($queryFilter = null, array $fields = array(), array $sortArray = array(), $pageIndex = 1, $pageSize = 10) {
		$pageIndex = $pageIndex ? (int) $pageIndex : 1;
		$skip = ($pageIndex - 1) * $pageSize;
		$skip = ($skip < 0) ? 0 : (int) $skip;
		$sql = 'SELECT ' . static::getFieldsStr($fields) . ' FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter != '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$sql .= static::getSortStr($sortArray);
		$sql .= " LIMIT {$skip},{$pageSize}";
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}
		return $result;
	}

	/**
	 * 排序榜专用的哈
	 * 链接用户表查询,排除 (2：封号；99：GM）
	 * @param type $queryFilter
	 * @param array $fields
	 * @param array $sortArray
	 * @param type $pageIndex
	 * @param type $pageSize
	 * @return type
	 */
	public static function rankingsFetchPage($queryFilter = null, array $fields = array(), array $sortArray = array(), $pageIndex = 1, $pageSize = 10, $wFields = 'userId') {
		$pageIndex = $pageIndex ? (int) $pageIndex : 1;
		$skip = ($pageIndex - 1) * $pageSize;
		$skip = ($skip < 0) ? 0 : (int) $skip;

		if (empty($fields))
			$fields = array_keys(static::$_SCHEMA_['columnNameList']);
		foreach ($fields as $v) {
			$getFieldsStr[] = 'r.' . $v;
		}
		if (count($getFieldsStr) == 1) {
			$fields = $getFieldsStr[0];
		} else {
			$fields = implode(',', $getFieldsStr);
		}

		$sql = 'SELECT ' . $fields . ' FROM ' . static::getTbName() . ' AS r LEFT JOIN eu3g_user AS u ON r.' . $wFields . ' = u.userId';
		$sql .= ' WHERE u.level >= 25 AND u.status NOT IN ( 2, 99 )';
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter != '') {
				$sql .= ' AND ' . $filter;
			}
		}

		$sort = array();
		foreach ($sortArray as $k => $v) {
			$sort['r.' . $k] = $v;
		}
		$sql .= static::getSortStr($sort);
		$sql .= " LIMIT {$skip},{$pageSize}";
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);

		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}
		return $result;
	}

	/**
	 * 分页获取记录
	 * @param string $columnName 字段名
	 * @param mixed  $value 字段值
	 * @param array $fields 要查询的字段(空则全部返回)
	 * @param array $sortArray 排序字段(columnName=>1,columnName=>-1);
	 * @param int $pageIndex 目标分页
	 * @param int $pageSize 分页大小
	 */
	public static function fetchPageBy($columnName, $value, array $fields = array(), array $sortArray = array(), $pageIndex = 1, $pageSize = 10) {
		$filter = new QueryFilter();
		$filter->eq($columnName, $value);
		return self::fetchPage($filter, $fields, $sortArray, $pageIndex, $pageSize);
	}

	/**
	 * 分页获取记录(skip)
	 * @param unknown_type $queryFilter
	 * @param unknown_type $sortArray
	 * @param unknown_type $skip
	 * @param unknown_type $pageSize
	 */
	public static function fetchPageUseSkip($queryFilter = null, array $fields = array(), array $sortArray = array(), $skip, $pageSize) {
		$sql = 'SELECT ' . static::getFieldsStr($fields) . ' FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$sql .= static::getSortStr($sortArray);
		$sql .= " LIMIT {$skip},{$pageSize}";
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}
		return $result;
	}

	/**
	 * 分页获取记录(skip)
	 * @param string $columnName 字段名
	 * @param mixed  $value 字段值
	 * @param array $fields 要查询的字段(空则全部返回)
	 * @param array $sortArray 排序字段(columnName=>1,columnName=>-1);
	 * @param int $skip 跳过多少页
	 * @param int $pageSize 分页大小
	 */
	public static function fetchPageUseSkipBy($columnName, $value, array $fields = array(), array $sortArray = array(), $skip, $pageSize) {
		$filter = new QueryFilter();
		$filter->eq($columnName, $value);
		return self::fetchPageUseSkip($filter, $fields, $sortArray, $skip, $pageSize);
	}

	/**
	 * 获取分页VO(自动获取分记录总数)
	 * @param int $pageIndex
	 * @param int $pageSize
	 * @param QueryFilter $queryFilter
	 * @param array $fields
	 * @param array $sortArray
	 * @return array
	 * 		count=>记录总数
	 * 		list=>分页记录集
	 *
	 */
	public static function getPageVO($pageIndex = 1, $pageSize = 10, $queryFilter = null, array $fields = array(), array $sortArray = array()) {
		$result = array('count' => 0, 'list' => array());

		$sqlCounts = 'SELECT count(*) counts FROM ' . static::getTbName();

		$sql = 'SELECT ' . static::getFieldsStr($fields) . ' FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
				$sqlCounts .= ' WHERE ' . $filter;
			}
		}
		$sql.= static::getSortStr($sortArray);
		$skip = ($pageIndex - 1) * $pageSize;
		$sql .= " LIMIT {$skip},{$pageSize}";
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				//查询此库的总记录数
				$sth = $currPdo->prepare($sqlCounts);
				$sth->execute();
				$row = $sth->fetch(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				$currCount = 0;
				if (!empty($row)) {
					$currCount = $row['counts'];
					$result['count'] += $currCount;
				}
				if ($currCount > 0) {
					/**
					 * @var $sth PDOStatement
					 */
					$sth = $currPdo->prepare($sql);
					$sth->execute();
					$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
					$sth->closeCursor();
					if (!empty($rows)) {
						static::handleRows($rows);
						$result['list'] = array_merge($result['list'], $rows);
					}
				}
			}
		} else {
			$sth = $pdo->prepare($sqlCounts);
			$sth->execute();
			$row = $sth->fetch(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			if (!empty($row)) {
				$result['count'] = $row['counts'];
			}

			if ($result['count'] > 0) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $pdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result['list'] = $rows;
				}
			}
		}
		return $result;
	}

	/**
	 * 获取分页VO(自动获取分记录总数)
	 * @param int $pageIndex
	 * @param int $pageSize
	 * @param string $columnName
	 * @param object $value
	 * @param array $fields
	 * @param array $sortArray
	 * @return array
	 * 		count=>记录总数
	 * 		list=>分页记录集
	 *
	 */
	public static function getPageVOBy($pageIndex = 1, $pageSize = 10, $columnName, $value, $fields = array(), array $sortArray = array()) {
		$filter = new QueryFilter();
		$filter->eq($columnName, $value);
		return self::getPageVO($pageIndex, $pageSize, $filter, $fields, $sortArray);
	}

	/**
	 * 批量插入记录
	 *
	 * @param array $documents
	 */
	public static function insertBatch(array & $documents) {
		foreach ($documents as &$document) {
			$document['_id'] = static::insert($document);
		}
	}

	/**
	 * 移除记录
	 * @param QueryFilter $queryFilter
	 * @return int 移除记录数
	 */
	public static function remove(QueryFilter $queryFilter, array $options = array()) {
		$sql = 'DELETE FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		$count = 0;
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				$count += $currPdo->exec($sql);
			}
		} else {
			$count = $pdo->exec($sql);
		}
		return $count;
	}

	/**
	 * 移除记录
	 * Enter description here ...
	 * @param unknown_type $columnName
	 * @param unknown_type $value
	 * @return int 移除记录数
	 */
	public static function removeBy($columnName, $value) {
		$filter = new QueryFilter();
		$filter->eq($columnName, $value);
		return self::remove($filter);
	}

	/**
	 * 	更新记录
	 * @param $criteria
	 * @param $object
	 * @return int 更新记录数
	 */
	public static function update(QueryFilter $queryFilter, array $object, array $options = array()) {
		//检测是否包含未定义的字段
		static::beforeUpdate($object);
		$keySlashes = self::KEY_SLASHES;
		$setList = '';
		$comma = '';
		$bindValues = array();
		foreach ($object as $columnName => $columnValue) {
			$setList .= $comma . $keySlashes . $columnName . $keySlashes . '=?';
			$comma = ',';
			$bindValues[] = $columnValue;
		}
		$tbname = static::getTbName();
		$sql = "UPDATE {$keySlashes}{$tbname}{$keySlashes} SET {$setList}";
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		$count = 0;
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				$stmt = $currPdo->prepare($sql);
				$stmt->execute($bindValues);
				$count += $stmt->rowCount();
				$stmt->closeCursor();
			}
		} else {
			$stmt = $pdo->prepare($sql);
			$stmt->execute($bindValues);
			$count = $stmt->rowCount();
			$stmt->closeCursor();
		}
		return $count;
	}

	/**
	 * 更新
	 * @param unknown_type $columnName
	 * @param unknown_type $value
	 * @param array $object 要更新的内容
	  safe 是否返回操作结果信息 默认为ture
	  fsync 是否直接插入到物理硬盘 默认为flase
	  multiple 是否更新多条配置的记录 默认为false
	  @return int 更新记录数
	 */
	public static function updateBy($columnName, $value, $object, array $options = array()) {
		$queryFilter = new QueryFilter();
		$queryFilter->eq($columnName, $value);
		return self::update($queryFilter, $object, $options);
	}

	/**
	 * increase documents from this collection
	 * Enter description here ...
	 * @param string $columnName
	 * @param unknown_type $value
	 * @param array $object 要更新的内容
	 */
	public static function increase(QueryFilter $queryFilter, array $object, array $options = array()) {
		//检测是否包含未定义的字段
		static::beforeUpdate($object);
		$sql = 'UPDATE ' . static::getTbName() . ' SET ';
		$i = 0;
		foreach ($object as $key => $value) {
			if ($i++ != 0) {
				$sql .= ",`{$key}`=`{$key}`+{$value}";
			} else {
				$sql .= "`{$key}`=`{$key}`+{$value}";
			}
		}
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		$count = 0;
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				$count += $currPdo->exec($sql);
			}
		} else {
			$count = $pdo->exec($sql);
		}
		return $count;
	}

	/**
	 * increase documents from this collection
	 * Enter description here ...
	 * @param string $columnName
	 * @param unknown_type $value
	 * @param array $object 要更新的内容
	 */
	public static function increaseBy($columnName, $value, $object, array $options = array()) {
		$queryFilter = new QueryFilter();
		$queryFilter->eq($columnName, $value);
		return self::increase($queryFilter, $object, $options);
	}

	/**
	 * 字段自减
	 * @param string $columnName
	 * @param unknown_type $value
	 * @param array $object 要更新的内容
	 */
	public static function reduce(QueryFilter $queryFilter, array $object, array $options = array()) {
		//检测是否包含未定义的字段
		static::beforeUpdate($object);
		$sql = 'UPDATE ' . static::getTbName() . ' SET ';
		$i = 0;
		foreach ($object as $key => $value) {
			if ($i++ != 0) {
				$sql .= ",`{$key}`=`{$key}`-{$value}";
			} else {
				$sql .= "`{$key}`=`{$key}`-{$value}";
			}
		}
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}
		$conArray = $queryFilter == null ? null : $queryFilter->getConditionsArray();
		$pdo = static::getCurrDbConn($conArray);
		$count = 0;
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				$count += $currPdo->exec($sql);
			}
		} else {
			$count = $pdo->exec($sql);
		}
		return $count;
	}

	public static function reduceBy($columnName, $value, $object, array $options = array()) {
		$queryFilter = new QueryFilter();
		$queryFilter->eq($columnName, $value);
		return self::reduce($queryFilter, $object, $options);
	}

	/**
	 * 处理返回来的记录
	 * @param array $rows
	 */
	private static function handleRows(&$rows) {
		if (!empty($rows) && isset(static::$_SCHEMA_['codeArray']) && static::$_SCHEMA_['codeArray']) {
			//需要将array数据由json返编码过来
			foreach ($rows as &$row) {
				foreach ($row as $key => &$value) {
					if (static::$_SCHEMA_['columnNameList'][$key]['type'] == 'array') {
						if (!empty($value)) {
							$value = json_decode($value, true);
						}
					}
				}
			}
		}
	}

	/**
	 * 处理返回来的记录
	 * @param array $row
	 */
	private static function handleRow(&$row) {
		if (!empty($row) && isset(static::$_SCHEMA_['codeArray']) && static::$_SCHEMA_['codeArray']) {
			//需要将array数据由json返编码过来
			foreach ($row as $key => &$value) {
				if (static::$_SCHEMA_['columnNameList'][$key]['type'] == 'array') {
					if (!empty($value)) {
						$value = json_decode($value, true);
					}
				}
			}
		}
	}

	private static function getSortStr(array $sort = array()) {
		$sortStr = '';
		if (!empty($sort)) {
			$sortStr = " ORDER BY ";
			$isFrist = true;
			foreach ($sort as $colName => $order) {
				if ($isFrist === false) {
					$sortStr .= ",{$colName} {$order}";
				} else {
					$sortStr .= "{$colName} {$order}";
					$isFrist = false;
				}
			}
		}
		return $sortStr;
	}

	/**
	 * 求和字段
	 * @param array $sumColumnName		array('别名'=>'字段名')
	 * @param \sys\dal\base\QueryFilter $queryFilter
	 * @param string $group	 分组字段
	 * @param array $fields
	 * @param array $sort
	 * @return array
	 * 例子：ClanCityApplyDao::sumColumnFetchAll(array('sumArmys' => 'armys','sumTroops' => 'troops'),$filter,'cityId')
	 * 执行的sql： SELECT *,SUM(`armys`) as sumArmys,SUM(`troops`) as troops FROM eu3g_clan_city_apply GROUP BY cityId
	 */
	public static function sumColumnFetchAll(array $sumColumnName, QueryFilter $queryFilter = null, $group = null, array $fields = array(), array $sort = array()) {
		$sum = array();
		foreach ($sumColumnName as $asName => $column) {
			$asName = is_string($asName) ? $asName : $column;
			$sum[] = 'SUM(`' . $column . '`) as ' . $asName;
		}
		$sql = 'SELECT ' . static::getFieldsStr($fields) . ', ' . join(', ', $sum) . ' FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}

		if ($group != null) {
			$sql .= ' GROUP BY ' . $group;
		}
		$sql.= static::getSortStr($sort);

		$pdo = static::getCurrDbConn();
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}

		return $result;
	}

	/**
	 * 统计唯一字段
	 * @author vergil
	 * @param 字段名 $columnName
	 * @return int
	 * 调用例子： ClanCityApplyDao::countForDistinct('clanId')
	 * 执行的sql：SELECT COUNT( DISTINCT (`clanId`) ) AS count FROM  `eu3g_clan_city_apply`
	 */
	public static function countForDistinct($columnName) {
		$sql = 'SELECT COUNT(DISTINCT(`' . $columnName . '`)) AS count  FROM ' . static::getTbName();
		$pdo = static::getCurrDbConn();
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetch(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}
		return (int) $result['count'];
	}

	public static function countColumnFetchAll(array $sumColumnName, QueryFilter $queryFilter = null, $group = null, array $fields = array(), array $sort = array()) {
		$sum = array();
		foreach ($sumColumnName as $asName => $column) {
			$asName = is_string($asName) ? $asName : $column;
			$sum[] = 'count(`' . $column . '`) as ' . $asName;
		}
		$sql = 'SELECT ' . static::getFieldsStr($fields) . ', ' . join(', ', $sum) . ' FROM ' . static::getTbName();
		if ($queryFilter != null) {
			$filter = static::parseQueryFilter($queryFilter);
			if ($filter !== '') {
				$sql.= ' WHERE ' . $filter;
			}
		}

		if ($group != null) {
			$sql .= ' GROUP BY ' . $group;
		}
		$sql.= static::getSortStr($sort);

		$pdo = static::getCurrDbConn();
		if (is_array($pdo)) {
			//需要查询多个库的信息
			$result = array();
			foreach ($pdo as $currPdo) {
				/**
				 * @var $sth PDOStatement
				 */
				$sth = $currPdo->prepare($sql);
				$sth->execute();
				$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (!empty($rows)) {
					static::handleRows($rows);
					$result = array_merge($result, $rows);
				}
			}
		} else {
			/**
			 * @var $sth PDOStatement
			 */
			$sth = $pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$sth->closeCursor();
			self::handleRows($result);
		}

		return $result;
	}

}