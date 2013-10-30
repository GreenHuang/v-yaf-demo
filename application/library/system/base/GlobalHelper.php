<?php

namespace system\base;

/**
 * 全局帮助类
 * @package system\base\GlobalHelper
 * @author vergil<vergil@vip.163.com>
 */
final class GlobalHelper {
	
	public static function GP($key, $defaultValue = null) {
		if(isset($_POST[$key])) {
			
		} elseif(isset($_GET[$key])) {
			
		}
	}
	
	/**
	 * 重定向
	 *
	 * @param	string	$uri the URL
	 * @param	string	$method: location or redirect
	 * @return	string
	 */
	public static function redirect($uri = '', $method = 'location', $http_response_code = 302) {
		if (!preg_match('#^https?://#i', $uri)) {
			$uri = self::site_url($uri);
		}

		switch ($method) {
			case 'refresh' : 
				header("Refresh:0;url=" . $uri);
				break;
			default : 
				header("Location: " . $uri, TRUE, $http_response_code);
				break;
		}
		exit;
	}
	
	/**
	 * 构造url
	 * @todo 目前只实现简单的链接功能
	 * @param string $uri
	 */
	public static function site_url($uri = '') {
		//$globalConfig = \config\GlobalConfig::getInstance();
		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $uri;
	}

	/**
	 * 计算字符串的数字类型的哈希码值
	 *
	 * @param string $str
	 * @param bool $useMd5
	 * @param int $hashOffset
	 * @return int
	 */
	public static function hashString($str, $useMd5 = false, $hashOffset = 5381) {
		if (!is_scalar($str))
			throw new InvalidArgumentException('Argument $str is not an scalar.');

		if ((func_num_args() > 2) AND !self::isInteger($hashOffset)) {
			throw new InvalidArgumentException('Argument $hashOffset is not an integer.');
		}

		if ($useMd5) {
			$realStr = substr(md5($str), 8, 16);
			$strLen = 16;
		} else {
			$realStr = $str;
			$strLen = strlen($str);
		}

		for ($i = 0; $i < $strLen;) {
			$hashOffset = ($hashOffset << 5) + $hashOffset + ord($realStr{$i++});
//			$tmp = $hashOffset << 5;
//			if ($tmp < -2147483648) $tmp = 2147483647;
//			elseif ($tmp > 2147483647) $tmp = -2147483648;
//			$hashOffset = $tmp + $hashOffset + ord($realStr{$i++});
		}
		return $hashOffset;
	}

	/**
	 * 计算字符串的32位哈希值
	 *
	 * @param string $str
	 * @param bool $useMd5
	 * @return int
	 */
	public static function string2Hashcode32($str, $useMd5 = false) {
		if (true === $useMd5)
			return crc32(md5($str));
		else
			return crc32($str);
	}

	/**
	 * 判断是否为正整数
	 *
	 * @param mixed $arg
	 * @return bool
	 */
	public static function isPositiveInteger($arg) {
		try {
			if (is_nan($arg))
				return false;
		} catch (Exception $ex) {
			return false;
		}

		if (($arg > 0) AND (false === strpos($arg, '.')) AND (false === stripos($arg, 'e'))) {
			return true;
		}
		else
			return false;
	}

	/**
	 * 判断是否为整数(包括负整数和正整数)
	 *
	 * @param mixed $arg
	 * @return bool
	 */
	public static function isInteger($arg) {
		try {
			if (is_nan($arg))
				return false;
		} catch (Exception $ex) {
			return false;
		}

		if (is_numeric($arg) AND (false === strpos($arg, '.')) AND (false === stripos($arg, 'e'))) {
			return true;
		}
		else
			return false;
	}

	/**
	 * 判断是否为浮点数（支持字符串验证）
	 *
	 * @param scalar $arg
	 * @return bool
	 */
	public static function isFloat($arg) {
		try {
			if (is_nan($arg))
				return false;
		} catch (Exception $ex) {
			return false;
		}

		return is_float(abs($arg));
	}

	/**
	 * 判断是否为自然数
	 * 自然数是零和所有的正整数
	 *
	 * @param mixed $arg
	 * @return bool
	 */
	public static function isNaturalNumber($arg) {
		try {
			if (is_nan($arg))
				return false;
		} catch (Exception $ex) {
			return false;
		}

		if (is_numeric($arg) AND ($arg >= 0) AND (false === strpos($arg, '.')) AND (false === stripos($arg, 'e'))) {
			return true;
		}
		else
			return false;
	}

	/**
	 * 判断是否为负数
	 *
	 * @param mixed $arg
	 * @return bool
	 */
	public static function isNegative($arg) {
		try {
			if (is_nan($arg))
				return false;
		} catch (Exception $ex) {
			return false;
		}
		if (is_numeric($arg) AND ($arg < 0))
			return true;
		else
			return false;
	}

	/**
	 * 计算是否满足几分之几的机率
	 *
	 * @param int $numerator	分子
	 * @param int $denominator	分母
	 * @return bool
	 */
	public static function isProbabilityMatch($numerator, $denominator) {
		if (!self::isPositiveInteger($numerator)) {
			throw new InvalidArgumentException('Argument $numerator is not an positive integer.');
		}
		if (!self::isPositiveInteger($denominator)) {
			throw new InvalidArgumentException('Argument $denominator is not an positive integer.');
		}
//		$rand_val = rand(1, $denominator);
		$rand_val = mt_rand(1, $denominator);
		if ($rand_val <= $numerator)
			return true;
		else
			return false;
	}

	/**
	 * 如果 $arg 超出范围，返回 true，否则返回 false
	 *
	 * @param int $arg
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	public static function isOutOfRange($arg, $min = null, $max = null) {
		if (!is_numeric($arg))
			throw new InvalidArgumentException('$arg');

		if (isset($min)) {
			if (!is_numeric($min))
				throw new InvalidArgumentException('$min');
			$t1 = true;
		}
		else
			$t1 = false;
		if (isset($max)) {
			if (!is_numeric($max))
				throw new InvalidArgumentException('$max');
			$t2 = true;
		}
		else
			$t2 = false;
		if (!$t1 AND !$t2)
			throw new InvalidArgumentException('$min and $max is null');

		if ($t1 AND $t2)
			return (($arg < $min) OR ($arg > $max));
		elseif ($t1)
			return ($arg < $min);
		else
			return ($arg > $max);
	}

	/**
	 * 将 Id 数组转换为逗号分隔的字符串，相当于 implode(',', $arrId);
	 *
	 * @param array $arrId
	 * @param bool $throwIfEmpty
	 * @param bool $allowZeroId
	 * @return string
	 */
	public static function convert2CommaIdList(array $arrId, $throwIfEmpty = true, $allowZeroId = false) {
		if (empty($arrId)) {
			if ($throwIfEmpty) {
				throw new InvalidArgumentException('Argument $arrId is empty.');
			}
			else
				return '';
		}

		if (1 === count($arrId)) {
			$currId = current($arrId);
			if (false === $allowZeroId) {
				if (!self::isPositiveInteger($currId)) {
					throw new InvalidArgumentException('Argument $arrId.Index(' . $currId . ') is not an positive integer.');
				}
			} else {
				if (!self::isNaturalNumber($currId)) {
					throw new InvalidArgumentException('Argument $arrId.Index(' . $currId . ') is not an natural integer.');
				}
			}
			return $currId;
		} else {
			$retvl = $comma = '';
			foreach ($arrId as $currId) {
				if (false === $allowZeroId) {
					if (!self::isPositiveInteger($currId)) {
						throw new InvalidArgumentException('Argument $arrId.Index(' . $currId . ') is not an positive integer.');
					}
				} else {
					if (!self::isNaturalNumber($currId)) {
						throw new InvalidArgumentException('Argument $arrId.Index(' . $currId . ') is not an natural integer.');
					}
				}
				$retvl .= $comma . intval($currId);
				$comma = ',';
			}
			return $retvl;
		}
	}

	/**
	 * 执行公式
	 * @param string $formula	公式
	 * @param array $data		数据，每个key替换成value的值
	 */
	public static function runFormula($formula, array $data = array()) {

		if (empty($formula)) {
			throw new Exception('Argument Error!');
		}
		if ($data) {
			$nv = null;
			$formula = strtr($formula, $data);
			eval('$nv = ' . $formula . ';');
			return $nv;
		}
		return false;
	}

	/**
	 * 判断是否在当天00:00之后
	 * 
	 * @param integer $time		需要判断的时间戳
	 * @return boolean			是否时间戳大于当前的00:00
	 */
	public static function isToday($time) {
		return strtotime(date('Y-m-d')) < $time;

		$today0 = date('Y-m-d');
//		if ($time < strtotime($today0)) return false;
//		elseif ($time > strtotime($today0 . ' 23:59:59')) return false;
//		else return true;
		if (($time >= strtotime($today0)) AND ($time <= strtotime($today0 . ' 23:59:59')))
			return true;
		else
			return false;
	}

	/**
	 * 判断是否在第二天0点之后
	 * 
	 * @param integer $time		需要判断的时间戳
	 * @return boolean
	 */
	public static function isTomorrow($time) {
		return strtotime(date('Y-m-d') . ' 23:59:59') < $time;
	}

	/**
	 * 判断时间是否为当天
	 * 
	 * @param integer $time		需要判断的时间戳
	 * @return boolean
	 */
	public static function isTodayDate($time) {
		if (date('Y-m-d', $_SERVER['REQUEST_TIME']) == date('Y-m-d', $time))
			return true;
		return false;
	}

	/**
	 * 转换时间长度为多少天多少小时多少分钟多少秒
	 * @param integer $time
	 * @return string
	 */
	public static function matchTimestampToTips($time) {
		$tips = '';
		$timediff = $time;
		$days = intval($timediff / 86400);
		if ($days > 0) {
			$tips .= $days . '天';
		}
		$remain = $timediff % 86400;
		$hours = intval($remain / 3600);
		if ($hours > 0) {
			$tips .= $hours . '小时';
		}
		$remain = $remain % 3600;
		$mins = intval($remain / 60);
		if ($mins > 0) {
			$tips .= $mins . '分钟';
		}
		$secs = $remain % 60;
		if ($secs > 0) {
			$tips .= $secs . '秒';
		}
		return $tips;
	}

	/**
	 * 获取客户端IP地址
	 *
	 * @return string
	 */
	public static function getClientIp() {
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
			return $_SERVER["HTTP_CLIENT_IP"];

		if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
			$proxy_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		elseif (($tmp_ip = getenv("HTTP_X_FORWARDED_FOR")))
			$proxy_ip = $tmp_ip;
		else
			$proxy_ip = '';

		if ('' !== $proxy_ip) {
			if (false === strpos($proxy_ip, ','))
				return $proxy_ip;

			foreach (explode(',', $proxy_ip) as $curr_ip) {// 处理可能有多级代理的情况
				if (false === stripos($curr_ip, 'unknown'))
					$curr_ip = ltrim($curr_ip);
				else
					continue;

				if (0 === strpos($curr_ip, '192.168.'))
					continue; // 内网IP
				if (0 === strpos($curr_ip, '10.'))
					continue; // 内网IP
				if (0 === strpos($curr_ip, '172.16.'))
					continue; // 内网IP
				return $curr_ip;
			}
		}

		if (!empty($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];
		elseif (($retvl = getenv("HTTP_CLIENT_IP")))
			return $retvl;
		elseif (($retvl = getenv("REMOTE_ADDR")))
			return $retvl;
		else
			return '0.0.0.0';
	}

	/**
	 * 获取某个数值在那个key区间
	 * 
	 * @param integer $value		被检测的数值
	 * @param array $arrRange		被检测的数组区间
	 * @param string $matchKey		指定数值区间的某个key作为检测数据
	 * @param integer $default		默认值
	 * @return integer				返回数组区间的对应的索引
	 */
	public static function getKeyByRangValue($value, $arrRange, $matchKey = null, $default = 1) {
		$key = $default;
		if (isset($matchKey)) {
			foreach ($arrRange as $key1 => $arr) {
				if ($arr[$matchKey] > $value)
					break;
				$key = $key1;
			}
		}
		else {
			foreach ($arrRange as $key1 => $value1) {
				if ($arr[$matchKey] > $value)
					break;
				$key = $key1;
			}
		}
		return $key;
	}

	/**
	 * 根据数组指定的可以的值,按照数值概率,获取对应的key
	 * 
	 * 例子1：
	 * 		$arrRange = array(
	 * 			1 => 90,
	 * 			2 => 10
	 * 		);
	 * 		getKeyByRand($arrRange , null , 1) ; 
	 * 		返回的结果可能是   : 1(90%) , 2(10%)
	 *
	 * 例子2:
	 *  	$arrRange = array(
	 *  		1 => array(
	 *  			'key111'	=>	10,
	 *  		),
	 *  		2 => array(
	 *  			'key111'	=>	20,
	 *  		),
	 *  		3 => array(
	 *  			'key111'	=>	30,
	 *  		),
	 *  		4 => array(
	 *  			'key111'	=>	40,
	 *  		),
	 *  	);
	 *  	getKeyByRand($arrRange , 'key111' , 1); 
	 *  	返回的结果   : 1(10%) , 2(20%) , 3(30%) , 4(40%)
	 *  
	 * @param array $arrRange		数据来源
	 * @param string $matchKey		指定获取数值的下标
	 * @param string $default		默认返回值
	 * @return string				返回匹配的下标
	 */
	public static function getKeyByRand($arrRange, $matchKey = null, $default = 1) {
		$total = 0;
		$map = array();
		if (isset($matchKey)) {
			foreach ($arrRange as $key1 => $arr) {
				$total+=$arr[$matchKey];
				$map[$key1] = isset($map[$key1]) ? $map[$key1] + $total : $total;
			}
		} else {
			foreach ($arrRange as $key1 => $value) {
				$total += $value;
				$map[$key1] = isset($map[$key1]) ? $map[$key1] + $total : $total;
			}
		}
		$map = array_reverse($map, true);
		$rank = mt_rand(0, $total);

		foreach ($map as $key => $value) {
			if ($rank <= $value) {
				$default = $key;
			} else {
				break;
			}
		}
		return $default;
	}

	/**
	 * 数组强制转换成对象
	 * 
	 * @param (object or array) $e
	 * @return object
	 */
	public static function arrayToObject($e) {
		if (gettype($e) != 'array')
			return $e;
		foreach ($e as $k => $v) {
			if (gettype($v) == 'array' || getType($v) == 'object')
				$e[$k] = (object) self::arrayToObject($v);
		}
		return (object) $e;
	}

	/**
	 * 以子进程方式执行PHP文件
	 *
	 * @param string $phpScriptFilePath	要执行的 PHP 文件的绝对路径
	 * @param mixed $args
	 * @param string $phpExeFile	PHP可执行文件或php.exe所在位置的绝对路径
	 * @return void|mixed
	 * @throws Exception
	 */
	public static function executePhpScriptFile($phpScriptFilePath, $arguments = null, $phpExeFile = null) {
		if (!is_readable($phpScriptFilePath)) {
			throw new Exception($phpScriptFilePath . ' is not readable.');
		}

		if (!isset($phpExeFile)) {
			$config = GlobalConfig::getInstance();
			$tmp = $config['phpPath'];
			if (false === strpos($tmp, ' '))
				$phpExeFile = $tmp;
			else {
				list($phpExeFile) = explode(' ', $tmp);
			}
		}

		if (!is_executable($phpExeFile)) {
			throw new Exception($phpExeFile . ' is not executable.');
		}

		$pipes = array();
		$descriptorspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$fp = proc_open(
				$phpExeFile . " " . $phpScriptFilePath
				, $descriptorspec
				, $pipes
		);
		if (!is_resource($fp)) {
			throw new RuntimeException('无法执行 ' . $phpScriptFilePath);
		}

		$retvl = null;
		try {
			if (null !== $arguments) {
				fwrite($pipes[0], json_encode($arguments));
				fclose($pipes[0]);
			}

			// 子进程标准输出
			$resultData = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			// 子进程的标准错误输出
			$errMsg = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			proc_close($fp);

			if ('' === $resultData)
				$retvl = '';
			else {
				$tmp = json_decode($resultData, true);
				if (empty($tmp) OR ($tmp == $resultData))
					$retvl = $resultData;
				else
					$retvl = $tmp;
			}

			if (!empty($errMsg))
				return $errMsg;
		} catch (Exception $ex) {
			if (is_resource($fp))
				proc_close($fp);
			throw $ex;
		}

		return $retvl;
	}

	/**
	 * 在子进程中，调用此方法可以获得主进程传递过来的参数
	 *
	 * @return void|mixed
	 */
	public static function getArgumentsFromMainProcess() {
		$stdin = fgets(STDIN);
		if (empty($stdin))
			return null;
		else
			return json_decode($stdin, true);
	}

	/**
	 * 根据设定的几率，随机从数组中抽取数组下标
	 * @param array $arrayRand array(array(值,几率))
	 * @param int baseNum 随机基数 默认为10000
	 * @return int 数组下标
	 */
	public static function getRandIndex(array $arrayRand, $baseNum = 10000) {
		$pbList = array();
		$lastPb = 0;
		foreach ($arrayRand as $item) {
			$tempPB = (int) ($lastPb + ($item[1] * $baseNum));
			$pbList[] = $tempPB;
			$lastPb = $tempPB;
		}
		$randNum = mt_rand(0, $lastPb);
		$targetIndex = 0;
		foreach ($pbList as $pbNum) {
			if ($randNum <= $pbNum) {
				break;
			}
			$targetIndex++;
		}

		return $targetIndex;
	}

	/**
	 * 根据设定的几个率，随机从数组中抽取N个值
	 * @param array $arr array(value1=>几率,value2=>几率,value3=>几率...)
	 * @param int $num 抽取元素的个数
	 * @return array(value1,value2)
	 */
	public static function getRandValues($arr, $num) {
		/**
		 * 根据设定的权重，循环N次去抽取值，抽取后删除此值
		 */
		$result = array();
		if (empty($arr))
			return $result;
		$num = $num > count($arr) ? count($arr) : $num;
		for ($i = 0; $i < $num; $i++) {
			$totalRate = 0;
			$rateList = array();
			foreach ($arr as $value => $rate) {
				$totalRate += $rate;
				$rateList[] = $totalRate;
			}
			if ($totalRate > 0) {
				$randNum = mt_rand(1, $totalRate);
				$arrCount = count($arr);
				$targetIndex = 0;
				foreach ($arr as $value => $rete) {
					//抽中
					if ($randNum <= $rateList[$targetIndex]) {
						$result[] = $value;
						unset($arr[$value]);
						break;
					}
					$targetIndex++;
				}
			}
		}
		return $result;
	}

	/**
	 * 将普通数转换成Map形式，便于检索数据
	 * @param array $arr
	 * @param string $keyName 用此值当为map的key
	 */
	public static function arrayToMap(array $arr, $keyName) {
		$map = array();
		foreach ($arr as $vo) {
			$map[$vo[$keyName]] = $vo;
		}
		return $map;
	}

	/**
	 * 获取数组中指定数量的随机数
	 * 
	 * @param array $array 数组
	 * @param int $num 随机数量
	 * @return int | array
	 */
	public static function getArrayRand($array, $num = 1) {
		$num = (int) $num;
		if ($num == 1) {
			$luckKey = array_rand($array);
			return $array[$luckKey];
		}
		$count = count($array);
		if ($num > 1 && $count >= $num) {
			$luckKeys = array_rand($array, $num);
			$retval = array();
			foreach ($luckKeys as $key) {
				if (isset($array[$key])) {
					$retval[] = $array[$key];
				}
			}
		} else {
			return $array;
		}
		return $retval;
	}

	/**
	 * 并发调用多个URL
	 * @param unknown_type $urls
	 * @return boolean|multitype:string
	 */
	public static function remote($urls) {
		if (!is_array($urls) or count($urls) == 0) {
			return false;
		}

		$curl = $text = array();
		$handle = curl_multi_init();
		foreach ($urls as $k => $v) {
			$curl[$k] = curl_init($v);
			curl_setopt($curl[$k], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl[$k], CURLOPT_HEADER, 0);
			curl_multi_add_handle($handle, $curl[$k]);
		}

		$active = null;
		do {
			$mrc = curl_multi_exec($handle, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($handle) != -1) {
				do {
					$mrc = curl_multi_exec($handle, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}

		foreach ($curl as $k => $v) {
			if (curl_error($curl[$k]) == "") {
				$text[$k] = (string) curl_multi_getcontent($curl[$k]);
			}
			curl_multi_remove_handle($handle, $curl[$k]);
			curl_close($curl[$k]);
		}
		curl_multi_close($handle);
		return $text;
	}

	/**
	 * 获取加key后的Url
	 * 
	 * @param unknown_type $apiUrl 模块的接口地址
	 * @return 
	 */
	public static function getOpenApiUrl($apiUrl) {
		$globConfig = GlobalConfig::getInstance();
		$url = $globConfig['openApiUrl'];
		return $url . $apiUrl . '?key=' . $globConfig['openApiKEY'];
	}

	/**
	 * 用DZ中的authcode进行加密
	 * @param string $string 明文
	 * @param string $key 密钥
	 * @param int $expiry 密文有效期
	 * @return string 密文
	 */
	public static function authEncode($string, $key, $expiry = 0) {
		return static::authcode($string, 'ENCODE', $key, $expiry = 0);
	}

	/**
	 * 用DZ中的authcode进行解密
	 * @param string $string 密文
	 * @param string $key 密钥
	 * @return string 明文
	 */
	public static function authDecode($string, $key) {
		return static::authcode($string, 'DECODE', $key);
	}

	/**
	 * DZ中的加密方法
	 * @param string $string 密文名明文
	 * @param string $operation DECODE = 解密,ENCODE=加密
	 * @param string $key 密钥
	 * @param int $expiry 密文有效期
	 */
	public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
		// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
		// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
		// 当此值为 0 时，则不产生随机密钥
		$ckey_length = 4;

		// 密匙
		$key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

		// 密匙a会参与加解密
		$keya = md5(substr($key, 0, 16));
		// 密匙b会用来做数据完整性验证
		$keyb = md5(substr($key, 16, 16));
		// 密匙c用于变化生成的密文
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
		// 参与运算的密匙
		$cryptkey = $keya . md5($keya . $keyc);
		$key_length = strlen($cryptkey);
		// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
		// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		// 产生密匙簿
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		// 核心加解密部分
		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			// 从密匙簿得出密匙进行异或，再转成字符
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if ($operation == 'DECODE') {
			// substr($result, 0, 10) == 0 验证数据有效性
			// substr($result, 0, 10) - time() > 0 验证数据有效性
			// substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
			// 验证数据有效性，请看未加密明文的格式
			if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
			// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
			return $keyc . str_replace('=', '', base64_encode($result));
		}
	}

	/**
	 * 获取明天的时间戳
	 */
	public static function getTomorrowTime() {
		$now = self::getCurrentTimeStamp();
		$tomorrow = $now + 3600 * 24; //明天
		$str = date('Y-m-d', $tomorrow);
		return strtotime($str);
	}

	/**
	 * 获取下一个时间点的unix时间戳
	 * 如求当前为晚上2012-04-13 11点，求下一个晚上10点的时间戳即为2012-04-14 22
	 * @param unknown_type $h
	 */
	public static function getNextTime($h) {
		$now = self::getCurrentTimeStamp();
		if (date('H', $now) >= $h) {
			$tomorrow = $now + 3600 * 24; //明天
			return strtotime(date('Y-m-d', $tomorrow) . " {$h}:00:00");
		} else {
			return strtotime(date('Y-m-d', $now) . " {$h}:00:00");
		}
	}

	/**
	 * 获取第二天零时时间戳
	 * @return int
	 */
	public static function getNextDayZeroHourTime($timestamp) {
		$day = strtotime(date('Y-m-d', $timestamp) . ' 00:00:00');
		return $day + 60 * 60 * 24;
	}

	/**
	 * 将秒数转换成字符串
	 * @param int $second
	 * @return string
	 */
	public static function time2string($second) {
		if ((int) $second > 0) {
			$string = '';
			$day = floor($second / (3600 * 24));
			//除去整天后剩余的时间
			$second = $second % (3600 * 24);
			$hour = floor($second / 3600);
			//除去整小时剩余的时间
			$second = $second % 3600;
			$minute = floor($second / 60);
			//除去整分钟剩余的时间
			$second = $second % 60;

			if ($day)
				$string .= $day . '天';

			if ($hour)
				$string .= $hour . ':';

			$string .= str_pad($minute, 2, 0, STR_PAD_LEFT) . ':' . str_pad($second, 2, 0, STR_PAD_LEFT);
			return $string;
		}
	}

}

