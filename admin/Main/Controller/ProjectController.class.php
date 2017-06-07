<?php
namespace Main\Controller;
use Sys\P;

ulib('Page');
use Sys\Page;


// @@@NODE-2###Project###项目管理###
class ProjectController extends BaseController {
    
    protected $_pagetitle_ = '项目管理';
    protected $_pagedesc_  = '录入、修改、审批、删除项目数据';
    
    
    // @@@NODE-3###delkind###删除项目分类###
    public function delkind(){
        $this->title('删除项目分类');
        
        $db = M('project_kind');
        $id = I('id', -1);
        
        $kinds = get_project_kinds();
        
        $where = "id in ($id";
        $flag = 99;
        for ($i = 0; $i < count($kinds); $i++ ) {
            if ($kinds[$i]['id'] == $id) {
                $flag = $kinds[$i]['level'];
                continue;
            }
            if ($kinds[$i]['level'] > $flag) {
                $where .= ',' . $kinds[$i]['id'];
            } else {
                break;
            }
        }
        $where .= ')';
        $iddel = $db->where($where)->delete();
        $this->success('删除成功！', U('Project/kind'));
    }
    
    
    // @@@NODE-3###addkind###添加修改项目分类###
    public function addkind() {
        $this->title('添加/修改项目分类');

        $db = M('project_kind');
        $pid  = I('pid', 0);
        
        $id = I('id',0);
        if ($pid <= 0) {
            $father = array();
            $father['level'] = 0;
            $father['id'] = 0;
            $father['name'] = '顶级分类';
        
        } else {
            $father = M('project_kind')->find($pid);
        }
        
        $this->father = $father;
        
        if(isset($_POST['dosubmit'])){
        
            $info = I('info','');
            	
            if(!$id){
                $isadd = $db->add($info);
                if($isadd) {
                    $this->success('添加成功！',U('Project/kind'));
                } else {
                    $this->error('添加失败：' . $db->getError());
                }
            }else{
                $isedit = $db->data($info)->where(array('id'=>$id))->save();
                if($isedit) {
                    $this->success('修改成功！',U('Project/kind'));
                } else {
                    $this->error('修改失败：' . $db->getError());
                }
            }
            	
        }else{
        
            if (!$id) {
                $this->row = false;
            } else {
                $this->row = $db->find($id);
                if (!$this->row) {
                    $this->error('无此数据！', U('Project/kind'));
                }
            }
            $this->display('addkind');
        }
    }
    
    
    // @@@NODE-3###kind###项目分类列表###
    public function kind() {
        $this->title('项目分类');
    
        $this->lists = get_project_kinds();
        $this->pages = '';
        $this->display('kind');
    }
	
    // @@@NODE-3###index###项目列表###
    public function index(){
        $this->title('项目列表');
		$db = M('project');
        $page = new Page($db->count(), P::PAGE_SIZE);
        $this->pages = $page->show();
		
		$this->lists = $db->where('1='.get_priv_condition('project'))->order($this->orders('id'))->limit($page->firstRow . ',' . $page->listRows)->select();
        //P($db->getLastSql());
        $this->status = array(
                P::AUDIT_STATUS_NOT_AUDIT => '待审批',
                P::AUDIT_STATUS_PASS      => '审批通过', 
                P::AUDIT_STATUS_NOT_PASS  => '审批未通过',
        );
		$this->display('index');
    }
    
    // @@@NODE-3###view###查看项目###
    public function view(){
        $this->title('查看项目');
		$id = I('id', -1);
		$row =  M('project')->find($id);
		
		if($row){
			
			$where = array();
			$where['req_type'] = P::REQ_TYPE_PROJECT_NEW;
			$where['req_id']   = $id;
			$audit = M('audit_log')->where($where)->find();
			if($audit['dst_status']==0){
				$show = '未审批';
				$show_user = '未审批';
				$show_time = '等待审批';
			}else if($audit['dst_status']==1){
				$show = '已通过';
				$show_user = $audit['audit_uname'];
				$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
			}else if($audit['dst_status']==2){
				$show = '未通过';
				$show_user = $audit['audit_uname'];
				$show_time = date('Y-m-d H:i:s',$audit['audit_time']);
			}
			$row['showstatus'] = $show;
			$row['show_user']  = $show_user;
			$row['show_time']  = $show_time;
			
		}else{
			$this->error('项目不存在' . $db->getError());	
		}
		
		$this->row = $row;
		$where = empty($row['pids'])? '0' :"id in (". $row['pids'].")";
		$this->pids = M('product')->where($where)->select();

        $this->display('view');
    }
	
    
    // @@@NODE-3###del###删除项目###
    public function del(){
        $this->title('删除项目');
		$db = M('project');
		$id = I('id', -1);
		$iddel = $db->delete($id);
		$this->success('删除成功！');	
    }
    
	
    // @@@NODE-3###add###添加项目###
    public function add() {
        $this->title('添加项目');
         if (isset($_POST['dosubmit'])) {
             $info = I('info');
             $info['pids'] = join(',', I('pids'));
             $id = I('id');
             if ($id) {
                 M('project')->where("id=$id")->data($info)->save();
                 $this->success('修改成功！', U('Project/index'));
             } else {
                 $info['input_time']  = time();
                 $info['input_user']  = session('userid');
                 $info['input_uname'] = session('name');
                 $info['status']      = P::AUDIT_STATUS_NOT_AUDIT;
                 $req_id = M('project')->add($info);
                 $this->request_audit(P::REQ_TYPE_PROJECT_NEW, $req_id);
                 $this->success('保存成功！', U('Project/index')); 
             }
         } else {
             $id = I('id');
             if ($id) {
                 $this->row = M('project')->find($id);
                 $where = empty($this->row['pids'])? '0' :"id in (". $this->row['pids'].")";
                 $this->pids = M('product')->where($where)->select();
             } else {
                 $this->row = false; 
                 $this->pids = false;
             }
             
             $this->kinds = get_project_kinds();
             $this->display('add');
         }
    }
    

    /*
    public function audit () {
        $this->title('项目审批');
        $db = M('project');
        $id = I('id', -1);
		if(isset($_POST['dosubmit'])){
			$info = I('info');
			if($info['status']){
				$info['audit_user']   = session('userid');
                $info['audit_uname']  = session('name');
                $info['audit_time']   = time();
			}
        	$iddel = $db->where("id=$id")->save($info);
			$this->success('审批成功！');
			
		}else{
			$this->id = $id;
			$this->display('audit');
		}
    }
     
    
    public function projectpriv(){
        $this->title('项目默认权限设置');
    
        $db = M('rights');
    
        $restable = 'project';
        $resid    = '0';
    
        if (isset($_POST['dosubmit'])) {
            $roles = I('roles');
            $info = $_POST['info'];
    
            $db->where("restable='$restable' and resid='$resid'")->delete();
            $alldata = array();
    
            foreach ($roles as $row) {
                $data = array();
                $data['input_user'] = session('userid');
                $data['input_uname'] = session('name');
                $data['input_time']  = time();
                $data['restable'] = $restable;
                $data['resid']    = $resid;
                $data['isdel']    = 0;
                $data['roleid']   = $row;
                $data['v']        = isset($info[$row][v]) ? $info[$row][v] : 0;
                $data['d']        = isset($info[$row][d]) ? $info[$row][d] : 0;
                $data['u']        = isset($info[$row][u]) ? $info[$row][u] : 0;
                $alldata[] = $data;
            }
    
            $db->addAll($alldata);
    
            $this->success('操作成功！');
    
        } else {
    
            $this->rights = M('rights')->where("restable='$restable' and resid='$resid'")
            ->getField('roleid,v,d,u', true);
    
            $this->roles = get_roles();
            $this->res   = $restable;
            $this->resid = $resid;
    
            $this->display('projectpriv');
        }
    
    }
    */
    
}