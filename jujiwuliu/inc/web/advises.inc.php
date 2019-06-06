<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 19/5/6
 * Time: 上午9:02
 */
global $_W,$_GPC;

$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];

if($op == 'display'){
    $model = isset($_GPC['model']) ? intval($_GPC['model']) : -1;
    $show = isset($_GPC['show']) ? intval($_GPC['show']) : -1;
    $pageIndex = max(1,intval($_GPC['page']));
    $pageSize = 20;
    $conditions = 'uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if($model >= 0){
        $conditions .= ' and model=:model';
        $params[':model'] = $model;
    }
    if($show >= 0){
        $conditions .= ' and is_show=:show';
        $params[':show'] = $show;
    }

    $count = pdo_fetchcolumn("select count(id) from " . tablename('jujiwuliu_advises') . " where " . $conditions, $params);
    $pager = pagination($count, $pageIndex, $pageSize);

    $list = pdo_fetchall("select * from " . tablename('jujiwuliu_advises') . " where " . $conditions . " order by id desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize, $params);
    if(!empty($list)){
        foreach ($list as &$item){
            $aid = $item['id'];
            $total = pdo_fetchcolumn("select count(id) from " . tablename('jujiwuliu_advises_reply') . " where advisies_id=:id", array(':id' => $aid));
            $item['reply'] = intval($total);
        }
        unset($item);
    }
}elseif ($op == 'reply'){
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select * from " . tablename('jujiwuliu_advises') . " where id=:id", array(':id' => $id));
    if(empty($item)){
        message('没有找到信息', $this->createWebUrl('advises'), 'error');
    }
    $item['images'] = explode(',', $item['images']);
    foreach ($item['images'] as $ke => $v){
        if(!empty($v)){
            $item['images'][$ke] = tomedia($v);
        }
    }

    if($_W['ispost']){
        $data = array(
            'uniacid' => $_W['uniacid'],
            'advisies_id' => $id,
            'content' => $_GPC['content'],
            'createtime' => time(),
            'browse' => 0
        );
        pdo_insert('jujiwuliu_advises_reply', $data);

        message('回复成功', $this->createWebUrl('advises'), 'success');
    }
}elseif ($op == 'set_show'){
    $id = intval($_GPC['id']);
    $show = intval($_GPC['show']);
    $update['is_show'] = empty($show) ?  1 : 0;
    pdo_update('jujiwuliu_advises', $update, array('id' => $id));
    die(json_encode(array('show' => $update['is_show'], 'name' => empty($update['is_show']) ? '显示' : '不显示')));
}

include $this->template('advises');