<?php
/**
 * 区域管理
 * Created by PhpStorm.
 * User: administer
 * Date: 2019/5/3
 * Time: 14:55
 */

global $_W,$_GPC;

$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];

if($op == 'display'){
    $status = isset($_GPC['status']) ? $_GPC['status'] : -1;
    $pageIndex = max(1,intval($_GPC['page']));
    $pageSize = 20;

    $conditions = "uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];

    if($status > -1){
        $conditions .= " and status=:status";
        $params[':status'] = $status;
    }

    $count = pdo_fetchcolumn("select count(id) from " . tablename('jujiwuliu_area_manager') . " where " . $conditions, $params);
    $pager = pagination($count, $pageIndex, $pageSize);

    $list = pdo_fetchall("select * from " . tablename('jujiwuliu_area_manager') . " where " . $conditions . " order by id desc limit " . ($pageIndex - 1) * $pageSize . $pageSize, $params);
}elseif ($op == 'apply'){
    $id = intval($_GPC['id']);
    if($_W['ispost']){
        $status = intval($_GPC['status']);
        pdo_update('jujiwuliu_area_manager', array('status' => $status),  array('id' => $id));
        message('审核成功', referer(), 'success');
    }
    $item = pdo_fetch("select * from " . tablename('jujiwuliu_area_manager') . " where id=:id", array(':id' => $id));
    if(!empty($item['license'])){
        $item['license'] = tomedia($item['license']);
    }
    include $this->template('area_apply');
    die;
}elseif ($op == 'delete'){
    $id = intval($_GPC['id']);
    pdo_delete('jujiwuliu_area_manager', array('id' => $id));
    message('删除成功', referer(), 'success');
}

include $this->template('area');