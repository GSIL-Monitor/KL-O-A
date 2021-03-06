<?php
namespace Main\Controller;
use Sys\P;

ulib('Page');
use Sys\Page;
ulib('Pinyin');
use Sys\Pinyin;

// @@@NODE-2###Customer###销售管理###
class CustomerController extends BaseController {
    
    protected $_pagetitle_ = '销售管理';
    protected $_pagedesc_  = '';
    
	
	
	// @@@NODE-3###o2o###支撑服务校记录###
    public function o2o(){
        $this->title('支撑服务校记录');
		
		
		$db = M('customer_gec');
		$keywords     = I('keywords');
		$type         = I('type');
		$cm           = I('cm');
		$address      = I('address');
		$province     = I('province');
		$city         = I('city');
		$county       = I('county');
		$level        = I('level');
		$qianli       = I('qianli');
		
		$where = array();
		$where['status']	= 1;
		$where['com']		= 1;
		if($keywords)    $where['company_name'] = array('like','%'.$keywords.'%');
		if($type)        $where['type'] = $type;
		if($address)     $where['contacts_address'] = array('like','%'.$address.'%');
		if($cm)          $where['cm_name'] = array('like','%'.$cm.'%');
		if($province)    $where['province'] = array('like','%'.$province.'%');
		if($city)        $where['city'] = array('like','%'.$city.'%');
		if($county)      $where['county'] = array('like','%'.$county.'%');
		if($level)       $where['level'] = array('like','%'.$level.'%');
		if($qianli)      $where['qianli'] = array('like','%'.$qianli.'%');
		
		//分页
		$pagecount = $db->where($where)->count();
		$page = new Page($pagecount, P::PAGE_SIZE);
		$this->pages = $pagecount>P::PAGE_SIZE ? $page->show():'';

        $lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('create_time'))->select();
		foreach($lists as $k=>$v){
			$hz = M('op')->where(array('customer'=>$v['company_name'],'audit_status'=>1))->order('create_time DESC')->find();	
			$lists[$k]['hezuo'] = $hz['create_time'] ? '<a href="'.U('Op/index',array('cus'=>$v['company_name'])).'">'.date('Y-m-d',$hz['create_time']).'</a>' : '无结算记录';
			$lists[$k]['hezuocishu'] = $hz['create_time'] ? M('op')->where(array('customer'=>$v['company_name'],'audit_status'=>1))->count() : '';	
		}
		
		
		$this->lists   = $lists;
		
		$this->display('o2o');
    }
	
	// @@@NODE-3###GEC###分配客户###
	public function o2o_apply(){
		
		$fid		= I('fid');
		if(isset($_POST['dosubmit']) && $fid){
			
			$userid = I('userid');
			$user	= M('account')->find($userid);
			$fid	= str_replace(".",",",$fid);
			
			//保存数据
			$data = array();
			$data['cm_id']		= $userid;
			$data['cm_name']	= $user['nickname'];
			$data['status']		= 0;
			M('customer_gec')->data($data)->where(array('id'=>array('in',$fid)))->save();
			
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			
			//用户列表
			$key		= I('key');
			$db			= M('account');
			$where		= array();
			$where['postid'] = array('in','1,2,4,31,32');
			$where['status'] = 0;
			if($key) $where['nickname'] = array('like','%'.$key.'%');
			$pagecount = $db->where($where)->count();
			$page = new Page($pagecount,6);
			$this->pages = $pagecount>6 ? $page->show():'';
			$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('roleid'))->select();
			$this->role  =  M('role')->getField('id,role_name', true);
			$this->fid   = $fid;
			$this->display('o2o_apply');
		}
		
		
	}
	
	
    // @@@NODE-3###GEC###政企客户管理###
    public function GEC(){
        $this->title('政企客户管理');
		
		
		$db = M('customer_gec');
		$keywords     = I('keywords');
		$type         = I('type');
		$cm           = I('cm');
		$address      = I('address');
		$province     = I('province');
		$city         = I('city');
		$county       = I('county');
		$level        = I('level');
		$qianli       = I('qianli');
		
		$where = array();
		$where['status']	= 0;
		if($keywords)    $where['company_name'] = array('like','%'.$keywords.'%');
		if($type)        $where['type'] = $type;
		if($address)     $where['contacts_address'] = array('like','%'.$address.'%');
		if($cm)          $where['cm_name'] = array('like','%'.$cm.'%');
		if($province)    $where['province'] = array('like','%'.$province.'%');
		if($city)        $where['city'] = array('like','%'.$city.'%');
		if($county)      $where['county'] = array('like','%'.$county.'%');
		if($level)       $where['level'] = array('like','%'.$level.'%');
		if($qianli)      $where['qianli'] = array('like','%'.$qianli.'%');
		
		if(C('RBAC_SUPER_ADMIN')==cookie('username') || cookie('roleid')==10 || cookie('roleid')==28 || cookie('roleid')==11 || cookie('roleid')==30 || cookie('roleid')==47|| cookie('roleid')==45|| cookie('roleid')==14){
			
		}else{
			$where['cm_id'] = array('in',Rolerelation(cookie('roleid')));
		}
		//分页
		$pagecount = $db->where($where)->count();
		$page = new Page($pagecount, P::PAGE_SIZE);
		$this->pages = $pagecount>P::PAGE_SIZE ? $page->show():'';

        $lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('create_time'))->select();
		foreach($lists as $k=>$v){
			$hz = M('op')->where(array('customer'=>$v['company_name'],'audit_status'=>1))->order('create_time DESC')->find();	
			$lists[$k]['hezuo'] = $hz['create_time'] ? '<a href="'.U('Op/index',array('cus'=>$v['company_name'])).'">'.date('Y-m-d',$hz['create_time']).'</a>' : '无结算记录';
			$lists[$k]['hezuocishu'] = $hz['create_time'] ? M('op')->where(array('customer'=>$v['company_name'],'audit_status'=>1))->count() : '';	
		}
		
		
		
		$this->lists   = $lists;
		
		$this->display('GEC');
    }
	
	

	
    public function op(){
      
		$db = M('customer_gec');
		$PinYin = new Pinyin();
		
		$id = M('op')->where(array('customer'=>array('like','%散客%')))->Getfield('id',true);
		
		
		$where = array();
		$where['customer'] = array('neq','NULL');
		$where['id'] = array('not in',implode(',',$id));
		$where['customer'] = array('neq',' ');
		
		$i = 0;
		$list = M('op')->field('customer,create_user,create_user_name,create_time')->where($where)->group('customer')->select();
		foreach($list as $v){
			$company_name = iconv("utf-8","gb2312",trim($v['customer']));
			$data = array();
			$data['company_name'] = $v['customer'];
			$data['cm_id'] = $v['create_user'];
			$data['cm_name'] = $v['create_user_name'];
			$data['cm_time'] = $v['create_time'];
			$data['create_time'] = $v['create_time'];
			$data['pinyin'] = strtolower($PinYin->getFirstPY($company_name));	
			if(!M('customer_gec')->where(array('company_name'=>$v['customer'],'cm_id'=>$v['create_user']))->find()){
				$aaa = M('customer_gec')->add($data);
				if($aaa) $i++;
			}
		}
		
		echo $i;
    }
	
	
	
	
	// @@@NODE-3###GEC_edit###编辑政企客户###
    public function GEC_edit(){
        $this->title('政企客户管理');
		
		$db = M('customer_gec');
		$id = I('id');
		$referer = I('referer');
		$PinYin = new Pinyin();
		
		if(isset($_POST['dosubmint']) && $_POST['dosubmint']){
			
			$gec_id = I('gec_id');
			$info = I('info');
			$info['cm_time'] = time();
			$company_name = iconv("utf-8","gb2312",trim($info['company_name']));
			$info['pinyin'] = strtolower($PinYin->getFirstPY($company_name));	
			
			if($info){
				
				if($gec_id){
					
					$u = $db->find($gec_id);
					if($u['cm_id']==cookie('userid') || C('RBAC_SUPER_ADMIN')==cookie('username') || cookie('roleid')==10 || cookie('roleid')==28 || cookie('roleid')==11 || cookie('roleid')==30){
						$isok = $db->data($info)->where(array('id'=>$gec_id))->save();
					}else{
						$this->error('您没有权限修改该用户信息' . $db->getError());		
					}
				}else{
					$info['create_time'] = time();
					$info['cm_id'] = cookie('userid');
					$isok = $db->add($info);
				}
				
				if($isok){
					$this->success('保存成功！',$referer);		
				}else{
					$this->error('保存失败' . $db->getError());	
				}
				
			}else{
				$this->error('请填写企业信息' . $db->getError());	
			}
			
		}else{
			$this->gec       = $db->find($id);
			//合作记录
			$where = array();
			$where['o.customer'] = $this->gec['company_name'];
			$where['s.audit_status'] = 1;
			
			$this->hezuo = M()->table('__OP_SETTLEMENT__ as s')->field('s.*,o.group_id,o.project')->join('__OP__ as o on o.op_id = s.op_id','LEFT')->where($where)->select();
			
			$this->display('GEC_edit');
		}
		
		
    }
	
	// @@@NODE-3###GEC_transfer###交接客户###
	public function GEC_transfer(){
		if(isset($_POST['dosubmint']) && $_POST['dosubmint']){
			
			$referer = I('referer');
			$fm      = I('fm');
			$fmid    = I('fmid');
			$to      = I('to');
			$toid    = I('toid');
			$gec     = I('gec');
			
			if(!$toid){
				$user = M('account')->where(array('nickname'=>$to))->find();	
				$toid = $user['id'];
			}
			$i = 0;
			foreach($gec as $k=>$v){
				$data = array();
				$data['cm_id']   = $toid;
				$data['cm_name'] = $to;
				$save = M('customer_gec')->data($data)->where(array('id'=>$v))->save();
				if($save){
					$i++;	
				}
			}
			
			$this->success('成功交接了'.$i.'条客户信息！',$referer);		
			
		}else{
			
			$role = M('role')->GetField('id,role_name',true);
			$user =  M('account')->select();
			$key = array();
			foreach($user as $k=>$v){
				$text = $v['nickname'].'-'.$role[$v['roleid']];
				$key[$k]['id']         = $v['id'];
				$key[$k]['user_name']  = $v['nickname'];
				$key[$k]['pinyin']     = strtopinyin($text);
				$key[$k]['text']       = $text;
				$key[$k]['role']       = $v['roleid'];
				$key[$k]['role_name']  = $role[$v['roleid']];
			}
			
			$this->userkey = json_encode($key);	
			$this->display('GEC_transfer');
		}
	}
	
	
	// @@@NODE-3###GEC_viwe###编辑政企客户###
    public function GEC_viwe(){
        $this->title('政企客户管理');
		
		$db = M('customer_gec');
		$id = I('id');
		$referer = I('referer');
		
		
		$this->gec       = $db->find($id);
		//合作记录
		$where = array();
		$where['o.customer'] = $this->gec['company_name'];
		$where['s.audit_status'] = 1;
		
		$this->hezuo = M()->table('__OP_SETTLEMENT__ as s')->field('s.*,o.group_id,o.project')->join('__OP__ as o on o.op_id = s.op_id','LEFT')->where($where)->select();
		
		$this->display('GEC_viwe');
		
		
		
    }
	
	
	// @@@NODE-3###delgec###删除客户###
	public function delgec(){
		$id = I('id');
		if($id){
			$isdel = M('customer_gec')->where(array('id'=>$id))->delete();
			if($isdel){
				$this->success('删除成功！');		
			}else{
				$this->error('删除失败！');	
			}
		}else{
			$this->error('数据不存在！');	
		}
	}
	
	
    // @@@NODE-3###IC###参团客户###
    public function IC(){
        $this->title('参团客户');
		
		$db = M('op_member');
		$nm = I('nm');
		$sex = I('sex');
		$no = I('no');
		$tel = I('tel');
		$ec = I('ec');
		$ectel = I('ectel');
		$dw = I('dw');
		
		$where = array();

		if($nm)    $where['name'] = array('like','%'.$nm.'%');
		if($sex)   $where['sex'] = $sex;
		if($no)    $where['number'] = array('like','%'.$no.'%');
		if($tel)   $where['mobile'] = array('like','%'.$tel.'%');
		if($ec)    $where['ecname'] = array('like','%'.$ec.'%');
		if($ectel) $where['ecmobile'] = array('like','%'.$ectel.'%');
		if($dw)    $where['remark'] = array('like','%'.$dw.'%');
		
		
		if(C('RBAC_SUPER_ADMIN')==cookie('username') || cookie('roleid')==10 || cookie('roleid')==28 || cookie('roleid')==11 || cookie('roleid')==30){
			
		}else{
			$where['sales_person_uid'] = array('in',Rolerelation(cookie('roleid')));
		}
		
		//分页
		$pagecount = $db->where($where)->count();
		$page = new Page($pagecount, P::PAGE_SIZE);
		$this->pages = $pagecount>P::PAGE_SIZE ? $page->show():'';
        
		
		$account = M('account')->Getfield('id,nickname',true);
		
		$lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('sales_time'))->select();
		foreach($lists as $k=>$v){
			$lists[$k]['user'] = $account[$v['sales_person_uid']];	
		}
		
		$this->lists = $lists;
		
		$this->kinds   =  M('project_kind')->getField('id,name', true);
		
		$this->display('IC');
    }
	
	
	
	// @@@NODE-3###IC_edit###编辑参团客户###
    public function IC_edit(){
        $this->title('参团客户管理');
		
		$db = M('op_member');
		$id = I('id');
		$referer = I('referer');
		
		
		if(isset($_POST['dosubmint']) && $_POST['dosubmint']){
			
			$ic_id = I('ic_id');
			$info = I('info');
			
			if($info){
				
				if($ic_id){
					$u = $db->find($ic_id);
					if($u['sales_person_uid']==cookie('userid') || C('RBAC_SUPER_ADMIN')==cookie('username') || cookie('roleid')==10 || cookie('roleid')==28 || cookie('roleid')==11 || cookie('roleid')==30){
						$isok = $db->data($info)->where(array('id'=>$ic_id))->save();
					}else{
						$this->error('您没有权限修改该用户信息' . $db->getError());		
					}
				}else{
					$info['sales_person_uid'] = cookie('userid');
					$isok = $db->add($info);
				}
				
				if($isok){
					$this->success('保存成功！',$referer);		
				}else{
					$this->error('保存失败' . $db->getError());	
				}
				
			}else{
				$this->error('请填写企业信息' . $db->getError());	
			}
			
		}else{
			$this->ic       = $db->find($id);
			
			//合作记录
			$where = array();
			$where['o.op_id'] = $this->ic['op_id'];
			//$where['s.audit_status'] = 1;
		
			$this->hezuo = M()->table('__OP__ as o')->field('s.renjunmaoli,o.group_id,o.project')->join('__OP_SETTLEMENT__ as s on o.op_id = s.op_id')->where($where)->select();
		
			$this->display('IC_edit');
		}
		
		
    }
	
	
	
    
}