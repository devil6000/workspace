<?php
class userSocket
{
	/**
     * @param $server
     * @param $data
     */
	public function onMessage($server, $data, $fd)
	{
		global $_W;
		$data = $this->special($data);
		if (empty($data['type']) || empty($data['uid'] || empty($data['uniacid']))) {
			return false;
		}
		if($data['type']=='verify'){//用户信息验证
			//判断用户是否存在 如果不存在就存储消息队列
			$user=pdo_fetch2('select * from '.tablename('jujiwuliu_members').' where uniacid='.$data['uniacid'].' and uid='.$data['uid']);
//			$user =pdo_get2('jujiwuliu_members',array('uniacid'=>$data['uniacid'],'uid'=>$data['uid']));
			if(empty($user)){
				$data['user']=$data;
				$data['attach']['type']='user_del';
				$data['msg']='您的用户不存在或者已被管理员删除，请重新注册！';
				$server->redis->lpush('msg_queue',serialize($data));//入队
				echo "发送队列消息";
			}
//			$sendArr['status'] = 3;//返回值
//			$this->send($server, $fd, $sendArr);
		}
		if($data['type']=='location'){//转换地理位置
			$map_addr=file_get_contents('https://apis.map.qq.com/ws/geocoder/v1/?location='.$data['lat'].','.$data['lng'].'&key='.$server->map_apikey.'&get_poi=1 ');
			$map_addr=json_decode($map_addr,TRUE);
			$data['nearby_address']=$map_addr['result']['address']?$map_addr['result']['address']:'无';

			
			$data['user']=$data;
			$data['attach']['type']='location';
			$data['msg']='地理位置获取成功！';
			$server->redis->lpush('msg_queue',serialize($data));//入队
			echo "发送队列消息";
		}
		
		return $server;
	}
	
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
	var_dump(IA_ROOT);
?>