<?php

namespace config;

use system\resources\ConfigResourceBase;

/**
 * 全局配置类
 * @package config\GlobalConfig;
 * @author vergil<vergil@vip.163.com>
 */
class GlobalConfig extends ConfigResourceBase {
	
	protected function getDefaultConfigImpl() {
		return array(
			'appName' => '网站名称',
			
			/**
			 * MySQL链接
			 * 范例：
			 * '连接名' => array(
			 *		'cutDB' => boolean,//是否分库
			 *		'dsn' => string,//PDO DSN
			 *		'username' => string,//用户名
			 *		'password' => string,//密码
			 * )
			 */
			'mysqls' => array(
				'yaftest' => array(
					'cutDB' => false,
					'tablePrefix' => 'yaf_',
					'dsn' => 'mysql:dbname=yaftest;host=localhost;port=3306',
					'username' => 'root', 
					'password' => ''
				),
			),
			
			/**
			 * Memcache配置
			 */
			'memcacheSettings' => array(
				'persistentId' => '',
				'keyPrefix' => '',
				'servers' => array(
					array(
						'host' => '127.0.0.1',
						'port' => 11211,
						'weight' => 0,
					),
				)
			),
			
		);
	}
	
}