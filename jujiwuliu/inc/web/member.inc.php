<?php

global $_W, $_GPC;
		
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if($op=='display'){
	if(!empty($_GPC['type'])){
		$type = $_GPC['type']==2 ? 0 : $_GPC['type'];
		$condition.=' and type='.$type;
	}
	$keyword = $_GPC['keyword'];
	if(!empty($keyword)){
		$condition.=' and (nickname like "%'.$keyword.'%" or mobile like "%'.$keyword.'%" or realname like "%'.$keyword.'%")';
	}
	$searchtime = intval($_G['searchtime']);
	if(!empty($searchtime)){
        $starttime = strtotime($_GPC['time']['start']);
        $endtime = strtotime($_GPC['time']['end']);
        $condition .= " and (createtime between {$starttime} and {$endtime})";
    }

	$pindex = max(1,intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabmember).' where uniacid ='.$_W['uniacid'].$condition);
	$sql = 'select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].$condition.' order by id desc';
	if(empty($_GPC['export'])){
	    $sql .= ' limit ' . ($pindex-1) * $psize . ',' . $psize;
    }
	$list = pdo_fetchall($sql);
	$pager = pagination($total, $pindex, $psize);
	if($_GPC['export'] == 1){
	    set_time_limit(0);
	    foreach ($list as &$item){
	        $item['type_str'] = $this->type_set[$item['type']];
        }
        unset($item);
        require_once IA_ROOT . '/addons/jujiwuliu/excel.php';
        $excel = new Excel();
        $excel->export($list, array(
            'title' => '会员信息',
            'columns' => array(
                array('title' => 'id', 'field' => 'id', 'width' => 12),
                array('title' => '昵称', 'field' => 'nickname', 'width' => 20),
                array('title' => '真实姓名', 'field' => 'realname', 'width' => 20),
                array('title' => '手机号码', 'field' => 'mobile', 'width' => 12),
                array('title' => '会员身份', 'field' => 'type_str', 'width' => 12),
                array('title' => '余额', 'field' => 'credit2', 'width' => 12),
                array('title' => '积分', 'field' => 'credit1', 'width' => 12)
            )
        ));
    }
}

if($op=='post'){
	$id = intval($_GPC['id']);
	$item = pdo_fetch('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' and id='.$id);
	if($_W['ispost']){
		$data=array(
			'realname' => $_GPC['realname'],
			'mobile' => $_GPC['mobile'],
			'updatetime' => time()
		);
		pdo_update($this->tabmember, $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
		message('更新成功！', referer(), 'success');
	}
}

if($op=='del'){
	set_time_limit(0);
	$id=intval($_GPC['id']);
	$member = pdo_fetch('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0');
	if(!empty($member)){
		pdo_delete($this->tabmember, array('id' => $id, 'uniacid' => $_W['uniacid']));
		//插入消息队列
		//$user 用户信息
		//$msg 消息内容
		//$attach  附加信息（如前台更新缓存）
		$data['user']=$member;
		$data['attach']['type']='user_del';
		$data['msg']='您的用户不存在或者已被管理员删除，请重新注册！';
		

		$this->redis->lpush('msg_queue',serialize($data));//入队
		
		//下面是验证出队是否成功的代码
		//				$msg_queue=$this->redis->Rpop('msg_queue');//出队
//				$msg_queue=unserialize($msg_queue);
//				var_dump($msg_queue['user']['uid']);
//				$user=$this->redis->get('user_'.$msg_queue['user']['uid'].'_'.$msg_queue['user']['uniacid']);
//				$user=unserialize($user);
		
//				var_dump($user);
//				die;
		
		message('删除成功！', referer(), 'success');
	}else{
		message('不存在或已删除！', referer(), 'warning');
	}
}
if($op=='recharge'){
	$id = intval($_GPC['id']);
	load()->model('mc');
	if(!empty($id)){
		$user=pdo_get($this->tabmember,array('id'=>$id,'uniacid'=>$_W['uniacid']));
		$uid = mc_openid2uid($user['openid']);
	}
	if(empty($user)){
		message('未查询到此用户信息!', referer() , 'error');
	}
	if(checksubmit('submit')){
		$creditval=floatval($_GPC['re_num']);
		$credittype=intval($_GPC['credittype']);
		if($credittype==1){
			$credittype='credit1';
			$credittype_text='积分';
			if(is_numeric($_GPC['re_num'])&& strpos($_GPC['re_num'],'.')){
				message('积分只支持整数！', referer() , 'error');
			}
		}elseif($credittype==2){
			$credittype='credit2';
			$credittype_text='余额';
		}else{
			message('请选择正确的充值类型', referer() , 'error');
		}
		
		if($creditval<0.01&&$creditval>-0.01){ //0.01 和-0.01之前的数
			message('请输入正确的数字！', referer() , 'error');
		}
		/*
		 *uid 
		*$credittype credit1  credit2
		*$creditval float
		*$is_ret 是返回还是直接message提示
		* $log = array(
			0 => 操作管理员uid
			1 => 增减积分备注
			2 => 模块标识，例如：we7_store
			3 => 店员uid
			4 => 门店id
			5 => 1(线上操作) 2(系统后台,公众号管理员和操作员) 3(店员)  4系统自动处理
		 *  6 => 订单号
			);
		 * */
		$log[0]=$_W['user']['id'];
		$log[1]='后台'.($creditval>0?'充值':'减少').$credittype_text;
		$log[2]=$_GPC['m'];
		$log[5]=2;

		$ret=$this->credit_update($uid, $credittype, $creditval, $log ,$is_ret=false);
		if($ret){
			message('操作成功！', referer() , 'success');
		}else{
			message('操作失败！', referer() , 'error');
		}
	}

	include $this->template('member/recharge');
	die;
}


include $this->template('member');
