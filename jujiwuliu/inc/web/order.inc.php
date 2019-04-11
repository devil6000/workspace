
<?php
global $_W, $_GPC;

$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if($op=='display'){
    $rid=$_GPC['r_id'];
    $keyword = $_GPC['keyword'];
    $condition = 'uniacid=' . $_W['uniacid'] . ' and deleted=0';
    if(!empty($keyword)){
        $condition.=' and (id ='.$keyword.')';
    }
    if(!empty($rid)){
        $condition.=' and (rid ='.$rid.')';
    }
    $pindex = max(1,intval($_GPC['page']));
    $psize = 20;
    $total = pdo_fetchcolumn('select count(*) from '.tablename($this->taborder).' where uniacid ='.$_W['uniacid'].' '.$condition.'  and deleted=0  ');
    $list = pdo_fetchall('select * from '.tablename($this->taborder).' where '.$condition.' order by id desc LIMIT '.($pindex-1)*$psize.','.$psize);
    foreach($list as & $res){
        $res['release'] = pdo_fetch('select * from '.tablename($this->tabrelease).' where uniacid='.$_W['uniacid'].' and id="'.$res['rid'].'"  and deleted=0 ');
        
        $res['release']['typename'] = $this->type_set[$res['release']['type']];
        $res['release']['unit'] = $this->unit_set[$res['release']['type']];	
        $res['release']['staticname'] = $this->static_set[$res['release']['static']];
        $res['release']['sex'] = $this->sex_set[$res['release']['sex']];
        $res['release']['statusname'] =  $this->status_set[$res['release']['status']];

        $res['release']['images']=unserialize($res['release']['images']);
    }

    $pager = pagination($total, $pindex, $psize);
}

if($op=='post'){
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename($this->taborder).' where uniacid='.$_W['uniacid'].' and id='.$id);
    if($_W['ispost']){
        $data=array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile'],
            'updatetime' => time()
        );
        pdo_update($this->taborder, $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
        message('更新成功！', referer(), 'success');
    }
}

if($op=='del'){
    $id=intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename($this->taborder).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0');
    if(!empty($order)){
        pdo_update($this->taborder,array('deleted'=>1), array('id' => $id, 'uniacid' => $_W['uniacid']));
        message('删除成功！', referer(), 'success');
    }else{
        message('不存在或已删除！', referer(), 'warning');
    }
}

if($op == 'refund'){
    $id=intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename($this->taborder).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0 and can_refund_bond=1');
    if(empty($order)){
        message('不存在或已删除！', referer(), 'warning');
    }
    $log=array(
        1,
        '接单方申请退保证金',
        $this->module['name'],
        '',
        '',
        4,
        $order['depositsn']
    );
    $i = $this->credit_update($order['openid'], 'credit2', $order['deposit'], $log, true);
    if($i){
        pdo_update($this->taborder, array('deposit_paystatus' => 3), array('id' => $id));
        message('退保证金成功！', referer(), 'success');
    }
}

if($op == 'payapply'){
    $id=intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename($this->taborder).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0 and apply=1');
    if(empty($order)){
        message('不存在或已支付！', referer(), 'warning');
    }

    $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id', array(':id' => $order['rid']));

    $log=array(
        1,
        '发布方确认打款',
        $this->module['name'],
        '',
        '',
        4,
        $release['ordersn']
    );
    $setting = pdo_fetch("SELECT * FROM ".tablename($this->tabsetting)." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
    $price = $release['total_price'] - $release['total_price'] * $setting['pumping'] / 100;
    $i = $this->credit_update($order['openid'], 'credit2', $price, $log, true);
    if($i){
        pdo_update($this->taborder, array('apply' => 2), array('id' => $id));

        $count = pdo_fetchcolumn('select count(id) from ' . tablename($this->taborder) . ' where rid=:id and apply=2 and status=3', array(':id' => $order['rid']));
        /*
        if($count >= $release['nums']){
            pdo_update($this->tabrelease, array('status' => 3), array('id' => $order['rid']));
        }
        */

        message('打款成功！', referer(), 'success');
    }

}

include $this->template('order');