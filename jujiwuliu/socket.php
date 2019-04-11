<?php
set_time_limit(0);
ob_end_clean();
ob_implicit_flush();
error_reporting(E_ALL);//关闭报错
header('X-Accel-Buffering: no'); // 关键是加了这一行。
require dirname(__FILE__) . '/../../framework/bootstrap.inc.php';
if (!function_exists('socket')) {
	function socket($name)
	{
		static $_plugins = array();
		
		if (isset($_plugins[$name])) {
			return $_plugins[$name];
		}

		$socket = IA_ROOT . '/addons/jujiwuliu/sockets/' . strtolower($name) . '_socket.php';
		if (!is_file($socket)) {
			return false;
		}

		require_once $socket;
		$class_name = ucfirst($name) . 'Socket';

		if (!class_exists($class_name)) {
			return false;
		}
	
		$_plugins[$name] = new $class_name($name);

		return $_plugins[$name];
	}
}

$server = new Socket();
class Socket 
{
	public $server;
	public function __construct() 
	{
		global $_W;

		
		echo '现在是：'.date('H:i:s')."\r\n";

		if (!class_exists('swoole_websocket_server') && (!function_exists('swoole_websocket_server') || is_error(swoole_websocket_server()))) {	
			return $this->error('Swoole扩展未安装或服务未启动');
		}
		if (!class_exists('redis') && (!function_exists('redis') || is_error(redis()))) {
			return $this->error('Redis扩展未安装或服务未启动');
		}

		$_this = $this;
		
		$server = new swoole_websocket_server("127.0.0.1", 39001);
		
		$server->on('open', function($server, $req) {
		    echo "connection open: {$req->fd}\r\n";
		});
		$server->on('workerstart', function($server, $id) use(&$_W) {
			if ($id != 0) {
				return false;
			}

			$redis_config = $_W['config']['setting']['redis'];

			if (empty($redis_config['server'])) {
				$redis_config['server'] = '127.0.0.1';
			}

			if (empty($redis_config['port'])) {
				$redis_config['port'] = '6379';
			}

			$redis = new redis();

			if ($redis_config['pconnect']) {
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

			
			
//			if (!$server->redis->exists("cache:".$commentid)) //模拟守护进程 只启动一次  (这里有个bug  就是进程运行的生活 redis 会报错  get方法不存在 ) 目前还没想到解决办法 EdisonLiu_
//			{
//				$server->redis->set('guard_start',1);
//				$plugin_scene=socket('guard'); 
//				$result = $plugin_scene->onMessage($server);
//				echo "received message: 启动守护进程\r\n";
//				unset($plugin_scene);	
//			}
			$server->redis = $redis;
		});
		$server->on('message', function($server, $frame)use(&$_W, &$_this)  {
			$data = json_decode($frame->data, true);//解析前台发过来的数据
			$data = $_this->special($data);
			$_W['uniacid'] = $data['uniacid'];

			$plugin_scene=socket('guard'); 
			$server = $plugin_scene->onMessage($server);
			
			$_this->log('msg_server', json_encode($frame));
			
			//缓存用户信息 以及$fd 标识
			//添加用户队列
			$user_redis=$server->redis->get('user_'.$data['uid'].'_'.$data['uniacid']);
			echo 'user_'.$data['uid'].'_'.$data['uniacid'];
			
//			if(empty($user_redis)){
				$cache_user['fd']=$frame->fd;
				$cache_user['uniacid']=$data['uniacid'];
				$cache_user['uid']=$data['uid'];
				$server->redis->set('user_'.$data['uid'].'_'.$data['uniacid'], serialize($cache_user));
//			}
			
			if (empty($data) || empty($data['scene'])) 
			{
				return $_this->error('scene值为空', 1);
			}
			if ($data['scene'] == 'reload') //重新加载服务
			{
				$server->reload();
				return $_this->error('reload服务', 1);
			}
			
			
			
			if (empty($data['uniacid'])) //如果公众号id为空
			{
				return $_this->error('uniacid值为空', 1);
			}
			
//			while(1){
//				sleep(3);
//				$user=$server->redis->get('user_6_3');
//				$user=unserialize($user);
//				print_r($user);
//				$sendArr['aaa']=1231231;
//				$this->push_send($server, $user['fd'], $sendArr);
//				
//				echo "发送队列消息";
//			}
			
//			$pid=getmypid();
//			print_r($pid);
//			exec("kill -9 ".$pid,$op,$status);
//			die;
			$plugin_scene=socket($data['scene']);
			if(empty($plugin_scene)){
				return $_this->error('不存在这个服务'.$data['scene'], 1);
			}
			$result = $plugin_scene->onMessage($server, $data, $frame->fd);
			unset($plugin_scene);
//			if ($result) 
//			{
//				$_this->setScene($frame->fd, $data);
//			}
			
		    echo "received message: {$frame->data}\r\n";
//		    $server->push($frame->fd, json_encode(array("hello", "world")));
		});
		
		$server->on('close', function($server, $fd) {
		    echo "connection close: {$fd}\r\n";
		});
		$this->server = $server;
		$this->server->start();
	}
	/**
     * 向单个用户发送消息
     * @param $server
     * @param $data
     * @return bool
     */
	protected function push_send($server = NULL, $fd = 0, $data = array())
	{
		if (empty($server) || empty($fd) || empty($data)) {
			return false;
		}
		if (is_array($data)) {
			if (!isset($data['time'])) {
				$data['time'] = time();
			}
			$data = json_encode($data);
		}
		
		$result = $server->push($fd, $data);

		return $result;
	}
	public function error($msg, $type = 0) 
	{
		echo $msg . "\n";
	}

	public function log($name, $text) 
	{
		$filename = dirname(__FILE__) . '/sockets/log_' . $name . '.log';
		$text = '[' . date('Y-m-d H:i:s', time()) . '] ' . $text;
		file_put_contents($filename, $text . "\r\n", FILE_APPEND);
		
	}
//	public function setScene($fd, $scene) 
//	{
//		$scene = ((is_array($scene) ? json_encode($scene) : $scene));
//		$this->redis->hSet($this->tablename, $fd, $scene);
//		return true;
//	}
	public function special($obj) 
	{
		if (!(is_array($obj))) 
		{
			$obj = stripslashes($obj);
			$obj = htmlspecialchars($obj);
		}
		else 
		{
			foreach ($obj as $k => &$v ) 
			{
				$v = stripslashes($v); 
				$v = htmlspecialchars($v);
			}
		}
		return $obj;
	}
}


