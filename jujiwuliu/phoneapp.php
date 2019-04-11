<?php
/**
 * nearby_stores模块APP接口定义
 *
 * @author EdisonLiu_
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class jijuwuliuModulePhoneapp extends WeModulePhoneapp {
	public function doPageTest(){
		global $_GPC, $_W;
		$errno = 0;
		$message = '返回消息';
		$data = array();
		return $this->result($errno, $message, $data);
	}
	 
	
}