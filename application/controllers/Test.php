<?php

class TestController extends Yaf_Controller_Abstract {
	
	/**
	 * 如果定义了控制器的init的方法, 会在__construct以后被调用
	 */
	public function init() {
		//关闭视图输出
		Yaf_Dispatcher::getInstance()->disableView();
	}
	
	public function indexAction() {
		echo 'test';
	}
	
	public function cacheAction() {
		$cache = \system\dal\cache\CacheHelper::getCacheInstance();
		if( ! $name = $cache->get('name') ) {
			echo 'set cache<br />';
			$name = 'vergil';
			$cache->set('name', $name, 10);
		}
		var_dump($name);
	}
	
	public function configAction() {
		$config = Yaf_Registry::get('config')->toArray();
		var_dump($config);
	}
	
	public function routerAction() {
		$router = Yaf_Dispatcher::getInstance()->getRouter();
		$routers = Yaf_Registry::get("config")->routes;
		
		$route = new Yaf_Route_Simple("m", "c", "a");
		$router->addRoute("name", $route);
		
		var_dump($router->getRoutes());
		var_dump($router->getCurrentRoute());
	}
	
	public function urlAction() {
		$url = \system\base\GlobalHelper::site_url('gueskbook/post');
		echo $url;
	}
	
}