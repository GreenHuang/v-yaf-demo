<?php

/**
 * 程序入口文件
 * @author vergil<vergil@vip.163.com>
 */

if( ! extension_loaded('yaf')) exit('<a href="http://pecl.php.net/package/yaf" target="_blank">请先安装Yaf扩展</a>');

define('APPLICATION_PATH',  realpath(dirname(__FILE__) . '/../'));

define ("IS_WINDOWS", DIRECTORY_SEPARATOR == '\\');

$application = new Yaf_Application( APPLICATION_PATH . '/conf/application.ini', 'development');

$application->bootstrap()->run();