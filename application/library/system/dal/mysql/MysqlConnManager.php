<?php

namespace system\dal\mysql;

/**
 * MySQL连接管理
 * @package system\dal\mysql\MysqlConnManager
 * @author vergil<vergil@vip.163.com>
 */
class MysqlConnManager {

	private static $connMap = array();

	/**
	 * 根据连接名称获取连接
	 * @param string $connName
	 * @return PDO
	 */
	public static function getConn($connName) {
		if (!isset(self::$connMap[$connName])) {
			$config = \Yaf_Registry::get("config")->db->mysqls->toArray();
			if (isset($config[$connName])) {
				$currConfig = $config[$connName];
				//使用长链接
				$pdo = new \PDO($currConfig['dsn'], $currConfig['username'], $currConfig['password'],
								array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8;",
									\PDO::ATTR_PERSISTENT => false,
									\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
								)
				);
				//$pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
				//$pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);
				self::$connMap[$connName] = $pdo;
			} else {
				throw new Exception("{$connName} 连接不存在");
			}
		}
		return self::$connMap[$connName];
	}

	/**
	 * 获取所有分库的连接 
	 */
	public static function getCutConns() {
		$conns = array();
		$config = \Yaf_Registry::get("config")->db->mysqls->toArray();
		foreach ($config as $connName => $currConfig) {
			if ($currConfig['cutDB']) {
				if (USER_DB_NUM === 1) {
					//如果只有一个用户库，则返回user_1
					return self::getConn($connName);
				} else {
					if (!isset(self::$connMap[$connName])) {
						$pdo = new \PDO($currConfig['dsn'], $currConfig['username'], $currConfig['password'],
										array(
											\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8;",
											\PDO::ATTR_PERSISTENT => false,
											\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
										)
						);
						//$pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
						self::$connMap[$connName] = $pdo;
					}
					$conns[$connName] = self::$connMap[$connName];
				}
			}
		}
		return $conns;
	}
}