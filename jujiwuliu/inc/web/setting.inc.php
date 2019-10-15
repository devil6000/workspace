<?php
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	
if($op=='display'){
	

}
	$item = pdo_fetch('select * from '.tablename($this->tabsetting).' where uniacid='.$_W['uniacid'].' and uniacid='.$_W['uniacid']);
	$item['rolling_diagram']=unserialize($item['rolling_diagram']);
	$item['share_img']=unserialize($item['share_img']);
	$item['rolling_areas'] = unserialize($item['rolling_areas']);
	$item['rolling_advises'] = unserialize($item['rolling_advises']);
	if(checksubmit('submit')){
		if($op=='display'){
			$data['rolling_diagram']=serialize($_GPC['rolling_diagram']);
			$data['share_img']=serialize($_GPC['share_img']);
			$data['share_title']=$_GPC['share_title'];
			$data['rolling_time']=$_GPC['rolling_time'];
			$data['rolling_interval_time']=$_GPC['rolling_interval_time'];
			$data['rolling_areas'] = serialize($_GPC['rolling_areas']);
			$data['rolling_advises'] = serialize($_GPC['rolling_advises']);
		}
		//搬运设置
		if($op=='carry'){
			//平台设置
			$data['pumping']=$_GPC['pumping'];
			//发布设置
			$data['dock_light']=$_GPC['dock_light'];
			$data['dock_moderate']=$_GPC['dock_moderate'];
			$data['dock_heavy']=$_GPC['dock_heavy'];
			$data['temporary_light']=$_GPC['temporary_light'];
			$data['temporary_moderate']=$_GPC['temporary_moderate'];
			$data['temporary_heavy']=$_GPC['temporary_heavy'];
			$data['issue_cancel_time']=$_GPC['issue_cancel_time'];
			$data['auto_settle_time'] = intval($_GPC['auto_settle_time']);
			
			//接单设置
			$data['security']=$_GPC['security'];
			$data['pre_sales_penalty']=$_GPC['pre_sales_penalty'];
			$data['customer_service_penalty']=$_GPC['customer_service_penalty'];
			$data['issue_worker_time']=$_GPC['issue_worker_time'];
			$data['auto_cancel_time'] = $_GPC['auto_cancel_time'];
		}
		//使用说明
        if($op == 'explain'){
            $data['explain'] = $_GPC['explain'];
            $data['explain_receipt'] = $_GPC['explain_receipt'];
        }
        //协议
        if($op == 'agree'){
            $data['agree_on'] = $_GPC['agree_on'];
        }
		$res=pdo_update($this->tabsetting,$data,array('uniacid'=>$_W['uniacid']));
		if(!empty($res)){
			message('保存成功！', referer(), 'success');

		}else{
			message('保存失败，请重试！', referer(), 'error');
			
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
	$id=intval($_GPC['id']);
	$member = pdo_fetch('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0');
	if(!empty($member)){
		pdo_delete($this->tabmember, array('id' => $id, 'uniacid' => $_W['uniacid']));
		message('删除成功！', referer(), 'success');
	}else{
		message('不存在或已删除！', referer(), 'warning');
	}
}

include $this->template('setting');
?>