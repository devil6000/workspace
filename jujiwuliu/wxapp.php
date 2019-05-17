<?php
/**
 * nearby_stores模块小程序接口定义
 *
 * @author EdisonLiu_
 * @url 
 */

defined('IN_IA') or exit('Access Denied');
@require_once (IA_ROOT . '/framework/function/file.func.php');
class jujiwuliuModuleWxapp extends WeModuleWxapp {
	public $tabmember = 'jujiwuliu_members';//会员表
	public $tabcategory = 'jujiwuliu_category';//分类表
	public $tabremover = 'jujiwuliu_remover';//搬运工表
	public $taborder = 'jujiwuliu_order';//订单表
	public $tabrelease = 'jujiwuliu_release';//发布表
	public $tabcode= 'jujiwuliu_code';//短信验证码表
	public $tabcreditlog = 'jujiwuliu_credits_record';//积分、余额记录
	public $tabglide = 'jujiwuliu_glide';//充值提现记录
	public $tabsetting= 'jujiwuliu_setting';//系统设置表
	public $tabfirm= 'jujiwuliu_firm';//开票信息
    public $tabformid = 'jujiwuliu_formid'; //小程序formID
    public $tabbond = 'jujiwuliu_bond'; //接单方保证金支付临时表
    public $tabareamanager = 'jujiwuliu_area_manager';  //代理区域表
    public $tabadvises = 'jujiwuliu_advises'; //投诉建议
    public $tabadvisesreply = 'jujiwuliu_advises_reply'; //投诉建议解答
	
	
	public $map_apikey="LNYBZ-JK5K4-XLOUZ-DFA76-53Y66-ZFFEL";//腾讯地图apikey
    //public $map_apikey="MF2BZ-SUJCQ-ISK5A-GHYV5-LQU42-KPB3I";//腾讯地图apikey
	
	function __construct(){
		global $_GPC, $_W;
		load()->model('mc');

		$this->type_set = array('0' => '装卸工', '1' => '临时工');
		$this->unit_set = array('0' => '吨', '1' => '天');		
		$this->static_set = array('1' => '轻度活', '2' => '中度活', '3' => '重度活', '4' => '一口价');
		$this->sex_set = array('0' => '不限', '1' => '男', '2' => '女');
		$this->status_set = array('0' => '发布中', '1' => '工作中', '3' => '已完成', '4' => '已取消');
		$this->distance_set = array('0' => 10, '1' => 20);
		$this->seeting=pdo_fetch("SELECT * FROM ".tablename($this->tabsetting)." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
		$this->seeting['dock_light']=floatval($this->seeting['dock_light']);
		$this->seeting['dock_moderate']=floatval($this->seeting['dock_moderate']);
		$this->seeting['dock_heavy']=floatval($this->seeting['dock_heavy']);
		$this->seeting['temporary_light']=floatval($this->seeting['temporary_light']);
		$this->seeting['temporary_moderate']=floatval($this->seeting['temporary_moderate']);
		$this->seeting['temporary_heavy']=floatval($this->seeting['temporary_heavy']);
		$uid=mc_openid2uid($_W['openid']);
		$this->user=mc_fetch($uid);

		$this->runTasks();
		
	}

	public function runTasks(){
	    global $_W;
        $key = md5($_W['uniacid'] . '_new_cancel_order');
        $key1 = md5($_W['uniacid'] . '_new_cancel_order_time');
        $lasttime = strtotime(cache_load($key));
        $interval = intval(cache_load($key1));
        if(empty($interval)){
            $interval = 60;
        }

        $interval *= 60;
        $current = time();
        if(($lasttime + $interval) <= $current ){
            cache_write($key,date('Y-m-d H:i:s', $current));
            ihttp_request($_W['siteroot'] . 'addons/jujiwuliu/inc/tasks/order_operation.php', null,null, 1);
        }

        $key = md5($_W['uniacid'] . '_new_settle_order');
        $key1 = md5($_W['uniacid'] . '_new_settle_order_time');
        $lasttime = strtotime(cache_load($key));
        $interval = intval(cache_load($key1));
        if(empty($interval)){
            $interval = 60;
        }

        $interval *= 60;
        $current = time();
        if(($lasttime + $interval) <= $current ){
            cache_write($key,date('Y-m-d H:i:s', $current));
            ihttp_request($_W['siteroot'] . 'addons/jujiwuliu/inc/tasks/order_settle.php', null,null, 1);
        }

    }

    /**
     * 保存formId表
     * @param $openid
     * @param $formid
     */
    public function saveFormId($openid, $formid){
        global $_W;

        if($formid == 'undefined' || $formid == 'the formId is a mock one' || empty($formid)){
            return false;
        }

        $insert = array(
            'uniacid' => $_W['uniacid'],
            'openid'  => $openid,
            'formid'  => $formid,
            'createtime' => time(),
            'isuserd'   => 0
        );

        pdo_insert($this->tabformid, $insert);
    }

    /**
     * 获取formId表
     * @param $openid
     * @return bool
     */
    public function getFormId($openid){
        global $_W;
        $condition = 'uniacid=:uniacid and openid=:openid and TIMESTAMPDIFF(DAY,from_unixtime(createtime),now()) < 7 and isuserd=0';
        $params = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
        $info = pdo_fetch('select * from ' . tablename($this->tabformid) . ' where ' . $condition . ' order by createtime asc limit 1', $params);
        if(!empty($info)){
            if($info['formid'] == 'undefined' || $info['formid'] == 'the formId is a mock one' || empty($info['formid'])){
                $this->updateFormId($info['openid'], $info['id']);
                $info = $this->getFormId($openid);
            }
        }
        return $info;
    }

    public function updateFormId($openid, $id) {
        pdo_update($this->tabformid, array('isuserd' => 1), array('id' => $id, 'openid' => $openid));
    }


    public function doWssGetMsg(){
		echo 1;die;
	}

	public function sendTplNotice($touser, $template_id, $postdata, $url = '', $form_id = '', $topcolor = '#FF683F') {
		$account_api = WeAccount::create();

		if(empty($touser)) {
			return error(-1, '参数错误,粉丝openid不能为空');
		}
		if(empty($template_id)) {
			return error(-1, '参数错误,模板标示不能为空');
		}
		if(empty($postdata) || !is_array($postdata)) {
			return error(-1, '参数错误,请根据模板规则完善消息内容');
		}
		$token = $account_api->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		

		$data = array();
		$data['touser'] = $touser;
		$data['template_id'] = trim($template_id);
		$data['page'] = trim($url);
		$data['topcolor'] = trim($topcolor);
        $data['form_id'] = $form_id;
		$data['data'] = $postdata;
		$data = json_encode($data);
		
		$post_url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$token}";
		$response = ihttp_request($post_url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},信息详情：{$this->errorCode($result['errcode'])}");
		}
		return true;
	}

	public function sendMessage($id, $openid, $url, $formid, $type = ''){
	    $setting = $this->seeting;
	    $binds = unserialize($setting['notice_binds']);
        $template_id = $binds[$type];
        $template = pdo_fetch('select * from ' . tablename('jujiwuliu_notice') . ' where id=:id', array(':id' => $template_id));
        if(!empty($template) && !empty($template['template_id'])){
            $template['data'] = unserialize($template['data']);
            $data['first'] = array('value' => $template['first'], 'color' => $template['firstcolor']);
            foreach ($template['data'] as $item){
                $data[$item['key']] = array('value' => $item['value'], 'color' => $item['color']);
            }
            $data['remark'] = array('value' => $template['remark'], 'color' => $template['remarkcolor']);

            if($type == 'issue_ok'){
                //发布方发布消息
                $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id', array(':id' => $id));
                foreach ($data as $key => $item){
                    $tmp = $item['value'];
                    $tmp = str_replace('[工种]', $this->type_set[$release['type']], $tmp);
                    $tmp = str_replace('[类型]', $this->static_set[$release['static']], $tmp);
                    $tmp = str_replace('[数量]', $release['count'], $tmp);
                    $tmp = str_replace('[单位]', $this->unit_set[$release['type']], $tmp);
                    $tmp = str_replace('[发布时间]', date('Y-m-d H:i:s', $release['createtime']), $tmp);
                    $tmp = str_replace('[开工时间]', date('Y-m-d H:i:s', $release['starttime']), $tmp);
                    $tmp = str_replace('[单价]', $release['price'], $tmp);
                    $tmp = str_replace('[单人金额]', $release['total_price'], $tmp);
                    $tmp = str_replace('[总价]', $release['count_price'], $tmp);
                    $tmp = str_replace('[地址]', $release['address'], $tmp);
                    $data[$key]['value'] = $tmp;
                }
            }elseif ($type == 'issue_cancel'){
                //取消发布信息
                $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id', array(':id' => $id));
                foreach ($data as $key => $item){
                    $tmp = $item['value'];
                    $tmp = str_replace('[工种]', $this->type_set[$release['type']], $tmp);
                    $tmp = str_replace('[类型]', $this->static_set[$release['static']], $tmp);
                    $tmp = str_replace('[数量]', $release['count'], $tmp);
                    $tmp = str_replace('[单位]', $this->unit_set[$release['type']], $tmp);
                    $tmp = str_replace('[发布时间]', date('Y-m-d H:i:s', $release['createtime']), $tmp);
                    $tmp = str_replace('[开工时间]', date('Y-m-d H:i:s', $release['starttime']), $tmp);
                    $tmp = str_replace('[单价]', $release['price'], $tmp);
                    $tmp = str_replace('[单人金额]', $release['total_price'], $tmp);
                    $tmp = str_replace('[总价]', $release['count_price'], $tmp);
                    $tmp = str_replace('[地址]', $release['address'], $tmp);
                    $data[$key]['value'] = $tmp;
                }
            }elseif ($type == 'take_ok'){
                //接单成功信息
                $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id', array(':id' => $id));
                $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where rid=:rid and openid=:openid', array(':rid' => $id, ':openid' => $openid));
                foreach ($data as $key => $item){
                    $tmp = $item['value'];
                    $tmp = str_replace('[工种]', $this->type_set[$release['type']], $tmp);
                    $tmp = str_replace('[类型]', $this->static_set[$release['static']], $tmp);
                    $tmp = str_replace('[数量]', $release['count'], $tmp);
                    $tmp = str_replace('[单位]', $this->unit_set[$release['type']], $tmp);
                    $tmp = str_replace('[发布时间]', date('Y-m-d H:i:s', $release['createtime']), $tmp);
                    $tmp = str_replace('[开工时间]', date('Y-m-d H:i:s', $release['starttime']), $tmp);
                    $tmp = str_replace('[单价]', $release['price'], $tmp);
                    $tmp = str_replace('[单人金额]', $release['total_price'], $tmp);
                    $tmp = str_replace('[总价]', $release['count_price'], $tmp);
                    $tmp = str_replace('[地址]', $release['address'], $tmp);
                    $tmp = str_replace('[接单时间]', date('Y-m-d H:i:s', $order['createtime']), $tmp);
                    $data[$key]['value'] = $tmp;
                }
            }elseif ($type == 'take_cancel'){
                //取消接单信息
                $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id', array(':id' => $id));
                $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where rid=:rid and openid=:openid', array(':rid' => $id, ':openid' => $openid));
                foreach ($data as $key => $item){
                    $tmp = $item['value'];
                    $tmp = str_replace('[工种]', $this->type_set[$release['type']], $tmp);
                    $tmp = str_replace('[类型]', $this->static_set[$release['static']], $tmp);
                    $tmp = str_replace('[数量]', $release['count'], $tmp);
                    $tmp = str_replace('[单位]', $this->unit_set[$release['type']], $tmp);
                    $tmp = str_replace('[发布时间]', date('Y-m-d H:i:s', $release['createtime']), $tmp);
                    $tmp = str_replace('[开工时间]', date('Y-m-d H:i:s', $release['starttime']), $tmp);
                    $tmp = str_replace('[单价]', $release['price'], $tmp);
                    $tmp = str_replace('[单人金额]', $release['total_price'], $tmp);
                    $tmp = str_replace('[总价]', $release['count_price'], $tmp);
                    $tmp = str_replace('[地址]', $release['address'], $tmp);
                    $data[$key]['value'] = $tmp;
                }
            }

            return $this->sendTplNotice($openid, $template['template_id'], $data, $url, $formid);
        }
    }


	//下面是公共方法
	public function mid2openid($mid){
	 	global	$_W;
		$user = pdo_fetch("SELECT * FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and id=:id and deleted=0", array(':uniacid' => $_W['uniacid'], ':id' => $mid));
		return $user['openid'];
	}
	//积分余额记录
	/*
	 * 
	 * $log = array(
		0 => 操作管理员uid
		1 => 增减积分备注
		2 => 模块标识，例如：we7_store
		3 => 店员uid
		4 => 门店id
		5 => 1(线上操作) 2(系统后台,公众号管理员和操作员) 3(店员)  4系统自动处理
	 *  6 => 订单号
		);
	 * */
	public function credit_update($uid, $credittype, $creditval, $log = array(),$is_ret=false){
		global $_W,$_GPC;
		load()->model('mc');
		//如果是扣款先看余额或者积分是否充足
		$cr_type=array('credit1'=>'积分','credit2'=>'余额');
        $clerk_types = array(
            '1' => '线上操作',
            '2' => '系统后台',
            '3' => '店员',
        );

		if($creditval<0){
			$reg=mc_fetch($uid,array($credittype));
			if(($reg[$credittype]+$creditval)<0){
				if(empty($is_ret)){
					message('操作失败,该用户的'.$cr_type[$credittype].'不足以扣除;', 'error');
				}else{
					return false;
				}
			}
		}

		//先修改系统信息 再修改表
		
		if(empty($creditval)){
			if(empty($is_ret)){
				message('操作'.$cr_type[$credittype].'失败','error');
			}else{
				return false;
			}	
			
		}

		$up_credit=mc_credit_update($uid, $credittype, $creditval, $log);

		if($up_credit!=true){
			if(empty($is_ret)){
				message('操作'.$cr_type[$credittype].'失败','error');
			}else{
				return false;
			}
		}
		
		if (empty($log) || !is_array($log)) {
			load()->func('logging');
			if (!empty($GLOBALS['site']) && $GLOBALS['site'] instanceof WeModuleSite) {
				$log = array(
					$uid,
					$GLOBALS['site']->module['title'] . '模块内消费' . logging_implode($_GET),
					$GLOBALS['site']->module['name'], 
					0,
				);
			} elseif (!empty($GLOBALS['_GPC']['m'])) {
				$modules = uni_modules();
				$log = array(
					$uid,
					$modules[$GLOBALS['_GPC']['m']]['title'] . '模块内消费' . logging_implode($_GET),
					$GLOBALS['_GPC']['m'],
					0,
				);
			} else {
				$log = array($uid, '未记录', 0, 0);
			}
		}
		if ($credittype == 'credit1') {
			$credittype_name = '积分';
		} elseif ($credittype == 'credit2') {
			$credittype_name = '元';
		}
		if (empty($log[1])) {
			if ($creditval > 0) {
				$log[1] = $clerk_types[$log[5]] . ': 添加' . $creditval . $credittype_name;
			} else {
				$log[1] = $clerk_types[$log[5]] . ': 减少' . -$creditval . $credittype_name;
			}
	
		}
        $reg=mc_fetch($uid,array($credittype));

		$clerk_type = intval($log[5]) ? intval($log[5]) : 1;
		$ordersn=strval($log[6]) ? strval($log[6]) : '';
		$data = array(
			'uid' => $uid,
			'credittype' => $credittype,
			'uniacid' => $_W['uniacid'],
			'num' => $creditval,
			'createtime' => TIMESTAMP,
			'operator' => intval($log[0]),
			'module' => trim($log[2]),
			'clerk_id' => intval($log[3]),
			'store_id' => intval($log[4]),
			'clerk_type' => $clerk_type,
            'residue'=>$reg[$credittype],
			'ordersn' => $ordersn,
			'remark' => $log[1],
		);
		pdo_insert($this->tabcreditlog,$data);
		pdo_update($this->tabmember,array($credittype=>$reg[$credittype]),array('uid'=>$uid,'uniacid'=>$_W['uniacid']));
		
		return true;
	}
	//获取系统设置
	public function doPageGetSetting(){
		global $_GPC, $_W;
//		$setting = pdo_fetch("SELECT * FROM ".tablename($this->tabsetting)." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
      	//return $this->result(1, '系统升级中');
		$setting = $this->seeting;
		$setting['rolling_diagram']=unserialize($setting['rolling_diagram']);
		foreach ($setting['rolling_diagram'] as & $value) {
			$value=tomedia($value);
		}
		return $this->result(0, '系统设置获取成功！',$setting);
	}
	//	获取会员信息
	public function doPageGetCenter(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$user = pdo_fetch("SELECT * FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		$mcuser=mc_fetch($user['uid'],array('credit1','credit2'));
		$user=array_merge($user,$mcuser);
		//如果是发布方则发送发布数量等信息
		if($user['type']==0 && !empty($user)){
			$user['issuer_num']=pdo_fetchcolumn("SELECT count(id) FROM ".tablename($this->tabrelease)." WHERE uniacid=".$_W['uniacid']."  and openid='".$_W['openid']."' and pay_status=1  ");
		}
		
		//如果是搬运工则发送订单数量
		$data['info'] = $user;
		if(!empty($user)){
			return $this->result($errno, $message, $data);
		}else{
			return $this->result(1, '获取失败！');
		}
	}
	//修改会员信息
	public function doPageMemberEdit(){
		global $_GPC, $_W;	
		$errno = 0;
		$message = 'success';
		$data['alipay_account']=$_GPC['alipay_account'];
		$data['alipay_real']=$_GPC['alipay_real'];
		$data['opening_bank']=$_GPC['opening_bank'];
		$data['bank_cardid']=$_GPC['bank_cardid'];
		$data['bank_cardreal']=$_GPC['bank_cardreal'];

		pdo_update($this->tabmember,$data,array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid']));

		return $this->result(0, '操作成功');
	}	
	//会员充值
	public function doPageRecharge(){
		global $_GPC, $_W;	
		$errno = 0;
		$message = 'success';
		$gettype=intval($_GPC['type']);
		$id=intval($_GPC['id']);
		if($gettype==1 && empty($id)){//充值
			//充值
			$money=floatval($_GPC['money']);
			if(empty($money) || $money<0){
				return $this->result(0, '请输入正确的金额！');
			}
			$uid=mc_openid2uid($_W['openid']);
			//创建充值订单
			$data['ordersn']=$this->createNO('release','ordersn','RE');	
			$data['money']	=$money;
			$data['uid']	=$uid;
			$data['type']	=1;
			$data['title']	='会员前台发起充值！';
			$data['createtime']	=time();
			$data['openid']	=$_W['openid'];
			$data['uniacid']	=$_W['uniacid'];
			
			$in_glide=pdo_insert($this->tabglide,$data);
			if(empty($in_glide)){
				return $this->result(0, '创建订单失败，请重试！');
			}
			$data['id']=pdo_insertid();
			
			//生成支付参数，返回给小程序端
			$pay_order['type']=2;
			$pay_order['ordersn']=$data['ordersn'];
        	$pay_data = $this->doPagePay($pay_order);

			// $data['credit_enough']=$this->;//余额是否足够
			$data['pay_data']=$pay_data;
			if(!empty($data)){
				return $this->result($errno, $message, $data);
			}else{
				return $this->result(0, '创建订单失败，请重试！');
			}
		}
		if($gettype==2 || !empty($id)){//查询充值状态
			if(empty($id)){
				return $this->result(0, '订单查询失败，请重试！');
			}
			$data=pdo_get($this->tabglide,array('uniacid'=>$_W['uniacid'],'id'=>$id,'type'=>1));
			//生成支付参数，返回给小程序端
			$pay_order['type']=2;
			$pay_order['ordersn']=$data['ordersn'];
        	$pay_data = $this->doPagePay($pay_order);

			$data['pay_data']=$pay_data;
			
			if(!empty($data)){
				return $this->result($errno, $message, $data);
			}else{
				return $this->result(0, '创建订单失败，请重试！');
			}
		}
		
	}
	
	//获取积分余额记录
	public function doPageGetCredit(){
		global $_GPC, $_W;	
		$errno = 0;
		$message = 'success';
		$page = empty($_GPC["page"]) ? "" : $_GPC["page"];
		$pindex  = max(1, intval($page));
		$pagesize = 6;
		$type=intval($_GPC["type"]);//获取类型（余额、充值、积分）
		if($type==0){
			$uid=mc_openid2uid($_W['openid']);
			$data = pdo_fetchall("SELECT *,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') as createtime,remark as title  FROM ".tablename($this->tabcreditlog)." WHERE uniacid=".$_W['uniacid']." and credittype='credit2' and uid='".$uid."' ORDER BY id desc LIMIT " . (($pindex - 1) * $pagesize) . ',' . $pagesize);
		
		}
		if($type==1){
			$data = pdo_fetchall("SELECT *,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') as createtime,title,money as num  FROM ".tablename($this->tabglide)." WHERE uniacid=".$_W['uniacid']." and type=1 and status=1 and openid='".$_W['openid']."' ORDER BY id desc LIMIT " . (($pindex - 1) * $pagesize) . ',' . $pagesize);
		}
		if($type==2){
			$uid=mc_openid2uid($_W['openid']);
			$data = pdo_fetchall("SELECT *,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') as createtime,remark as title  FROM ".tablename($this->tabcreditlog)." WHERE uniacid=".$_W['uniacid']." and credittype='credit1' and uid='".$uid."' ORDER BY id desc LIMIT " . (($pindex - 1) * $pagesize) . ',' . $pagesize);
		}
		
		
		
		
		if(!empty($data)){
			return $this->result($errno, $message, $data);
		}else{
			return $this->result(0, '没有了！');
		}
	}
	//获取地理位置
	public function doPageGetLocation(){
		global $_GPC, $_W;	 	
		$lat=$_GPC['lat'];
		$lng=$_GPC['lng'];
		
		$map_addr=file_get_contents('https://apis.map.qq.com/ws/geocoder/v1/?location='.$lat.','.$lng.'&key='.$this->map_apikey.'&get_poi=1 ');
		$map_addr=json_decode($map_addr,TRUE);
		$errno = 0;
		$message = 'success'.$_W['openid'];
		 
		//距离当前定位最近的地址
		// $data['nearby_address']=$map_addr['result']['address']?$map_addr['result']['address']:'无';
		return $this->result($errno, $message, $map_addr);
	}
		
	//获取2点之间的距离
	function getDistance($lat1, $lng1, $lat2, $lng2){
		$pi = 3.1415926535898 ;
		$earth_radius = 6378.137 ;
		$radLat1 = $lat1 * ($pi / 180);
		$radLat2 = $lat2 * ($pi / 180);
		$a = $radLat1 - $radLat2;
		$b = ($lng1 * ($pi / 180)) - ($lng2 * ($pi / 180));
		$s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
		$s = $s * $earth_radius;
		$s = round($s * 10000) / 10000;
		return round($s,1);
	}
//	发送验证码
	public function doPageGetvertify(){
		global $_GPC, $_W;
		
		$errno = 0;
		
		$message = 'success';
		
		$mobile = $_GPC['mobile'];

		
		$user = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and mobile=:mobile and deleted=0", array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
		if(!empty($user)){
			$errno = 1;
			$message = "该手机号码已注册！";
			return $this->result($errno, $message);
		}
  
		$sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
		
		$code = random(5, true);
		
		pdo_delete($this->tabcode, array('uniacid' => $_W['uniacid'], 'mobile' => $mobile));
		
		$data = array('uniacid' => $_W['uniacid'], 'mobile' => $mobile, 'code' => $code, 'createtime' => time());
		  
		$smsConf = array(
		    'key'   => '1473d2a413fbba0b455ceb3c6912c1ed', //您申请的APPKEY
		    'mobile'    => $mobile, //接受短信的用户手机号码
		    'tpl_id'    => '107030', //您申请的短信模板ID，根据实际情况修改
		    'tpl_value' =>'#code#='.$code.'&#company#=聚合数据' //您设置的模板变量，根据实际情况修改
		);
		
		$content = $this->juhecurl($sendUrl, $smsConf, 1);
		 
		if($content){
		    $result = json_decode($content,true);
		    $error_code = $result['error_code'];
		    if($error_code == 0){
		        //状态为0，说明短信发送成功
		        pdo_insert($this->tabcode, $data);
		        return $this->result($errno, $message);
		    }else{
		        //状态非0，说明失败
		        $msg = $result['reason'];
		        return $this->result(1, $msg);
		    }
		}else{
		    //返回内容异常，以下可根据业务逻辑自行修改
		   return $this->result(1, '发送失败！');
		}
	}
	
	/**
	 * 请求接口返回内容
	 * @param  string $url [请求的URL地址]
	 * @param  string $params [请求的参数]
	 * @param  int $ipost [是否采用POST形式]
	 * @return  string
	 */
	public function juhecurl($url,$params=false,$ispost=0){
		$httpInfo = array();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if($ispost){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_URL, $url);
		}else{
			if($params){
				curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
			}else{
				curl_setopt($ch, CURLOPT_URL, $url);
			}
		}
		$response = curl_exec($ch);
		if($response === FALSE){
			return false;
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
		curl_close($ch);
		return $response;
	}
//	注册会员信息
	public function doPageGetBind(){ 
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		if(empty($_W['openid'])){
			$errno = 1;
			$message = "授权错误！";
			return $this->result($errno, $message);
		}
		$introducer_id = intval($_GPC['introducer_id']);
	
		$member=mc_fetch($_W['openid']);
		$user = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and mobile=:mobile and deleted=0", array(':uniacid' => $_W['uniacid'], ':mobile' => $_GPC['mobile']));
		if(!empty($user)){
			$errno = 1;
			$message = "该手机号码已注册！";
			return $this->result($errno, $message);
		}
		$user = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		if(!empty($user)){
			$errno = 1;
			$message = "该微信号已绑定！";
			return $this->result($errno, $message);
		}
		$code = pdo_fetch("SELECT * FROM ".tablename($this->tabcode)." WHERE uniacid=:uniacid and mobile=:mobile", array(':uniacid' => $_W['uniacid'], ':mobile' => $_GPC['mobile']));
		if(empty($code)){
			return $this->result(1, '验证码未发送！');
		}
		
		if($code['code']!=$_GPC['code']){
			return $this->result(1, '验证码错误！');
		}else{
			$time = time();
			$time = $time - ($code['createtime'] + 300);
			if($time>0){
				return $this->result(1, '验证码已过期！');
			}
		}
		load()->model('mc');
		load()->func('logging');
		
		$data = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'uid' => $member['uid'], 'type' => $_GPC['type'], 'mobile' => $_GPC['mobile'], 'password' => $member['password'], 'nickname' => $member['nickname'], 'avatar' => $member['avatar'], 'salt' =>$member['salt'],'introducer_id'=>$introducer_id?$introducer_id:'', 'createtime' => time(), 'updatetime' => time(), 'realname' => $_GPC['realname']);
		pdo_insert($this->tabmember, $data);
		$id = pdo_insertid();
		if($id){
			$data['id']=$id;
			
			if(!empty($introducer_id)){
				//赠送推荐积分
				$intolucer=pdo_get($this->tabmember,array('id'=>$introducer_id,'uniacid'=>$_W['uniacid']));
				if(!empty($intolucer)){
	
					$uid = mc_openid2uid($fromuser);
//					mc_credit_update($intolucer['uid'], 'credit1', '1', array(0, '推荐用户赠送1积分！'));
					$this->credit_update($intolucer['uid'], 'credit1', '1', array(0, '推荐用户赠送1积分！'));
					
				}else{
					//上级不存在 赠送积分失败 并且写日志 
					logging_run($data,'wxapp_warning_introducer_lose','jujiwuliu_add_user');
				}
			}

			return $this->result($errno, $message, $data);
		}else{
			return $this->result(1, '注册失败！', $data);
		}
	}
	public function randomkeys($length){   //随机数生成
	  $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';  
	    for($i=0;$i<$length;$i++)   
	    {   
	        $key .= $pattern{mt_rand(0,35)};    //生成php随机数   
		}
		return $key;   
	}   
	//	图片上传
	public function doPageImgupload(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		
		$uptypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png');
        $max_file_size = 2000000; //上传文件大小限制, 单位BYTE
        $destination_folder = "../attachment/images/".$_W['uniacid']."/".date('Y')."/".date('m').'/'; //上传文件路径
        $return_folder = "./images/".$_W['uniacid']."/".date('Y')."/".date('m').'/'; //返回文件路径
        if (!is_uploaded_file($_FILES["file"]['tmp_name']))
        //是否存在文件
        {
			$errno=1;
			$message="上传失败，请重试!" ;
			return $this->result($errno, $message, $data);
        }
        $file = $_FILES["file"];
        if ($max_file_size < $file["size"])
        //检查文件大小
        {
			$errno=1;
			$message="文件太大!" ;
			return $this->result($errno, $message, $data);

        }
        if (!in_array($file["type"], $uptypes))
        //检查文件类型
        {
        	$errno=1;
			$message="文件类型不符，不支持".$file["type"]."格式文件!" ;
			return $this->result($errno, $message, $data);
			
        }

        if (!file_exists($destination_folder)) {
        	mkdirs($destination_folder);
        }
        $filename = $file["tmp_name"];
        $pinfo = pathinfo($file["name"]);
        $ftype = $pinfo['extension'];
		$save_ename=str_shuffle(time() . rand(111111, 999999)) . "." . $ftype;//文件名
        $destination = $destination_folder . $save_ename;
		$return_file= $return_folder . $save_ename;
        if (file_exists($destination) && $overwrite != true) {
            $errno=1;
			$message="同名文件已经存在了,请重试！" ;
			return $this->result($errno, $message, $data);
        }
        if (!move_uploaded_file($filename, $destination)) {
            $errno=1;
			$message="上传失败,请重试！" ;
			return $this->result($errno, $message, $data);
        }
        $pinfo = pathinfo($destination);
        $fname = $pinfo['basename'];


        @$filename = $fname;
        @file_remote_upload($filename);//传到地方存储 如果有配置第三方
        
 		$data['file_name']=$return_file;
		return $this->result($errno, $message, $data);
	}
	//生成支付参数
	/*$paydata:
	 * 	type 支付类型 1发布方支付 2充值支付
	 * 	ordersn 订单号
	 * */
	public function doPagePay($paydata=array()) {
        global $_GPC, $_W;
		if(empty($paydata)){
			//获取订单号，保证在业务模块中唯一即可
	        $ordersn = strval($_GPC['ordersn']);
	        $fee = floatval($_GPC['fee']);
			$openid =$_W['openid'];
		}else{
			if($paydata['type']==1){
				$release=pdo_get($this->tabrelease,array('uniacid'=>$_W['uniacid'],'ordersn'=>$paydata['ordersn']));
				$ordersn=$release['ordersn'];
				$fee = ($release['count_price'] + $release['cancel_refund_money']);
				$openid=$release['openid'];	
				
				$order['title']='巨吉搬运发布付款';
			}elseif($paydata['type']==2){
				$recharge=pdo_get($this->tabglide,array('uniacid'=>$_W['uniacid'],'ordersn'=>$paydata['ordersn'],'type'=>1));
				$ordersn=$recharge['ordersn'];
				$fee=$recharge['money'];	
				$openid=$recharge['openid'];	
				
				$order['title']='巨吉搬运充值付款';
			}elseif($paydata['type'] == 3){
			    //$info = pdo_get($this->taborder, array('uniacid' => $_W['uniacid'], 'depositsn' => $paydata['ordersn']));
			    $info = pdo_get($this->tabbond, array('uniacid' => $_W['uniacid'], 'ordersn' => $paydata['ordersn']));
                $ordersn = $info['ordersn'];
			    $fee = $info['fee'];
			    $openid = $info['openid'];

			    $order['title'] = '巨吉搬运接单保证金';
            }
		}
		
		//构造支付参数
       	$order['tid']=$ordersn;
		$order['user']=$openid;//用户OPENID
		$order['fee']=floatval($fee);//金额

        //生成支付参数，返回给小程序端
        $pay_params = $this->pay($order);
		if(empty($paydata)){//如果是js调用直接返回
	        if (is_error($pay_params)) {
	            return $this->result(1, '支付失败，请重试');
	        }
	        return $this->result(0, '', $pay_params);
		}else{
			return $pay_params;
		}
    }
	public function payResult($log) {
		global $_GPC, $_W;
		load()->func('logging');
		// logging_run($log, 'trace', 'wx_app_pay_log');
		$log['pay_type']=$log['pay_type']?$log['pay_type']:1;
		//先查询
		$r_ord=pdo_get($this->tabrelease,array('ordersn'=>$log['tid']));
		if(!empty($r_ord)){
			pdo_update($this->tabrelease,array('pay_status'=>1,'pay_type'=>$log['pay_type']),array('ordersn'=>$log['tid']));
			//发送发布信息
            $result = $this->sendMessage($r_ord['id'], $r_ord['openid'], '', $log['formid'], 'issue_ok');
		}
		//先查询
		$recharge=pdo_get($this->tabglide,array('ordersn'=>$log['tid']));
		if(!empty($recharge)){
			pdo_update($this->tabglide,array('status'=>1,'pay_type'=>$log['pay_type']),array('ordersn'=>$log['tid']));
			//充值
			$uid = mc_openid2uid($recharge['openid']);
			$this->credit_update($uid, 'credit2', $recharge['money'], array(0, '用户申请充值！'));
		}
		//接单方保证金
        $order = pdo_get($this->tabbond, array('ordersn' => $log['tid'], 'status' => 0));
        if(!empty($order)){
            $this->getTakeOrder($order,$log['pay_type']);
        }

        /*
        $order = pdo_get($this->taborder, array('depositsn' => $log['tid']));
		if(!empty($order)){
		    pdo_update($this->taborder, array('deposit_paystatus' => 1, 'deposit_paytype' => $log['pay_type']), array('depositsn' => $log['tid']));
            $result = $this->sendMessage($order['rid'], $order['openid'], '', $log['formid'], 'take_ok');
        }
        */
	}
//	$refund= $this->refund('PU20181125103540849649',0.01,'后台处理退款！');//退款方法调用
	
	private function refund($tid, $fee = 0, $reason = '') {
		load()->model('refund');
		$refund_id = refund_create_order($tid, $this->module['name'], $fee, $reason);
		if (is_error($refund_id)) {
			return $refund_id;
		}
		return refund($refund_id);
	}
	
	//formids 记录模板消息id
	public function doPagePostFormids($data=array()){
		global $_GPC, $_W;
		
	}
	//修改开票企业信息
	public function doPagePostFirm(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$id=intval($_GPC['id']);
		$data['uniacid']=$_W['uniacid'];
		$data['openid']=$_W['openid'];
		if(!empty($id)){
			$data['id']=$id;
			$is_firm=pdo_get($this->tabfirm,$data);
			unset($data['id']);
		}
		$data['uid']=mc_openid2uid($_W['openid']);
		$data['firm_name']=$_GPC['firm_name'];
		$data['firm_identifier']=$_GPC['firm_identifier'];
		$data['firm_email']=$_GPC['firm_email'];
		$data['firm_bod'] = $_GPC['firm_bod'];
		$data['firm_oaa'] = $_GPC['firm_oaa'];
		$data['firm_address'] = $_GPC['firm_address'];
		$data['firm_tel'] = $_GPC['firm_tel'];
		if(empty($is_firm)){
			$in_upres=pdo_insert($this->tabfirm,$data);
		}else{
			$in_upres=pdo_update($this->tabfirm,$data,array('id'=>$id));
		}
		if(empty($in_upres)){
			$errno = 1;
			$message = '操作失败,请重试!';
			return $this->result($errno,$message, $data);
		}

		$message='操作成功!';
		return $this->result($errno,$message, $data);
	}
	//获取开票企业信息
	public function doPageGetFirmList(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$data=pdo_getall($this->tabfirm,array('uniacid'=>$_W['uniacid'],'deleted'=>0,'openid'=>$_W['openid']));
		

		return $this->result($errno,$message, $data);
	}
	//开票请求
	public function doPagePostinvoice(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		//查询订单
		$check_firmid=intval($_GPC['check_firmid']);
		$issuer_id=intval($_GPC['issuer_id']);
		if(empty($issuer_id)||empty($check_firmid)){
			$errno = 0;
			$message = '操作失败,请重试!';
			return $this->result($errno,$message, $data);
		}
		pdo_update($this->tabrelease,array('invoice_status'=>1,'firm_id'=>$check_firmid),array('uniacid'=>$_W['uniacid'],'id'=>$issuer_id));
		$errno = 0;
		$message='申请成功!';
		return $this->result($errno,$message, $data);
	}
	//删除开票企业信息
	public function doPageDelFirm(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$id=intval($_GPC['id']);
		$in_upres=pdo_update($this->tabfirm,array('deleted'=>1),array('id'=>$id));
		
		return $this->result($errno,$message);
	}
	//提现
	public function doPagePost_withdraw($data=array()){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$money=$_GPC['money'];
		if(($this->user['credit2']-$money)<0){
			$errno = 1;
			$message='您当前最多可提现'.$this->user['credit2'].'元';
			return $this->result($errno,$message, $data);
		}
		//扣除余额
		$uid=mc_openid2uid($_W['openid']);
		$cr_up=$this->credit_update($uid, 'credit2', '-'.$money, array(0, '用户申请提现'));
		if(empty($cr_up)){
			$errno = 1;
			$message='操作失败,请重试';
			return $this->result($errno,$message, $data);
		}
		//创建提现记录
		$data['ordersn']=$this->createNO('release','ordersn','WI');	
		$data['money']	=$money;
		$data['uid']	=$uid;
		$data['type']	=2;
		$data['title']	='会员前台发起提现申请！';
		$data['createtime']	=time();
		$data['openid']	=$_W['openid'];
		$data['uniacid']	=$_W['uniacid'];
		$in_glide=pdo_insert($this->tabglide,$data);

		$message='申请提现成功,请等待管理员处理';
		return $this->result($errno,$message, $data);
	}
	//公共方法结束
	//下面是发布方所有方法
	
//	发布
	//生成订单号  $table表名  $field字段名 $prefix前缀
	public function createNO($table, $field, $prefix) 
	{
		$billno = $prefix . date('YmdHis') . random(6, true);
		while (1) 
		{
			$count = pdo_fetchcolumn('select count(*) from ' . tablename('jujiwuliu_' . $table) . ' where ' . $field . '=:billno limit 1', array(':billno' => $billno));
			if ($count <= 0) 
			{
				break;
			}
			$billno = $prefix . date('YmdHis') . random(6, true);
		}
		return  $billno;
	}
	//申请打款
	public function doPageMakeMoney(){
		global $_GPC, $_W;
		$set = $this->seeting;
		$errno = 0;
		$message = 'success';
		$orderid=$_GPC['orderid'];
		$issuer_id=$_GPC['issuer_id'];
		$make_money_check=$_GPC['make_money_check'];
		$make_money_date=$_GPC['make_money_date'];
		if(empty($make_money_check)){
			$errno = 1;
			$message='请选择打款方式';
			return $this->result($errno,$message, $data);
		}
		$advance_payment=strtotime($make_money_date);
		$params['uniacid']=$_W['uniacid'];
		$params['status in']=array(1,2);
		//查询要操作的所有订单
		if(!empty($issuer_id)){
			$params['rid']=$issuer_id;
		}elseif(!empty($orderid)){
			$params['id']=$orderid;
		}
		$orders=pdo_getall($this->taborder,$params);
		if(empty($orders)){
			$errno = 1;
			$message='未查询到需要结算的订单';
			return $this->result($errno,$message, $data);
		}
		$release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid', array(':id' => $issuer_id, ':uniacid' => $_W['uniacid']));
		$money = $release['total_price'] - $release['total_price'] * $set['pumping'] / 100;

		//如果选择预打款
		if($make_money_check==2){
			foreach ($orders as $o_key => $ord) {
				if(($ord['status']==0||$ord['status']==1)&&$ord['deposit_paystatus']==1){
					pdo_update($this->taborder,array('advance_payment'=>$advance_payment),array('uniacid'=>$_W['uniacid'],'id'=>$ord['id']));
				}
			}
		}
		//如果选择立即打款
		if($make_money_check==1){
			load()->model('mc');
			foreach ($orders as $o_key1 => $ord1) {
				if((in_array($ord1['status'], array(1,2)))&&$ord1['deposit_paystatus']==0){
					//计算打款金额

					//打款到余额
					$uid=mc_openid2uid($ord1['openid']);
					$log[0]=0;
					$log[1]='发布方申请结算（立即打款）';
					$log[3]=0;
					$log[4]=0;
					$log[5]=1;
					$log[6]=$ord1['ordersn'];
					$cr_up=$this->credit_update($uid,'credit2',$money,$log,1);
					//修改打款状态
					//pdo_update($this->taborder,array('advance_payment'=>$advance_payment),array('uniacid'=>$_W['uniacid'],'id'=>$ord1['id']));
                    pdo_update($this->taborder,array('status' => 3),array('uniacid'=>$_W['uniacid'],'id'=>$ord1['id']));
				}
			}

            pdo_update($this->tabrelease, array('status' => 3), array('id' => $issuer_id));
		}
		
		return $this->result($errno,$message);
	}
	//获取联动地址
	public function  doPageGetchildren(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$id=$_GPC['id'];
		if(!empty($id)){
			$term='&id='.$id;
		}
		$data=file_get_contents('https://apis.map.qq.com/ws/district/v1/getchildren?key='.$this->map_apikey.$term);
		$data=json_decode($data);

		return $this->result($errno,$message, $data);
	}
	//获取所有地区
    public function doPageGetAllArea(){
	    $data = file_get_contents('https://apis.map.qq.com/ws/district/v1/list?key=' . $this->map_apikey);
	    $data = json_decode($data);
	    return $this->result(0,'success',$data);
    }

	//获取可指定人员名单
	public function doPageGetAppoint(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		if(empty($_W['openid'])||$_W['openid'] ==null){
			$errno = 1;
			$message = "用户错误！";
			return $this->result($errno, $message);
		}
		$user = pdo_fetch('select * from ' . tablename($this->tabmember) . ' where  openid=:openid and uniacid =:uniacid limit 1', array(':openid' => $_W['openid'],'uniacid'=>$_W['uniacid']));
		if(empty($user['historical_worker'])){
			$errno = 1;
			$message = "未查询到历史工人！";
			return $this->result($errno,$message);
		}
		$user['historical_worker']=explode(',',$user['historical_worker']);
		if(empty($user['historical_worker'])){
			$errno = 1;
			$message = "未查询到历史工人！";
			return $this->result($errno,$message);
		}
		foreach($user['historical_worker'] as $ap_k=>$worker_id){
			$worker_id=intval(str_replace("'",'',$worker_id));
			if(empty($worker_id))continue;
			$worker=pdo_fetch('select id,avatar,nickname from ' . tablename($this->tabmember) . ' where  status=1 and id=:id and uniacid =:uniacid limit 1', array(':id' => $worker_id,'uniacid'=>$_W['uniacid']));
			if(empty($worker))continue;
			$worker['active']=0;
			$workers[] =$worker;
		}
		if(empty($workers)){
			$errno = 0;
			$message = "未查询到历史工人！";
			return $this->result($errno,$message);
		}

		return $this->result($errno,$message, $workers);
	}
	
	//发布任务
	public function doPageGetRelease(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		if(empty($_W['openid'])||$_W['openid'] ==null){
			$errno = 1;
			$message = "用户错误！";
			return $this->result($errno, $message);
		}
		$id=intval($_GPC['order_id']);
		if(!empty($_GPC['appoint'])){
			$_GPC['appoint']=explode(',',$_GPC['appoint']);
			foreach($_GPC['appoint'] as & $appint){
				$appint="'".$appint."'";
			}
			$_GPC['appoint']=implode(',',$_GPC['appoint']);
		}

		$release=pdo_get($this->tabrelease,array('uniacid'=>$_W['uniacid'],'count_price'=>$_GPC['count_price'],'id'=>$id));
		if($_GPC['static'] == 4){
		    $_GPC['price'] = $_GPC['count_price'];
		    /*
            if($_GPC['nums'] * $_GPC['price'] != $_GPC['count_price']){
                $errno = 1;
                $message = "提交失败，请稍后重试！";
                return $this->result($errno, $message);
            }
		    */
        }else{
            if($_GPC['price']  * $_GPC['count']!= $_GPC['count_price']){
                $errno = 1;
                $message = "提交失败，请稍后重试！";
                return $this->result($errno, $message);
            }
        }

        $setting = $this->seeting;

		$_GPC['nums'] = empty($_GPC['nums']) ? 1 : $_GPC['nums'];
		$total_price = round(($_GPC['count_price'] - $_GPC['count_price'] * $setting['pumping'] / 100) / $_GPC['nums'],2); //每个接单人的金额

		$address=$_GPC['region'].' '.$_GPC['address'];
		$data = array(
			'uniacid' => $_W['uniacid'], 
			'openid' => $_W['openid'], 
			'createtime' => time(), 
			'type' => $_GPC['type'], 
			'static' => $_GPC['static'], 
			'price' => $_GPC['price'],
			'images' => serialize(explode(',', $_GPC['images'])), 
			'total_price' => $total_price,
			'count_price' => $_GPC['count_price'], 
			'count' =>$_GPC['count'], 
			'starttime' => strtotime($_GPC['starttime']), 
			'address' => $address,
			'nums' => $_GPC['nums'],
			'sex' => $_GPC['sex'],
			'description' => $_GPC['description'],
			'appoint' => $_GPC['appoint'],
			'lat' => $_GPC['lat'],
			'lng' => $_GPC['lng'],
            'cancel_refund_money' => $_GPC['bond'],
            'can_refund_bond' => 1
		);
		
		//由于提交到微信的订单金额无法修改下面方法不可取只能每次生成新订单了……
		if(empty($release)){
			$data['ordersn']=$this->createNO('release','ordersn','PU');	
			pdo_insert($this->tabrelease, $data);
			$id = pdo_insertid();
		}else{
			if(empty($release['ordersn'])){
				$data['ordersn']=$this->createNO('release','ordersn','PU');	
			}

			pdo_update($this->tabrelease, $data,array('uniacid'=>$_W['uniacid'],'id'=>$release['id']));
			$data['ordersn']=$release['ordersn'];
			$id=$release['id'];
		}
		
		if($id){
			$data['id']=$id;
			$data['credit_enough']=($this->user['credit2'] - $data['count_price'] - $data['cancel_refund_money'])>0;//余额是否足够,总金额+保证金

			//调用支付
			$pay_data=$this->doPagePay(array('type'=>1,'ordersn'=>$data['ordersn']));
			$data['pay_data']=$pay_data;
			$data['all_money'] = $data['count_price'] + $data['cancel_refund_money'];

			$formId = $_GPC['formid'];
			$this->saveFormId($_W['openid'], $formId);
			return $this->result($errno, $message, $data);
		}else{
			return $this->result(1, '发布失败！', $data);
		}
	}
	//余额支付
	public function doPageCreditIssusrPay(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$order_id=$_GPC['order_id'];
		$formId = $_GPC['formid'];
		$order=pdo_get($this->tabrelease,array('uniacid'=>$_W['uniacid'],'id'=>$order_id));
		if($order['pay_status']==1){
			$errno = 1;
			$message = '订单已支付,请勿重复支付';
			return $this->result($errno, $message, $data);
		}
		// [2018-11-24 00:58:34] trace  /payment/wechat/notify.php? weid=; uniacid=3; acid=3; result=success; type=wxapp; from=notify; tid=112334561; uniontid=2018112400582800001748764644; transaction_id=; trade_type=JSAPI; follow=0; user=oydkk0dlJhWPxBof7DX4Y3AZWL5Y; fee=0.01; tag={acid=3; uid=6; transaction_id=4200000233201811242837695091; }; is_usecard=0; card_type=0; card_fee=0.01; card_id=; paytime=1542992313; 
		$money = $order['count_price'] + $order['cancel_refund_money'];
        if($this->user['credit2'] < $money){
			$errno = 1;
			$message = '您的余额不足以支付,请选择其他支付方式!';
			return $this->result($errno, $message, $data);
		}
		//扣余额
		 $log[0]='';
		 $log[1]='发布方支付订单!';
		 $log[2]='jujiwuliu';
		 $log[3]='';
		 $log[4]='';
		 $log[5]='1';
		 $log[6]=$order['ordersn'];
		 $uid=mc_openid2uid($order['openid']);
		 $ref=$this->credit_update($uid, 'credit2', '-'.$money, $log ,1);
		if(empty($ref)){
			$errno = 1;
			$message = '付款失败,请重试!';
			return $this->result($errno, $message);
		}
		$log['pay_type']=2;
		$log['tid']=$order['ordersn'];
		$log['formid'] = $formId;
		$this->payResult($log);

		$errno = 0;
		$message = '付款成功!';
		return $this->result($errno, $message);
	}
	//我的发布列表
	public function doPageGetIssusrList(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$page = empty($_GPC["page"]) ? "" : $_GPC["page"];
		$pindex  = max(1, intval($page));
		$pagesize = 6;
		$lat=$_GPC['lat'];
		$lng=$_GPC['lng'];
		$type=intval($_GPC["type"]);//获取类型（进行中、已完成、已取消）
        $status = array(0,1,3,4);

//		$data = pdo_fetchall("SELECT *,(lat-:lat) * (lat-:lat) + (lng-:lng) * (lng-:lng) as dist FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and status=".$type." and openid=:openid and pay_status=1 ORDER BY dist LIMIT " . (($pindex - 1) * $pagesize) . ',' . $pagesize, array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ":lat" => $lat, ":lng" => $lng));
		$data = pdo_fetchall("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=".$_W['uniacid']." and status=".$status[$type]." and openid='".$_W['openid']."' and pay_status=1 ORDER BY id DESC LIMIT " . (($pindex - 1) * $pagesize) . ',' . $pagesize);

		foreach($data as & $res){
			$res['images']=unserialize($res['images']);
			foreach ($res['images'] as $k => $image){
			    if(empty($image)){
			        $res['images'][$k] = 'default.png';
                }
            }

			$res['typename'] = $this->type_set[$res['type']];
			$res['unit'] = $this->unit_set[$res['type']];
			$res['staticname'] = $this->static_set[$res['static']];
			$res['sex'] = $this->sex_set[$res['sex']];
			$res['starttime'] = !empty($res['starttime']) ? date('Y-m-d H:i:s', $res['starttime']) : '';
			$res['statusname'] =  $this->status_set[$res['status']];
			$res['yet'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and rid=:rid and deleted=0", array(':uniacid' => $_W['uniacid'], ':rid' => $res['id']));
			$res['gptu'] = $res['nums']  - $res['yet'];
            $res['bondname'] = empty($res['can_refund_bond']) ? '不可退' : '可退';
			
			$res['distance']=$this->getDistance($res['lat'],$res['lng'],$lat,$lng);
			if($res['distance']<15){
				$distance=file_get_contents("HTTPS://apis.map.qq.com/ws/distance/v1/matrix/?mode=driving&from=".$res['lat'].",".$res['lng']."&to=".$lat.",".$lng."&key=F2GBZ-SREWQ-A3K56-GSLK5-ELOHS-PRB2X");
				$distance=json_decode($distance,true);
	 			$res['distance']=$distance['result']['rows'][0]['elements'][0]['distance']?rand((($distance['result']['rows'][0]['elements'][0]['distance'])/1000),2):$res['distance'];
			}
		}
		
		return $this->result($errno, $message, $data);
	}
	//单条发布信息
	public function doPageGetIssusr(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$id=intval($_GPC['id']);
		if(empty($id)){
			$errno = 1;
			$message = '未查询到此记录！';
		}
		$data = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and openid=:openid and id =:id ", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'],':id'=>$_GPC['id']));
		if(empty($data)){
			$errno = 1;
			$message = '未查询到此记录！';
		}else{
			
			//查询指定人员
			$data['is_appoint']=$data['appoint']?1:0;
			$data['appoint']=explode(',',$data['appoint']);
			if(!empty($data['is_appoint'])){
				foreach ($data['appoint'] as $appk =>$appval) {
					$appval=intval(str_replace("'",'',$appval));
					$us_openid=$this->mid2openid($appval);
					//查询此人是否接单
					$accept=pdo_get($this->taborder,array('uniacid' => $_W['uniacid'],'rid'=>$data['id'],'openid'=>$us_openid));
					$data['accepts'][$appk]['order']=$accept?$accept:0;
					$data['accepts'][$appk]['user']=pdo_get($this->tabmember,array('uniacid' => $_W['uniacid'],'openid'=>$us_openid));
					unset($us_openid);
				}
			}
			
			//查询接单人
			if(empty($data['is_appoint'])){
				$accepts=pdo_getall($this->taborder,array('uniacid' => $_W['uniacid'],'rid'=>$data['id']));
				foreach ($accepts as $cakey=> $accept) {
					$data['accepts'][$cakey]['order']=$accept;
					$data['accepts'][$cakey]['user']=pdo_get($this->tabmember,array('uniacid' => $_W['uniacid'],'openid'=>$accept['openid']));
				}
			}

			$data['images']=unserialize($data['images']);
			$data['typename'] = $this->type_set[$data['type']];
			$data['unit'] = $this->unit_set[$data['type']];
			$data['staticname'] = $this->static_set[$data['static']];
			$data['sex'] = $this->sex_set[$data['sex']];
			$data['starttime'] = !empty($data['starttime']) ? date('Y-m-d H:i:s', $data['starttime']) : '';
			$data['statusname'] =  $this->status_set[$data['status']];

			//判断是否有剩余金额
            $data['apply_surplus'] = 0; //默认不用申请
            if($data['status'] == 3 && $data['refund_money'] == 0 && $data['surplus_status'] == 0){
                $hasMoneyNum = pdo_fetchcolumn("select count(id) from " . tablename($this->taborder) . " where status<>4 and rid=:id", array(':id' => $data['id']));
                if($hasMoneyNum < $data['nums']){
                    $data['apply_surplus'] = 1;
                }
            }

			//判断是否支付
            if(empty($data['pay_status'])){
                $data['credit_enough']=($this->user['credit2'] - $data['count_price'] - $data['cancel_refund_money'])>0;//余额是否足够,总金额+保证金
                //调用支付
                $pay_data=$this->doPagePay(array('type'=>1,'ordersn'=>$data['ordersn']));
                $data['pay_data']=$pay_data;
            }
		}
		
		return $this->result($errno, $message, $data);
	}
	//取消发布订单
	public function doPageIssusrCancel(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$seeting=$this->seeting;
		$id=intval($_GPC['id']);
        $formId = $_GPC['formid'];
		if(empty($id)){
			$errno = 1;
			$message = '未查询到此记录！';
		}
		$data = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and openid=:openid and id =:id ", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'],':id'=>$id));
		if(empty($data)){
			$errno = 1;
			$message = '未查询到此记录！';
		    return $this->result($errno, $message, $data);

		}else{
		    //获取接单信息，判断是否已经有人接单
            $orderCount = pdo_fetchcolumn('select count(id) from' . tablename($this->taborder) . ' where rid=:rid and uniacid=:uniacid and status!=4', array(':rid' => $id, ':uniacid' => $_W['uniacid']));
            if($orderCount){
                return $this->result(1,'已有人接单，不能取消');
            }


			if($data['status']==0){
                $data['status'] = $update['status'] = 4;
			    //超出时间不能退保证金
			    if(($data['createtime'] - time()) >= ($seeting['issue_cancel_time'] * 60)){
			        $data['can_refund_bond'] = $update['can_refund_bond'] = 0;
                }

                $ref = pdo_update($this->tabrelease,$update,array('uniacid'=>$_W['uniacid'],'id'=>$data));
                if($ref){
                    //取消订单消息
                    $data = array(
                        'first' => array(
                            'value' => "取消发布成功！",
                            'color' => '#ff510'
                        ),
                        'keyword1' => array(
                            'value' => '',
                            'color' => '#ff510'
                        ),
                        'keyword2' => array(
                            'value' => '已取消',
                            'color' => '#ff510'
                        ),
                        'keyword3' => array(
                            'value' => date('Y-m-d H:i:s', time()),
                            'color' => '#ff510'
                        ),
                        'keyword4' => array(
                            'value' => '' ,
                            'color' => '#ff510'
                        ),
                    );
                    /*
                    $result = $this->sendTplNotice($_W['openid'], 'hWvSnAaQ_Atx_BOFLNuTMCxQRttC1Wu3El1U8MCvRS4', $data, $formId);
                    if(is_error($result)){
                        return $this->result($result['errno'], $result['message']);
                    }
                    */

                    return $this->result(0, '操作成功!', $data);
                }else{
                    return $this->result(1, '操作失败!', $data);
                }
			    /*
                //退款金额，当前不退保证金
                $cancel_refund_money=$data['count_price']-$data['refund_money'];
                //金额只退款到余额
                $log[0]='';
                $log[1]='发布方申请退款!';
                $log[2]='jujiwuliu';
                $log[3]='';
                $log[4]='';
                $log[5]='1';
                $log[6]=$data['ordersn'];
                $uid=mc_openid2uid($data['openid']);

                $ref=$this->credit_update($uid, 'credit2', $cancel_refund_money, $log ,1);
                if($ref){
                    pdo_update($this->tabrelease,$update,array('uniacid'=>$_W['uniacid'],'id'=>$data));
                    return $this->result(0, '操作成功!', $data);
                }else{
                    return $this->result(1, '操作失败!', $data);
                }
			    */


			    //如果是余额支付则退款到余额.
                /*
				if($data['pay_type']==2){
					 //积分余额记录
					$log[0]='';
					$log[1]='发布方申请退款!';
					$log[2]='jujiwuliu';
					$log[3]='';
					$log[4]='';
					$log[5]='1';
					$log[6]=$data['ordersn'];
					$uid=mc_openid2uid($data['openid']);

					$ref=$this->credit_update($uid, 'credit2', $cancel_refund_money, $log ,1);
					if($ref){
						//修改订单
						pdo_update($this->tabrelease,array('status'=>2),array('uniacid'=>$_W['uniacid'],'id'=>$data));
						return $this->result(0, '操作成功!', $data);
					}else{
						return $this->result(1, '操作失败!', $data);
					}
				}
                */
			//如果是微信支付则调用微信退款方法
                /*
				if($data['pay_type']==1){
					$refund= $this->refund($data['ordersn'],$cancel_refund_money,'发布方申请微信退款!');//退款方法调用
					if($refund['errno']!=1){
						//修改订单
						pdo_update($this->tabrelease,array('status'=>2),array('uniacid'=>$_W['uniacid'],'id'=>$data));
						return $this->result(0, '操作成功!', $data);
					}else{
						return $this->result(1,$refund['message'], $data);
					}
				}
                */
			}else{
				return $this->result(1, '订单已取消!', $data);
			}
			
		}
		return $this->result(0, '操作成功!', $data);
	}

    /**
     * 申请退还多余金额不用
     */
    public function apply_surplus(){
        global $_W,$_GPC;
        $id = intval($_GPC['rid']);
        $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid and status=4 and apply_refund=0', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($release)){
            return $this->result(1,'没有找到需要退款的订单');
        }
        $count = pdo_fetchcolumn("select count(id) from " . tablename($this->taborder) . " where status<>4 and rid=:id", array(':id' => $release['id']));
        if($release['surplus_status'] == 0 && $release['status'] == 3 && ($count < $release['nums'])){
            pdo_update($this->tabrelease, array('surplus_status' => 1), array('id' => $release['id']));
            return $this->result(0);
        }
        return $this->result(1,'没有找到需要退款的订单');
    }

    /**
     * 申请退款
     */
	public function doPageApplyMoney(){
        global $_GPC, $_W;
        $errno = 0;
        $message = 'success';
        $id = intval($_GPC['id']);
        $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid and status=4 and apply_refund=0', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($release)){
            return $this->result(1,'没有找到需要退款的订单');
        }

        $ref = pdo_update($this->tabrelease, array('apply_refund' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
        if($ref){
            return $this->result($errno, $message);
        }else{
            return $this->result(1,'申请退款失败');
        }
    }

    /**
     * 申请保证金退款
     */
    public function doPageApplyBond(){
        global $_GPC, $_W;
        $errno = 0;
        $message = 'success';
        $id = intval($_GPC['id']);
        $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid and status in (3,4) and apply_refund_bond=0 and can_refund_bond=1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($release)){
            return $this->result(1,'没有找到需要退款的订单');
        }
        $ref = pdo_update($this->tabrelease, array('apply_refund_bond' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
        if($ref){
            return $this->result($errno, $message);
        }else{
            return $this->result(1,'申请保证金退款');
        }
    }

    /**
     * 发布方申请结算
     */
    public function doPageSetreleaseapply(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $formid = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formid);
        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and apply=0 and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($order)){
            return $this->result(1, '没有找到可结算订单');
        }
        $ref = pdo_update($this->taborder, array('apply' => 1, 'apply_time'=>time(), 'status' => 2), array('id' => $id, 'uniacid' => $_W['uniacid']));
        if($ref){
            return $this->result(0,'');
        }else{
            return $this->result(1,'结算失败');
        }
    }

    /**
     * 发布方确认打款
     */
    public function doPageReleasePay(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $formid = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formid);
        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and apply=1 and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($order)){
            return $this->result(1, '没有找到可结算订单');
        }

        $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid', array(':id' => $order['rid'], ':uniacid' => $_W['uniacid']));

        $member = mc_openid2uid($release['openid']);

        $log=array(
            $member,
            '发布方确认打款',
            'jujiwuliu',
            '',
            '',
            4,
            $release['ordersn']
        );

        $uid=mc_openid2uid($order['openid']);

        $i = $this->credit_update($uid, 'credit2', $release['total_price'], $log);
        if($i){
            pdo_update($this->taborder, array('status' => 3,'apply' => 2), array('id' => $id, 'uniacid' => $_W['uniacid']));
            return $this->result(0,'');
        }else{
            return $this->result(1, '确认打款失败');
        }
    }

    /**
     * 人未到场
     */
    public function doPageNotPresent(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $formid = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formid);
        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(!empty($order)){
            $update = array(
                'status' => 4,
                'can_refund_bond' => 0
            );
            pdo_update($this->taborder, $update, array('id' => $id));
        }

        return $this->result(0);
    }

	//发布方所有方法结束
	
	//下面是搬运工所有方法
//	搬运列表
	public function doPageGetList(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$lat=$_GPC['lat'];
		$lng=$_GPC['lng'];
		$default_distance = intval($_GPC['distance']);  //距离
		$seeting=$this->seeting;
		if(empty($_W['openid'])||$_W['openid'] ==null){
			$errno = 1;
			$message = "用户错误！";
			return $this->result($errno, $message);
		}
		if($_GPC['status']==1){
//				$list = pdo_fetchall("SELECT *,(lat-:lat) * (lat-:lat) + (lng-:lng) * (lng-:lng) as dist FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and openid=:openid and pay_status=1 ORDER BY dist desc " , array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ":lat" => $lat, ":lng" => $lng));
			
			//查询没接单的
//			select id FROM usertable LEFT JOIN (select id as i from blog) as t1 ON usertable.id=t1.i where t1.i IS NULL
			$list = pdo_fetchall("SELECT *,(t.lat-:lat) * (t.lng-:lng) as dist FROM ".tablename($this->tabrelease)." as t left join (select rid as o_rid from ".tablename($this->taborder)." where openid = :openid) as o on t.id=o.o_rid WHERE o.o_rid is null and t.uniacid=:uniacid and (starttime-".time().")>10 and t.pay_status=1 and t.status<>4 and t.deleted=0 ORDER BY dist desc " , array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ":lat" => $lat, ":lng" => $lng));
			// var_dump("SELECT *,(t.lat-".$lat.") * (t.lng-".$lng.") as dist FROM ".tablename($this->tabrelease)." as t left join (select rid as o_rid from ".tablename($this->taborder)." where openid = '".$_W['openid']."') as o on t.id=o.o_rid WHERE o.o_rid is null and t.uniacid='".$_W['uniacid']."' and (starttime-".time().")>1800 and t.pay_status=1 ORDER BY dist desc ");
			//查询开工时间半小时以上的发布信息 秒1800
//				$list = pdo_fetchall("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and deleted=0 and id not in(SELECT id FROM ".tablename($this->taborder)." WHERE openid = '".$_W['openid']."') order by id desc", array(':uniacid' => $_W['uniacid']));

		}
		if($_GPC['status']==2){
			$list = pdo_fetchall("SELECT b.* FROM ".tablename($this->taborder)." a left join ".tablename($this->tabrelease)." b on a.rid=b.id WHERE b.uniacid=:uniacid and b.deleted=0 and a.openid=:openid and a.status<=2 and a.status>=0 and a.deleted=0 order by a.id desc", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		}
		
		if($_GPC['status']==3){
			$list = pdo_fetchall("SELECT b.* FROM ".tablename($this->taborder)." a left join ".tablename($this->tabrelease)." b on a.rid=b.id WHERE b.uniacid=:uniacid and b.deleted=0 and a.openid=:openid and a.status=4 and a.deleted=0 order by a.id desc", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		}
		if($_GPC['status']==4){
			$list = pdo_fetchall("SELECT b.* FROM ".tablename($this->taborder)." a left join ".tablename($this->tabrelease)." b on a.rid=b.id WHERE b.uniacid=:uniacid and b.deleted=0 and a.openid=:openid and a.status=3 and a.deleted=0 order by a.id desc", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		}

		$workers = 0;   //已接单数量
		foreach($list as $key => $res){
		    //判断是否已经被接单
            $count = pdo_fetchcolumn('select count(id) from ' .  tablename($this->taborder) . ' where rid=:rid and status<>4', array(':rid' => $res['id']));
            if($_GPC['status'] == 1){
                if(!empty($count) && $count >= $res['nums']){
                    unset($list[$key]);
                    continue;
                }
            }

            /*
            if($_GPC['status']){
                $count = pdo_fetchcolumn('select count(id) from ' .  tablename($this->taborder) . ' where rid=:rid', array(':rid' => $res['id']));
            }
            */

            //计算距离
            $res['distance']=$this->getDistance($res['lat'],$res['lng'],$lat,$lng);
            if($_GPC['status'] == 1){
                if($default_distance > 0){
                    $distance_value = $this->distance_set[$default_distance];
                    if($res['distance'] > $distance_value){
                        unset($list[$key]);
                        continue;
                    }
                }
            }

            //if($res['distance'] < $default_distance){
                $distance=file_get_contents("HTTPS://apis.map.qq.com/ws/distance/v1/matrix/?mode=driving&from=".$res['lat'].",".$res['lng']."&to=".$lat.",".$lng."&key=MF2BZ-SUJCQ-ISK5A-GHYV5-LQU42-KPB3I");
                $distance=json_decode($distance,true);
                $res['distance']=$distance['result']['rows'][0]['elements'][0]['distance']?rand((($distance['result']['rows'][0]['elements'][0]['distance'])/1000),2):$res['distance'];
            //}

			$res['images']=unserialize($res['images']);
            foreach ($res['images'] as $k => $image){
                if(empty($image)){
                    $res['images'][$k] = 'default.png';
                }
            }
			$res['typename'] = $this->type_set[$res['type']];
			$res['unit'] = $this->unit_set[$res['type']];
			$res['staticname'] = $this->static_set[$res['static']];
			$res['sex'] = $this->sex_set[$res['sex']];
			$res['starttime'] = !empty($res['starttime']) ? date('Y-m-d H:i:s', $res['starttime']) : '';
			$res['statusname'] =  $this->status_set[$res['status']];
            //$res['yet'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and rid=:rid and deleted=0 and status<>4", array(':uniacid' => $_W['uniacid'], ':rid' => $res['id']));
            $res['yet'] = empty($count) ? 0 : $count;
            $res['gptu'] = $res['nums']  - $res['yet'];
//			if($_GPC['status']==1){

//			}
			//计算抽成后价格与金额
			
			//$res['price']=$res['price']-$res['price']*$seeting['pumping']/100;
			//$res['total_price']=$res['total_price']-$res['total_price']*$seeting['pumping']/100;
            $res['price'] = round($res['total_price'] / $res['count'],2);
            $res['total_price']=$res['total_price'];
			$res['count_price']=$res['count_price']-$res['count_price']*$seeting['pumping']/100;

			$list[$key] = $res;
		}
		//unset($res);
		$data['list'] = $list;
//		$data['status1'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and deleted=0 and id not in(SELECT id FROM ".tablename($this->taborder)." WHERE openid = '".$_W['openid']."')", array(':uniacid' => $_W['uniacid']));
		$data_status1 = pdo_fetchall("SELECT lat,lng,id FROM ".tablename($this->tabrelease)." as t left join (select rid as o_rid from ".tablename($this->taborder)." where openid = :openid) as o on t.id=o.o_rid WHERE o.o_rid is null and t.uniacid=:uniacid and t.pay_status=1 and t.deleted=0 and (starttime-".time().")>10" , array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		foreach ($data_status1 as $key => $item){
            $count = pdo_fetchcolumn('select count(id) from ' .  tablename($this->taborder) . ' where rid=:rid and status<>4', array(':rid' => $item['id']));
            if(!empty($count) && $count >= $res['nums']){
                unset($data_status1[$key]);
                continue;
            }
            $distance = $this->getDistance($item['lat'],$item['lng'],$lat,$lng);
            if($default_distance > 0){
                $distance_value = $this->distance_set[$default_distance];
                if($distance > $distance_value){
                    unset($data_status1[$key]);
                    continue;
                }
            }
        }
		$data['status1'] = count($data_status1);
		$data['status4'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and openid=:openid and status=3 and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		$data['status2'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and openid=:openid and status<=2 and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		$data['status3'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and openid=:openid and status=4 and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		if($data){
			return $this->result($errno, $message, $data);
		}else{
			return $this->result(1, '数据为空！');
		}
	}
	//	搬运详情
	public function doPageGetListdetails(){
		global $_GPC, $_W;
		$errno = 0;
		$message = 'success';
		$id = $_GPC['id']; 
		$rid = $_GPC['rid']; 
		$lat=$_GPC['lat'];
		$lng=$_GPC['lng'];
		$setting=$this->seeting;
		
		$prim=array(':uniacid' => $_W['uniacid']);
		if(!empty($rid)){
			$prim[':rid']=$rid;
			$condition=' and id =:rid ';
		}
		// var_dump("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and deleted=0 and id=:id".$condition,$prim);die;
		$item = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and deleted=0  ".$condition, $prim);
		// var_dump($id);die;
		if(!empty($item)){
            $deposit = $item['total_price'] * $setting['security'] / 100;   //保证金
            $or_prim=array(':uniacid' => $_W['uniacid'], ':rid' => $item['id']);
            if(empty($id)){
                $or_prim[':openid']=$_W['openid'];
                $or_condition=' and openid =:openid ';
            }else{
                $or_prim[':id']=$id;
                $or_condition=' and id =:id ';
            }

			$order = pdo_fetch("SELECT * FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and deleted=0 and rid=:rid".$or_condition,$or_prim);
			if(!empty($order)){
				$item['orderid'] = $order['id'];
				$item['apply'] = $order['apply'];
				$item['deposit_paystatus'] = $order['deposit_paystatus'];
				$item['can_refund_bond'] = $order['can_refund_bond'];
				$deposit = $order['deposit'];
			}
			$item['images']=unserialize($item['images']);
			
			$item['typename'] = $this->type_set[$item['type']];
			$item['unit'] = $this->unit_set[$item['type']];
			$item['staticname'] = $this->static_set[$item['static']];
			$item['sex'] = $this->sex_set[$item['sex']];
			$item['starttime'] = !empty($item['starttime']) ? date('Y-m-d H:i:s', $item['starttime']) : '';
			$item['statusname'] =  $this->status_set[$item['status']];
			$item['yet'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and rid=:rid and status<>4 and deleted=0", array(':uniacid' => $_W['uniacid'], ':rid' => $item['id']));
			$item['gptu'] = $item['nums']  - $item['yet'];
			$item['deposit_paystatus'] = empty($order) ? 1 : $order['deposit_paystatus'];
			$item['credit_enough'] = ($this->user['credit2'] - $deposit) >= 0 ? 1 : 0;
			$item['order_status'] = empty($order) ? 0 : $order['status'];
			
			//计算距离
			$item['distance']=$this->getDistance($item['lat'],$item['lng'],$lat,$lng);

			if($item['distance']<15){
				$distance=file_get_contents("HTTPS://apis.map.qq.com/ws/distance/v1/matrix/?mode=driving&from=".$item['lat'].",".$item['lng']."&to=".$lat.",".$lng."&key=F2GBZ-SREWQ-A3K56-GSLK5-ELOHS-PRB2X");
				$distance=json_decode($distance,true);
	 			$item['distance']=$distance['result']['rows'][0]['elements'][0]['distance']?rand((($distance['result']['rows'][0]['elements'][0]['distance'])/1000),2):$item['distance'];
			}

			//获取发布方手机号
            $phone = pdo_fetchcolumn('select mobile from ' . tablename($this->tabmember) . ' where openid=:openid', array(':openid' => $item['openid']));
			$item['release_mobile'] = $phone;
			//获取接单方手机号
            if(empty($order)){
                $jd_openid = $_W['openid'];
            }else{
                $jd_openid = $order['openid'];
            }
            $jd_phone = pdo_fetchcolumn('select mobile from ' . tablename($this->tabmember) . ' where openid=:openid', array(':openid' => $jd_openid));
			$item['jd_mobile'] = $jd_phone;
            //计算抽成后价格与金额
			
			//$item['price']=$item['price']-$item['price']*$setting['pumping']/100;
			//$item['total_price']=$item['total_price']-$item['total_price']*$setting['pumping']/100;
            $item['price'] = round($item['total_price'] / $item['count'],2);
			$item['count_price']=$item['count_price']-$item['count_price']*$setting['pumping']/100;
			$item['overtime'] = empty($setting['issue_worker_time']) ? 0 : $setting['issue_worker_time'];
			
		}
		if($item){
			return $this->result($errno, $message, $item);
		}else{
			return $this->result(1, '数据为空！');
		}
	}
	
	
//	搬运工开工休息
	public function doPageGetrest(){
		global $_GPC, $_W;
		if(empty($_W['openid'])){
			return $this->result(1, 'openid为空！');
		}
		$errno = 0;
		$message = 'success';
		$user = pdo_fetch("SELECT id, status FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));

		if(!empty($user)){
		//如果有工作中订单将不允许开工
			$status = ($user['status'] == 0) ? 1 : 0;
			$is_order = pdo_fetch("SELECT id, status FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and openid=:openid and status!=3 and status!=4 and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
			if($is_order && $status==1){
				return $this->result(1, '您尚有未完成的订单，请完成订单再来接单！');
			}
			$result = pdo_update($this->tabmember, array('status' => $status), array('id' => $user['id']));
			if($result){
				return $this->result($errno, $message, $status);
			}else{
				return $this->result(1, '修改失败！');
			}
		}else{
			return $this->result(1, '获取失败！');
		}
	}

    public function doPageGetWorkerPay(){
        global $_GPC,$_W;
        $formId = $_GPC['formid'];
        $id = $_GPC['id'];
        $this->saveFormId($_W['openid'], $formId);
        $is_order = pdo_fetch("SELECT id, status FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and openid=:openid and status!=3 and status!=4 and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if($is_order){
            return $this->result(1, '您尚有未完成的订单，请完成订单再来接单！');
        }
        $release = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and id=:id and deleted=0 and pay_status=1", array(':uniacid' => $_W['uniacid'], ':id' => $id));
        if(!empty($release)){
            //判断单子是否已经取消
            if($release['status'] == 4){
                return $this->result(1,'单子已被取消，不能接单');
            }

            //判断是否已经有人接单
            $oldOrder = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->taborder) . ' WHERE uniacid=:uniacid AND status<>4 AND rid=:rid', array(':uniacid' => $_W['uniacid'], ':rid' => $id));
            if($oldOrder && ($oldOrder >= $release['nums'])){
                return $this->result(1,"单子已被接单");
            }

            $member = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid ", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
            //判断性别

            $setting=$this->seeting;

            //押金
            $deposit = $release['count_price'] * $setting['security'] / 100;

            $depositsn = $this->createNO('order','depositsn','JU');

            //判断余额是否足够
            $credit_enough = ($this->user['credit2'] - $deposit) >= 0 ? 1 : 0;

            $insertData = array(
                'uniacid' => $_W['uniacid'],
                'ordersn'   => $depositsn,
                'fee' => $deposit,
                'rid' => $id,
                'openid' => $_W['openid'],
                'status' => 0
            );

            pdo_insert($this->tabbond, $insertData);

            $pay_data=$this->doPagePay(array('type'=>3,'ordersn'=>$depositsn));

            return $this->result(0,'', array('ordersn' => $depositsn,'credit_enough' => $credit_enough, 'pay_data' => $pay_data, 'bond' => $deposit));
        }else{
            return $this->result(1, '没有找到接单信息');
        }
    }

    public function getTakeOrder($info,$paytype){
        global $_W;

        $release = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and id=:id and deleted=0 and pay_status=1", array(':uniacid' => $_W['uniacid'], ':id' => $info['rid']));

        //判断是否已经被接单
        $count = pdo_fetchcolumn('select count(id) from ' . tablename($this->taborder) . ' where rid=:id and status<>4', array(':id' => $release['id']));
        if(!empty($count) && $count >= $release['nums']){
            $uid = mc_openid2uid($_W['openid']);
            $this->credit_update($uid,'credit2', $info['fee'], array(0,'系统退还保证金','jujiwuliu','','',4,$info['ordersn']), true);
            return $this->result(1,'单子已被接，无法接单');
        }


        $member = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid ", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));

        $data = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $info['openid'],
            'rid' => $info['rid'],
            'createtime' => time(),
            'deposit' => $info['fee'],  //押金
            'deposit_paystatus' => 1,
            'deposit_paytype' => $paytype,
            'depositsn' => $info['ordersn'],
            'can_refund_bond' => 1
        );
        pdo_insert($this->taborder, $data);
        $id = pdo_insertid();

        //写入历史接单人
        $release_member = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid ", array(':uniacid' => $_W['uniacid'], ':openid' => $release['openid']));
        if($release_member){
            $release_member['historical_worker']=explode(',', $release_member['historical_worker']);
            if(!in_array("'".$member['id']."'", $release_member['historical_worker'])){
                $release_member['historical_worker'][]="'".$member['id']."'";
                $release_member['historical_worker']=implode(',', $release_member['historical_worker']);
                pdo_update($this->tabmember,$release_member,array('uniacid' => $_W['uniacid'], 'openid' => $release['openid']));
            }
        }

        pdo_update($this->tabbond, array('status' => 1), array('id' => $info['id']));

        $formidInfo = $this->getFormId($_W['openid']);
        if(!empty($formidInfo)){
            $this->sendMessage($release['id'],$_W['openid'],'',$formidInfo['formid'],'take_ok');
            $this->updateFormId($formidInfo['openid'],$formidInfo['id']);
        }

        return $this->result(0, '接单成功');
    }
	
//	接单
	public function doPageGettakeOrder(){
		global $_GPC, $_W;
		if(empty($_W['openid'])){
			return $this->result(1, 'openid为空！');
		}
		$errno = 0;
		$message = 'success';
		$id = $_GPC['id'];
		$formId = $_GPC['formid'];
		$is_order = pdo_fetch("SELECT id, status FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and openid=:openid and status!=3 and status!=4 and deleted=0", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		if($is_order){
			return $this->result(1, '您尚有未完成的订单，请完成订单再来接单！');
		}
		$release = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and id=:id and deleted=0 and pay_status=1", array(':uniacid' => $_W['uniacid'], ':id' => $id));
		if(!empty($release)){
		    //判断单子是否已经取消
            if($release['status'] == 4){
                return $this->result(1,'单子已被取消，不能接单');
            }

		    //判断是否已经有人接单
            $oldOrder = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->taborder) . ' WHERE uniacid=:uniacid AND status<>4 AND rid=:rid', array(':uniacid' => $_W['uniacid'], ':rid' => $id));
            if($oldOrder && ($oldOrder >= $release['nums'])){
                return $this->result(1,"单子已被接单");
            }

            $member = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid ", array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
            //判断性别

            $setting=$this->seeting;

            //押金
            $deposit = ($release['count_price'] / $release['nums']) * $setting['security'] / 100;

            $depositsn = $this->createNO('order','depositsn','JU');
			$data = array(
				'uniacid' => $_W['uniacid'],
				'openid' => $_W['openid'],
				'rid' => $release['id'],
				'createtime' => time(),
                'deposit' => $deposit,  //押金
                'deposit_paystatus' => 0,
                'depositsn' => $depositsn,
                'can_refund_bond' => 1
			);
			pdo_insert($this->taborder, $data);
			$id = pdo_insertid();
			
			//写入历史接单人
			$release_member = pdo_fetch("SELECT id FROM ".tablename($this->tabmember)." WHERE uniacid=:uniacid and openid=:openid ", array(':uniacid' => $_W['uniacid'], ':openid' => $release['openid']));
			if($release_member){
				$release_member['historical_worker']=explode(',', $release_member['historical_worker']);
				if(!in_array("'".$member['id']."'", $release_member['historical_worker'])){
					$release_member['historical_worker'][]="'".$member['id']."'";
					$release_member['historical_worker']=implode(',', $release_member['historical_worker']);
					pdo_update($this->tabmember,$release_member,array('uniacid' => $_W['uniacid'], 'openid' => $release['openid']));
				}
			}
			//接单提醒
            $credit_enough = ($this->user['credit2'] - $deposit) >= 0 ? 1 : 0;

            $result = $this->sendMessage($id, $release['openid'],'', $formId, 'issue_cancel');
			
			if($id){
                $pay_data=$this->doPagePay(array('type'=>3,'ordersn'=>$data['depositsn']));
				//修改状态为休息状态
				//pdo_update($this->tabmember, array('status' => 0), array('id' => $user['id']));
                if($release['nums'] <= ($oldOrder + 1)){
                   //pdo_update($this->tabrelease, array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
                }
				return $this->result($errno, $message, array('id' => $id, 'paystatus' => 0, 'credit_enough' => $credit_enough, 'pay_data' => $pay_data, 'bond' => $deposit));
			}else{
				return $this->result(1, '接单失败！');
			}
		}else{
			return $this->result(1, '该发布不存在！');
		}
	}

	//支付保证金
	public function doPageCreateWorkerPay(){
        global $_W,$_GPC;
        $ordersn = $_GPC['ordersn'];

        $order = pdo_get($this->tabbond, array('uniacid' => $_W['uniacid'], 'ordersn' => $ordersn));
        $money = $order['fee'];
        if($this->user['credit2'] < $money){
            $errno = 1;
            $message = '您的余额不足以支付,请选择其他支付方式!';
            return $this->result($errno, $message);
        }

        //扣余额
        $log[0]='';
        $log[1]='接单方支付保证金!';
        $log[2]='jujiwuliu';
        $log[3]='';
        $log[4]='';
        $log[5]='1';
        $log[6]=$order['ordersn'];
        $uid=mc_openid2uid($order['openid']);

        $ref=$this->credit_update($uid, 'credit2', '-'.$money, $log ,1);
        if(empty($ref)){
            $errno = 1;
            $message = '付款失败,请重试!';
            return $this->result($errno, $message);
        }
        $log['pay_type']=2;
        $log['tid']=$order['ordersn'];
        $this->payResult($log);

        $errno = 0;
        $message = '付款成功!';
        return $this->result($errno, $message);

    }

	//搬运工申请结算
	public function doPageSetApply(){
		global $_GPC, $_W;
		if(empty($_W['openid'])){
			return $this->result(1, 'openid为空！');
		}
		$errno = 0;
		$message = 'success';
		$id = $_GPC['id'];
        $lat=$_GPC['lat'];
        $lng=$_GPC['lng'];
        $formid = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formid);

		$order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and uniacid=:uniacid and status=1 and apply=0 and openid=:openid', array(':id' => $id, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if(empty($order)){
            return $this->result(1, '订单已取消或不存在');
        }
		$release  = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and id=:id and deleted=0", array(':uniacid' => $_W['uniacid'], ':id' => $order['rid']));
        if($release){
            if(($release['starttime'] - time()) < 1800){
                //return $this->result(1, '请在开工后30分钟后再试');
            }

            $release['distance']=$this->getDistance($release['lat'],$release['lng'],$lat,$lng);
            if($release['distance'] > 15){
                //return $this->result(1, '请到场地后');
            }
            /*
            if($item['distance']<15){
                $distance=file_get_contents("HTTPS://apis.map.qq.com/ws/distance/v1/matrix/?mode=driving&from=".$item['lat'].",".$item['lng']."&to=".$lat.",".$lng."&key=F2GBZ-SREWQ-A3K56-GSLK5-ELOHS-PRB2X");
                $distance=json_decode($distance,true);
                $item['distance']=$distance['result']['rows'][0]['elements'][0]['distance']?rand((($distance['result']['rows'][0]['elements'][0]['distance'])/1000),2):$item['distance'];
            }
            */

        }


		$upord=pdo_update($this->taborder, array('apply'=>1,'apply_time'=>time(), 'status' => 2),array('uniacid'=>$_W['uniacid'],'id'=>$id));
		
		if($upord){
			return $this->result($errno, $message, $id);
		}else{
			return $this->result(1, '接单失败！');
		}
	}

	//搬运工取消订单
    public function doPageCancelOrder(){
	    global $_GPC,$_W;
        if(empty($_W['openid'])){
            return $this->result(1, 'openid为空！');
        }
        $errno = 0;
        $message = 'success';
        $id = $_GPC['id'];
        $formId = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formId);
        $setting = $this->seeting;

        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and uniacid=:uniacid and openid=:openid', array(':id' => $id, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if(empty($order)){
            return $this->result(1,'订单已取消或不存在');
        }

        $update['status'] = 4;
        //$update['deposit_paystatus'] = 2;   //退款申请中
        if($setting['issue_worker_time'] && ((time() - $order['createtime']) > ($setting['issue_worker_time'] * 60))){
            $update['can_refund_bond'] = 0;
        }

        $upord = pdo_update($this->taborder, $update, array('uniacid' => $_W['uniacid'], 'id' => $id));
        if($upord){
            $formIdInfo = $this->getFormId($_W['openid']);
            if(!empty($formIdInfo)){
                $result = $this->sendMessage($order['rid'], $_W['openid'], '', $formIdInfo['formid'], 'take_cancel');
            }
            return $this->result($errno,$message);
        }else{
            return $this->result(1, '取消订单失败！');
        }
    }

    //申请退保证金
    public function doPageRefundWorkerBond(){
        global $_GPC,$_W;
        if(empty($_W['openid'])){
            return $this->result(1, 'openid为空！');
        }
        $errno = 0;
        $message = 'success';
        $id = intval($_GPC['id']);

        $formid = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formid);

        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and uniacid=:uniacid and status in (3,4) and deposit_paystatus=1 and can_refund_bond=1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($order)){
            return $this->result(1,'订单已申请退款或不存在');
        }

        $update['deposit_paystatus'] = 2;
        $upord = pdo_update($this->taborder, $update, array('uniacid' => $_W['uniacid'], 'id' => $id));
        if($upord){
            return $this->result($errno,$message);
        }else{
            return $this->result(1, '申请退款失败');
        }

    }

    //开工
    public function doPageStartOperation(){
	    global $_W,$_GPC;
        if(empty($_W['openid'])){
            return $this->result(1, 'openid为空！');
        }
        $errno = 0;
        $message = 'success';
        $id = $_GPC['id'];
        $lat=$_GPC['lat'];
        $lng=$_GPC['lng'];
        $seeting=$this->seeting;

        $formid = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formid);

        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and uniacid=:uniacid and status=0 and openid=:openid', array(':id' => $id, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if(empty($order)){
            return $this->result(1,'订单已取消或不存在');
        }
        $release = pdo_fetch("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid and id=:id and deleted=0", array(':uniacid' => $_W['uniacid'], ':id' => $order['rid']));
        if(!empty($release)){
            //计算距离
            $release['distance']=$this->getDistance($release['lat'],$release['lng'],$lat,$lng);

            if($release['distance']<15){
                $distance=file_get_contents("HTTPS://apis.map.qq.com/ws/distance/v1/matrix/?mode=driving&from=".$release['lat'].",".$release['lng']."&to=".$lat.",".$lng."&key=F2GBZ-SREWQ-A3K56-GSLK5-ELOHS-PRB2X");
                $distance=json_decode($distance,true);
                $release['distance']=$distance['result']['rows'][0]['elements'][0]['distance']?rand((($distance['result']['rows'][0]['elements'][0]['distance'])/1000),2):$release['distance'];
            }

            //开工时间
            if($release['starttime'] > time()){
                return $this->result(1,"未到开工时间");
            }

            $release['orderid'] = $order['id'];
            $release['apply'] = $order['apply'];
            $release['images']=unserialize($release['images']);

            $release['typename'] = $this->type_set[$release['type']];
            $release['unit'] = $this->unit_set[$release['type']];
            $release['sex'] = $this->sex_set[$release['sex']];
            $release['starttime'] = !empty($release['starttime']) ? date('Y-m-d H:i:s', $release['starttime']) : '';
            $release['statusname'] =  $this->status_set[$release['status']];
            $release['yet'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and rid=:rid and deleted=0", array(':uniacid' => $_W['uniacid'], ':rid' => $release['id']));
            $release['gptu'] = $release['nums']  - $release['yet'];

            //计算抽成后价格与金额

            $release['price']=$release['price']-$release['price']*$seeting['pumping']/100;
            //$release['total_price']=$release['total_price']-$release['total_price']*$seeting['pumping']/100;
            $release['count_price']=$release['count_price']-$release['count_price']*$seeting['pumping']/100;

            if($release['status'] == 1){
                //已有人开工
                $upord1 = pdo_update($this->taborder, array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
                if($upord1){
                    $this->result($errno,$message,$release);
                }else{
                    $this->result(1,"开工时间或地点不正确");
                }
            }else{
                pdo_begin();
                $upord1 = pdo_update($this->taborder, array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
                $upord2 = pdo_update($this->tabrelease, array('status' => 1), array('id' => $order['rid'], 'uniacid' => $_W['uniacid']));
                if($upord1 && $upord2){
                    pdo_commit();

                    $release['static'] = 1;
                    $release['staticname'] = $this->static_set[$release['static']];

                    $this->result($errno,$message,$release);
                }else{
                    pdo_rollback();
                    $this->result(1,"开工时间或地点不正确");
                }
            }
        }
    }

    //完成（发布方）
    public function doPageCloseOperation(){
        global $_W,$_GPC;
        if(empty($_W['openid'])){
            return $this->result(1, 'openid为空！');
        }
        $errno = 0;
        $message = 'success';
        $rid = $_GPC['rid'];
        //获取所有接单
        $orders = pdo_fetchall('select * from ' . tablename($this->taborder) . ' where rid=:id and uniacid=:uniacid and status<>4', array(':id' => $rid, ':uniacid' => $_W['uniacid']));
        if($orders){
            $count = 0;
            foreach ($orders  as $order){
                if($order['status'] == 0 || $order['status'] == 1){
                    $count += 1;
                }
            }
            if($count){
                return $this->result(1,'还有未完成的接单，无法完成订单');
            }

            //判断是否有多余的金额需要退还
            if($count == 0){
                $count = count($orders);
                $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id', array(':id' => $rid));
                if($count < $release){
                    $surplus = $release['nums'] - $count;
                    if($surplus < 0){
                        $surplus = 0;
                    }
                    $refund_money = round($release['count_price'] * $surplus / $release['nums'],2);
                    $uid = mc_openid2uid($_W['openid']);
                    $log = array(1,'退还多余金额','jujiwuliu','','',4,$release['ordersn']);
                    $result = $this->credit_update($uid,'credit2',$refund_money, $log);
                    if(is_error($result)){
                        return $this->result(1,$result['message']);
                    }
                    $update['refund_money'] = $surplus;
                    $update['refund_money'] = $refund_money;
                }
            }
        }

        $update['status'] = 3;
        
        //将所有已申请结算，未完成的接单完成掉
        pdo_update($this->taborder, array('status' =>3), array('rid' => $rid,'status' => 2));
        pdo_update($this->tabrelease, $update, array('id' => $rid));
        return $this->result($errno,$message);
    }

    /**
     * 获取使用说明
     */
    public function doPageGetExplain(){
        global $_GPC;
        $setting = $this->seeting;
        $type = intval($_GPC['type']);
        if($type == 1){
            $html = htmlspecialchars_decode($setting['explain']);
        }elseif ($type == 2){
            $html = htmlspecialchars_decode($setting['explain_receipt']);
        }

        return $this->result(0,'',$html);
    }

    /**
     * 获取开工地点地图信息
     */
    public function doPageGetMapInfo(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid', array(':id' => $id, 'uniacid' => $_W['uniacid']));
        if(empty($release)){
            return $this->result(1, '订单不存在或已删除!');
        }

        $user = pdo_fetch('select * from ' . tablename($this->tabmember) . ' where openid=:openid', array(':openid' => $release['openid']));
        $release['realname'] = $user['realname'];
        $release['mobile'] = $user['mobile'];
        return $this->result(0,'', $release);

    }

    public function doPageGetOrderPay(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $order = pdo_fetch('select * from ' . tablename($this->taborder) . ' where id=:id and uniacid=:uniacid and deposit_paystatus=0', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if(empty($order)){
            return $this->result(1, '订单保证金已支付！');
        }
        $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid', array(':id' => $order['rid'], ':uniacid' => $_W['uniacid']));
        $count = pdo_fetchcolumn('select count(id) from ' . tablename($this->taborder) . ' where rid=:rid and status<>4', array(':rid' => $release['id']));
        if($release['nums'] >= intval($count)){
            return $this->result(1,'单子已被接，请选择其它单子');
        }

        $credit_enough = ($this->user['credit2'] - $order['deposit']) >= 0 ? 1 : 0;

        $depositsn = $this->createNO('order','depositsn','JU');
        $upord = pdo_update($this->taborder, array('depositsn' => $depositsn), array('id' => $id));
        if($upord){
            $pay_data=$this->doPagePay(array('type'=>3,'ordersn'=>$depositsn));
            return $this->result(0,'', array('paystatus' => $order['deposit_paystatus'], 'credit_enough' => $credit_enough, 'pay_data' => $pay_data, 'bond' => $order['deposit']));
        }else{
            return $this->result(1, '支付参数错误!');
        }

    }
	//搬运工方法结束

    public function doPageGetAreaManaager(){
        global $_W,$_GPC;
        $openid = $_W['openid'];
        $item = pdo_fetch("select * from " . tablename($this->tabareamanager) . " where openid=:openid", array(':openid' => $openid));
        return $this->result(0,'',$item);
    }

    //提交地区负责人
    public function doPageSetAreaManager(){
        global $_W,$_GPC;
        $openid = $_W['openid'];
        $formid = $_GPC['formid'];
        $realname = $_GPC['realname'];
        $mobile = $_GPC['mobile'];
        $ago = (empty($_GPC['ago']) || $_GPC['ago'] == 'undefined') ? 0 : $_GPC['ago'];
        $sex = $_GPC['sex'];
        $idcard = (empty($_GPC['idcard']) || $_GPC['idcard'] == 'undefined') ? '' : $_GPC['idcard'];
        $address = $_GPC['address'];
        $weixinhao = (empty($_GPC['weixinhao']) || $_GPC['weixinhao'] == 'undefined') ? '' : $_GPC['weixinhao'];
        //保存formid
        $this->saveFormId($openid, $formid);

        $insert = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $openid,
            'realname' => $realname,
            'mobile' => $mobile,
            'ago' => $ago,
            'sex' => $sex,
            'id_card' => $idcard,
            'area' => $address,
            'createtime' => time(),
            'weixinhao' => $weixinhao,
            'license' => (empty($_GPC['image']) || $_GPC['images'] == 'undefined') ? '' : $_GPC['images'],
            'status' => 0
        );

        pdo_insert($this->tabareamanager, $insert);

        return $this->result(0,'');
    }

    public function doPageGetAreaManagerArea(){
        global $_W,$_GPC;
        $province = $_GPC['province'];
        $city = $_GPC['city'];
        $district = $_GPC['district'];
        $area = $_GPC['area'];

        $address = $province . ' ' . $city . ' ' . $district . ' ' . $area;
        $info = pdo_fetch("select * from " . tablename($this->tabareamanager) . " where area like :area and uniacid=:uniacid", array(':area' => $address, ':uniacid' => $_W['uniacid']));
        if(!empty($info)){
            return $this->result(1, '地区已被代理，请选择其它地区');
        }
        return $this->result(0);
    }

    public function doPageGetAreaManagerList(){
        global $_W,$_GPC;
        $page = max(1,intval($_GPC['page']));
        //$formid = $_GPC['formid'];
        //$this->saveFormId($_W['openid'], $formid);
        $area = $_GPC['area'];
        $pageSize = 16;

        $conditions = " uniacid=:uniacid and status=1";
        $params[':uniacid'] = $_W['uniacid'];

        if(!empty($area)){
            $conditions .= " and area not like :area";
            $params[':area'] = $area;
        }

        if(!empty($area)){
            $cur = pdo_fetch("select * from " . tablename($this->tabareamanager) . " where uniacid=:uniacid and status=1 and area like :area", array(':uniacid' => $_W['uniacid'], ':area' => $area));
        }
        $list = pdo_fetchall("select * from " . tablename($this->tabareamanager) . " where " . $conditions . " order by id desc limit " . ($page - 1) * $pageSize . "," . $pageSize, $params);
        $data['curArea'] = empty($cur) ? false : $cur;
        $data['list'] = $list;
        return $this->result(0,'',$data);
    }

    /**
     * 保存投诉建议
     */
    public function doPageSaveAdvises(){
        global $_W,$_GPC;
        $formId = $_GPC['formid'];
        $this->saveFormId($_W['openid'], $formId);  //保存formid
        $data = array(
            'model' => intval($_GPC['model']),
            'uniacid' => $_W['uniacid'],
            'openid' => $_W['openid'],
            'title' => $_GPC['title'],
            'content' => $_GPC['content'],
            'images' => $_GPC['images'],
            'createtime' => time(),
            'mobile' => $_GPC['mobile'],
            'is_show' => 0
        );

        $id = pdo_insert($this->tabadvises, $data);
        if($id){
            return $this->result(0,'');
        }else{
            return $this->result(1, '提交投诉建议失败');
        }
    }

    /**
     * 获取投诉建议列表
     */
    public function doPageGetAdvisesList(){
        global $_W,$_GPC;
        $pageIndex = max(1,intval($_GPC['page']));
        $pageSize = 15;

        $list = pdo_fetchall("select * from " . tablename($this->tabadvises) . " where uniacid=:uniacid and is_show=0 order by createtime desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize, array(':uniacid' => $_W['uniacid']));
        if(!empty($list)){
            foreach ($list as &$item){
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
                if($item['openid'] == $_W['openid']){
                    //获取到已回覆并没有查看的投诉建议
                    $reply = pdo_fetchcolumn("select id from " . tablename($this->tabadvisesreply) . " where advisies_id=:aid and uniacid=:uniacid and browse=0", array(':aid' => $item['id'], ':uniacid' => $_W['uniacid']));
                    $item['reply'] = empty($reply) ? 0 : 1; //是否已回覆，并没有查看，0没有回复或已查看
                }
            }
            unset($item);
        }

        return $this->result(0,'',$list);
    }

    public function doPageGetAdvisesDetail(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $item = pdo_fetch("select * from " . tablename($this->tabadvises) . " where id=:id", array(':id' => $id));
        if(empty($item)){
            return $this->result(1,'没有找到投诉建议');
        }
        $item['images'] = explode(',', $item['images']);
        $reply = pdo_fetch("select * from " . tablename($this->tabadvisesreply) . " where advisies_id=:aid", array(':aid' => $item['id']));
        if(!empty($reply)){
            $item['reply_content'] = $reply['content'];
        }

        return $this->result(0,'',$item);
    }
}