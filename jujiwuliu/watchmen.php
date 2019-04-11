<?php
//普通守护进程 EdisonLiu_
set_time_limit(0);
ob_end_clean();
ob_implicit_flush();
error_reporting(E_ALL);//关闭报错
header('X-Accel-Buffering: no'); // 关键是加了这一行。
require dirname(__FILE__) . '/../../framework/bootstrap.inc.php';
global $_W,$_GPC;
$_W['uniacid']=$_GPC['i'];
$server = new Socket();
class Socket 
{
	public $server;
	public function __construct() 
	{
		global $_W,$_GPC;
		echo '现在是：'.date('H:i:s')."\r\n";
		if (!class_exists('redis') && (!function_exists('redis') || is_error(redis()))) {
			return $this->error('Redis扩展未安装或服务未启动');
		}
		$redis_config = $_W['config']['setting']['redis'];

		if (empty($redis_config['server'])) {
			$redis_config['server'] = '127.0.0.1';
		}

		if (empty($redis_config['port'])) {
			$redis_config['port'] = '6379';
		}

		$redis = new redis();

		if ($redis_config['pconnect']) {//是否长连接
			$connect = $redis->pconnect($redis_config['server'], $redis_config['port'], $redis_config['timeout']);
		}
		else {
			$connect = $redis->connect($redis_config['server'], $redis_config['port'], $redis_config['timeout']);
		}

		if (!$connect) {
			return false;
		}

		if (!empty($redis_config['requirepass'])) {
			$redis->auth($redis_config['requirepass']);
		}

		try {
			$ping = $redis->ping();
		}
		catch (ErrorException $e) {
			return false;
		}

		if ($ping != '+PONG') {
			return false;
		}
		$this->redis = $redis;
		$this->init();
	}
	public function init(){
		$this->release_expire();//订单未接完退款
		
//		$this->finish_order();//订单完成打款
		die;//执行完所有 直接结束进程
	}
	public function release_expire(){
		global $_W,$_GPC;
		echo $_W['uniacid'];
		$data = pdo_fetchall("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=".$_W['uniacid']." and status=0 and starttime<=".time()." and pay_status=1 ");
		foreach ($data as $key => $value) {
			//查询接单人数	
			$order = pdo_fetchall("SELECT * FROM ".tablename($this->taborder)." WHERE uniacid=".$_W['uniacid']." and deposit_paystatus=1 and rid=".$value['id']);//已支付押金的订单
			//剩余数量
			$residue=count($order)-$value['nums'];
			if($residue>0){//退部分款项
				
			}else if(count($order)==0){//没有人接单 直接取消发布 并退款
				
			}
			
			
			//如果没有满退款
		}

		
		
	}
	public function error($msg, $type = 0) 
	{
		echo $msg . "\n";
	}

	public function log($name, $text) 
	{
		$filename = dirname(__FILE__) . '/data/log_' . $name . '.log';
		$text = '[' . date('Y-m-d H:i:s', time()) . '] ' . $text;
		file_put_contents($filename, $text . "\r\n", FILE_APPEND);
		
	}
}


