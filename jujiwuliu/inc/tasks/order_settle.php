<?php
/**订单自动结算
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/10
 * Time: 14:23
 */

error_reporting(0);
require '../../../framework/bootstrap.inc.php';
global $_W;
global $_GPC;
ignore_user_abort();
set_time_limit(0);
$setting = pdo_fetch('select * from ' . tablename('jujiwuliu_setting') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));

$day = empty($setting['auto_settle_time']) ? 48 : $setting['auto_settle_time'];
$settle_time = $day * 3600;

$order = pdo_fetchall('select * from ' . tablename('jujiwuliu_order') . ' where uniacid=:uniacid and status in (2,3) and apply=1');
foreach ($order as $item){
    $time =  time() - $item['apply_time'];
    if($time > $settle_time){
        $uid = mc_uid2openid($item['openid']);
        $release = pdo_fetch('select * from ' . tablename('jujiwuliu_release') . ' where id=:id and status in(1,2,3)', array(':id' => $item['rid']));
        $money = $release['total_price'];
        $log = array(1,'系统自动打款','','',4,$release['ordersn']);
        $result = mc_credit_update($uid,'credit2', $money, $log);
        if(!is_error($result)){
            //自动打款到接单方余额
            $update = array(
                'apply' => 2,
                'status' => 3
            );
            pdo_update('jujiwuliu_order', $update, array('id' => $item['id']));
        }
    }
}