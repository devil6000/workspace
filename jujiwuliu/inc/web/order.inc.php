
<?php
global $_W, $_GPC;

$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

$setting=pdo_fetch("SELECT * FROM ".tablename('jujiwuliu_setting')." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));

if($op=='display'){
    $rid=$_GPC['r_id'];
    $keyword = $_GPC['keyword'];
    $condition = 'uniacid=' . $_W['uniacid'] . ' and deleted=0';
    if(!empty($keyword)){
        $openid = pdo_fetchcolumn('select openid from ' . tablename('jujiwuliu_members') . ' where (mobile like :keyword or realname like :keyword or nickname like :keyword) and uniacid=:uniacid', array(':keyword' => '%' . $keyword . '%', ':uniacid' => $_W['uniacid']));
        $condition.= " and openid='" . $openid . "'";
    }
    if(!empty($rid)){
        $condition.=' and (rid ='.$rid.')';
    }
    $status = isset($_GPC['status']) ?  intval($_GPC['status']) : -1;
    if($status > -1){
        $condition .=  ' and status=' . $status;
    }
    $searchtime = intval($_GPC['searchtime']);
    if(!empty($searchtime)){
        $starttime = strtotime($_GPC['time']['start']);
        $endtime = strtotime($_GPC['time']['end']);
        $condition .= " and createtime between {$starttime} and {$endtime}";
    }
    $pindex = max(1,intval($_GPC['page']));
    $psize = 20;
    $total = pdo_fetchcolumn('select count(*) from '.tablename($this->taborder).' where ' . $condition);
    $sql = 'select * from '.tablename($this->taborder).' where '.$condition.' order by rid desc';
    if(empty($_GPC['export'])){
        $sql .= ' LIMIT '.($pindex-1)*$psize.','.$psize;
    }
    $list = pdo_fetchall($sql);
    $nums = array();    //保存数量临时变量
    foreach($list as & $res){
        $res['createtime'] = date('m/d/y H:i', $res['createtime']);
        $res['member'] = pdo_fetch('select * from ' . tablename($this->tabmember) . ' where openid=:openid and uniacid=:uniacid', array(':openid' => $res['openid'], ':uniacid' => $_W['uniacid']));
        $release = pdo_fetch('select * from '.tablename($this->tabrelease).' where uniacid='.$_W['uniacid'].' and id="'.$res['rid'].'"  and deleted=0 ');
        if(!empty($release)){
            $nums[$res['rid']] += 1;    //人数累加
            $res['release']['ordersn'] = $release['ordersn'];
            $res['release']['nums'] = $release['nums'];
            $res['release']['num'] = intval($nums[$res['rid']]);
            $res['release']['money'] = round(floatval($release['count_price']) / intval($release['nums']),2);
            $res['release']['pumping'] = round(floatval($release['count_price']) / intval($release['nums']) * $setting['pumping'] / 100,2);
            $res['release']['total_price'] = $release['total_price'];
        }
        $res['status'] = $this->order_status_set[$res['status']];
    }
    unset($res);

    $pager = pagination($total, $pindex, $psize);

    if($_GPC['export'] == 1){
        set_time_limit(0);
        $export = array();
        foreach ($list as $key => $item){
            $temp = array(
                'ordersn' => $item['release']['ordersn'],
                'nickname' => $item['member']['nickname'],
                'create_time' => str_replace('/','-',$item['createtime']),
                'money' => $item['release']['money'],
                'pumping' => $item['release']['pumping'],
                'deposit' => $item['deposit'],
                'total_price' => $item['release']['total_price'],
                'status_str' => $item['status']
            );
            $export[$key] = $temp;
        }
        require_once IA_ROOT . '/addons/jujiwuliu/excel.php';
        $excel = new Excel();
        $excel->export($export, array(
            'title' => '接单信息',
            'columns' => array(
                array('title' => '接单号', 'field' => 'ordersn', 'width' => 20),
                array('title' => '接单人', 'field' => 'nickname', 'width' => 20),
                array('title' => '接单时间', 'field' => 'create_time', 'width' => 20),
                array('title' => '金额', 'field' => 'money', 'width' => 12),
                array('title' => '抽成', 'field' => 'pumping', 'width' => 12),
                array('title' => '保证金', 'field' => 'deposit', 'width' => 12),
                array('title' => '预计公费', 'field' => 'total_price', 'width' => 12),
                array('title' => '状态', 'field' => 'status_str', 'width' => 12)
            )
        ));
    }
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

    $uid = mc_openid2uid($order['openid']);

    $i = $this->credit_update($uid, 'credit2', $order['deposit'], $log, true);
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
        '系统确认打款',
        $this->module['name'],
        '',
        '',
        4,
        $release['ordersn']
    );

    $uid = mc_openid2uid($order['openid']);

    $setting = pdo_fetch("SELECT * FROM ".tablename($this->tabsetting)." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
    $price = $release['total_price'] - $release['total_price'] * $setting['pumping'] / 100;
    $i = $this->credit_update($uid, 'credit2', $price, $log, true);
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