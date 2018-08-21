<?php
namespace Main\Controller;
use Sys\P;

ulib('Page');
use Sys\Page;
ulib('Pinyin');
use Sys\Pinyin;

// @@@NODE-2###Op###计调操作###
class OpController extends BaseController {
    
    protected $_pagetitle_ = '计调操作';
    protected $_pagedesc_  = '';

    //初始化
    /*public function _initialize(){
        //获取出团确认时填写实际出团日期在当天的团
        $arr_opid = M('op_settlement')->where(array('audit_status'=>1))->getField('op_id',true); //已经做过结算的团op_id
        $where    = array();
        $where['op.op_id']       = array('not in',$arr_opid);
        $where['op.tcs_stu']     = array('neq',0);
        $lists    = M()->table('__OP__ as op')->field('op.*,c.tcs_end_time')->join('left join __OP_TEAM_CONFIRM__ as c on op.op_id=c.op_id')->where($where)->select();
        foreach ($lists as $v){
            //出团确认结束4个小时候自动改变辅导员/教师,专家状态
            if((time()-$v['tcs_end_time']>4*60*60) && $v['tcs_stu']==3){
                $info['tcs_stu'] = 4;
                M('op')->where(array('op_id'=>$v['op_id']))->save($info);
            }
        }
    }*/


    // @@@NODE-3###index###出团计划列表###
    public function index(){
        $this->title('出团计划列表');

		$db		= M('op');

		$title	= I('title');		//项目名称
		$opid	= I('id');			//项目编号
		$oid	= I('oid');			//项目团号
		$dest	= I('dest');			//目的地
		$ou		= I('ou');			//立项人
		$status	= I('status','-1');	//成团状态
		$as		= I('as','-1');		//审核状态
		$kind	= I('kind');			//类型
		$su		= I('su');			//销售
		$pin	= I('pin');
		$cus	= I('cus');			//客户单位
		$jd		= I('jd');			//计调

		$where = array();

		if($title)			$where['o.project']			= array('like','%'.$title.'%');
		if($oid)				$where['o.group_id']			= array('like','%'.$oid.'%');
		if($opid)			$where['o.op_id']			= $opid;
		if($dest)			$where['o.destination']		= $dest;
		if($ou)				$where['o.create_user_name']	= $ou;
		if($status!='-1')	$where['o.status']			= $status;
		if($as!='-1')		$where['o.audit_status']		= $as;
		if($kind)			$where['o.kind']				= $kind;
		if($su)				$where['o.sale_user']		= array('like','%'.$su.'%');
		if($cus)				$where['o.customer']			= $cus;
		if($pin==1)			$where['o.create_user']		= cookie('userid');
		if($jd)				$where['a.nickname']			= array('like','%'.$jd.'%');

		//分页
		$pagecount		= $db->table('__OP__ as o')->field($field)->join('__OP_AUTH__ as u on u.op_id = o.op_id','LEFT')->join('__ACCOUNT__ as a on a.id = u.line','LEFT')->where($where)->count();
		$page			= new Page($pagecount, P::PAGE_SIZE);
		$this->pages	= $pagecount>P::PAGE_SIZE ? $page->show():'';


		$field	= 'o.*,a.nickname as jidiao';
		$lists = $db->table('__OP__ as o')->field($field)->join('__OP_AUTH__ as u on u.op_id = o.op_id','LEFT')->join('__ACCOUNT__ as a on a.id = u.line','LEFT')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('o.create_time'))->select();

		foreach($lists as $k=>$v){

			//判断项目是否审核通过
			if($v['audit_status']==0) $lists[$k]['zhuangtai'] = '<span class="blue">未审核</span>';
			if($v['audit_status']==1) $lists[$k]['zhuangtai'] = '<span class="blue">立项通过</span>';
			if($v['audit_status']==2) $lists[$k]['zhuangtai'] = '<span class="blue">立项未通过</span>';

			//判断预算是否通过
			$yusuan = M('op_budget')->where(array('op_id'=>$v['op_id']))->find();
			if($yusuan && $yusuan['audit_status']==0) $lists[$k]['zhuangtai'] = '<span class="green">已提交预算</span>';
			if($yusuan['audit_status']==1) $lists[$k]['zhuangtai'] = '<span class="green">预算通过</span>';
			if($yusuan['audit_status']==2) $lists[$k]['zhuangtai'] = '<span class="green">预算未通过</span>';

			//判断结算是否通过
			$jiesuan = M('op_settlement')->where(array('op_id'=>$v['op_id']))->find();
			if($jiesuan && $jiesuan['audit_status']==0) $lists[$k]['zhuangtai'] = '<span class="yellow">已提交结算</span>';
			if($jiesuan['audit_status']==1) $lists[$k]['zhuangtai'] = '<span class="yellow">完成结算</span>';
			if($jiesuan['audit_status']==2) $lists[$k]['zhuangtai'] = '<span class="yellow">结算未通过</span>';

		}
		$this->lists   =  $lists;
		$this->kinds   =  M('project_kind')->getField('id,name', true);
		$this->pin     = $pin;

		$this->display('index');
    }
	
    
    // @@@NODE-3###plans###制定出团计划###
    public function plans(){
		$PinYin = new Pinyin();
		
		if(isset($_POST['dosubmint']) && $_POST['dosubmint']){

			$db             = M('op');
			$op_cost_db     = M('op_cost');
			$op_guide_db    = M('op_guide');
			$op_member_db   = M('op_member');
			$op_supplier_db = M('op_supplier');
			
			$info       = I('info');
			$guide      = I('guide');
			$member     = I('member');
			$cost       = I('cost');
			$supplier   = I('supplier');
			$wuzi       = I('wuzi');

            $exe_role_ids = I('exe');
            /*if(!$exe_role_ids){
                $this->error('工单受理部门不能为空');
            }*/
			if(!$info['customer']){
				$this->error('客户单位不能为空' . $db->getError());	
				die();	
			}

			if($info){
				
				$opid = opid();

				$info['create_time'] = time();
				$info['op_id']       = $opid;
				$info['speed']       = 1;
				$info['create_user'] = cookie('userid');
				$info['create_user_name'] = cookie('name');
                $info['audit_status'] = 1; //项目不用审核,默认通过
				$addok  = $db->add($info);
				//$this->request_audit(P::REQ_TYPE_PROJECT_NEW, $addok);

				if($addok){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 1;
					$record['explain'] = '项目立项';
					op_record($record);
					
					/*
					//收录客户信息
					$company_name = iconv("utf-8","gb2312",trim($info['customer']));
					$data = array();
					$data['company_name'] = $info['customer'];
					$data['cm_id'] = $info['create_user'];
					$data['cm_name'] = $info['create_user_name'];
					$data['cm_time'] = $info['create_time'];
					$data['create_time'] = $info['create_time'];
					$data['pinyin'] = strtolower($PinYin->getFirstPY($company_name));	
					
					if(!M('customer_gec')->where(array('company_name'=>$info['customer'],'cm_id'=>$info['create_user']))->find()){
						M('customer_gec')->add($data);
					}
					*/

					//创建工单
                    $id                 = $info['kind'];
                    $pro_info           = M('project_kind')->where(array('id'=>$id) )->find();
                    $pid                = $pro_info['pid'];
                    $pro_name           = $pro_info['name'];
                    $worder             = array();
                    $worder['op_id']    = M("op")->where(array('id'=>$addok))->getField('op_id');
                    $worder['worder_title']     = $info['project'];
                    $worder['worder_content']   = $info['context'];
                    $worder['worder_type']      = 100;
                    $worder['status']           = 0;
                    $worder['ini_user_id']      = cookie('userid');
                    $worder['ini_user_name']    = cookie('name');
                    $worder['ini_dept_id']      = cookie('roleid');
                    $worder['ini_dept_name']    = cookie('rolename');
                    $worder['create_time']      = NOW_TIME;
                    $u_time                     = 5;    //默认5个工作日
                    //计划完成时间 $u_time为工作日
                    $worder['plan_complete_time']= strtotime(getAfterWorkDay($u_time));

                    if($exe_role_ids){
                        foreach($exe_role_ids as $v){
                            $exe_dept_id        = $v;
                            $exe_dept_name      = M('role')->where(array('id'=>$exe_dept_id))->getField('role_name');
                            $exe_user_id        = M('auth')->where(array('role_id'=>$exe_dept_id))->getField("worder_auth");
                            $exe_user_name      = M('account')->where(array('id'=>array('eq',$exe_user_id)))->getField('nickname');
                            $worder['exe_dept_id']      = $exe_dept_id;
                            $worder['exe_dept_name']    = $exe_dept_name;
                            $worder['exe_user_id']      = $exe_user_id;
                            $worder['exe_user_name']    = $exe_user_name;
                            $res = M('worder')->add($worder);
                            if($res){
                                //保存操作记录
                                $record = array();
                                $record['worder_id'] = $res;
                                $record['type']     = 0;
                                $record['explain']  = '立项/创建工单';
                                worder_record($record);
                                //发送系统消息
                                $uid     = cookie('userid');
                                $title   = '您有来自['.$worder['ini_dept_name'].'--'.$worder['ini_user_name'].']的工单待执行!';
                                $content = $worder['worder_content'];
                                $url     = U('worder/worder_info',array('id'=>$res));
                                $user    = '['.$worder['exe_user_id'].']';
                                send_msg($uid,$title,$content,$url,$user,'');
                            }
                        }
                    }

                    //发送立项消息提示[1线路(韩露) , 2课程(魏春竹) , 3其他(秦鸣)]
                    if($id == 1 || $pid ==1){
                        $exe_d_id            =  65;
                    }elseif ($id ==2 || $pid ==2){
                        $exe_d_id            =  76;
                    }elseif ($id ==3 || $pid==3){
                        $exe_d_id            =  14;
                    }
                    $exe_user_id        = M('auth')->where(array('role_id'=>$exe_d_id))->getField("worder_auth");
                    //发送系统消息
                    $uid     = cookie('userid');
                    $title   = '您有来自['.$worder['ini_dept_name'].'--'.$worder['ini_user_name'].']的['.$pro_name.']项目!请注意跟进!';
                    $content = $worder['worder_content'];
                    $url     = U('Op/plans_follow',array('opid'=>$worder['op_id']));
                    $user    = '['.$exe_user_id.']';
                    //send_msg($uid,$title,$content,$url,$user,'');

					$this->success('保存成功！',U('Op/index'));
				}else{
					$this->error('保存失败' . $db->getError());	
				}
				
			}else{
				$this->error('保存失败' . $db->getError());	
			}
			
		}else{
			
			//客户名称关键字
			$where = array();
			if(C('RBAC_SUPER_ADMIN')==cookie('username') || cookie('roleid')==10 || cookie('roleid')==28 || cookie('roleid')==11 || cookie('roleid')==30){
				$where['company_name'] = array('neq','');
			}else{
				$where['company_name'] = array('neq','');
				$where['cm_id'] = array('in',Rolerelation(cookie('roleid')));
			}
		
			$key =  M('customer_gec')->field('id,pinyin,company_name')->where($where)->group("company_name")->order('pinyin ASC')->select();
			foreach($key as $v){
				if(!$v['pinyin']){
					$company_name = iconv("utf-8","gb2312",trim($v['company_name']));
					$pinyin = strtolower($PinYin->getFirstPY($company_name));	
					M('customer_gec')->data(array('pinyin'=>$pinyin))->where(array('id'=>$v['id']))->save();
				}
			}
			//if($key) $this->keywords =  json_encode($key);
            //固定线路
            $linelist   = M('product_line')->field('id,title,pinyin')->where(array('type'=>2))->select();
            foreach ($linelist as $v){
                if(!$v['pinyin']){
                    $title = iconv("utf-8","gb2312",trim($v['title']));
                    $pinyin = strtolower($PinYin->getFirstPY($title));
                    M('product_line')->data(array('pinyin'=>$pinyin))->where(array('id'=>$v['id']))->save();
                }
            }
            $this->linelist    = json_encode($linelist);
			$this->geclist     = M('customer_gec')->field('id,pinyin,company_name')->where($where)->group("company_name")->order('pinyin ASC')->select();
			$this->kinds       = get_project_kinds();
			$this->userlist    =  M('account')->where('`id`>3')->getField('id,nickname', true);
			$this->rolelist    =  M('role')->where('`id`>10')->getField('id,role_name', true);
            $this->apply_to    = C('APPLY_TO');
			$this->title('出团计划');
			$this->display('plans');
		}
    }
    
	
	// @@@NODE-3###plans_info###出团计划###
    public function plans_info(){
		
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
		$guide      = M()->table('__OP_GUIDE__ as g')->field('g.*,c.cost,c.amount,c.total')->join('__OP_COST__ as c on c.relevant_id=g.guide_id','LEFT')->where(array('g.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>2))->order('g.id')->select();
		$supplier   = M()->table('__OP_SUPPLIER__ as s')->field('s.id as sid,s.op_id,s.supplier_id,s.supplier_name,s.city,s.kind,s.remark as sremark,c.*')->join('__OP_COST__ as c on c.relevant_id=s.supplier_id')->where(array('s.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>3))->order('sid')->select();
		$member     = M('op_member')->where(array('op_id'=>$opid))->order('id')->select();
		$costlist   = M('op_cost')->where(array('op_id'=>$opid))->order('cost_type')->select();
		$shouru     = $op['sale_cost']*$op['number'];
		$chengben   = M('op_cost')->where(array('op_id'=>$opid))->sum('total');
		$wuzi       = M()->table('__OP_MATERIAL__ as m')->field('c.*,m.*')->join('__OP_COST__ as c on m.material=c.remark')->where(array('m.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>4))->order('m.id')->select();
		$pretium    = M('op_pretium')->where(array('op_id'=>$opid))->order('id')->select();
		$costacc    = M('op_costacc')->where(array('op_id'=>$opid))->order('id')->select();
		
		$days       = M('op_line_days')->where(array('op_id'=>$opid))->select();
		$opauth     = M('op_auth')->where(array('op_id'=>$opid))->find();
		$record     = M('op_record')->where(array('op_id'=>$opid))->order('id DESC')->select();
		
		
		
		$where = array();
		$where['req_type'] = P::REQ_TYPE_PROJECT_NEW;
		$where['req_id']   = $op['id'];
		$audit = M('audit_log')->where($where)->find();
		if($audit['dst_status']==0){
			$show = '未审批';
			$show_user = '未审批';
			$show_time = '等待审批';
		}else if($audit['dst_status']==1){
			$show = '<span class="green">已通过</span>';
			$show_user = $audit['audit_uname'];
			$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
		}else if($audit['dst_status']==2){
			$show = '<span class="red">未通过</span>';
			$show_user = $audit['audit_uname'];
			$show_reason = $audit['audit_reason'];
			$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
		}
		$op['showstatus'] = $show;
		$op['show_user']  = $show_user;
		$op['show_time']  = $show_time;
		$op['show_reason']  = $show_reason;
		
		if($op['line_id']){
			$linetext   = M('product_line')->find($op['line_id']);
			$this->linetext = '<h4>行程来源：<a href="'.U('Product/view_line',array('id'=>$linetext['id'])).'" target="" id="travelcom">'.$linetext['title'].'</a><input type="hidden" name="line_id" value="'.$linetext['id'].'" ></h4>';	
		}else{
			$this->linetext = '';		
		}
		
		$this->kinds          =  M('project_kind')->getField('id,name', true);
		$this->user           =  M('account')->where('`id`>3')->getField('id,nickname', true);
		$this->op             = $op;
		$this->pro            = $pro;
		$this->guide          = $guide;
		$this->supplier       = $supplier;
		$this->member         = $member;
		$this->pretium        = $pretium;
		$this->costacc        = $costacc;
		$this->costlist       = $costlist;
		$this->chengben       = $chengben;
		$this->shouru         = $shouru;
		$this->wuzi           = $wuzi;
		$this->days           = $days;
		$this->opauth         = $opauth;
		$this->record         = $record;
		$this->business_depts = C('BUSINESS_DEPT');
		$this->subject_fields = C('SUBJECT_FIELD');
		$this->ages           = C('AGE_LIST');
		$this->display('plans_info');
	}
	
	
	
	// @@@NODE-3###plans_follow###项目跟进###
    public function plans_follow(){

        header('Content-Type:text/html;charset=utf-8');
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
		$guide      = M()->table('__OP_GUIDE__ as g')->field('g.*,c.cost,c.amount,c.total,c.gpk_id,gui.tel')->join('__OP_COST__ as c on c.link_id=g.id')->join('left join __GUIDE__ as gui on g.guide_id = gui.id')->where(array('g.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>2))->order('g.id')->select();
		$supplier   = M()->table('__OP_SUPPLIER__ as s')->field('s.id as sid,s.op_id,s.supplier_id,s.supplier_name,s.city,s.kind,s.remark as sremark,c.*')->join('__OP_COST__ as c on c.link_id=s.id')->where(array('s.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>3))->order('sid')->select();
		$member     = M('op_member')->where(array('op_id'=>$opid))->order('id')->select();
		$costlist   = M('op_cost')->where(array('op_id'=>$opid))->order('cost_type')->select();
		$shouru     = $op['sale_cost']*$op['number'];
		$chengben   = M('op_cost')->where(array('op_id'=>$opid))->sum('total');
		$wuzi       = M()->table('__OP_MATERIAL__ as m')->field('c.*,m.*')->join('__OP_COST__ as c on m.id=c.link_id')->where(array('m.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>4))->order('m.id')->select();
		$pretium    = M('op_pretium')->where(array('op_id'=>$opid))->order('id')->select();
		$costacc    = M('op_costacc')->where(array('op_id'=>$opid))->order('id')->select();
		
		$opauth     = M('op_auth')->where(array('op_id'=>$opid))->find();
		$record     = M('op_record')->where(array('op_id'=>$opid))->order('id DESC')->select();
		$budget     = M('op_budget')->where(array('op_id'=>$opid))->find();
		$settlement = M('op_settlement')->where(array('op_id'=>$opid))->find();
        $resource   = M('op_res')->where(array('op_id'=>$opid))->find();
        $res_money  = M('op_res_money')->where(array('op_res_id'=>$resource['id']))->select();

        //根据line_id判断是普通线路还是固定线路
        $line_id    = $op['line_id'];
        $line_type  = M('product_line')->where(array('id'=>$line_id))->getField('type');
        if ($line_type == 2){
            //固定线路
            $days = M('product_line_days')->where(array('line_id'=>$line_id))->select();
            $days['op_id'] = $opid;
        } else{
            //普通行程
            $days       = M('op_line_days')->where(array('op_id'=>$opid))->select();
        }

        $where = array();
		$where['req_type'] = P::REQ_TYPE_PROJECT_NEW;
		$where['req_id']   = $op['id'];
		$audit = M('audit_log')->where($where)->find();
		if($audit['dst_status']==0){
			/*$show = '未审批';
			$show_user = '未审批';
			$show_time = '等待审批';*/
            $show = '系统默认通过';
            $show_user = '系统默认';
            $show_time = date('Y-m-d H:i:s',$op['create_time']);
		}else if($audit['dst_status']==1){
			$show = '<span class="green">已通过</span>';
			$show_user = $audit['audit_uname'];
			$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
		}else if($audit['dst_status']==2){
			$show = '<span class="red">未通过</span>';
			$show_user = $audit['audit_uname'];
			$show_reason = $audit['audit_reason'];
			$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
		}
		$op['showstatus'] = $show;
		$op['show_user']  = $show_user;
		$op['show_time']  = $show_time;
		$op['show_reason']  = $show_reason;
		
		if($op['line_id']){
			$linetext   = M('product_line')->find($op['line_id']);
			$this->linetext = '<h4>已选方案：<a href="'.U('Product/view_line',array('id'=>$linetext['id'])).'" target="_blank" id="travelcom">'.$linetext['title'].'</a><input type="hidden" name="line_id" value="'.$linetext['id'].'" ></h4>';	
		}else{
			$this->linetext = '';		
		}
		
		//自动生成团号
		$roles = M('role')->where(array('role_name'=>$op['op_create_user']))->find();
		$tuanhao = $roles['name'].str_replace("-", "",$op['departure']);
		//验证团号是否可用
		$istuanhao = M('op')->where(array('group_id'=>array('like','%'.$tuanhao.'%')))->count();		
		if($istuanhao){
			$this->tuanhao    = $tuanhao.'-'.($istuanhao);
		}else{
			$this->tuanhao    = $tuanhao;
		}

		//项目需求单
        $service_type         = explode(',',$resource['service_type']);
        $act_need             = explode(',',$resource['act_need']);
        $les_field            = explode(',',$resource['les_field']);
        $act_field            = explode(',',$resource['act_field']);
        if ($resource['cou_time']) $resource['cou_time'] = date('Y-m-d',$resource['cou_time']);


        $job_name       = C('JOB_NAME');
        $job_names      = array();
        foreach($job_name as $key=>$value){
            $job_names[$key]['job_name'] = $value;
            foreach ($res_money as $k=>$v){
                if ($value == $v['job_name']){
                    $job_names[$key]['job_money']= $v['job_money'];
                }
            }
            $job_names[$key]['job_money']= $job_names[$key]['job_money']?$job_names[$key]['job_money']:null;
        }

        //项目类型
        //线路1 , 课程 2 , 其他 3
        $kind       = $op['kind'];
        $line       = M('project_kind')->where("id ='1' or pid ='1'")->getField('id',true);
        $lessions   = M('project_kind')->where("id ='2' or pid ='2'")->getField('id',true);
        $cgly       = M('project_kind')->where("name like '%常规旅游%'")->getField('id',true); //从'其他'栏目中提取 '常规旅游'放入线路中
        $lines      = array_merge($line,$cgly);
        $fixed_lineids  = M('product_line')->where(array('type'=>2))->getField('id',true);    //固定线路
        if (in_array($line_id,$fixed_lineids)){
            $this->isFixedLine = 1;
        }
        $guide_pk_id= M('guide_pricekind')->field('id,name')->select();
        $sum_cost = 0;
        foreach ($guide as $k=>$v){
            $sum_cost += $v['total'];
            foreach ($guide_pk_id as $val){
                if ($v['gpk_id'] == $val['id']){
                    $guide[$k]['gpk_name'] = $val['name'];
                }
            }
        }
        //获取职能类型
        $priceKind = M()->table('__GUIDE_PRICEKIND__ as gpk')->field('gpk.id,gpk.name')->join('left join __OP__ as op on gpk.pk_id = op.kind')->where(array("op.op_id"=>$opid))->select();
        $this->price_kind     = $priceKind;
        $this->opid           = $opid;
		$this->kinds          = M('project_kind')->getField('id,name', true);
		$this->user           = M('account')->where('`id`>3')->getField('id,nickname', true);
		$this->rolelist       = M('role')->where('`id`>10')->getField('id,role_name', true);
		$this->op             = $op;
		$this->pro            = $pro;
		$this->budget         = $budget;
        $this->sum_cost       = $sum_cost;
		$this->settlement     = $settlement;
		$this->supplier       = $supplier;
		$this->member         = $member;
		$this->pretium        = $pretium;
		$this->costacc        = $costacc;
		$this->costlist       = $costlist;
		$this->chengben       = $chengben;
		$this->shouru         = $shouru;
		$this->wuzi           = $wuzi;
		$this->days           = $days;
		$this->opauth         = $opauth;
		$this->record         = $record;
		$this->business_depts = C('BUSINESS_DEPT');
		$this->subject_fields = C('SUBJECT_FIELD');
		$this->ages           = C('AGE_LIST');
        $this->service_type   = C('SERVICE_TYPE');
        $this->act_need       = C('ACT_NEED');
        $this->les_field      = C('LES_FIELD');
        $this->act_field      = C('ACT_FIELD');
        $this->resource       = $resource;
        $this->service_types  = $service_type;
        $this->act_needs      = $act_need;
        $this->les_fields     = $les_field;
        $this->act_fields     = $act_field;
        $this->guide          = $guide;

        //$this->job_name      = array_filter(array_column($job_names,'job_money','job_name'));
        $this->job_name       = array_column($job_names,'job_money','job_name');
        $this->xuhao          = 1;
        $this->huikuan_status = M('contract_pay')->where(array('op_id'=>$opid))->getField('status');
        $this->guide_kind     = M('guidekind')->getField('id,name',true);
        $this->guide_price    = M('op_guide_price')->where(array('op_id'=>$opid))->select();
        $this->apply_to       = C('APPLY_TO');
        if ($this->guide_price) {
            $this->rad = 1;
        }else{
            $this->rad = 0;
        }

        //资源需求单接收人员(资源管理部经理)
        $this->men            = M('account')->field('id,nickname')->where(array('roleid'=>52))->find();
        $this->tcs = M()->table('__OP_GUIDE_PRICE__ as gp')
            ->field('gp.*,gk.name as gkname,gpk.name as gpkname')
            ->join('left join __GUIDEKIND__ as gk on gp.guide_kind_id = gk.id')
            ->join('left join __GUIDE_PRICEKIND__ as gpk on gp.gpk_id = gpk.id')
            ->where(array('gp.op_id'=>$opid))
            ->select();

        //客户名称关键字
		$where = array();
		if(C('RBAC_SUPER_ADMIN')==cookie('username') || cookie('roleid')==10 || cookie('roleid')==28 || cookie('roleid')==11 || cookie('roleid')==30){
			$where['company_name'] = array('neq','');
		}else{
			$where['company_name'] = array('neq','');
			$where['cm_id'] = array('in',Rolerelation(cookie('roleid')));
		}
		$this->geclist     = M('customer_gec')->field('id,pinyin,company_name')->where($where)->group("company_name")->order('pinyin ASC')->select();

        //人员名单关键字
        $user   = M('account')->field("id,nickname")->where(array('status'=>0))->select();
        $user_key    = array();
        foreach($user as $k=>$v){
            $text           = $v['nickname'];
            $user_key[$k]['id']  = $v['id'];
            $user_key[$k]['pinyin'] = strtopinyin($text);
            $user_key[$k]['text']       = $text;
        }
        $this->userkey = json_encode($user_key);

        $this->display('plans_edit');
		
	}
	
	
	// @@@NODE-3###public_save###保存项目###
    public function public_save(){
		
		if(isset($_POST['dosubmint']) && $_POST['dosubmint']){
			
			$db             = M('op');
			$op_cost_db     = M('op_cost');
			$op_guide_db    = M('op_guide');
			$op_member_db   = M('op_member');
			$op_supplier_db = M('op_supplier');
            $op_res_db      = M('op_res');
            $op_res_money_db= M('op_res_money');
            $op_guide_price_db = M('op_guide_price');


            $opid       = I('opid');
			$info       = I('info');
			$guide      = I('guide');
			$member     = I('member');
			$cost       = I('cost');
			$supplier   = I('supplier');
			$wuzi       = I('wuzi');
			$savetype   = I('savetype');
			$days       = I('days');
			$resid      = I('resid');
			$num        = 0;
			
			
			//保存专家辅导员信息
			if($opid && $savetype==2 ){
				$delid = array();
				foreach($guide as $k=>$v){
					$data = array();
					$data = $v;
					if($resid && $resid[$k]['id']){
						$edits = $op_guide_db->data($data)->where(array('id'=>$resid[$k]['id']))->save();
						$delid[] = $resid[$k]['id'];
						$cost[$k]['link_id'] = $resid[$k]['id'];
						$num++;
					}else{
						$data['op_id'] = $opid;
						$savein = $op_guide_db->add($data);
						$delid[] = $savein;
						$cost[$k]['link_id'] = $savein;
						if($savein) $num++;
					}
				}	
				
				$where = array();
				$where['op_id'] = $opid;
				if($delid) $where['id'] = array('not in',$delid);
				$del = $op_guide_db->where($where)->delete();
				if($del) $num++;
				
				if($num){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 4;
					$record['explain'] = '专家辅导员资源';
					op_record($record);
				}
				
				
			
			}
				
			//保存合格供方信息
			if($opid && $savetype==3 ){		
					
				$delid = array();
				foreach($supplier as $k=>$v){
					$data = array();
					$data = $v;
					if($resid && $resid[$k]['id']){
						$edits = $op_supplier_db->data($data)->where(array('id'=>$resid[$k]['id']))->save();
						$delid[] = $resid[$k]['id'];
						$cost[$k]['link_id'] = $resid[$k]['id'];
						$num++;
					}else{
						$data['op_id'] = $opid;
						$savein = $op_supplier_db->add($data);
						$delid[] = $savein;
						$cost[$k]['link_id'] = $savein;
						if($savein) $num++;
					}
				}	
				
				$where = array();
				$where['op_id'] = $opid;
				if($delid) $where['id'] = array('not in',$delid);
				$del = $op_supplier_db->where($where)->delete();
				if($del) $num++;
				
				if($num){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 4;
					$record['explain'] = '调度合格供方资源资源';
					op_record($record);
				}
			}
					
			//保存物资信息	
			if($opid && $savetype==4 ){
				
				$delid = array();
				foreach($wuzi as $k=>$v){
					$data = array();
					$data = $v;
					if($resid && $resid[$k]['id']){
						$edits = M('op_material')->data($data)->where(array('id'=>$resid[$k]['id']))->save();
						$cost[$k]['link_id'] = $resid[$k]['id'];
						$delid[] = $resid[$k]['id'];
						$num++;
					}else{
						$data['op_id'] = $opid;
						$savein = M('op_material')->add($data);
						$cost[$k]['link_id'] = $savein;
						$delid[] = $savein;
						if($savein) $num++;
					}
				}	
				
				$where = array();
				$where['op_id'] = $opid;
				if($delid) $where['id'] = array('not in',$delid);
				$del = M('op_material')->where($where)->delete();
				if($del) $num++;
				
				if($num){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 4;
					$record['explain'] = '调度物资';
					op_record($record);
				}
			}
			
			//保存用户名单信息
			if($opid && $savetype==5 ){
				
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
						$data['sales_person_uid'] = cookie('userid');
						$data['sales_time']       = time();
						$savein = $op_member_db->add($data);
						$delid[] = $savein;
						if($savein) $num++;
						
						//将名单保存至客户名单
						if(!M('customer_member')->where(array('number'=>$v['number']))->find()){
							$mem = $v;
							$mem['source'] = cookie('userid');
							$mem['create_time'] = time();
							M('customer_member')->add($mem);
						}
					}
				}	
				
				$where = array();
				$where['op_id'] = $opid;
				if($delid) $where['id'] = array('not in',$delid);
				$del = $op_member_db->where($where)->delete();
				if($del) $num++;
				
				if($num){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 7;
					$record['explain'] = '保存用户名单';
					op_record($record);
				}
				
			}
			
			//确定成团
			if($opid && $savetype==9 ){
				
				$data = array();
				$data['status'] = I('status');
				$data['group_id'] = strtoupper(I('gid'));
				$data['nogroup'] = I('nogroup');
				$op = M('op')->where(array('op_id'=>$opid))->find();
				if($op['audit_status']==1){
					//保存成团
					$issave = M('op')->data($data)->where(array('op_id'=>$opid))->save();
					if($issave) $num++;
				}
				if($num){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 6;
					if($data['status']==1){
						$record['explain'] = '项目成团操作';
					}elseif($data['status']==2){
						$record['explain'] = '项目不成团操作';
					}
					op_record($record);
				}
			}
			
			//修改项目基本信息
			if($opid && $savetype==10 ){
				
				$op = M('op')->where(array('op_id'=>$opid))->find();
				if($op['status']=='0' || cookie('roleid')==10) {
                    //保存成团
                    $issave = M('op')->data($info)->where(array('op_id' => $opid))->save();
                    if ($issave) $num++;
                }
				if($num){
					$record = array();
					$record['op_id']   = $opid;
					$record['optype']  = 1;
					$record['explain'] = '修改项目基本信息';
					op_record($record);
				}

			}
				
			//保存价格
			if($cost){
				$i = 0;
				$op_cost_db->where(array('op_id'=>$opid,'cost_type'=>$savetype))->delete();
				foreach($cost as $k=>$v){
					$data = array();
					$data = $v;
					$data['op_id'] = $opid;
					if($data['cost_type']==1){
						$data['amount'] = $info['number'];
					}
					$data['total'] = $data['cost']*$data['amount'];
					
					$op_cost_db->add($data);
					
					$i++;
				}	
			}

			//保存资源需求单
            if($opid && $savetype==11 ){
                header('Content-Type:text/html;charset=utf-8');
                $info['op_id']      = $opid;
                $data               = I('data');
                $service_types      = I('service_type');
                $act_needs          = I('act_need');
                $les_fields         = I('les_field');
                $act_fields         = I('act_field');
                $info['service_type']= implode(',',$service_types);
                $info['act_need']   = implode(',',$act_needs);
                $info['les_field']  = implode(',',$les_fields);
                $info['act_field']  = implode(',',$act_fields);
                $info['cou_time']   = strtotime($info['cou_time']);

                $saved_id = $op_res_db->where(array('op_id'=>$opid))->getField('id');
                if ($saved_id){
                    $op_res_db->where(array('id'=>$saved_id))->save($info);
                    $res = $saved_id;
                }else{
                    $info['ini_time']   = NOW_TIME;
                    $res = $op_res_db->add($info);
                }
                if($res){
                    $num++;
                    $op_res_money_db->where(array('op_res_id'=>$res))->delete();
                    foreach ($data as $v){
                        if ($v['job_name']) {
                            $v['op_res_id'] = $res;
                            $op_res_money_db->add($v);
                        }
                    }

                    /*$exe_dept_id        = 52;   //资源管理部经理
                    $exe_user_id        = M('auth')->where(array('role_id'=>$exe_dept_id))->getField("worder_auth");*/
                    $exe_user_id        = $info['exe_user_id'];
                    if (cookie('userid') != $info['exe_user_id']){
                        //发送系统消息
                        $uid     = cookie('userid');
                        $title   = '您有来自['.session('rolename').'--'.$info['ini_user_name'].']的项目需求单!';
                        $content = '项目编号: '.$opid;
                        $url     = U('Op/plans_follow',array('opid'=>$info['op_id']));
                        $user    = '['.$exe_user_id.']';
                        send_msg($uid,$title,$content,$url,$user,'');
                    }

                }
            }

            //保存辅导员/教师,专家需求
            if($opid && $savetype==12 ){
                $data = I('data');
                $savedel = $op_guide_price_db->where(array('op_id'=>$opid))->delete();
                $upd_tcs = array();
                $upd_tcs['tcs_stu'] = 0;
                $db->where(array('op_id'=>$opid))->save($upd_tcs);
                if ($savedel)  $num++;
                foreach($data as $k=>$v){
                    $v['op_id'] = $opid;
                    $savein     = $op_guide_price_db->add($v);
                    //修改专家辅导员状态
                    $group_id = $db->where(array('op_id'=>$opid))->getField('group_id');
                    if ($group_id){
                        $info['tcs_stu'] = 2;
                    }else{
                        $info['tcs_stu'] = 1;   //需要专家辅导员
                    }
                    $res = $db->where(array('op_id'=>$opid))->save($info);
                    if ($res){
                        $record = array();
                        $record['op_id']   = $opid;
                        $record['optype']  = 4;
                        $record['explain'] = '填写专家辅导员资源需求';
                        op_record($record);
                    }
                    if($savein) $num++;
                }
            }

            //保存辅导员/教师,专家需求信息
            if($opid && $savetype==13 ){
                $data = I('data');
                $savedel = $op_guide_price_db->where(array('op_id'=>$opid))->delete();
                if ($savedel)  $num++;
                foreach($data as $k=>$v){
                    $v['op_id'] = $opid;
                    $savein     = $op_guide_price_db->add($v);
                    if($savein) $num++;
                }
            }

            //保存辅导员/教师,专家需求信息
            if($opid && $savetype==14 ){
                $aaa = I();
                var_dump($aaa);die;
            }

            echo $num;
        }
	
	}


	//@@@NODE-3###assign_line###指派人员跟进线路行程信息###
    public function assign_line(){
		$opid       = I('opid');
		$info       = I('info');
		$user       =  M('account')->getField('id,nickname', true);
		
		if(isset($_POST['dosubmit']) && $info){
			
			$data = array();
			$data['line'] = $info;
			$auth = M('op_auth')->where(array('op_id'=>$opid))->find();

            //创建工单
            $thing  = "行程方案";
            project_worder($info,$opid,$thing);

            if($auth){
				M('op_auth')->data($data)->where(array('id'=>$auth['id']))->save();
			}else{
				$data['op_id'] = $opid;
				M('op_auth')->add($data);
			}
			
			$record = array();
			$record['op_id']   = $opid;
			$record['optype']  = 2;
			$record['explain'] = '指派【'.$user[$info].'】负责项目行程';
			op_record($record);
				
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			
			//用户列表
			$key = I('key');
			$db = M('account');
			$where = array();
			$where['id'] = array('gt',3);
			if($key) $where['nickname'] = array('like','%'.$key.'%');
			$pagecount = $db->where($where)->count();
			$page = new Page($pagecount,6);
			$this->pages = $pagecount>6 ? $page->show():'';
			$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('roleid'))->select();
			$this->role  = M('role')->getField('id,role_name', true);
			$this->opid = $opid;
			$this->display('assign_line');
		}
	}
	
	
	
	//@@@NODE-3###assign_res###指派人员跟进资源调度###
    public function assign_res(){
		$opid       = I('opid');
		$info       = I('info');
		$user       =  M('account')->getField('id,nickname', true);
		if(isset($_POST['dosubmit']) && $info){
			
			$data = array();
			$data['res'] = $info;
			$auth = M('op_auth')->where(array('op_id'=>$opid))->find();

            //创建工单
            $thing  = "物资调度";
            project_worder($info,$opid,$thing);

			if($auth){
				M('op_auth')->data($data)->where(array('id'=>$auth['id']))->save();
			}else{
				$data['op_id'] = $opid;
				M('op_auth')->add($data);
			}



			$record = array();
			$record['op_id']   = $opid;
			$record['optype']  = 2;
			$record['explain'] = '指派【'.$user[$info].'】负责项目所需资源调度';
			op_record($record);
			
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			
			//用户列表
			$key = I('key');
			$db = M('account');
			$where = array();
			$where['id'] = array('gt',3);
			if($key) $where['nickname'] = array('like','%'.$key.'%');
			$pagecount = $db->where($where)->count();
			$page = new Page($pagecount,6);
			$this->pages = $pagecount>6 ? $page->show():'';
			$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('roleid'))->select();
			$this->role  =  M('role')->getField('id,role_name', true);
			$this->opid = $opid;
			$this->display('assign_res');
		}
	}
	
	
	//@@@NODE-3###assign_res###指派人员跟进导游辅导员调度###
    public function assign_guide(){
		$opid       = I('opid');
		$info       = I('info');
		$user       =  M('account')->getField('id,nickname', true);
		if(isset($_POST['dosubmit']) && $info){
			
			$data = array();
			$data['guide'] = $info;
			$auth = M('op_auth')->where(array('op_id'=>$opid))->find();

            //创建工单
            $thing  = "专家辅导员调度";
            project_worder($info,$opid,$thing);

			if($auth){
				M('op_auth')->data($data)->where(array('id'=>$auth['id']))->save();
			}else{
				$data['op_id'] = $opid;
				M('op_auth')->add($data);
			}
			
			$record = array();
			$record['op_id']   = $opid;
			$record['optype']  = 2;
			$record['explain'] = '指派【'.$user[$info].'】负责项目导游辅导员调度';
			op_record($record);
			
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			
			//用户列表
			$key = I('key');
			$db = M('account');
			$where = array();
			$where['id'] = array('gt',3);
			if($key) $where['nickname'] = array('like','%'.$key.'%');
			$pagecount = $db->where($where)->count();
			$page = new Page($pagecount,6);
			$this->pages = $pagecount>6 ? $page->show():'';
			$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('roleid'))->select();
			$this->role  =  M('role')->getField('id,role_name', true);
			$this->opid = $opid;
			$this->display('assign_guide');
		}
	}
	
	
	//@@@NODE-3###assign_res###指派人员跟进导游辅导员调度###
    public function assign_material(){
		$opid       = I('opid');
		$info       = I('info');
		$user       =  M('account')->getField('id,nickname', true);
		if(isset($_POST['dosubmit']) && $info){
			
			$data = array();
			$data['material'] = $info;
			$auth = M('op_auth')->where(array('op_id'=>$opid))->find();

            //创建工单
            $thing  = "合格供方调度";
            project_worder($info,$opid,$thing);

			if($auth){
				M('op_auth')->data($data)->where(array('id'=>$auth['id']))->save();
			}else{
				$data['op_id'] = $opid;
				M('op_auth')->add($data);
			}
			
			$record = array();
			$record['op_id']   = $opid;
			$record['optype']  = 2;
			$record['explain'] = '指派【'.$user[$info].'】负责项目合格供方调度';
			op_record($record);
			
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			
			//用户列表
			$key = I('key');
			$db = M('account');
			$where = array();
			$where['id'] = array('gt',3);
			if($key) $where['nickname'] = array('like','%'.$key.'%');
			$pagecount = $db->where($where)->count();
			$page = new Page($pagecount,6);
			$this->pages = $pagecount>6 ? $page->show():'';
			$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('roleid'))->select();
			$this->role  =  M('role')->getField('id,role_name', true);
			$this->opid = $opid;
			$this->display('assign_material');
		}
	}
	
	
	//@@@NODE-3###assign_price###指派人员制定价格###
    public function assign_price(){
		$opid       = I('opid');
		$info       = I('info');
		$user       =  M('account')->getField('id,nickname', true);
		if(isset($_POST['dosubmit']) && $info){
			
			$data = array();
			$data['price'] = $info;
			$auth = M('op_auth')->where(array('op_id'=>$opid))->find();

            //创建工单
            $thing  = "项目标价";
            project_worder($info,$opid,$thing);

			if($auth){
				M('op_auth')->data($data)->where(array('id'=>$auth['id']))->save();
			}else{
				$data['op_id'] = $opid;
				M('op_auth')->add($data);
			}
			$record = array();
			$record['op_id']   = $opid;
			$record['optype']  = 2;
			$record['explain'] = '指派【'.$user[$info].'】负责项目标价';
			op_record($record);
			
			echo '<script>window.top.location.reload();</script>';
			
		}else{
			
			//用户列表
			$key = I('key');
			$db = M('account');
			$where = array();
			$where['id'] = array('gt',3);
			if($key) $where['nickname'] = array('like','%'.$key.'%');
			$pagecount = $db->where($where)->count();
			$page = new Page($pagecount,6);
			$this->pages = $pagecount>6 ? $page->show():'';
			$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('roleid'))->select();
			$this->role  =  M('role')->getField('id,role_name', true);
			$this->opid = $opid;
			$this->display('assign_price');
		}
	}
	
	
	
	//@@@NODE-3###public_save_price###保存项目价格###
    public function public_save_price(){
		
		$db         = M('op_pretium');
		$opid       = I('opid');
		$pretium    = I('pretium');
		$resid      = I('resid');
		$num        = 0;
		
		//保存价格政策
		if($opid && $pretium){
			
			$delid = array();
			foreach($pretium as $k=>$v){
				$data = array();
				$data = $v;
				if($resid && $resid[$k]['id']){
					$edits = $db->data($data)->where(array('id'=>$resid[$k]['id']))->save();
					$delid[] = $resid[$k]['id'];
					$num++;
				}else{
					$data['op_id'] = $opid;
					$savein = $db->add($data);
					$delid[] = $savein;
					if($savein) $num++;
				}
			}	
			
			$del = $db->where(array('op_id'=>$opid,'id'=>array('not in',$delid)))->delete();
			if($del) $num++;
		}
		
		if($num){
			$record = array();
			$record['op_id']   = $opid;
			$record['optype']  = 5;
			$record['explain'] = '保存项目标价';
			op_record($record);
		}
			
		echo $num;
	}
	
	
	
	
	
	
	
	// @@@NODE-3###public_save_line###保存线路###
    public function public_save_line(){
			
		$opid       = I('opid');
		$days       = I('days');
		$line_id    = I('line_id');
		$num        = 0;
		
		//保存线路
		$isadd = M('op')->data(array('line_id'=>$line_id))->where(array('op_id'=>$opid))->save();
		
		if($isadd)  $num++;
		
		//删除历史日程
		$del = M('op_line_days')->where(array('op_id'=>$opid))->delete();
		if($del) $num++;
		foreach($days as $v){
			 $data = array();
			 $data['op_id'] = $opid;
			 $data['citys']    =  $v['citys'];
			 $data['content']  =  $v['content'];
			 $data['remarks']  =  $v['remarks'];
			 $savein = M('op_line_days')->add($data);
			 if($savein) $num++;
		}	
		
		/*
		//剔除其他线路所带过来的物资
		$where_del = array();
		$where_del['line_id']   = array('gt',0);
		$where_del['line_id']   = array('neq',$line_id);
		$where_del['op_id']     = $opid;	
		$isdel = M('op_material')->where($where_del)->delete();
		if($isdel) $num++;
		
		//剔除其他线路所带过来的物资价格
		$where_del['cost_type'] = 4;	
		$isdel = M('op_cost')->where($where_del)->delete();
		if($isdel) $num++;
		
		//将线路中所包含的模块物资清单转入项目中
		$pdata = M('product_line_tpl')->where(array('line_id'=>$line_id,'type'=>1))->getField('pro_id',true);
		$where = array();
		$where['product_id'] = array('in',implode(',',$pdata));
		$list = M('product_material')->where($where)->select();
		
		//保存物资清单
		foreach($list as $v){
			
			//获取物资编号
			$mid = M('material')->where(array('material'=>$v['material']))->getField('id');
			
			$material = array();
			$material['op_id']       = $opid;
			$material['material']    = $v['material'];
			$material['remarks']     = $v['remarks'];
			$material['material_id'] = $mid;
			$material['line_id']     = $line_id;
			
			$cost = array();
			$cost['op_id']       = $opid;
			$cost['item']        = '物资费';
			$cost['cost']        = $v['unitprice'];
			$cost['amount']      = $v['amount'];
			$cost['total']       = $v['unitprice']*$v['amount'];
			$cost['cost_type']   = 4;
			$cost['remark']      = $v['material'];
			$cost['relevant_id'] = $mid;
			$cost['line_id']     = $line_id;
			
			//判断物资是否存在
			if(!M('op_material')->where(array('material'=>$v['material'],'op_id'=>$opid))->find()){
				$addmate = M('op_material')->add($material);
				$cost['link_id'] = $addmate;
				$addcost = M('op_cost')->add($cost);
			}
			
			if($addcost || $addmate) $num++;
		}
		*/
		 
		$record = array();
		$record['op_id']   = $opid;
		$record['optype']  = 3;
		$record['explain'] = '保存项目行程线路';
		op_record($record);	 
		
		echo $num;
	}
	
	
	// @@@NODE-3###public_ajax_line###获取线路日程###
	public function public_ajax_line(){
		$db = M('product_line_days');
		$line_id = I('id');
		$list = $db->where(array('line_id'=>$line_id))->select();
		if($list){
			foreach($list as $k=>$row){
			 	echo '<div class="daylist" id="task_a_'.$row['id'].'"><a class="aui_close" href="javascript:;" style="right:25px;" onClick="del_timu(\'task_a_'.$row['id'].'\')">×</a><div class="col-md-12 pd"><label class="titou"><strong>第<span class="tihao">'.($k+1).'</span>天</strong></label><div class="input-group"><input type="text" placeholder="所在城市" name="days['.$row['id'].'][citys]" class="form-control" value="'.$row['citys'].'"></div><div class="input-group pads"><textarea class="form-control" placeholder="行程安排"  name="days['.$row['id'].'][content]">'.$row['content'].'</textarea></div><div class="input-group"><input type="text" placeholder="房餐车安排" name="days['.$row['id'].'][remarks]" value="'.$row['remarks'].'" class="form-control"></div></div></div>';	
			}
		}
		
	}
	
	
	
	// @@@NODE-3###public_ajax_material###获取模块物资信息###
	public function public_ajax_material(){
		/*
		$db = M('product_material');
		$line_id = I('id');
		$pdata = M('product_line_tpl')->where(array('line_id'=>$line_id,'type'=>1))->getField('pro_id',true);
		$where = array();
		$where['product_id'] = array('in',implode(',',$pdata));
		$list = $db->where($where)->select();
		if($list){
			foreach($list as $k=>$row){
			 	echo '<tr class="expense mokuaiwuzi" id="wuzi_mokuai_'.$k.'"><td><input type="hidden" name="cost['.(2000+$k).'][item]" value="物资费"><input type="hidden" name="cost['.(2000+$k).'][cost_type]" value="4"><input type="hidden" name="cost['.(2000+$k).'][relevant_id]" value=""><input type="hidden" name="cost['.(2000+$k).'][remark]" value="'.$row['material'].'"><input type="hidden" name="wuzi['.(2000+$k).'][material]" value="'.$row['material'].'"><input type="hidden" name="wuzi['.(2000+$k).'][material_id]" value="">'.$row['material'].'</td><td><input type="text" name="cost['.(2000+$k).'][cost]" value="'.$row['unitprice'].'" placeholder="单价" class="form-control min_input cost"></td><td><span>X</span></td><td><input type="text" name="cost['.(2000+$k).'][amount]" value="" placeholder="'.$row['amount'].'" class="form-control min_input amount"></td><td class="total">¥'.$row['unitprice']*$row['amount'].'</td><td><input type="text" name="wuzi['.(2000+$k).'][remarks]" value="'.$row['remarks'].'" class="form-control"></td><td><a href="javascript:;" class="btn btn-danger btn-flat" onclick="delbox(\'wuzi_mokuai_'.$k.'\')">删除</a></td></tr>';	
			}
		}
		*/
		$opid = I('id');
		$list = M()->table('__OP_MATERIAL__ as m')->field('c.*,m.*')->join('__OP_COST__ as c on m.material=c.remark')->where(array('m.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>4))->order('m.id')->select();
		
		foreach($list as $v){
			
			echo '<tr class="expense" id="wuzi_nid_'.$v['id'].'"><td><input type="hidden" name="cost['.(20000+$v['id']).'][item]" value="物资费"><input type="hidden" name="cost['.(20000+$v['id']).'][cost_type]" value="4"><input type="hidden" name="cost['.(20000+$v['id']).'][relevant_id]" value="'.$v['material_id'].'"><input type="hidden" name="cost['.(20000+$v['id']).'][remark]" value="'.$v['material'].'"><input type="hidden" name="resid['.(20000+$v['id']).'][id]" value="'.$v['id'].'"><input type="hidden" name="wuzi['.(20000+$v['id']).'][material]" value="'.$v['material'].'"><input type="hidden" name="wuzi['.(20000+$v['id']).'][material_id]" value="'.$v['material_id'].'">'.$v['material'].'</td><td><input type="text" name="cost['.(20000+$v['id']).'][cost]" value="'.$v['cost'].'" placeholder="价格" class="form-control min_input cost"></td><td><span>X</span></td><td><input type="text" name="cost['.(20000+$v['id']).'][amount]" value="'.$v['amount'].'" placeholder="数量" class="form-control min_input amount"></td><td class="total">¥'.($v['cost']*$v['amount']).'</td><td><input type="text" name="wuzi['.(20000+$v['id']).'][remarks]" value="'.$v['remarks'].'" class="form-control"></td><td><a href="javascript:;" class="btn btn-danger btn-flat" onclick="delbox(\'wuzi_nid_'.$v['id'].'\')">删除</a></td></tr>';
			
		}
		
	}
	
	
	// @@@NODE-3###select_product###选择产品模板###
	public function select_product(){
		
		$key          = I('key');
		$status       = I('status','-1');
		$kind         = I('kind','-1');
		$mdd          = I('mdd');
		
		$db = M('product_line');
		$this->status = $status;
		$this->kind   = $kind;
		$where = array();
		if($this->status != '-1') $where['audit_status'] = $this->status;
		if($this->kind != '-1')   $where['kind'] = $this->kind;
		if($key)    $where['title'] = array('like','%'.$key.'%');
		if($mdd)    $where['dest']  = array('like','%'.$mdd.'%');
		
		
        $page = new Page($db->where($where)->count(),25);
        $this->pages = $pagecount>25 ? $page->show():'';
		$this->lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('input_time'))->select();
		$this->kindlist = M('project_kind')->select();
		
		$this->display('select_product');
		
	}
	
	
	// @@@NODE-3###select_guide###选择导游辅导员###
	public function select_guide(){
		$kind = I('kind');
		$key  = I('key');
		$sex  = I('sex');
        $opid = I('opid');

        //求项目类型,根据项目类型计算出所选专家的价格
        $kid = M('op')->where(array('op_id'=>$opid))->getField('kind');

		$where = array();
		$where['1'] = priv_where(P::REQ_TYPE_GUIDE_RES_U);
		if($kind) $where['kind'] = $kind;
		if($key)  $where['name'] = array('like','%'.$key.'%');
		if($sex)  $where['sex'] = $sex;
		
		//分页
		$pagecount = M('guide')->where($where)->count();
		$page = new Page($pagecount,25);
		$this->pages = $pagecount>25 ? $page->show():'';
        
        $this->reskind = M('guidekind')->getField('id,name', true);
        $lists = M('guide')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('input_time'))->select();
        foreach($lists as $k=>$v){
            $gk_id  = $v['kind'];
            $price  = M('guide_price')->where(array('kid'=>$kid,'gk_id'=>$gk_id))->getField('price');
            //if($v['fee'] == '0.00') $v['fee'] = null;
            $lists[$k]['fee'] = $price;

        }
        $this->lists = $lists;
		
		$this->display('select_guide');
	}
	
	
	// @@@NODE-3###select_supplier###选择合格供方###
	public function select_supplier(){
		
		$kind = I('kind');
		$key  = I('key');
		
		$where = array();
		$where['1'] = priv_where(P::REQ_TYPE_SUPPLIER_RES_U);
		if($kind) $where['kind'] = $kind;
		if($key)  $where['name'] = array('like','%'.$key.'%');
		
		//分页
		$pagecount = M('supplier')->where($where)->count();
		$page = new Page($pagecount,25);
		$this->pages = $pagecount>25 ? $page->show():'';
        
        $this->reskind = M('supplierkind')->getField('id,name', true);
        $this->lists = M('supplier')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('input_time'))->select();
		
		$this->display('select_supplier');
	}
    
	
	// @@@NODE-3###importuser###导入名单###
	public function importuser(){
		$time = time();
		if(isset($_POST['dosubmit'])){
			
			$data = array();
			
			$file = $_FILES["file"] ? $_FILES["file"] : $this->error('请提交要导入的文件！');	
			
			//获取文件扩展名
			$fileext = explode('.',$file["name"]);
			
			if($fileext[1]=='xls' || $fileext[1]=='xlsx'){
				if ($file["size"] < 10*1024*1024){
					if ($_FILES["file"]["error"] > 0){
						//报错
						$this->error($file["error"],I('referer',''));
					}else{
						
						//新文件名
						$newname = "upload/xls/".cookie('comid').'_'.date('YmdHis',time()).'.'.$fileext[1];
						
						//上传留存
						$ismove = move_uploaded_file($file["tmp_name"],$newname);	
						
						//读取EXCEL文件
						if($ismove) $data = importexcel($newname);
						$sum = count($data)-1;
						
						$this->data = $data;
						
						
					}
				}else{
					$this->error('文件大小不能超过10M！');	
				}
			}else{
				$this->error('请上传Excel文件！');	
			}
  			
			
		} 
		
		$this->display('importuser');
		
		
		
	}
	
	
	// @@@NODE-3###app_materials###申请物资###
	public  function  app_materials(){
		$opid = I('opid');
		
		if(!$opid) $this->error('出团计划不存在');	
		
		$where = array();
		$where['op_id'] = $opid;
		
		$op         = M('op')->where($where)->find();
		$budget     = M('op_budget')->where($where)->find();
		$settlement = M('op_settlement')->where(array('op_id'=>$opid))->find();
		
		$matelist       = M()->table('__OP_MATERIAL__ as m')->field('c.*,m.*')->join('__OP_COST__ as c on m.id=c.link_id')->where(array('m.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>4))->order('m.id')->select();
		foreach($matelist as $k=>$v){
			//获取物资库存
			$stock = M('material')->where(array('material'=>$v['material']))->find();	
			$matelist[$k]['stock']  = $stock['stock'] ?$stock['stock']:0;
			$matelist[$k]['stages'] = $stock['stages']?$stock['stages']:0;
			$matelist[$k]['lastcost'] = $stock ? $stock['price'] : '0.00';	
			
			$yichuku = $v['amount']-$v['outsum'];
			if($matelist[$k]['stock']<$yichuku){
				$matelist[$k]['status'] = $v['purchasesum'] ? '<span class="yellow">等待入库</span>' : '<span class="red">申请采购</span>';	
			}else{
				$matelist[$k]['status'] = $v['outsum'] ? '<span class="blue">完成出库</span>' : '<span class="green">申请出库</span>';		
			}
			
		}
		
		$where = array();
		$where['req_type'] = P::REQ_TYPE_BUDGET;
		$where['req_id']   = $budget['id'];
		$audit = M('audit_log')->where($where)->find();
		if($audit['dst_status']==0){
			$show = '未审批';
			$show_user = '未审批';
			$show_time = '等待审批';
		}else if($audit['dst_status']==1){
			$show = '<span class="green">已通过</span>';
			$show_user = $audit['audit_uname'];
			$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
		}else if($audit['dst_status']==2){
			$show = '<span class="red">未通过</span>';
			$show_user = $audit['audit_uname'];
			$show_reason = $audit['audit_reason'];
			$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
		}
		$op['showstatus'] = $show;
		$op['show_user']  = $show_user;
		$op['show_time']  = $show_time;
		$op['show_reason']  = $show_reason;

        $member               = M('op_member')->where(array('op_id'=>$opid))->order('id')->select();
        $this->member         = $member;
		$this->op             = $op;
		$this->matelist       = $matelist;
		$this->budget         = $budget;
		$this->settlement     = $settlement;
		$this->business_depts = C('BUSINESS_DEPT');
		$this->subject_fields = C('SUBJECT_FIELD');
		$this->ages           = C('AGE_LIST');
		$this->kinds          =  M('project_kind')->getField('id,name', true);
		$this->display('app_materials');
	}
	
	
	// @@@NODE-3###out_materials###申请物资###
	public  function  out_materials(){
		$opid = I('opid');
		
		//获取项目信息
		$where = array();
		$where['op_id'] = $opid;
		$op         = M('op')->where($where)->find();
		$budget     = M('op_budget')->where($where)->find();
		$roledet    = M('role')->where(array('role_name'=>$op['op_create_user']))->find();
		
		$ckinfo = array();
		$cginfo = array();
		
		
		//物资列表
		$matelist       = M()->table('__OP_MATERIAL__ as m')->field('c.*,m.*')->join('__OP_COST__ as c on m.id=c.link_id')->where(array('m.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>4))->order('m.id')->select();
		foreach($matelist as $k=>$v){
			//获取物资库存
			$wz = M('material')->where(array('material'=>$v['material']))->find();	
			$stock    = $wz['stock']?$wz['stock']:0;
			$lastcost = $wz['price'] ? $wz['price'] : 0;	
			$wz_id = $wz['id'];
			
			
			/*处理出库*/
			$outrand = M('material_out')->where(array('op_id'=>$opid,'material'=>$v['material'],'audit_status'=>array('neq',2)))->sum('amount');
			$outsum = $v['amount']-$outrand;
			$daichuku = $outrand - $v['outsum'];
			$stock = $stock-$daichuku;
			if($outsum>0 && $stock>0){
				//申请出库
				$ckinfo[$k]['op_id']           = $opid;
				$ckinfo[$k]['material_id']     = $wz_id;
				$ckinfo[$k]['material']        = $v['material'];
				$ckinfo[$k]['unit_price']      = $lastcost;
				$ckinfo[$k]['order_id']        = $op['group_id'];
				$ckinfo[$k]['receive_liable']  = cookie('nickname');
				$ckinfo[$k]['out_time']        = time();
				if($stock>=$outsum){
					//如果库存充足，直接出库
					$ckinfo[$k]['amount']          = $outsum;
					$ckinfo[$k]['total']           = $outsum*$lastcost;	
				}else{
					//如果库存不够，申请部分出库
					$ckinfo[$k]['amount']          = $stock;
					$ckinfo[$k]['total']           = $stock*$lastcost;		
				}
			}
			
			
			/*处理采购*/
			$gourand = M('material_purchase')->where(array('op_id'=>$opid,'material'=>$v['material'],'audit_status'=>array('neq',2)))->sum('amount');
			$gousum = $v['amount']-$gourand-$outrand;
			$caigou = $gousum-$stock;
			if($stock < $gousum && $caigou>0){
				//申请采购
				$cginfo[$k]['op_id']           = $opid;
				$cginfo[$k]['material_id']     = $wz_id;
				$cginfo[$k]['material']        = $v['material'];
				$cginfo[$k]['unit_price']      = $v['cost'];
				$cginfo[$k]['order_id']        = $op['group_id'];
				$cginfo[$k]['department']      = $roledet['id'];
				$cginfo[$k]['create_time']     = time();
				$cginfo[$k]['amount']          = $caigou;
				$cginfo[$k]['total']           = $caigou*$v['cost'];	
				$cginfo[$k]['op_user']         = $op['create_user_name'];	
			}
			
		}
		
		
		$opnum = 0;
		if(count($ckinfo)){
			//申请出库
			$ck = array();
			$ck['type']            = 0;
			$ck['order_id']        = $op['group_id'];
			$ck['receive_liable']  = cookie('nickname');
			$ck['op_id']           = $opid;
			$ck['name']            = $op['project'];
			$ck['out_time']        = time();
			$ck['app_user']        = cookie('nickname');
			$batch_id = M('material_out_batch')->add($ck);
			if($batch_id){
				$this->request_audit(P::REQ_TYPE_GOODS_OUT, $batch_id);
				foreach($ckinfo as $v){
					$info = array();
					$info = $v;
					$info['batch_id'] = $batch_id;
					M('material_out')->add($info);
				}	
				$opnum++;
			}
		}
		
		if(count($cginfo)){
			
			//采购备注
			$proid = M()->table('__PRODUCT_LINE_TPL__ as t')->join('__OP__ as o on o.line_id = t.line_id')->where(array('o.op_id'=>$opid,'t.type'=>1))->GetField('pro_id',true);
			
			//申请采购
			$cg = array();
			$cg['op_id']           = $opid;
			$cg['order_id']        = $op['group_id'];
			$cg['name']            = $op['project'];
			$cg['department']      = $roledet['id'];
			$cg['create_time']     = time();
			$cg['app_user']        = cookie('nickname');
			$cg['op_user']         = $op['create_user_name'];
			$batch_id = M('material_purchase_batch')->add($cg);
			if($batch_id){
				$this->request_audit(P::REQ_TYPE_GOODS_PURCHASE, $batch_id);
				foreach($cginfo as $v){
					$info = array();
					$info = $v;
					$info['batch_id'] = $batch_id;
					//采购信息
					$wzcg = M('product_material')->where(array('material'=>$info['material'],'product_id'=>array('in',implode(',',$proid))))->find();
					if($wzcg){
						$info['unit_price'] = $wzcg['unitprice'];
						$info['total'] = $wzcg['unitprice']*$v['amount'];
						$info['remarks'] = $wzcg['channel'];
					}
					M('material_purchase')->add($info);
				}	
				$opnum++;
			}
		}
		
		if($opnum){
			M('op')->data(array('app_material_time'=>time()))->where(array('op_id'=>$opid))->save();	
		}
		
		echo $opnum;
		
		
	}
	
	
	
	// @@@NODE-3###revert_materials###归还物资###
	public  function  revert_materials(){
		$opid = I('opid');
		
		$matelist       = M()->table('__OP_MATERIAL__ as m')->field('c.*,m.*')->join('__OP_COST__ as c on m.id=c.link_id')->where(array('m.op_id'=>$opid,'c.op_id'=>$opid,'c.cost_type'=>4))->order('m.id')->select();
		foreach($matelist as $k=>$v){
			//获取物资库存
			$stock = M('material')->where(array('material'=>$v['material']))->find();	
			$matelist[$k]['stock']  = $stock['stock'] ?$stock['stock']:0;
			$matelist[$k]['stages'] = $stock['stages']?$stock['stages']:0;
			$matelist[$k]['lastcost'] = $stock ? $stock['price'] : 0;	
		}
		
		$this->matelist       = $matelist;
		$this->kinds          =  M('project_kind')->getField('id,name', true);
		$this->display('revert_materials');
	}
	
	
	// @@@NODE-3###select_material###调度物资###
	public  function  select_material(){
		//物料关键字
		$key =  M('material')->field('id,pinyin,material')->where(array('asset'=>0))->select();
		if($key) $this->keywords =  json_encode($key);
		$this->material = M('material')->select();
		$this->display('select_material');
	}
	
	
	
	public function public_checkname_ajax(){
		$group_id = I('gid',0);
		
		//判断会员是否存在
		$db = M('op');
		if($db->where(array('group_id'=>$group_id))->find()) {
			exit('0');
		}else {
			exit('1');
		}	
	}
	
	
	
	// @@@NODE-3###delpro###删除项目###
    public function delpro(){
        $this->title('删除项目');
		
        $id = I('id', -1);
		
		$op = M('op')->find($id);
		if($op &&( cookie('roleid')==10 || cookie('roleid')==1)){
			$opid = $op['op_id'];
			//删除项目相关信息
			M('op_auth')->where(array('op_id'=>$opid))->delete();
			M('op_budget')->where(array('op_id'=>$opid))->delete();
			M('op_cost')->where(array('op_id'=>$opid))->delete();
			M('op_costacc')->where(array('op_id'=>$opid))->delete();
			M('op_guide')->where(array('op_id'=>$opid))->delete();
			M('op_line_days')->where(array('op_id'=>$opid))->delete();
			M('op_material')->where(array('op_id'=>$opid))->delete();
			M('op_partake')->where(array('op_id'=>$opid))->delete();
			M('op_pretium')->where(array('op_id'=>$opid))->delete();
			M('op_record')->where(array('op_id'=>$opid))->delete();
			M('op_settlement')->where(array('op_id'=>$opid))->delete();
			M('op_supplier')->where(array('op_id'=>$opid))->delete();
			M('order')->where(array('op_id'=>$opid))->delete();
			
			//删除主项目
			M('op')->delete($id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！：' . $db->getError());	
		}
    }
	
	
	//排课
	public function course(){
		$op_id    = I('opid');
		$guide_id = I('id');
		
		//判断项目是否已结算
		$jiesuan = M('op_settlement')->where(array('op_id'=>$op_id))->find();
		
		$this->op_id     = $op_id;
		$this->guide_id  = $guide_id;
		$this->jiesuan   = $jiesuan['audit_status'] ? $jiesuan['audit_status'] : 0;
		$this->display('course');	
	}
 	
	//排课详情
	public function courselist(){
		
		$op_id    = I('get.opid');
		$guide_id = I('get.id');

		$rows = M('op_course')->where(array('op_id'=>$op_id,'guide_id'=>$guide_id))->select();
		$data = array();
		foreach($rows as $k =>$v){
			$data[$k]['id']  = $v['id'];
			$data[$k]['task'] = $v['userid'];
			$data[$k]['builddate'] = $v['coures_date'];
		}
		echo json_encode($data);
	}
	
	
	//排课详情
	public function addcourse(){
		
		$op_id    = I('op_id');
		$guide_id = I('guide_id');
		$date     = I('date');
		
		$info = array();
		$info['op_id']    = $op_id;
		$info['guide_id'] = $guide_id;
		$info['coures_date'] = $date;
		$info['userid'] = cookie('userid'); 
				
		$add = M('op_course')->add($info);
		if($add){
			echo $add;	
		}else{
			echo 0;	
		}
		
	}
	
	
	//删除课程
	public function delcourse(){
		$id = I('id');	
		$course = M('op_course')->find($id);
		//if($course && $course['userid'] == cookie('userid')){
			$del = M('op_course')->where(array('id'=>$id))->delete();
			if($del){
				echo 1;	
			}else{
				echo 0;	
			}
		//}else{
		//	echo 0;	
		//}
	}

	// @@@NODE-3###confirm###出团确认###
	public  function  confirm(){
		$opid = I('opid');
		if(!$opid) $this->error('项目不存在');	
		
		$where = array();
		$where['op_id'] = $opid;
		$op				= M('op')->where($where)->find();
		$confirm		= M('op_team_confirm')->where($where)->find();
        $upd_num        = $confirm['upd_num'];

		if(isset($_POST['dosubmit']) && $_POST['dosubmit']){

			$info	    = I('info');
            $data       = I('data');
            /*$tcs_time   = I('tcs_time');
            $in_day         = I('in_day');
            $tcs_begin_time = $in_day.' '.substr($tcs_time,0,8);
            $tcs_end_time   = $in_day.' '.substr($tcs_time,11,8);*/

			//判断团号是否可用
			$where = array();
			$where['group_id']	= $info['group_id'];
			$where['op_id']		= array('neq',$opid);
			$check				= M('op')->where($where)->find();
			if($check)  $this->error($info['group_id'].'团号已存在');	 

			$info['op_id']			= $opid; 
			$info['user_id']		= cookie('userid'); 
			$info['user_name']		= cookie('nickname'); 
			$info['dep_time']		= $info['dep_time'] ? strtotime($info['dep_time']) : 0;
			$info['ret_time']		= $info['ret_time'] ? strtotime($info['ret_time']) : 0;
            /*$info['tcs_begin_time'] = strtotime($tcs_begin_time);
            $info['tcs_end_time']   = strtotime($tcs_end_time);*/
			$info['confirm_time']	= time();
			//判断是否已经确认
			if($confirm){
                if($upd_num == 1){
                    $this->error('您已经修改过一次了,不能反复修改!');
                }else{
                    $info['upd_num']    = 1;    //用来判断修改次数
                    M('op_team_confirm')->data($info)->where(array('op_id'=>$opid))->save();
                }
			}else{
				M('op_team_confirm')->add($info);
			}

			$num = 0;
			$res = M('op_guide_price')->where(array('op_id'=>$opid))->delete();
            if ($res){
                $num++;
            }
            if ($data){
                foreach($data as $k=>$v){
                    $v['op_id'] = $opid;
                    M('op_guide_price')->add($v);
                    $num++;
                }
            }
            if ($num != 0){
                $record = array();
                $record['op_id']   = $opid;
                $record['optype']  = 4;
                $record['explain'] = '成团确认--确认专家辅导员资源需求';
                op_record($record);
            }
			
			//修正tcs项目中状态
			$infos = array();
			$infos['group_id']	    = $info['group_id'];
			$infos['status']		= 1;
            if($op['tcs_stu'] ==1){
                $infos['tcs_stu']    = 2;    //已确认需求(已成团)
            }
			M('op')->data($infos)->where(array('op_id'=>$opid))->save();

            //给教务专员发送系统新消息  81
            $jwzy_ids= M('account')->where(array('roleid'=>81,'status'=>0))->getField('id',true);
            foreach ($jwzy_ids as $v){
                $uid     = cookie('userid');
                $title   = '您有来自'.$op['create_user_name'].'的团号为['.$info['group_id'].']的团待安排专家辅导员!';
                $content = '项目编号:'.$op['op_id'].';团号:'.$info['group_id'].';请登录"辅导员/教师、专家管理系统完成相关操作(如其他同事已完成操作,请忽略)!"';
                $url     = 'http://tcs.kexueyou.com/op.php?m=Main&c=Task&a=detail&id='.$op['id'];
                $user    = '['.$v.']';
                send_msg($uid,$title,$content,$url,$user,'');
            }
			
			$this->success('保存成功！');
		
		}else{
            if ($confirm){
                $in_day          = substr(date('Y-m-d H:i:s',$confirm['tcs_begin_time']),0,10);
                $tcs_begin_time  = substr(date('Y-m-d H:i:s',$confirm['tcs_begin_time']),11,8);
                $tcs_end_time    = substr(date('Y-m-d H:i:s',$confirm['tcs_end_time']),11,8);
                $confirm['tcs_time'] = $tcs_begin_time.' - '.$tcs_end_time;
                $confirm['in_day']   = $in_day;
            }

			$this->kinds	= M('project_kind')->getField('id,name', true);
			$this->op		= $op;
            //辅导员/教师、专家
            $this->guide_price  = M('op_guide_price')->where(array('op_id'=>$opid))->select();
            $this->guide_kind   = M('guidekind')->getField('id,name',true);
            //获取职能类型
            $priceKind          = M()->table('__GUIDE_PRICEKIND__ as gpk')->field('gpk.id,gpk.name')->join('left join __OP__ as op on gpk.pk_id = op.kind')->where(array("op.op_id"=>$opid))->select();
            $this->price_kind   = $priceKind;
            $this->fields       = C('GUI_FIELDS');

            $jiesuan            = M('op_costacc')->where(array('op_id'=>$opid,'status'=>2))->select();
            $this->jiesuan      = $jiesuan;

            //人员列表
            $stu_list       = M('op_member')->where(array('op_id'=>$opid))->select();
            $member         = M('op_member')->where(array('op_id'=>$opid))->order('id')->select();
            $this->member   = $member;
            $this->stu_list = $stu_list;
			$this->confirm 	= $confirm;
            $this->upd_num  = $confirm['upd_num'];
			$this->display('confirm');
		}
	}
	
	
	
	// @@@NODE-3###relpricelist###项目比价记录###
    public function relpricelist(){
        $this->title('项目比价记录');
		
		$db		= M('rel_price');
		$kinds	= C('REL_TYPE');
		
		$title	= I('title');		//项目名称
		$opid	= I('opid');			//项目编号
		$op		= I('op');			//计调
		$type 	= I('type');
		
		$where = array();
		
		if($title)			$where['business_name']			= array('like','%'.$title.'%');
		if($op)				$where['op_user_name']			= array('like','%'.$op.'%');
		if($opid)			$where['op_id']					= $opid;
		if($type)			$where['type']					= $type;
		
		//分页
		$pagecount		= $db->where($where)->count();
		$page			= new Page($pagecount, P::PAGE_SIZE);
		$this->pages	= $pagecount>P::PAGE_SIZE ? $page->show():'';
        
       
		$lists = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($this->orders('create_time'))->select();
		foreach($lists as $k=>$v){
			$lists[$k]['kinds'] 			= $kinds[$v['type']];	
			$lists[$k]['create_time'] 	= date('Y-m-d H:i:s',$v['create_time']);
		}
		
		
		$this->lists   		=  $lists;  
		$this->kinds 		= C('REL_TYPE');
		$this->opid 			= $opid;
		$this->type 			= $type;
		$this->display('relpricelist');
    }
	
	
	// @@@NODE-3###confirm###项目比价###
	public  function  relprice(){
		$opid 			= I('opid');
		$relid			= I('relid');
		$type 			= I('type');
		$op				= M('op')->where(array('op_id'=>$opid))->find();
	
		
		
		if(isset($_POST['dosubmint']) && $_POST['dosubmint']){
			
			$info		= I('info');
			$com		= I('com');
			$reid 		= I('reid');
			
			$info['op_user_id']		= cookie('userid');
			$info['op_user_name']	= cookie('name');
			
			//保存主表
			if($reid){
				M('rel_price')->where(array('id'=>$reid))->data($info)->save();	
			}else{
				$info['create_time']		= time();
				$reid = M('rel_price')->add($info);	
			}
			
			
			
			$coms = array();
			$list = array();
			
			foreach($com as $k=>$v){
				//保存比价单位
				$cominfo = array();
				$cominfo['rel_id']			= $reid;
				$cominfo['op_id']			= $info['op_id'];
				$cominfo['company']			= $v['company'];
				$cominfo['contacts']			= $v['contacts'];
				$cominfo['contacts_tel']		= $v['contacts_tel'];
				$cominfo['contacts_email']	= $v['contacts_email'];
				$cominfo['checkout']			= 0;//isqual($v['company']);
				if($v['comid']){
					M('rel_price_com')->where(array('id'=>$v['comid']))->data($cominfo)->save();	
					$comid 		= $v['comid'];
					$coms[] 	= $v['comid'];
				}else{
					$comid 		= M('rel_price_com')->add($cominfo);	
					$coms[] 	= $comid;
				}
				
				//保存比价项目		
				foreach($v['info'] as $kk=>$vv){
					$termlist = array();
					$termlist['op_id'] 			= $info['op_id'];
					$termlist['rel_id'] 			= $reid;	
					$termlist['rel_com_id']		= $comid;	
					$termlist['term'] 			= $vv['term'];	
					$termlist['term_standard']	= $vv['term_standard'];	
					$termlist['price'] 			= $vv['price'];	
					if($vv['id']){
						M('rel_price_list')->where(array('id'=>$vv['id']))->data($termlist)->save();	
						$list[]	= $vv['id'];
					}else{
						$list[]	= M('rel_price_list')->add($termlist);
					}
					
				}	
					
			}
			
			//清除已删除单位和项目
			$where = array();
			$where['rel_id'] 	= $reid;
			$where['id'] 		= array('not in',implode(',',$coms));
			M('rel_price_com')->where($where)->delete();
			
			$where = array();
			$where['rel_id'] 	= $reid;
			$where['id'] 		= array('not in',implode(',',$list));
			M('rel_price_list')->where($where)->delete();
			
			$this->success('保存成功！',I('referer',''));
		
		}else{
			
			if($relid){
				$rel = M('rel_price')->find($relid);
				$com = M('rel_price_com')->where(array('rel_id'=>$relid))->select();
				foreach($com as $k=>$v){
					$com[$k]['info'] = M('rel_price_list')->where(array('rel_id'=>$relid,'rel_com_id'=>$v['id']))->select();
				}
			}
			
			$this->kinds 		= C('REL_TYPE');
			$this->b_name		= $rel['business_name'] ? $rel['business_name'] : $op['project'];
			$this->op_id		= $rel['op_id'] ? $rel['op_id'] : $opid;
			$this->vtype 		= $rel['type'] ? $rel['type'] : $type;
			$this->op 			= $op;
			$this->rel			= $rel;
			$this->com 			= $com;
			$this->display('relprice');
		}
	}
	
	

	// @@@NODE-3###delrel###删除项目比价###
    public function delrel(){
		
		$relid	= I('relid');
		M('rel_price')->where(array('id'=>$relid))->delete();
		M('rel_price_com')->where(array('rel_id'=>$relid))->delete();
		M('rel_price_list')->where(array('rel_id'=>$relid))->delete();
		
		$this->success('删除成功！');
		
    }
	
	
	
	// @@@NODE-3###evaluate###项目评价###
    public function evaluate(){
		
		$opid = I('opid');
		if(!$opid) $this->error('项目不存在');	
		
		$where = array();
		$where['op_id'] = $opid;
		$op				= M('op')->where($where)->find();
		
		
		if(isset($_POST['dosubmit']) && $_POST['dosubmit']){
			
			$info	= I('info');
			
			if(!$info[1]['evaluate']) 	$this->error('产品评价内容不能为空！');	
			if(!$info[2]['evaluate']) 	$this->error('计调评价内容不能为空！');	
			if(!$info[3]['evaluate']) 	$this->error('资源评价内容不能为空！');	
			if(!$info[4]['evaluate']) 	$this->error('物资评价内容不能为空！');	
			
			//保存
			foreach($info as $k=>$v){
				
				$data = array();
				$data['op_id']			= $v['op_id'];
				$data['eval_type']		= $v['eval_type'];
				$data['liable_uid']		= $v['liable_uid'];
				$data['liable_uname']	= $v['liable_uname'];
				$data['score']			= $v['score'];
				$data['evaluate']		= $v['evaluate'];
				$data['eval_uid']		= cookie('userid');
				$data['eval_uname']		= cookie('name');
				$data['eval_time']		= time();
				
				$eval = M('op_eval')->where(array('op_id'=>$v['op_id'],'eval_type'=>$v['eval_type']))->find();
				if($eval){
					M('op_eval')->data($data)->where(array('op_id'=>$v['op_id'],'eval_type'=>$v['eval_type']))->save();
				}else{
					M('op_eval')->add($data);
				}	
			}
			
			
			$this->success('保存成功！');
		
		}else{
			
			$this->kinds	= M('project_kind')->getField('id,name', true);
			$this->op		= $op;
            $member         = M('op_member')->where(array('op_id'=>$opid))->order('id')->select();
            $this->member   = $member;
			
			$auth = M('op_auth')->where(array('op_id'=>$opid))->find();
			//获取产品负责人信息
			
			$cp['uid'] 		= '';
			$cp['uname'] 	= '';
			
			//获取计调负责人信息
			$jd['uid'] 		= $auth['line'];
			$jd['uname'] 	= username($auth['line']);
			
			//获取物资负责人信息
			$wz['uid'] 		= $auth['res'];
			$wz['uname'] 	= username($auth['res']);
			
			//获取资源负责人信息
			$zy['uid'] 		= $auth['material'];
			$zy['uname'] 	= username($auth['material']);
			
			$this->cp 		= $cp;
			$this->jd 		= $jd;
			$this->wz 		= $wz;
			$this->zy 		= $zy;
			
			
			$this->cpv 		= M('op_eval')->where(array('op_id'=>$opid,'eval_type'=>1))->find();
			$this->jdv 		= M('op_eval')->where(array('op_id'=>$opid,'eval_type'=>2))->find();
			$this->zyv 		= M('op_eval')->where(array('op_id'=>$opid,'eval_type'=>3))->find();
			$this->wzv 		= M('op_eval')->where(array('op_id'=>$opid,'eval_type'=>4))->find();
			
			$this->cps 		= $this->cpv ? $this->cpv['score'] : 100;
			$this->jds 		= $this->jdv ? $this->jdv['score'] : 100;
			$this->zys 		= $this->zyv ? $this->zyv['score'] : 100;
			$this->wzs 		= $this->wzv ? $this->wzv['score'] : 100;
			
			$this->confirm 	= $confirm; 
			$this->display('evaluate');
		}
		
    }
    
}