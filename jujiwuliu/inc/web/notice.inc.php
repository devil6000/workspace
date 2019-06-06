<?php
/**
 * 消息设置
 * Created by PhpStorm.
 * User: appleimac
 * Date: 19/3/18
 * Time: 上午9:37
 */
global $_W,$_GPC;

$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if($op == 'display') {
    $pageIndex = max(1,intval($_GPC['page']));
    $pageSize = 20;

    $list = pdo_fetchall('select * from ' . tablename('jujiwuliu_notice') . ' where uniacid=:uniacid order by id desc limit ' . ($pageIndex - 1) * $pageSize . ',' . $pageSize, array(':uniacid' => $_W['uniacid']));

    include $this->template('notice');
}

if($op == 'post'){
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from ' . tablename('jujiwuliu_notice') . ' where id=:id', array('id' => $id));
    if($item){
        $data = unserialize($item['data']);
    }
    if($_W['ispost']){
        $keys = $_GPC['data_key'];
        $values = $_GPC['data_val'];
        $colors = $_GPC['data_cor'];

        $data = array();
        foreach ($keys as $key => $val){
            $da = array('key' => $val, 'value' => $values[$key], 'color' => $colors[$key]);
            $data[] = $da;
        }

        $insert = array(
            'title' => $_GPC['title'],
            'template_id' => $_GPC['template_id'],
            'first' => $_GPC['first'],
            'firstcolor' => $_GPC['firstcolor'],
            'remark' => $_GPC['remark'],
            'remarkcolor' => $_GPC['remarkcolor'],
            'data' => serialize($data)
        );

        if(empty($id)){
            $insert['uniacid'] = $_W['uniacid'];
            pdo_insert('jujiwuliu_notice', $insert);
        }else{
            pdo_update('jujiwuliu_notice', $insert, array('id' => $id));
        }

        message('操作成功！', $this->createWebUrl('notice'), 'success');
    }

    include $this->template('notice');
}

if($op == 'delete'){
    $binds = false;
    $id = intval($_GPC['id']);
    $setting = pdo_fetch('select * from ' . tablename($this->tabsetting) . ' where uniacid=:uniacid',array(':uniacid' => $_W['uniacid']));
    $template = unserialize($setting['notice_binds']);
    if($template){
        foreach ($template as $key => $value){
            if($value == $id){
                $binds = true;
                break;
            }
        }
    }

    if($binds){
        message('消息已绑定，不能删除!', $this->createWebUrl('notice'), 'error');
        exit;
    }

    pdo_delete('jujiwuliu_notice', array('id' => $id));
    message('删除成功!', $this->createWebUrl('notice'), 'success');
}

if($op == 'bind'){
    $list = pdo_fetchall('select id,title from ' . tablename('jujiwuliu_notice') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));

    $setting = pdo_fetch('select * from ' . tablename($this->tabsetting) . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
    $template = unserialize($setting['notice_binds']);
    if($_W['ispost']){
        $update['notice_binds'] = serialize($_GPC['template']);
        pdo_update($this->tabsetting, $update, array('uniacid' => $_W['uniacid']));
        message('消息绑定成功!', $this->createWebUrl('notice'), 'success');
    }
    include $this->template('notice');
}

if($op == 'addTemplate'){
    include $this->template('notice_template');
}