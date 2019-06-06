<?php
/**
 * nearby_stores模块微站定义
 *
 * @author EdisonLiu_
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class jujiwuliuModuleSite extends WeModuleSite {
	
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
	public function __construct(){
		global $_W,$_GPC;
		load()->model('mc');
		//下面是redis 数据库连接
		if (!class_exists('redis') && (!function_exists('redis') || is_error(redis()))) {	
			echo 'Redis扩展未安装或服务未启动';
		}
		$redis_config = $_W['config']['setting']['redis'];

		if (empty($redis_config['server'])) {
			$redis_config['server'] = '127.0.0.1';
		}

		if (empty($redis_config['port'])) {
			$redis_config['port'] = '6379';
		}

		$redis = new redis();

		if ($redis_config['pconnect']) {
			$connect = $redis->pconnect($redis_config['server'], $redis_config['port'], $redis_config['timeout']);
		}
		else {
			$connect = $redis->connect($redis_config['server'], $redis_config['port'], $redis_config['timeout']);
		}

		if (!$connect) {
			return false;
		}

		if (!empty($redis_config['requirepass'])) {
			$redis->auth($redis_config['requirepass']);
		}

		try {
			$ping = $redis->ping();
		}
		catch (ErrorException $e) {
			return false;
		}

		if ($ping != '+PONG') {
			return false;
		}

		$this->redis = $redis;
		//redis 数据库连接 完成
	
		$this->type_set = array('0' => '装卸工', '1' => '临时工');
		$this->unit_set = array('0' => '吨', '1' => '天');		
		$this->static_set = array('1' => '轻度活', '2' => '中度活', '3' => '重度活', '4' => '一口价');
		$this->sex_set = array('0' => '不限', '1' => '男', '2' => '女');
		$this->status_set = array('0' => '发布中', '1' => '工作中', '3' => '已完成', '4' => '已取消');
		$this->order_status_set = array('0' => '接单中', '1' => '工作中', '2' => '已完工', '3' => '已完成', '4' => '已取消');
		
//		$refund= $this->refund('PU20181125103540849649',0.01,'后台处理退款！');//退款方法
		
		//运行守护进程
		if($_GPC['token']=='to_numen'){
			$this->init();
		}
		
	}
	//下面是守护进程
	public function init(){
		$this->release_expire();//订单未接完退款
		
//		$this->finish_order();//订单完成打款
		die;//执行完所有 直接结束进程
	}
	public function release_expire(){
		global $_W,$_GPC;
		
		$data = pdo_fetchall("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=".$_W['uniacid']." and status=0 and starttime<=".time()." and pay_status=1 ");
		foreach ($data as $key => $value) {
			//查询接单人数	
			$order = pdo_fetchall("SELECT * FROM ".tablename($this->taborder)." WHERE uniacid=".$_W['uniacid']." and deposit_paystatus=1 and rid=".$value['id']);//已支付押金的订单
			//剩余数量
			$residue=count($order)-$value['nums'];
				/*
	 *
	 * $log = array(
		0 => 操作管理员uid
		1 => 增减积分备注
		2 => 模块标识，例如：we7_store
		3 => 店员uid
		4 => 门店id
		5 => 1(线上操作) 2(系统后台,公众号管理员和操作员) 3(店员) 
	 *  6 => 订单号
		);
	 * */
			if($residue>0){//退部分款项
				$log=array(
					1,
					'发布信息工人未完全接单部分自动退款',
					$this->module['name'],
					'',
					'',
					4,
					$value['ordersn']
				);
				//退款金额 价格乘以剩余数量
			$re_price=$value['total_price']*$residue;
			//退款
			$uid=mc_openid2uid($value['openid']);
			$res_upcredit=$this->credit_update($uid, 'credit2', $re_price, $log,true);
			if(!empty($res_upcredit)){
				//发模板消息
				pdo_update($this->tabrelease,array('refund_money'=>$re_price,'refund_num'=>$residue),array('uniacid'=>$_W['uniacid'],'id'=>$value['id']));
				//修改发布信息为取消状态
			}
			
			}else if(count($order)==0){//没有人接单 直接取消发布 并退款
				$uid=mc_openid2uid($value['openid']);

				$log=array(
					1,
					'发布信息没有个人接单自动退款',
					$this->module['name'],
					'',
					'',
					4,
					$value['ordersn']
				);
				
				//退款
				$res_upcredit=$this->credit_update($uid, 'credit2', $value['count_price'], $log,true);
				if(!empty($res_upcredit)){
					//发模板消息
					pdo_update($this->tabrelease,array('refund_money'=>$value['count_price'],'status'=>2,'refund_num'=>$value['nums']),array('uniacid'=>$_W['uniacid'],'id'=>$value['id']));
					//修改发布信息为取消状态
				}
			}
			
			unset($uid);
			
			//如果没有满退款
		}

		
		
	}
	public function error($msg, $type = 0) 
	{
		echo $msg . "\n";
	}

	public function log($name, $text) 
	{
		$filename = dirname(__FILE__) . '/data/log_' . $name . '.log';
		$text = '[' . date('Y-m-d H:i:s', time()) . '] ' . $text;
		file_put_contents($filename, $text . "\r\n", FILE_APPEND);
		
	}
	//守护进程结束
	
	//积分余额记录
	/*
	 *uid 
	 *$credittype credit1  credit2
	 *$creditval float
	 *$is_ret 是返回还是直接message提示
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

		if($creditval<0){
			$reg=mc_fetch($uid,array($credittype));
			if(($reg[$credittype]+$creditval)<0){
				if(empty($is_ret)){
					message('操作失败,该用户的'.$cr_type[$credittype].'不足以扣除;',referer() , 'error');
				}else{
					return false;
				}
			}
		}
		//先修改系统信息 再修改表
		if(empty($creditval)){
			if(empty($is_ret)){
				message('操作'.$cr_type[$credittype].'失败',referer() , 'error');
			}else{
				return false;
			}	
		}
		$up_credit=mc_credit_update($uid, $credittype, $creditval, $log);
		if($up_credit!=true){
			if(empty($is_ret)){
				message('操作'.$cr_type[$credittype].'失败',referer() , 'error');
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
				$log[1] = '系统后台: 添加' . $creditval . $credittype_name;
			} else {
				$log[1] = '系统后台: 减少' . -$creditval . $credittype_name;
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
	
	
	public function template($filename, $type = TEMPLATE_INCLUDEPATH) {
		global $_W;
		$name = strtolower($this -> modulename);
		if (defined('IN_SYS')) {
			$source = IA_ROOT . "/web/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if (!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$name}/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/addons/{$name}/template/web/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/addons/{$name}/template/web/{$filename}/index.html";
			}
			if (!is_file($source)) {
				$source = IA_ROOT . "/web/themes/{$_W['template']}/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$filename}.html";
			} 
		} else {
			$template = $this->module['config']['style'];
			$file = IA_ROOT . "/addons/{$name}/data/template/shop_" . $_W['uniacid'];
			if (is_file($file)) {
				$template = file_get_contents($file);
				if (!is_dir(IA_ROOT . '/addons/{$name}/template/mobile/' . $template)) {
					$template = "default";
				} 
			} 
			$compile = IA_ROOT . "/data/tpl/app/{$name}/{$template}/mobile/{$filename}.tpl.php";
			$source = IA_ROOT . "/addons/{$name}/template/mobile/{$template}/{$filename}.html";
			if (!is_file($source)) {
				$source = IA_ROOT . "/addons/{$name}/template/mobile/default/{$filename}.html";
			}
			if (!is_file($source)) {
				$source = IA_ROOT . "/app/themes/{$_W['template']}/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/app/themes/default/{$filename}.html";
			} 
		} 
		if (!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		} 
		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		} 
		return $compile;
	}
	

	
	public function doWebRemover() {
		global $_W, $_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		if($op=='display'){
			$keyword = $_GPC['keyword'];
			if(!empty($keyword)){
				$condition.=' and (nickname like "%'.$keyword.'%" or mobile like "%'.$keyword.'%" or realname like "%'.$keyword.'%")';
			}
			$pindex = max(1,intval($_GPC['page']));
			$psize = 20;
			$total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabmember).' where uniacid ='.$_W['uniacid'].' '.$condition);
			$list = pdo_fetchall('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' '.$condition.' order by id desc LIMIT '.($pindex-1)*$psize.','.$psize);
			$pager = pagination($total, $pindex, $psize);
		}
		
		if($op=='post'){
			$id = intval($_GPC['id']);
			$item = pdo_fetch('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' and id='.$id);
			if($_W['ispost']){
				$data=array(
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'updatetime' => time()
				);
				pdo_update($this->tabmember, $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
				message('更新成功！', referer(), 'success');
			}
		}
		
		if($op=='del'){
			$id=intval($_GPC['id']);
			$member = pdo_fetch('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0');
			if(!empty($member)){
				pdo_delete($this->tabmember, array('id' => $id, 'uniacid' => $_W['uniacid']));
				message('删除成功！', referer(), 'success');
			}else{
				message('不存在或已删除！', referer(), 'warning');
			}
		}

		include $this->template('remover');
	}
	

	
	public function doWebRelease() {
		global $_W, $_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if($op=='display'){
			$keyword = $_GPC['keyword'];
			if(!empty($keyword)){
				$condition.=' and (nickname like "%'.$keyword.'%" or mobile like "%'.$keyword.'%" or realname like "%'.$keyword.'%")';
			}
			$status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
			if($status > -1){
			    $condition .= ' and status=' . $status;
            }

			$pindex = max(1,intval($_GPC['page']));
			$psize = 20;

			$type=$_GPC["type"];//获取类型（进行中、已完成、已取消）
	
			$total = pdo_fetchcolumn('select count(*) from '.tablename($this->tabrelease).' where uniacid ='.$_W['uniacid'].' and pay_status=1  and deleted=0' . $condition);
			$list = pdo_fetchall("SELECT * FROM ".tablename($this->tabrelease)." WHERE uniacid=:uniacid  and pay_status=1 and deleted=0" . $condition . " order by createtime desc LIMIT " . (($pindex - 1) * $psize) . ',' . $psize, array(':uniacid' => $_W['uniacid']));
			foreach($list as & $res){
				$res['member'] = pdo_fetch('select * from '.tablename($this->tabmember).' where uniacid='.$_W['uniacid'].' and openid=\''.$res['openid'].'\'');

				$res['images']=unserialize($res['images']);
				$res['typename'] = $this->type_set[$res['type']];
				$res['unit'] = $this->unit_set[$res['type']];
				$res['staticname'] = $this->static_set[$res['static']];
				$res['sex'] = $this->sex_set[$res['sex']];
				$res['createtime'] = date('m/d/y H:i', $res['createtime']);
				$res['starttime'] = !empty($res['starttime']) ? date('m/d/y H:i', $res['starttime']) : '';
				$res['statusname'] =  $this->status_set[$res['status']];
				$res['yet'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->taborder)." WHERE uniacid=:uniacid and rid=:rid and deleted=0", array(':uniacid' => $_W['uniacid'], ':rid' => $res['id']));
				$res['gptu'] = $res['nums']  - $res['yet'];
			}

			unset($res);
			
			$pager = pagination($total, $pindex, $psize);
		}
		
		if($op=='post'){
			$id = intval($_GPC['id']);
			$item = pdo_fetch('select * from '.tablename($this->tabrelease).' where uniacid='.$_W['uniacid'].' and id='.$id);
			if($_W['ispost']){
				$data=array(
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'updatetime' => time()
				);
				pdo_update($this->tabrelease, $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
				message('更新成功！', referer(), 'success');
			}
		}
		
		if($op=='del'){
			$id=intval($_GPC['id']);
			$member = pdo_fetch('select * from '.tablename($this->tabrelease).' where uniacid='.$_W['uniacid'].' and id='.$id.' and deleted=0');
			if(!empty($member)){
				pdo_update($this->tabrelease, array('deleted'=>1),array('id' => $id, 'uniacid' => $_W['uniacid']));
				// pdo_delete($this->tabrelease, array('id' => $id, 'uniacid' => $_W['uniacid']));
				message('删除成功！', referer(), 'success');
			}else{
				message('不存在或已删除！', referer(), 'warning');
			}
		}

        if($op == 'refund'){
            $id=intval($_GPC['id']);
            $type = $_GPC['type'];
            if(!in_array($type, array('money','bond'))){
                message('不存在或已删除！', referer(), 'warning');
            }

            if($type == 'money'){
                $condition = ' and apply_refund=1';
            }elseif ($type == 'bond'){
                $condition = ' and apply_refund_bond=1 and can_refund_bond=1';
            }elseif($type == 'surplus'){
                $condition = ' and surplus_status=1 and status = 3';
            }

            $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid' . $condition, array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if(empty($release)){
                message('不存在或已删除！', referer(), 'warning');
            }

            $uid = pdo_fetchcolumn('select uid from' . tablename($this->tabmember) . ' where openid=:openid', array(':openid' => $release['openid']));

            if($type == 'money'){
                $log=array(
                    1,
                    '发布方申请退款',
                    $this->module['name'],
                    '',
                    '',
                    4,
                    $release['ordersn']
                );
                $price = $release['count_price'] - $release['refund_money'];
                $i = $this->credit_update($uid, 'credit2', $price, $log, true);
                if($i){
                    $update = array('apply_refund' => 2, 'status' => 4);
                    pdo_update($this->tabrelease, $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
                }
            }elseif ($type == 'bond'){
                $log=array(
                    1,
                    '发布方申请退款保证金',
                    $this->module['name'],
                    '',
                    '',
                    4,
                    $release['ordersn']
                );
                $i = $this->credit_update($uid, 'credit2', $release['cancel_refund_money'], $log, true);
                if($i){
                    $update = array('apply_refund_bond' => 2);
                    pdo_update($this->tabrelease, $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
                }
            }elseif($type == 'surplus'){
                $log=array(
                    1,
                    '发布方申请退余款',
                    $this->module['name'],
                    '',
                    '',
                    4,
                    $release['ordersn']
                );
                $count = pdo_fetchcolumn("select count(id) from " . tablename($this->taborder) . " where status<>4 and rid=:id", array(':id' => $release['id']));
                $num = $release['nums'] - $count;
                $price = round($release['count_price'] * $num / $release['nums'],2);
                $i = $this->credit_update($uid, 'credit2', $price, $log, true);
                if($i){
                   pdo_update($this->tabrelease, array('surplus_status' => 2,'refund_money' => $price), array('id' => $id, 'uniacid' => $_W['uniacid']));
                }
            }

            message('退款成功！', referer(), 'success');
        }

        if($op == 'detail'){
            $setting=pdo_fetch("SELECT * FROM ".tablename('jujiwuliu_setting')." WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
            $id = intval($_GPC['id']);
            $release = pdo_fetch('select * from ' . tablename($this->tabrelease) . ' where id=:id and uniacid=:uniacid' . $condition, array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if(empty($release)){
                message('信息不存在或已删除', referer(), 'error');
                exit;
            }
            $release_member = pdo_fetch('select * from ' . tablename($this->tabmember) . ' where openid=:openid', array(':openid' => $release['openid']));
            $release['nickname'] = $release_member['nickname'];
            $release['avatar'] = $release_member['avatar'];
            if($release['static'] == '4') {
                $release['release_str'] = '一口价 + 保证金';
            }else{
                if($release['type'] == '0'){
                    $release['release_str'] = '单价 * 吨数 + 保证金';
                }else{
                    $release['release_str'] = '单价 * 天数 + 保证金';
                }
            }
            $release['money'] = $release['count_price'] + $release['cancel_refund_money'];
            $release['createtime'] = date('Y/m/d', $release['createtime']);
            $release['starttime'] = date('m/d/y', $release['starttime']);
            $release['status'] = $this->status_set[$release['status']];
            $release['unit_str'] = $this->unit_set[$release['type']];

            $list = pdo_fetchall('select * from ' . tablename($this->taborder) . ' where rid=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if($list){
                $total = 0;
                foreach ($list as $key => $item){
                    $user = pdo_fetch('select * from ' . tablename($this->tabmember) . ' where openid=:openid', array(':openid' => $item['openid']));
                    $item['nickname'] = $user['nickname'];
                    $item['createtime'] = date('m/d/y', $item['createtime']);
                    $item['avatar'] = $user['avatar'];
                    $item['pumping'] = round(floatval($release['count_price']) / intval($release['nums']) * $setting['pumping'] / 100,2);
                    $total += 1;
                    $item['num'] += $total;
                    $item['status_str'] = $this->order_status_set[$item['status']];
                    $list[$key] = $item;
                }
            }
        }

		include $this->template('release');
	}
	
	public function doWebCategory() {
		global $_W, $_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		if($op=='display'){
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update($this->tabcategory, array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序成功！', referer(), 'success');
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tabcategory) . ' where uniacid =' . $_W['uniacid']);
			$cates = pdo_fetchall('select * from ' . tablename($this->tabcategory) . ' where uniacid=' . $_W['uniacid'] . ' order by displayorder asc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize);
			$pager = pagination($total, $pindex, $psize);
		}
		
		if($op=='post'){
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM " . tablename($this->tabcategory) . " where uniacid=:uniacid and id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if ($_W['ispost']) {
				$data = array('uniacid' => $_W['uniacid'], 'displayorder' => $_GPC['displayorder'], 'catename' => $_GPC['catename'], 'cate_pic' => $_GPC['cate_pic'], 'enabled' => $_GPC['enabled']);
				if (!empty($id)) {
					pdo_update($this->tabcategory, $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
					message('编辑成功！', referer(), 'success');
				} else {
					pdo_insert($this->tabcategory, $data);
					message('添加成功！', $this -> createWebUrl('category'), 'success');
				}
			}
		}
		
		if($op=='del'){
			$id = intval($_GPC['id']);
			$isdel = pdo_fetch('select * from '.tablename($this->tabcategory).' where uniacid='.$_W['uniacid'].' and id='.$id);
			if(!empty($isdel)){
				pdo_delete($this->tabcategory, array('id' => $id, 'uniacid' => $_W['uniacid']));
				message('删除成功！', referer(), 'success');
			}else{
				message('不存在或已删除！', referer(), 'warning');
			}
		}
 
		include $this->template('category');
	}
	
	public function doWebShop() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}
		
    public function pay_money($openid = '', $money = 0, $trade_no = '', $desc = ''){
        global $_W;
        $setting = uni_setting($_W['uniacid'], array('payment'));
        if (!is_array($setting['payment'])) {
            return error(1, '没有设定支付参数');
        }
        $wechat = $setting['payment']['wechat'];
        $refund = $setting['payment']['wechat_refund'];
        $sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
        $row = pdo_fetch($sql, array(':uniacid' => 1));
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $pars = array();
        $pars['mch_appid'] = $row['key'];
        $pars['mchid'] = $wechat['mchid'];
        $pars['nonce_str'] = random(32);
        $pars['partner_trade_no'] = empty($trade_no) ? time() . random(6, true) : $trade_no;
        $pars['openid'] = $openid;
        $pars['check_name'] = 'NO_CHECK';
        $pars['amount'] = $money;
        $pars['desc'] = empty($desc) ? '佣金提现' : $desc;
        $pars['spbill_create_ip'] = gethostbyname($_SERVER["HTTP_HOST"]);
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach ($pars as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key=" . $wechat['signkey'];
        $pars['sign'] = strtoupper(md5($string1));
        //var_dump($pars);die();
        $xml = array2xml($pars);
        $extras = array();
        if($refund['cert'] && $refund['key']){
            $cert = authcode($refund['cert'], 'DECODE');
            $key = authcode($refund['key'], 'DECODE');
            file_put_contents(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_cert.pem', $cert);
            file_put_contents(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_key.pem', $key);
            $extras['CURLOPT_SSLCERT'] = ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_cert.pem';
            $extras['CURLOPT_SSLKEY'] = ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_key.pem';
        }
        load()->func('communication');
        $resp = ihttp_request($url, $xml, $extras);
        @unlink(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_cert.pem');
        @unlink(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_key.pem');
        if (is_error($resp)) {
            return error(-2, $resp['message']);
        }
        if (empty($resp['content'])) {
            return error(-2, '网络错误');
        } else {
            $arr = json_decode(json_encode((array)simplexml_load_string($resp['content'])), true);
            $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
            $dom = new \DOMDocument();
            if ($dom->loadXML($xml)) {
                $xpath = new \DOMXPath($dom);
                $code = $xpath->evaluate('string(//xml/return_code)');
                $ret = $xpath->evaluate('string(//xml/result_code)');
                if (strtolower($code) == 'success' && strtolower($ret) == 'success') {
                    return true;
                } else {
                    if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
                        $error = $xpath->evaluate('string(//xml/return_msg)');
                    } else {
                        $error = $xpath->evaluate('string(//xml/return_msg)') . "<br/>" . $xpath->evaluate('string(//xml/err_code_des)');
                    }
                    return error(-2, $error);
                }
            } else {
                return error(-1, '未知错误');
            }
        }
    }
}