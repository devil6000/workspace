<?php
/**
 * 开票管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/17
 * Time: 10:53
 */

global $_W,$_GPC;
$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];

if($op == 'dislpay'){

    $pageIndex = max(1,intval($_GPC['page']));
    $pageSize = 20;

    $condition = 'uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];

    $_GPC['delete'] = !isset($_GPC['delete']) ? -1 : $_GPC['delete'];

    if($_GPC['delete'] > -1){
        $condition .= ' and deleted=:deleted';
        $params[':deleted'] = $_GPC['delete'];
    }

    $count = pdo_fetchcolumn('select count(id) from ' .  tablename($this->tabfirm) . ' where ' . $condition, $params);
    $pager = pagination($count, $pageIndex, $pageSize);

    $list = pdo_fetchall('select * from ' . tablename($this->tabfirm) . ' where ' . $condition . ' order by id desc limit ' . ($pageIndex - 1) * $pageSize . ',' . $pageSize, $params);

}

if($op == 'delete'){
    $id = intval($_GPC['id']);
    pdo_update($this->tabfirm, array('deleted' => 1), array('id' => $id));
    message('删除发票成功', $this->createWebUrl('receipt'), 'success');
}

include  $this->template('receipt');

