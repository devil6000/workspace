<?php
class fabuSocket
{
	/**
     * @param $server
     * @param $data
     */
	public function onMessage($server, $data, $fd)
	{
		global $_W;
		$data = $this->special($data);
		if (empty($data['type']) || empty($data['uid'])) {
			return false;
		}
		if($data['type']=='upload'){//图片上传
			$sendArr['data']=$data['suffix'];
			$filename = dirname(__FILE__) . '/this_is_image.'.$data['suffix'];
			$file_data=base64_decode($data['filedata']);
			file_put_contents($filename, $file_data );
		}
		
		
		
		
		$sendArr['status'] = 3;//返回值
		$this->send($server, $fd, $sendArr);
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
	
?>