<?php

use system\base\GlobalHelper;

use system\dal\base\QueryFilter;

/**
 * 留言板控制器
 * @author vergil<vergil@vip.163.com>
 */
class GuestbookController extends Yaf_Controller_Abstract {
	
	public function indexAction() {
		$model = new GuestbookModel();
		$lists = $model->getAll(array(), array('create_time'=>'DESC'));
		$this->getView()->assign('lists', $lists);
	}
	
	public function postAction() {
		if(!empty($_POST)) {
			$model = new GuestbookModel();
			$params = $_POST;
			$params['create_time'] = time();
			$params['ip'] = GlobalHelper::getClientIp();
			
			$res = (boolean)$model->insert($params);
			echo $res ? '留言成功' : '留言失败';
			sleep(2);
			GlobalHelper::redirect(GlobalHelper::site_url('guestbook'));
			
			//关闭视图输出
			Yaf_Dispatcher::getInstance()->disableView();
		}
		$str = '这是yaf测试留言板';
		$this->getView()->assign("str", $str);
	}
	
	public function edit($id) {
		//构造查询条件
		$queryFilter = new QueryFilter();
		//查找id=1的记录
		$queryFilter->eq('id', $id);
		
		//实例化模型
		$model = new GuestbookModel();
		$data = $model->fetchOne($queryFilter);
		var_dump($data);
		
		return false;
	}
	
	
	
	
}