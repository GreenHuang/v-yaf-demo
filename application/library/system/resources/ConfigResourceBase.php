<?php

namespace system\resources;

/**
 * 配置文件基础类
 * @package system\resources\ConfigResourceBase;
 * @author vergil<vergil@vip.163.com>
 */
abstract class ConfigResourceBase extends \ArrayObject {

	private $templateInstance = null;
	private static $singletonList = array();

	public function __construct(array $initData = null) {
		$defaultConfig = $this->getDefaultConfigImpl();
		if (empty($initData)) {
			parent::__construct($defaultConfig);
		} else {
			foreach ($initData as $key => $value) {
				if (array_key_exists($key, $defaultConfig))
					$defaultConfig[$key] = $value;
			}
			parent::__construct($defaultConfig);
		}
	}

	/**
	 * 获取实例
	 * @return ConfigResourceBase
	 */
	public static function getInstance() {
		$className = get_called_class();

		if (isset(self::$singletonList[$className]))
			return self::$singletonList[$className];

		$instance = static::getConfig($className);
		self::$singletonList[$className] = $instance;
		return $instance;
	}

	/**
	 * 获取配置
	 * @param type $configClassName
	 * @return \system\resources\configClassName
	 * @throws InvalidArgumentException
	 */
	private static function getConfig($configClassName) {
		if (is_subclass_of($configClassName, __CLASS__)) {
			return new $configClassName();
		} else {
			throw new \InvalidArgumentException('"' . $configClassName . '" is not supported by ' . __CLASS__);
		}
	}

	protected abstract function getDefaultConfigImpl();
}