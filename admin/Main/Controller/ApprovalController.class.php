<?php
namespace Main\Controller;
use Think\Controller;
use Sys\P;
ulib('Page');
use Sys\Page;
class ApprovalController extends BaseController {

    //首页显示
    public function Approval_Index(){
        $count                  = M('approval_flie')->where('type=1')->count();
        $page                   = new Page($count,10);
        $pages                  = $page->show();
        $approval               = M('approval_flie')->where('type=1')->limit("$page->firstRow","$page->listRows")->order('createtime desc')->select();
        $this->file             = D('Approval')->approval_update_sql($approval);//循环更改文件数据
        $this->type             = session_userid();

        $this->pages            = $pages;
        $this->display();
    }

    //选择审批人
    public function Approval_Upload(){
        $sum                    = (int)trim(I('type'));
        $id                     = (int)trim(I('id'));
        $type                   = session_userid();
        if($sum==3 && $type==1){
            $type               = 3;// 特殊人员添加审批人和批准人状态
            $this->id           = $id;
        }
        $arr                    = explode(",",$_COOKIE['xuequ_approval']);
        for($i=0;$i<(count($arr)/4);$i++){
            for($k=0;$k<5;$k++){
                $array[$i][$k]  = $arr[$i*4+$k];
            }
        }
        $this->type             = $type;
        $this->personnel        = personnel();
        $this->cooki            = $array;
        $this->display();
    }

    //上传文件和审批人
    public function Approval_file(){
        $user_id                = $_POST['user_id'];
        $style                  = trim($_POST['style']);
        $approve_id             = trim($_POST['approve_id']);
        $approval               = D('Approval');
        if($style==3){
            $id                 = trim($_POST['id']);
        }
        $judge                  = $approval->approval_upload('approval_flie',$user_id,$style,$approve_id,$id);
        if($judge==1){
            $this->success('保存文档数据成功!');//最后一次错误
        }elseif($judge==2){
            $this->error('保存文档数据失败!');//最后一次错误
        }elseif($judge==3){
            $this->error('您只能更改自己提交的文件!');//最后一次错误
        }
    }

    //文件详情
    public function Approval_Update(){
        $id                     = trim(I('id'));//文件id
        $file[0]                = D('Approval')->approval_update($id);
        $this->id               = $id;
        $approval_file          = D('Approval')->approval_update_sql($file);//循环更改文件数据
//PRINT_r($approval_file);DIE;
        if($approval_file[0]['flie_update']['file_url']!==""){
            $this-> url         = $_SERVER['SERVER_NAME'].'/'.$approval_file[0]['flie_update']['file_url'];
        }else{
            $this-> url         = $_SERVER['SERVER_NAME'].'/'.$approval_file[0]['file']['file_url'];
        }
        $this->username         = user_table($_SESSION['userid']);
        $this->status           = session_userid();

        $this->assign('approval_file',$approval_file);
        $this->display();
    }

 }
