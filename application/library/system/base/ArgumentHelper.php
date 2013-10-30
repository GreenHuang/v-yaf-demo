<?php
namespace system\base;

/**
 * 参数帮助工具类
 * @package system/base/ArgumentHelper.php
 * @author vergil<vergil@vip.163.com>
 */
class ArgumentHelper{
	
	/**
	 * 检测参数是否为标量
	 * 标量变量是指那些包含了 integer、float、string 或 boolean的变量，而 array、object 和 resource 则不是标量
	 * @param mixed $var 参数
	 * @param boolean $throwException 非标题时是否抛出异常
	 * @param string $exceptionMsg 抛出异常时的信息
	 */
	public static function isScalar($var,$throwException=TRUE,$exceptionMsg=null){
		$result = is_scalar($var);
		if($throwException){
			if(! $result){
				throw new \InvalidArgumentException($exceptionMsg==null? '参数 $var 必须是一个标量值。' : $exceptionMsg);    
			}
		}
		return $result;
		
	}
	
	/**
	 * 检测参数是否为标量
	 * 标量变量是指那些包含了 integer、float、string 或 boolean的变量，而 array、object 和 resource 则不是标量
	 * @param unknown_type $var 参数
	 * @param boolean $throwException 非标题时是否抛出异常
	 * @param string $exceptionMsg 抛出异常时的信息
	 */
	public static function isInt($var,$throwException=TRUE,$exceptionMsg=null){
		$result = \GlobalHelper::isNaturalNumber($var);
		if($throwException){
			if(! $result){
				throw new \InvalidArgumentException($exceptionMsg==null? '参数 $var 必须是整数。' : $exceptionMsg);    
			}
		}
		return $result;
	}
	
}