<?php
/**
 * 订单操作
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/10
 * Time: 11:47
 */

error_reporting(0);
require '../../../framework/bootstrap.inc.php';
global $_W;
global $_GPC;
ignore_user_abort();
set_time_limit(0);
$setting = pdo_fetch('select * from ' . tablename('jujiwuliu_setting') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));

$day = empty($setting['auto_cancel_time']) ? 48 : $setting['auto_cancel_time'];
$cancel_time = $day * 3600;
$order = pdo_fetchall('select * from ' . tablename('jujiwuliu_order') . ' where uniacid=:uniacid and status in (0,1)');
foreach ($order as $item){
    $time =  time() - $item['createtime'];
    if($time > $cancel_time){
        $update = array(
            'can_refund_bond' => 0,
            'status' => 4
        );
        pdo_update('jujiwuliu_order', $update, array('id' => $item['id']));
    }
}