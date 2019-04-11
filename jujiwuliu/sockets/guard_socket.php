<?php
class guardSocket
{
	
	/**
     * @param $server
     * @param $data
     */
     
     	public function onMessage($server,$data=array(), $fd='')
	{
		global $_W;
		$msg_queue=$server->redis->Rpop('msg_queue');
		$msg_queue=unserialize($msg_queue);

		while(!empty($msg_queue)){
			//通过消息队列查找用户在线标识 并推送队列里的消息
//			$user=$server->redis->get('user_6_3');
			if(!empty($msg_queue)){
				$user=$server->redis->get('user_'.$msg_queue['user']['uid'].'_'.$msg_queue['user']['uniacid']);
	
				$user=unserialize($user);
				print_r($user);
				//这里读取消息队列的内容  进行发送
				$this->send($server, $user['fd'], $msg_queue);
				echo "发送队列消息";
				
				$msg_queue=$server->redis->Rpop('msg_queue');
				$msg_queue=unserialize($msg_queue);
			}
			
		}
		return $server;
	}
	
//	public function onMessage($server,$data=array(), $fd='')
//	{
//		
//		global $_W;
//		$msg_queue=$server->redis->Rpop('msg_queue');
//		$msg_queue=unserialize($msg_queue);
//		var_dump($msg_queue)；
//		while(!empty($msg_queue)){
//			//通过消息队列查找用户在线标识 并推送队列里的消息
////			$user=$server->redis->get('user_6_3');
//			if(!empty($msg_queue)){
//				$user=$server->redis->get('user_'.$msg_queue['user']['uid'].'_'.$msg_queue['user']['uniacid']);
//	
//				$user=unserialize($user);
//				print_r($user);
//				//这里读取消息队列的内容  进行发送
//				$this->send($server, $user['fd'], $msg_queue);
//				echo "发送队列消息";
//				
//				$msg_queue=$server->redis->Rpop('msg_queue');
//				$msg_queue=unserialize($msg_queue);
//			}
//			
//		}
////		//获取队列长度  Llen
////		$msg_Llen=$server->redis->Llen('msg_queue');
////		for($i=0,$i<$msg_Llen,$++){
////			//通过消息队列查找用户在线标识 并推送队列里的消息
//////			$user=$server->redis->get('user_6_3');
////			$msg_queue=$server->redis->Rpop('msg_queue');
////			$msg_queue=unserialize($msg_queue);
////			if(!empty($msg_queue)){
////				
////				$user=$server->redis->get('user_'.$msg_queue['user']['uid'].'_'.$msg_queue['user']['uniacid']);
////	
////				$user=unserialize($user);
////				print_r($user);
////				//这里读取消息队列的内容  进行发送
////				$this->send($server, $user['fd'], $msg_queue);
////				echo "发送队列消息";
////				
////				
////			}
////			
////		} 
//		return true;//貌似返回值很重要 
//	}
	
	/**
     * 向单个用户发送消息
     * @param $server
     * @param $data
     * @return bool
     */
	protected function send($server = NULL, $fd = 0, $data = array())
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
	public function special($obj)
	{
		if (!is_array($obj)) {
			$obj = istripslashes($obj);
			$obj = ihtmlspecialchars($obj);
		}
		else {
			foreach ($obj as $k => &$v) {
				$v = istripslashes($v);
				$v = ihtmlspecialchars($v);
			}
		}

		return $obj;
	}
}
require_once __DIR__.'/pdo.php';

?>