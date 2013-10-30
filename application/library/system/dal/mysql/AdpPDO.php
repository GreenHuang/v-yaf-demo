<?php
namespace system\dal\mysql;
/**
 * PDO
 * @package system\dal\mysql\AdpPDO
 * @author	Jacky<ccx1999@163.com>
 */

class AdpPDO extends \PDO{
	
	public function __construct ($dsn ,$usrname,$password, array $driver_options = array()){
		parent::__construct($dsn, $usrname,$password, $driver_options);
	}
	
	public function prepare($sql, $driverOptions = array()) {
		if (EU_DEBUG) {
			return new AdpPDOStatement(parent::prepare($sql, $driverOptions));
		} else {
			return parent::prepare($sql, $driverOptions);
		}
	}
	
	
}