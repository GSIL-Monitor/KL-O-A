<?php
namespace Main\Controller;
use Sys\P;

ulib('Page');
use Sys\Page;


// @@@NODE-2###Op###计调操作###
class SaleController extends BaseController {
    
    protected $_pagetitle_ = '计调操作';
    protected $_pagedesc_  = '';
    
	
    // @@@NODE-3###index###出团计划列表###
    public function index(){
        $this->title('出团计划列表');
		
		$db = M('op');
		$opid     = I('opid');
		$groupid     = I('groupid');
		$project     = I('project');
		$departure   = I('departure');
		$destination = I('destination');
		
		$where = array();
		$where['o.audit_status'] = 1;
		$where['p.id'] = array('gt',0);
		if($opid)        $where['o.op_id'] = $opid;
		if($groupid)     $where['o.group_id'] = $groupid;
		if($project)     $where['o.project'] = array('like','%'.$project.'%');
		if($departure)   $where['o.departure'] = array('like','%'.$departure.'%');;
		if($destination) $where['o.destination'] = array('like','%'.$destination.'%');
		
		//分页
		$pagecount = $db->table('__OP__ as o')->field('o.*')->join('__OP_PRETIUM__ as p on p.op_id = o.op_id')->group('o.op_id')->where($where)->count();
		$page = new Page($pagecount, P::PAGE_SIZE);
		$this->pages = $pagecount>P::PAGE_SIZE ? $page->show():'';

        $lists = $db->table('__OP__ as o')->field('o.*')->join('__OP_PRETIUM__ as p on p.op_id = o.op_id')->group('o.op_id')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('o.create_time'))->select();
		foreach($lists as $k=>$v){
			$cost = M('op_pretium')->where(array('op_id'=>$v['op_id']))->order('sale_cost')->find();	
			$lists[$k]['sale_cost'] = $cost['sale_cost'];
			$lists[$k]['peer_cost'] = $cost['peer_cost'];
		}
		
		
		$this->lists   = $lists;
		
		$this->display('index');
    }
	
	
	
    // @@@NODE-3###order###销售记录###
    public function order(){
        $this->title('销售记录');
		
		$db = M('order');
		$orderid  = I('orderid');
		$opid     = I('opid');
		$groupid  = I('groupid');
		$keywords = I('keywords');
		
		$where = array();
		if($orderid)  $where['o.order_id'] = $orderid;
		if($opid)     $where['o.op_id'] = $opid;
		if($groupid)  $where['o.group_id'] = $groupid;
		if($keywords)  $where['p.project'] = array('like','%'.$keywords.'%');
		
		//分页
		$pagecount = $db->where($where)->count();
		$page = new Page($pagecount, P::PAGE_SIZE);
		$this->pages = $pagecount>P::PAGE_SIZE ? $page->show():'';
        
        $this->lists = $db->table('__ORDER__ as o')->field('o.*,p.project')->join('__OP__ as p on p.op_id = o.op_id','LEFT')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('sales_time'))->select();
		
		
		$this->kinds   =  M('project_kind')->getField('id,name', true);
		
		$this->display('order');
    }
	
	
	
	
	// @@@NODE-3###goods###产品详情###
    public function goods(){
		
		$opid = I('opid');
		$id   = I('id');
		if($id){
			$op   = M('op')->where($where)->find($id);
			$opid = $op['op_id'];		
		}else if($opid){
			$where = array();
			$where['op_id'] = $opid;
			$op   = M('op')->where($where)->find();	
		}
		
		if(!$op){
			$this->error('项目不存在');	
		}
		
		$pro        = M('product')->find($op['product_id']);
		$pretium    = M('op_pretium')->where(array('op_id'=>$opid))->order('id')->select();
		$days       = M('op_line_days')->where(array('op_id'=>$opid))->select();
		$member     = M('op_member')->where(array('op_id'=>$opid))->order('id')->select();
		
		//客户详情
		$this->kh = M('customer_gec')->where(array('company_name'=>$op['customer']))->find();
		
		$this->kinds          = M('project_kind')->getField('id,name', true);
		$this->op             = $op;
		$this->pro            = $pro;
		$this->pretium        = $pretium;
		$this->days           = $days;
		$this->member         = $member;
		$this->business_depts = C('BUSINESS_DEPT');
		$this->subject_fields = C('SUBJECT_FIELD');
		$this->ages           = C('AGE_LIST');
		$this->display('goods');
	}
    
	
	
	//@@@NODE-3###signup###我要报名###
    public function signup(){
		$opid       = I('opid');
		$id         = I('id');
		$info       = I('info');
		$member     = I('member');
		$fornum     = I('fornum');
		
		if(isset($_POST['dosubmit']) && $info){
			
			//保存订单
			$orderid = date('Ymd').rand(1000,9999);   //订单号
			$info['order_id']          = $orderid;
			$info['op_id']             = $opid;
			$info['number']            = $info['amount']*$fornum;
			$info['sales_person']      = cookie('name');
			$info['sales_person_uid']  = cookie('userid');
			$info['sales_time']        = time();
			
			if(!M('order')->where(array('order_id'=>$orderid))->find()){
				M('order')->add($info);
			}
			
			//保存名单
			if($member){
				foreach($member as $v){
					if($v['name'] || $v['sex'] || $v['number'] || $v['mobile'] || $v['remark']){
						$data = array();
						$data = $v;
						$data['op_id']            = $opid;
						$data['order_id']         = $orderid;
						$data['sales_person_uid'] = cookie('userid');
						$data['sales_time']       = time();
						//if(!M('op_member')->where($data)->find()){
							M('op_member')->add($data);
						//}
					}
				}	
			}
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			$sale             = M('op_pretium')->find($id);
			$this->fornum     = $sale['adult']+$sale['children'];
			$this->sale       = $sale;
			$this->opid       = $opid;
			$this->id         = $id;
			$this->op         = M('op')->where(array('op_id'=>$opid))->find();	
			$this->display('signup');
		}
	}
    
	
	
	// @@@NODE-3###order_viwe###订单详情###
    public function order_viwe(){
		
		$oid = I('oid');
		
		$order      = M('order')->where(array('order_id'=>$oid))->find();
		if($order){
			$opid       = $order['op_id'];
			$op         = M('op')->where(array('op_id'=>$opid))->find();
			$pretium    = M('op_pretium')->find($order['pretium_id']);
			$days       = M('op_line_days')->where(array('op_id'=>$opid))->select();
			$member     = M('op_member')->where(array('order_id'=>$oid))->order('id')->select();
			
			$this->op             = $op;
			$this->order          = $order;
			$this->pro            = $pro;
			$this->pretium        = $pretium;
			$this->days           = $days;
			$this->member         = $member;
			$this->save_member    = count($member);
			$this->fornum         = $pretium['adult']+$pretium['children'];
			$this->business_depts = C('BUSINESS_DEPT');
			$this->subject_fields = C('SUBJECT_FIELD');
			$this->ages           = C('AGE_LIST');
			$this->display('order_viwe');
		}else{
			$this->error('订单不存在');
		}
	}
	
	
	// @@@NODE-3###edit_order###修改订单名单###
	public function edit_order(){
		$opid = I('opid');
		$order_id = I('order_id');
		$member = I('member');
		$op_member_db   = M('op_member');
		
		$delid = array();
		foreach($member as $k=>$v){
			$data = array();
			$data = $v;
			if($resid && $resid[$k]['id']){
				$edits = $op_member_db->data($data)->where(array('id'=>$resid[$k]['id']))->save();
				$delid[] = $resid[$k]['id'];
				$num++;
			}else{
				$data['op_id'] = $opid;
				$data['order_id'] = $order_id;
				$data['sales_person_uid'] = cookie('userid');
				$data['sales_time']       = time();
				$savein = $op_member_db->add($data);
				$delid[] = $savein;
				if($savein) $num++;
				
				//将名单保存至客户名单
				if(!M('member')->where(array('number'=>$v['number']))->find()){
					M('member')->add($v);
				}
			}
		}	
		
		$where = array();
		$where['op_id'] = $opid;
		if($delid) $where['id'] = array('not in',$delid);
		$del = $op_member_db->where($where)->delete();
		if($del) $num++;
		
		
		echo $num;
	}
    
}