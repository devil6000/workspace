
<?php
global $_W, $_GPC;

$op = !empty($_GPC['op']) ? $_GPC['op'] : 'credit2';
$type = $_GPC['type'];
if($type==2){
    $condition.=' and num>0';
}elseif($type==1){
    $condition.=' and num<0';
}

if($op=='credit2'){
    $pindex = max(1,intval($_GPC['page']));
    $psize = 5;
    $keyword = $_GPC['keyword'];
    if(!empty($keyword)){
        $condition.=' and (id ='.$keyword.'")';
    }
   
    $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabcreditlog).' where credittype="credit2" and uniacid ='.$_W['uniacid'].' '.$condition);
    $list = pdo_fetchall('select * from '.tablename($this->tabcreditlog).' where credittype="credit2" and uniacid='.$_W['uniacid'].' '.$condition.' order by id desc LIMIT '.($pindex-1)*$psize.','.$psize);
    foreach($list as & $res){
        $res['user']=pdo_get($this->tabmember,array('uid'=>$res['uid'],'uniacid'=>$_W['uniacid']));
        $res['release']['images']=unserialize($res['release']['images']);
    }

    $pager = pagination($total, $pindex, $psize);
}

if($op=='credit1'){
    $pindex = max(1,intval($_GPC['page']));
    $psize = 5;
    $keyword = $_GPC['keyword'];
    if(!empty($keyword)){
        $condition.=' and (id ='.$keyword.'")';
    }
   
    $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabcreditlog).' where credittype="credit1" and uniacid ='.$_W['uniacid'].' '.$condition);
    $list = pdo_fetchall('select * from '.tablename($this->tabcreditlog).' where credittype="credit1" and uniacid='.$_W['uniacid'].' '.$condition.' order by id desc LIMIT '.($pindex-1)*$psize.','.$psize);
    foreach($list as & $res){
        $res['user']=pdo_get($this->tabmember,array('uid'=>$res['uid'],'uniacid'=>$_W['uniacid']));
        $res['release']['images']=unserialize($res['release']['images']);
    }

    $pager = pagination($total, $pindex, $psize);
}


if($op=='withdraw'){
    $pindex = max(1,intval($_GPC['page']));
    $psize = 5;
    $keyword = $_GPC['keyword'];
    if(!empty($keyword)){
        $condition.=' and (id ='.$keyword.'")';
    }
   
    $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabglide).' where type=2 and uniacid ='.$_W['uniacid'].' '.$condition);
    $list = pdo_fetchall('select * from '.tablename($this->tabglide).' where type=2 and uniacid='.$_W['uniacid'].' '.$condition.' order by id desc LIMIT '.($pindex-1)*$psize.','.$psize);
    foreach($list as & $res){
        $res['user']=pdo_get($this->tabmember,array('uid'=>$res['uid'],'uniacid'=>$_W['uniacid']));
        $res['release']['images']=unserialize($res['release']['images']);
    }

    $pager = pagination($total, $pindex, $psize);
}
if($op=='recharge'){
    $pindex = max(1,intval($_GPC['page']));
    $psize = 5;
    $keyword = $_GPC['keyword'];
    if(!empty($keyword)){
        $condition.=' and (id ='.$keyword.'")';
    }
   
    $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabglide).' where type=1 and status=1  and uniacid ='.$_W['uniacid'].' '.$condition);
    $list = pdo_fetchall('select * from '.tablename($this->tabglide).' where type=1 and status=1 and uniacid='.$_W['uniacid'].' '.$condition.' order by id desc LIMIT '.($pindex-1)*$psize.','.$psize);
    foreach($list as & $res){
        $res['user']=pdo_get($this->tabmember,array('uid'=>$res['uid'],'uniacid'=>$_W['uniacid']));
        $res['release']['images']=unserialize($res['release']['images']);
    }

    $pager = pagination($total, $pindex, $psize);
}

if($op=='dispose'){//驳回提现
    //查订单
    $dis_type=$_GPC['type'];
    $id=$_GPC['id'];
    $item = pdo_fetch('select * from '.tablename($this->tabglide).' where id='.$id.' and status=0 and uniacid='.$_W['uniacid']);
    if(empty($item)){
        message('未查询到此提现信息,或此信息已处理!','','error');
        die;
    }
    if($dis_type=='reject' ){//驳回
        $data['status']=2;
        //退款
        $log=array(
            1,
            '管理员驳回提现,退回余额',
            $this->module['name'],
            '',
            '',
            4,
            $item['ordersn']
        );
        
        $res_upcredit=$this->credit_update($item['uid'], 'credit2', $item['money'], $log,true);

    }
    elseif($dis_type=='alipay' || $dis_type=='bank'){//支付宝 和打款 直接修改状态
        $data['status']=1;
        $data['pay_type']=$dis_type=='alipay'?3:4;
    }
    elseif($dis_type=='wechat' ){//微信打款方法(开发中)
        $data['status']=1;
        $data['pay_type']=1;
        message('微信提现功能未开通!',$this->createWebUrl('finance',array('op'=>'withdraw')),'success');
        die;
    }
    pdo_update($this->tabglide,$data,array('uniacid'=>$_W['uniacid'],'id'=>$id));
    message('操作成功!',$this->createWebUrl('finance',array('op'=>'withdraw')),'success');
   die;
    
}



include $this->template('finance');