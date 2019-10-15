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
    $static = isset($_GPC['static']) ? $_GPC['static'] : -1;
    $pageIndex = max(1,intval($_GPC['page']));
    $pageSize = 20;

    $conditions = "uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];

    if($status > -1){
        $conditions .= " and status=:status";
        $params[':status'] = $status;
    }
    if($static > -1){
        $conditions .= " and static=:static";
        $params[':static'] = $static;
    }
    if(!empty($_GPC['keyword'])){
        $conditions .= " and (realname like :keyword or mobile like :keyword)";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    $count = pdo_fetchcolumn("select count(id) from " . tablename('jujiwuliu_area_manager') . " where " . $conditions, $params);
    $pager = pagination($count, $pageIndex, $pageSize);

    $sql = "select * from " . tablename('jujiwuliu_area_manager') . " where " . $conditions . " order by id desc ";
    if(empty($_GPC['export'])){
        $sql .= " limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize;
    }

    $list = pdo_fetchall($sql, $params);

    if($_GPC['export'] == 1){
        foreach ($list as &$item){
            $item['static_str'] = empty($item['static']) ? '个人' : '企业';
            $item['status_str'] = empty($item['status']) ? '待审核' : ($item['status'] == 1 ? '已通过' : '未通过');
        }
        unset($item);
        require_once IA_ROOT . '/addons/jujiwuliu/excel.php';
        $excel = new Excel();
        $excel->export($list, array(
            'title' => '区域管理',
            'columns' => array(
                array('title' => '姓名', 'field' => 'realname', 'width' => 15),
                array('title' => '手机号', 'field' => 'mobile', 'width' => 15),
                array('title' => '地区', 'field' => 'area', 'width' => 20),
                array('title' => '状态', 'field' => 'status_str', 'width' => 15),
                array('title' => '类型', 'field' => 'static_str', 'width' => 15)
            )
        ));
    }
}elseif ($op == 'apply'){
    $id = intval($_GPC['id']);
    if($_W['ispost']){
        $openid = pdo_fetchcolumn("select openid from " . tablename('jujiwuliu_area_manager') . " where id=:id", array(':id' => $id));

        $status = intval($_GPC['status']);
        pdo_update('jujiwuliu_area_manager', array('status' => $status),  array('id' => $id));

        $formIdInfo = $this->getFormId($openid);
        if(!empty($formIdInfo)){
            $setting = pdo_fetch("SELECT * FROM ".tablename($this->tabsetting)." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
            $binds = unserialize($setting['notice_binds']);
            $template_id = $binds['take_area'];
            $template = pdo_fetch('select * from ' . tablename('jujiwuliu_notice') . ' where id=:id', array(':id' => $template_id));
            if(!empty($template) && !empty($template['template_id'])){
                $template['data'] = unserialize($template['data']);
                $data['first'] = array('value' => $template['first'], 'color' => $template['firstcolor']);
                foreach ($template['data'] as $item){
                    $data[$item['key']] = array('value' => $item['value'], 'color' => $item['color']);
                }
                $data['remark'] = array('value' => $template['remark'], 'color' => $template['remarkcolor']);
                foreach ($data as $key => $item){
                    $tmp = $item['value'];
                    $tmp = str_replace('[审核状态]', ($status == 1 ? '已通过' : '未通过'), $tmp);
                    $tmp = str_replace('[审核日期]', date('Y-m-d H:i:s', time()), $tmp);
                    $data[$key]['value'] = $tmp;
                }
                $this->sendTplNotice($openid, $template['template_id'], $data, $url, $formIdInfo['formid']);
                $this->updateFormId($openid, $formIdInfo['id']);
            }
        }

        /*
        $account = WeAccount::create();
        if(!is_null($account)){
            $info = "区域负责人审核通知\n";
            $info .= "您提交的区域负责人申请管理员已审核，审核结果为：" . ($status == 1 ? "已通过" : "未通过");
            $info .= ".审核日期为：" . date('Y-m-d H:i:s', time());
            $custom = array(
                'msgtype' => 'text',
                'text' => array('content' => urlencode($info)),
                'touser' => $openid,
            );
            $account->sendCustomNotice($custom);
        }
        */

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