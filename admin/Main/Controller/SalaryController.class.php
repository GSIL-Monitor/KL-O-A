<?php
namespace Main\Controller;
use Think\Controller;
use Sys\P;
ulib('Page');
use Sys\Page;
class SalaryController extends BaseController {

    /**
     * @salaryindex 工资列表
     *
     */
     public function salaryindex(){

         $userid['status'] = 4;

         $userid['account_id']              = trim($_POST['id']);

         $userid['department']              = trim($_POST['employee_member']);
         $name                              = trim($_POST['name']);
         $userid['datetime']                = trim($_POST['month']);
         $userid                             = array_filter($userid);
         if($name){
             $account = M('account')->where('nickname='.$name)->find();
             if($account){
                 $userid['account_id'] = $account['id'];
             }else{
                 $this->error('您查看的数据不存在！');die;
             }
         }

//        if($_SESSION['userid']!==11 ||$_SESSION['userid']!==55 || $_SESSION['userid']!==77){//判断权限
//
//        }else{
//            $userid['account_id'] = $_SESSION['userid'];
//            if($where['account_id']!==$_SESSION['userid']){
//                $this->error('您只能查看自己的工资！');die;
//            }
//        }

         $count = M('salary_wages_month')->where($userid)->count();

         $page                                  = new Page($count,15);

         $pages                                 = $page->show();

         $info = M('salary_wages_month')->where($userid)->limit("$page->firstRow","$page->listRows")->order('datetime desc')->select();//工资生成数据
//         print_r($info);die;

         $this->assign('info',$info);
         $this->assign('page',$pages);
         $this->display();
    }


    /**
     * @salarydetails员工详情页
     * 参数id 用户id  datetime年月
     * sql_query 调用自封装函数
     * sql_query参数（1查2增3删4修,查询字段,表名,条件,1倒叙2正常顺序,1查一条0所有）
     */
    public function salarydetails(){

        if(!is_numeric(trim($_GET['id'])) || !is_numeric(trim($_GET['datetime']))){
            $this->error('您的数据有误!请重新选择！');die;
        }

//        if($_SESSION['userid']!==11 ||$_SESSION['userid']!==55 || $_SESSION['userid']!==77){//判断权限
//
//        }else{
//            $id['account_id'] = $_SESSION['userid'];

//
//        }
        $id['id']                               = trim($_GET['id']);
        $id['datetime']                         = trim($_GET['datetime']);
        $user_info1                             = M('salary_wages_month')->where($id)->find();
//        if(!$user_info1){
//            $this->error('您查找的数据有误!请重新选择！');die;
//        }
        $user_info['wages_month']               = $user_info1;
        $uid = $user_info1['account_id'];

        $user_info['account']                   = M('account')->where(array('id='.$user_info1['account_id']))->find();

        $user_info['salary']                    = M('salary')->where(array('id='.$user_info1['salary_id']))->find();

        $user_info['attendance']                = M('salary_attendance')->where(array('id='.$user_info1['attendance_id']))->find();

        $user_info['bonus']                     = M('salary_bonus')->where(array('id='.$user_info1['bonus_id']))->find();

        $user_info['income']                    = M('salary_income')->where(array('income_token='.$user_info1['income_token']))->select();

        $user_info['insurance']                 = M('salary_insurance')->where(array('id='.$user_info1['insurance_id']))->find();

        $user_info['subsidy']                   = M('salary_subsidy')->where(array('id='.$user_info1['subsidy_id']))->find();

        $user_info['withholding']               = M('salary_withholding')->where(array('token='.$user_info1['withholding_token']))->select();
        $que['a.nickname']                      = $user_info1['nickname'];
        $que['p.tab_user_id']                   = $user_info1['account_id'];
        $que['p.month']                         = $id['datetime'];

        $user_info['fen']                       = $this->query_score($que);
        $user_info['kpi']                       = $this->salary_kpi_month($user_info1['account_id'],$id['datetime']);
        $this->assign('info',$user_info);
        $this->display();
    }

    /**
     * salary_kpi_month 季度
     * kpi 目标任务 完成 提成
     */
    private function salary_kpi_month($where,$datetime){

        //kpi 目标任务 完成 提成
        $month                      = (int)substr($datetime,4);
        $year                       = (int)substr($datetime,0,4);
        $query['user_id']           = $where;
        if($month<10){
            $year                   = $year.'0';
        }
       if($month == 3 || $month == 6 || $month == 9 ||$month == 12){
           $count                   = 0;
           $sum                     = 0;
           $i                       = $month-3;
           for($i;$i<$month;$month--){
               $query['month']      = $year.$month;
               $kpi                 = M('kpi')->where($query)->find();

               if($kpi){
                   $lists           = M('kpi_more')->where(array('kpi_id'=>$kpi['id']))->find();
                   if($lists){
                       if($lists['automatic'] == 0){
//                           $this->error('KPI暂未锁定!请锁定后查看信息！');
                           return 0;
                       }

                   }else{
                       $this->error('您的数据有误!请重新选择！');die;
                   }
               }else{
                   return 0;
               }
               $count               += $lists['target'];//季度目标
               $sum                 += $lists['complete'];//季度完成
           }
           $number = $sum/$count;//项目季度百分比
           if($number <= 1){
              $Total                = $sum*0.05;//不超过100%
           }
          if(1<$number && $number <=1.5){
               $Total               = $sum*(($number-1)*0.2+0.05);//超过100% 不到150%
           }
           if(1.5 < $number){
               $Total               = $sum*(($number-1.5)*0.25+(1.5-1)*0.2+0.05);//超过150%
           }
           $content['target']       = $count;
           $content['complete']     = $sum;
           $content['total']        = ((int)($Total*100))/100;//保留两位小数
        }
        return $content;
    }

    /**
     * sql_query
     * $que['p.tab_user_id'] 用户id
     * $que['a.nickname'] 用户昵称
     *$que['p.month'] 查询年月
     */
    private function query_score($que){
        $lists 			                    = M()->table('__PDCA__ as p')->field('p.*,a.nickname')->join('__ACCOUNT__ as a on a.id = p.tab_user_id')->where($que)->select();
        foreach($lists as $k=>$v){

            $sum_total_score                = 0;

            $yu                             = $v['status'] !=5 ? 0 : $v['total_score']-100;

            //计算PDCA加减分
            $sum_total_score                += $yu;

            //品质检查加减分
            $sum_total_score                += $v['total_qa_score'];

            //整理品质检查加减分
            $lists[$k]['total_score_show']  = $v['status']!=5 ? '<font color="#ff9900">未完成评分</font>' : show_score($yu);

            //整理品质检查加减分
            $lists[$k]['show_qa_score']     =  show_score($v['total_qa_score']);

            //获取KPI数据
            $kpi                            = M('kpi')->where(array('month'=>$v['month'],'user_id'=>$v['tab_user_id']))->find();
            if($kpi && $kpi['month']>=201803){
                $kpiscore                   =  $kpi['score']-100;
            }else{
                $kpiscore                   =  0;
            }

            //KPI加减分
            $sum_total_score                += $kpiscore;

            //KPI
            $lists[$k]['total_kpi_score']   = show_score($kpiscore);

            //合计
            $lists[$k]['sum_total_score']   =  show_score($sum_total_score);

        }
        return $lists;

    }

    /**
     * calculation ['basic']基本工资
     * ['achievements']绩效
     * ['grant'] 应发工资
     */
//    private function calculation ($user_info){
//        $info['basic']          = $user_info['salary']['standard_salary']*$user_info['salary']['basic_salary']/10;//基本工资
//        $info['achievements']   = $user_info['salary']['standard_salary']*$user_info['salary']['performance_salary']/10;//绩效
//        $info['grant']          = $user_info['salary']['standard_salary']*$user_info['salary']['basic_salary']/10-$user_info['attendance']['withdrawing'];//应发工资
//        return $info;
//
//    }

    /**
     *query_wages 查询工资
     */
//    private function query_wages($where,$account_id){
//        $wages_month                    = sql_query(1,'*','oa_salary_wages_month',$account_id,1,1);//工资关联是否生成
//        if($wages_month){//判断生成  判断是否有权限
//
//            $account['id']              = $wages_month[0]['account_id'];
//            $department['id']           = $wages_month[0]['department_id'];
//            $posts['id']                = $wages_month[0]['posts_id'];
//            $salary['id']               = $wages_month[0]['salary_id'];
//            $attendance['id']           = $wages_month[0]['attendance_id'];
//            $bonus['id']                = $wages_month[0]['bonus_id'];
//            $income['income_token']     = $wages_month[0]['income_token'];
//            $insurance['id']            = $wages_month[0]['insurance_id'];
//            $subsidy['id']              = $wages_month[0]['subsidy_id'];
//            $withholding['token']       = $wages_month[0]['withholding_token'];
//
//            $user_info['month']         = $wages_month;
//            $user_info['account']       = sql_query(1,'*','oa_account',$account,2,1);//查询用户表
//            $user_info['department']    = sql_query(1,'*','oa_salary_department',$department,1,1);//查询部门
//            $user_info['posts']         = sql_query(1,'*','oa_posts',$posts,1,1);//查询岗位
//
//            $user_info['salary']        = sql_query(1,'*','oa_salary',$salary,1,1);//岗位薪酬
//            $user_info['attendance']    = sql_query(1,'*','oa_salary_attendance',$attendance,1,1);//员工考核
//            $user_info['bonus']         = sql_query(1,'*','oa_salary_bonus',$bonus,1,1);//提成/奖金/年终奖
//            $user_info['income'][]      = sql_query(1,'*','oa_salary_income',$income,1,0);//其他收入
//            $user_info['insurance']     = sql_query(1,'*','oa_salary_insurance',$insurance,1,1);//五险一金表
//            $user_info['subsidy']       = sql_query(1,'*','oa_salary_subsidy',$subsidy,1,0);//补贴
//            $user_info['withholding'][] = sql_query(1,'*','oa_salary_withholding',$withholding,1,0);//代扣代缴
//
////            $type['type']               = 2;//状态成功 前台判断
//
//        }else{//可以加判断是否是当前用户
//            $this->error('您的数据有误!请重新选择!');die;
//            unset($account_id['datetime']);
//            $user_info['account']       = sql_query(1,'*','oa_account',$where,2,1);//查询用户表
//            $department_r['id']         = $user_info['account'][0]['departmentid'];//部门
//            $user_info['department']    = sql_query(1,'*','oa_salary_department',$department_r,1,1);//查询部门
//            $posts_r['id']              = $user_info['account'][0]['postid'];
//            $user_info['posts']         = sql_query(1,'*','oa_posts',$posts_r,1,1);//查询岗位
//
//            $user_info['salary']        = sql_query(1,'*','oa_salary',$account_id,1,1);//岗位薪酬
//            $user_info['attendance']    = sql_query(1,'*','oa_salary_attendance',$account_id,1,1);//员工考核
//            $user_info['bonus']         = sql_query(1,'*','oa_salary_bonus',$account_id,1,1);//提成/奖金/年终奖
//            $user_info['income'][]      = sql_query(1,'*','oa_salary_income',$account_id,1,0);//其他收入
//            $user_info['insurance']     = sql_query(1,'*','oa_salary_insurance',$account_id,1,1);//五险一金表
//            $user_info['subsidy']       = sql_query(1,'*','oa_salary_subsidy',$account_id,1,0);//补贴
//            $user_info['withholding'][] = sql_query(1,'*','oa_salary_withholding',$account_id,1,0);//代扣代缴
//            $type['type']               = 1;//状态失败 前台判断
//        }
//        $content[0]                     = $user_info;
//
//        return $content;
//    }


    /**
     * @salary_attendance 考勤列表
     * grant_time  年月份搜索
     */
    public function salary_attendance(){

        if($_POST['grant_time']){//年月搜索
            $this->error('时间查询暂时未开通!请使用其它查询！', U('Salary/salary_attendance'));die;
        }
        if(IS_POST){//搜索列表结果
            $where['A.id']                  = trim($_POST['id']);//用户id
            $where['A.employee_member']     = trim($_POST['employee_member']);//编码
            $where['A.nickname']            = trim($_POST['nickname']);//昵称
            $where['A.status'] = 0;
           //$where['grant_time']           = trim($_POST['grant_time']);
            $where                          = array_filter($where);
            $count                          = M()->table('oa_account as A')->join('oa_salary_attendance as B on A.id=B.account_id')->where($where)->count();
            $page                           = new Page($count,12);
            $pages                          = $page->show();
            $account_r                      = M()->table('oa_account as A')->join('oa_salary_attendance as B on A.id=B.account_id')->where($where)->field('A.id as aid,A.employee_member,A.nickname,B.late1,B.late2,B.leave_absence,B.sick_leave,B.absenteeism,B.withdrawing')->limit("$page->firstRow","$page->listRows")->order('B.id desc')->select();

        }else{//默认列表结果
            $count                          = M()->table('oa_account as A')->join('oa_salary_attendance as B on A.id=B.account_id')->count();
            $page                           = new Page($count,12);
            $pages                          = $page->show();
            $account_r                      = M()->table('oa_account as A')->join('oa_salary_attendance as B on A.id=B.account_id')->field('A.id as aid,A.employee_member,A.nickname,B.late1,B.late2,B.leave_absence,B.sick_leave,B.absenteeism,B.withdrawing')->limit("$page->firstRow","$page->listRows")->order('B.id desc')->select();

        }
        $this->assign('list',$account_r);
        $this->assign('page',$pages);
        $this->display();
    }

    /**
     * salary_edtior 考勤详情 修改/编辑
     * salary_attendance 考勤
     */
    public function salary_edtior(){
        if(IS_POST){
            $info               = trim($_POST['info']);
            $where['id']        = trim($_POST['id']);
            $data               = array_filter($info);
            $attend_r           = M('salary_attendance')->where($where)->save($data);
//            echo M()->getLastSql();
            if(!$attend_r){
                $this->error('您的数据编辑失败!请重新编辑！', U('Salary/salary_attendance'));die;
            }else{
                salary_info(12,7);//操作记录 编辑
                $this->success('编辑数据成功！', U('Salary/salary_attendance'));die;
            }
        }elseif(IS_GET){
            $sid['salary_id']   = trim($_GET['sid']);//薪资id
            $sid['id']          = trim($_GET['tid']);//考核id
            $attend_r           = M('salary_attendance')->where($sid)->find();
            if(!$attend_r){
                $this->error('您的数据有误!请重新选择！', U('Salary/salary_attendance'));die;
            }
        }
        $this->assign('list',$attend_r);
        $this->display();
    }




    /**
     * salary_add_attendance 考勤数据录入搜索结果与操作记录
     * optype=12 考勤记录
     */
    public function salary_add_attendance(){
            $where['A.id']                              = trim($_POST['id']);
            $where['A.employee_member']                 = trim($_POST['employee_member']);
            $where['A.nickname']                        = trim($_POST['nickname']);
            $where['D.department']                      = trim($_POST['departmen']);
            $posts['post_name']                         = trim($_POST['posts']);
            $all = $_POST['all'];
            if($posts['post_name'] !==""){
                $postid                                 = M('posts')->where($posts)->find();
                $where['postid']                        = $postid['id'];
            }
            $where                                      = array_filter($where);

            if($all == '所有'){

                $count                                  = $this->salary_count(1,$where);
                $page                                   = new Page($count,16);
                $pages                                  = $page->show();

                $account_r                              = M()->table('oa_account as A')->join('oa_posts as P on A.postid=P.id')->join('oa_salary_department as D on D.id=A.departmentid')->field('A.id as aid,A.departmentid,A.employee_member,A.nickname,A.entry_time,D.department,P.post_name')->limit("$page->firstRow","$page->listRows")->select();

            }else{

                $count                                  = $this->salary_count(1,$where);
                $page                                   = new Page($count,16);
                $pages                                  = $page->show();

                $account_r                              = M()->table('oa_account as A')->join('oa_posts as P on A.postid=P.id')->join('oa_salary_department as D on D.id=A.departmentid')->field('A.id as aid,A.employee_member,A.departmentid,A.employee_member,A.nickname,A.entry_time,D.department,P.post_name')->where($where)->limit("$page->firstRow","$page->listRows")->select();

            }

            foreach($account_r as $key => $val){
                $aid['account_id']                      = $account_r[$key]['aid'];
                $account_r[$key]['salary_attendance']   = M('salary_attendance')->where($aid)->order('id desc')->find();
                $account_r[$key]['salary']              = M('salary')->field('id as salary_id,standard_salary')->where($aid)->order('id desc')->find();
            }
            if(!$account_r || $account_r == ""){$this->error('请添加员工编码或者员工部门！', U('Rbac/index'));die;}
        $this->assign('page',$pages);//数据分页
        $this->assign('list',$account_r);
        $this->display();
    }

    /**
     * salary_query 薪酬数据录入搜索结果 查询数据 操作记录
     * oa_post 岗位
     * department_name 部门
     */
    public function salary_query(){

            $type = trim(I('typeval'));
            $where['A.id']                                  = trim(I('id'));
            $where['A.employee_member']                     = trim(I('employee_member'));
            $where['A.nickname']                            = trim(I('nickname'));
            $where['D.department']                          = trim(I('departmen'));
            $posts['post_name']                             = trim(I('posts'));
            $all = trim(I('all'));

            if($posts['post_name'] !==""){

                $postid                                     = M('posts')->where($posts)->find();
                $where['postid']                            = $postid['id'];
            }
            $where = array_filter($where);

            if(count($where) !== 0 || $all !== ""){

                if($all == '所有'){
                    $count                                  = $this->salary_count(1,$where);
                    $page                                   = new Page($count,4);
                    $pages                                  = $page->show();

                    $account_r                              = M()->table('oa_account as A')->join('oa_posts as P on A.postid=P.id')->join('oa_salary_department as D on D.id=A.departmentid')->field('A.id as aid,A.departmentid,A.employee_member,A.nickname,A.entry_time,A.archives,D.department,P.post_name')->limit("$page->firstRow","$page->listRows")->select();

                }
                if(count($where) !== 0){

                    $count                                  = $this->salary_count(2,$where);
                    $page                                   = new Page($count,4);
                    $pages                                  = $page->show();

                    $account_r                              = M()->table('oa_account as A')->join('oa_posts as P on A.postid=P.id')->join('oa_salary_department as D on D.id=A.departmentid')->field('A.id as aid,A.employee_member,A.departmentid,A.employee_member,A.nickname,A.entry_time,A.archives,D.department,P.post_name')->where($where)->limit("$page->firstRow","$page->listRows")->select();

                }
                foreach($account_r as $key => $val){

                    $aid['account_id']                      = $account_r[$key]['aid'];

                    $salary = M('salary')->where($aid)->order('id desc')->find();//岗位薪资

                    $account_r[$key]['account_id']          = $salary['account_id'];
                    $account_r[$key]['standard_salary']     = $salary['standard_salary'];
                    $account_r[$key]['basic_salary']        = $salary['basic_salary'];
                    $account_r[$key]['performance_salary']  = $salary['performance_salary'];

                    $salary_bonus   = M('salary_bonus')->where($aid)->order('id desc')->find();//提成/奖金

                    $account_r[$key]['bonus_id']            = $salary_bonus['id'];
                    $account_r[$key]['extract']             = $salary_bonus['extract'];
                    $account_r[$key]['bonus']               = $salary_bonus['bonus'];
                    $account_r[$key]['annual_bonus']        = $salary_bonus['annual_bonus'];

                    $subsidy_r      = M('salary_subsidy')->where($aid)->order('id desc')->find();//补贴

                    $account_r[$key]['subsidy']             = $subsidy_r['id'];
                    $account_r[$key]['housing_subsidy']     = $subsidy_r['housing_subsidy'];
                    $account_r[$key]['foreign_subsidies']   = $subsidy_r['foreign_subsidies'];
                    $account_r[$key]['computer_subsidy']    = $subsidy_r['computer_subsidy'];

                    $account_r[$key]['insurance']           = M('salary_insurance')->where($aid)->order('id desc')->find();//五险一金

                    $income                                 = M('salary_income')->where($aid)->order('id desc')->find();//其他收入

                    if($income){

                        $wher['income_token']               = $income['income_token'];
                        $account_r[$key]['income']          = M('salary_income')->where($wher)->order('id desc')->select();//其他收入

                    }

                    $withholding                            = M('salary_withholding')->where($aid)->order('id desc')->find();//代扣代缴
                    if($withholding){

                        $query['token']                     = $withholding['token'];
                        $account_r[$key]['withholding']     = M('salary_withholding')->where($query)->order('id desc')->select();//代扣代缴
                    }
                }
                if(!$account_r || $account_r==""){$this->error('请添加员工编码或者员工部门！', U('Salary/salary_query'));die;}
            }
            $status                                         = trim(I('status'));
            if($status == 1){
                $this->assign('page',$pages);//数据分页
                $this->assign('list',$account_r);//数据
            }
            if($status == 2){
                $this->assign('page2',$pages);//数据分页
                $this->assign('rows',$account_r);//数据
            }
            if($status == 3){
                $this->assign('page3',$pages);//数据分页
                $this->assign('insurance',$account_r);//数据
            }
            if($status == 4){
                $this->assign('withholding',$account_r);//数据
            }
        $this->assign('type',$type);//数据
        $this->assign('department',query_department());//部门
        $this->assign('posts',query_posts());//岗位
        $this->display();
    }


    /**
     *salary_count 查询数量
     */
    private function salary_count($sum,$where){
        if($sum == 1){
            $count = M()->table('oa_account as A')->join('oa_posts as P on A.postid=P.id')->join('oa_salary_department as D on D.id=A.departmentid')->count();
        }
        if($sum == 2){
            $count = M()->table('oa_account as A')->join('oa_posts as P on A.postid=P.id')->join('oa_salary_department as D on D.id=A.departmentid')->where($where)->count();
        }
        return $count;
    }



    /**
     * salary_add_department 添加部门
     */
    public function salary_add_department(){
        if(IS_POST){
            $where['department']    = trim($_POST['department']);//部门名称
            $add['letter']          = trim($_POST['letter']);//大写字母
            if(!preg_match('/^[A-Z]+$/', $add['letter'])){

                $this->error('请添加大写字母！', U('Salary/salary_add_department'));die;

            }

            $department_r           = M('salary_department')->where($where)->find();
            $department_r1          = M('salary_department')->where($add)->find();

            if($department_r || $department_r1){

                $this->error('请不要重复添加部门或字母！', U('Salary/salary_add_department'));die;

            }
            $add['department']      = trim($_POST['department']);//部门名称
            $department             = M('salary_department')->add($add);

            if($department){

                $this->success('添加部门成功！', U('Salary/salary_add_department'));die;

            }else{

                $this->error('添加部门失败！请重写添加！', U('Salary/salary_add_department'));die;
            }
        }
        $this->display();
    }

    /**
     * record_list 数据操作记录
     */
    public function salary_list(){
        $status     = trim(I('status'));
        $sum        = M('op_record')->where('optype='.$status)->count();//操作记录数量
        $page       = I('page',1,'int');
        $limit      = 8;
        $fan        = 'salary_list';

        $record_r   = M('op_record')->where('optype='.$status)->order('op_time desc')->limit(($page-1)*$limit,$limit)->select();//操作记录
        $page_str   = $this->ajaxPageHtml($sum,$page,$limit,$fan);//数据总数 当前页面 显示条数 方法名

        $this->assign('pages',$page_str);//操作记录分页
        $this->assign('record',$record_r);//操作记录

        $this->display();
    }

    /**
     * @salary_excel_list 生成工资表
     */
    public function salary_excel_list(){//判断权限
//        if($_SESSION['userid']!==11 ||$_SESSION['userid']!==55 || $_SESSION['userid']!==77){
//            $sum                      = 0;
//            $msg                      = "您的权限不够!请联系管理员！";
//            echo json_encode(array('sum' => $sum, 'msg' => $msg));die;
//        }
        $monthly                        = trim($_POST['month']);
        $archives                       = trim($_GET['archives']);
        if(is_numeric($monthly)){
            $dateti['datetime']         =$monthly;
            $wages_month                = M('salary_wages_month')->where($dateti)->select();//已经提交数据
            if(!$wages_month){
                $this->error('您选择的数据不存在！');die;
            }
            $info                       = $this->arraysplit($wages_month);
            $sum                        = M('salary_departmen_count')->where($dateti)->select();
            $summoney                   = M('salary_count_money')->where($dateti)->find();
            $datetime                   = $monthly;
            $status                     = $wages_month[0]['status'];
        }else{
            if(!empty($archives)){
                if(!is_numeric($archives)){
                    $this->error('您的数据有误！请重新选择！');die;
                }
                $info                   = $this->salary_excel_sql($archives);//员工信息
                $sum                    = $this->countmoney($archives);//部门合计
                $summoney               = $this->summoney($sum); //总合计

            }else{
                $datetime               = trim($_GET['datetime']);
                $wages_month            = M('salary_wages_month')->where('status=3')->select();//已经提交数据
                if(!$wages_month){
                    $wages_month        = M('salary_wages_month')->where('status=2')->select();//已经提交数据
                    if(!$wages_month) {
                        $info           = $this->salary_excel_sql();//员工信息
                        $sum            = $this->countmoney();//部门合计
                        $summoney       = $this->summoney($sum); //总合计
                        $status         = 1;
                    }else{
                        $info           = $this->arraysplit($wages_month);
                        $sum            = M('salary_departmen_count')->where('datetime='.$wages_month[0]['datetime'])->select();
                        $summoney       = M('salary_count_money')->where('datetime='.$wages_month[0]['datetime'])->find();
                        $status         = $wages_month[0]['status'];
                    }
                }else{
                    $info               = $this->arraysplit($wages_month);
                    $sum                = M('salary_departmen_count')->where('datetime='.$wages_month[0]['datetime'])->select();
                    $summoney           = M('salary_count_money')->where('datetime='.$wages_month[0]['datetime'])->find();
                    $status             = $wages_month[0]['status'];
                }
            }
        }
        $this->assign('info',$info);//员工信息
        $this->assign('type',$archives);//状态
        $this->assign('sum',$sum);//部门合计
        $this->assign('count',$summoney);//部门合计
        $this->assign('time',$datetime);//表时间
        $this->assign('status',$status);//提交状态
        $this->display();
    }

    /**
     *  数组拆分
     */
    function arraysplit($wages_month){

        foreach($wages_month as $key => $val){
            $list[$key]['wages_mont_id']     = $val['id'];
            $list[$key]['account']['id']          = $val['account_id'];
            $list[$key]['account']['nickname']   = $val['user_name'];
            $list[$key]['department'][0]['department'] = $val['department'];
            $list[$key]['posts'][0]['post_name'] = $val['post_name'];
            $list[$key]['salary'][0]['standard_salary'] = $val['standard'];
            $list[$key]['salary'][0]['basic_salary'] = ((int)($val['basic_salary']/$val['standard']*1000))/100;
            $list[$key]['salary'][0]['performance_salary'] =  ((int)($val['performance_salary']/$val['standard']*1000))/100;

            $list[$key]['salary'][0]['id'] = $val['salary_id'];
            $list[$key]['attendance'][0]['id'] = $val['attendance_id'];
            $list[$key]['attendance'][0]['withdrawing'] = $val['withdrawing'];
            $list[$key]['bonus'][0]['id'] = $val['bonus_id'];
            $list[$key]['bonus'][0]['bonus'] = $val['bonus'];
            $list[$key]['income'][0]['income_token'] = $val['income_token'];
            $list[$key]['Other'] = $val['Other'];
            $list[$key]['insurance'][0]['id'] = $val['insurance_id'];
            $list[$key]['insurance_Total'] = $val['insurance_Total'];
            $list[$key]['insurance'][0]['medical_care_base'] = $val['medical_care'];
            $list[$key]['insurance'][0]['pension_base'] = $val['pension_ratio'];
            $list[$key]['insurance'][0]['unemployment_base'] = $val['unemployment'];
            $list[$key]['insurance'][0]['accumulation_fund_base'] = $val['accumulation_fund'];
            $list[$key]['insurance'][0]['medical_care_ratio'] = 1;
            $list[$key]['insurance'][0]['pension_ratio'] = 1;
            $list[$key]['insurance'][0]['unemployment_ratio'] = 1;
            $list[$key]['insurance'][0]['accumulation_fund_ratio'] = 1;
            $list[$key]['subsidy'][0]['id'] = $val['subsidy_id'];
            $list[$key]['subsidy'][0]['housing_subsidy'] = $val['housing_subsidy'];
            $list[$key]['withholding'][0]['token'] = $val['withholding_token'];
            $list[$key]['Extract']['target'] = $val['target'];
            $list[$key]['Extract']['complete'] = $val['complete'];
            $list[$key]['Extract']['total'] = $val['total'];
            $list[$key]['tax_counting'] = $val['tax_counting'];
            $list[$key]['personal_tax']= $val['personal_tax'];
            $list[$key]['Labour'] = $val['Labour'];
            $list[$key]['summoney'] = $val['summoney'];
            $list[$key]['real_wages'] = $val['real_wages'];
            $list[$key]['Achievements']['sum_total_score'] = $val['sum_total_score'];
            $list[$key]['Achievements']['total_score_show'] = $val['total_score_show'];
            $list[$key]['Achievements']['show_qa_score'] = $val['show_qa_score'];
            $list[$key]['Achievements']['count_money'] = $val['Achievements_withdrawing'];
        }
        return $list;
    }

    /**
     * @salary_excel_sql
     */
    private function salary_excel_sql($archives){

        $where['status']                            = 0;
        $where['archives']                          = $archives;
        if($archives==null || $archives==false){
            unset($where['archives']);
        }
        $info                                       =  M('account')->where($where)->order('employee_member ASC')->select();//个人数据
       foreach($info as $k => $v){//去除编码空的数据
           if($v['employee_member'] == ""){
                unset($info[$k]);
           }
       }
       $key = -1;
        foreach($info as $kee =>$val) {//薪资详情
            $key =$key+1;

            $user_info[$key]['account']             = $val;
            $id['account_id']                       = $val['id'];
            $department['id']                       = $val['departmentid'];//部门
            $user_info[$key]['department']          = sql_query(1,'*','oa_salary_department',$department,1,1);//查询部门
            $posts['id']                            = $val['postid'];
            $user_info[$key]['posts']               = sql_query(1,'*','oa_posts',$posts,1,1);//查询岗位

            $user_info[$key]['salary']              = sql_query(1, '*', 'oa_salary',$id, 1, 1);//岗位薪酬
            $user_info[$key]['attendance']          = sql_query(1, '*', 'oa_salary_attendance',$id, 1, 1);//员工考核
            $user_info[$key]['bonus']               = sql_query(1, '*', 'oa_salary_bonus',$id, 1, 1);//提成/奖金/年终奖
            if(count($user_info[$key]['salary'])==0){
                unset($user_info[$key]);continue;
            }
            $income                                 = sql_query(1, '*', 'oa_salary_income',$id, 1, 1);//其他收入

            if ($income) {
                $token['income_token']              = $income['income_token'];
                $token['account_id']                = $val['id'];
                $token                              = array_filter($token);
                $user_info[$key]['income']          = sql_query(1, '*', 'oa_salary_income', $token, 1, 0);//其他收入所有项目
                $countmoney                         = 0;
                foreach($user_info[$key]['income'] as $ke =>$va){
                    $countmoney                     += $va['income_money'];
                }
                $user_info[$key]['Other']           = $countmoney;//其他补款
            }
            $user_info[$key]['insurance']           = sql_query(1, '*', 'oa_salary_insurance', $id, 1, 1);//五险一金表

            $user_info[$key]['insurance_Total']     = $user_info[$key]['insurance'][0]['medical_care_base']*$user_info[$key]['insurance'][0]['medical_care_ratio']+$user_info[$key]['insurance'][0]['pension_base']*$user_info[$key]['insurance'][0]['pension_ratio']+$user_info[$key]['insurance'][0]['unemployment_base']*$user_info[$key]['insurance'][0]['unemployment_ratio']+$user_info[$key]['insurance'][0]['accumulation_fund_base']*$user_info[$key]['insurance'][0]['accumulation_fund_ratio'];//五险一金

//            print_r($user_info[166]);die;
            $user_info[$key]['subsidy']             = sql_query(1, '*', 'oa_salary_subsidy', $id, 1, 1);//补贴
            $withholding                            = sql_query(1, '*', 'oa_salary_withholding', $id, 1, 1);//代扣代缴
            if ($withholding) {
                $wit['token']                       = $withholding['token'];
                $wit['account_id']                  = $val['id'];
                $wit                                = array_filter($wit);
                $withh                              = sql_query(1, '*', 'oa_salary_withholding', $wit, 1, 0);//代扣代缴
                foreach($withh as $kk =>$vv){
                    $user_info[$key]['summoney']    += $vv['money']; //总代扣代缴
                }
            }
            // kpi  pdca 品质检查
            $que['p.tab_user_id']                   = $val['id'];//用户id
            $time_Y                                 = date('Y');
            $time_M                                 = date('m');
            $time_D                                 = date('d');
            if($time_D < 10){
                $time_M                             = $time_M-1;
            }
            $que['p.month']                       = $time_Y.$time_M ;//查询年月
            $user                                   = $this->query_score($que);//绩效增减
            $use1                                   = str_replace(array('<span class="red">','<span>','<span class="green">','</span>'),"",$user[0]['total_score_show']);//PDCA
            $use2                                   = str_replace(array('<span class="red">','<span>','<span class="green">','</span>'),"",$user[0]['show_qa_score']);//品质检查
            $use3                                   = str_replace(array('<span class="red">','<span>','<span class="green">','</span>'),"",$user[0]['sum_total_score']);//KPI
            $money                                  = $user_info[$key]['salary'][0]['standard_salary']/10*$user_info[$key]['salary'][0]['performance_salary']/100;//绩效金额
            $branch                                 =100;//给总共100分
            if(substr($use1,0,1)=='-' && is_numeric(substr($use1,1))){
                $user_info[$key]['Achievements']['total_score_show'] = $use1;//pdca分数
                $branch                             = $branch-substr($use1,1);
            }
            if(substr($use2,0,1)=='-' && is_numeric(substr($use2,1))){
                $user_info[$key]['Achievements']['show_qa_score'] = $use2;//品质检查分数
                $branch                             = $branch-substr($use2,1);
            }
            if(substr($use3,0,1)=='-' && is_numeric(substr($use3,1))){
                $user_info[$key]['Achievements']['sum_total_score'] = $use3;//KPI分数
                $branch                             = $branch-substr($use3,1);
            }
            if(substr($use1,0,1)=='+' && is_numeric(substr($use1,1))){
                $user_info[$key]['Achievements']['total_score_show'] = $use1;//pdca分数
                $branch                             = $branch-substr($use1,1);
            }
            if(substr($use2,0,1)=='+' && is_numeric(substr($use2,1))){
                $user_info[$key]['Achievements']['show_qa_score'] = $use2;//品质检查分数
                $branch                             = $branch-substr($use2,1);
            }
            if(substr($use3,0,1)=='+' && is_numeric(substr($use3,1))){
                $user_info[$key]['Achievements']['sum_total_score'] = $use3;//KPI分数
                $branch                             = $branch-substr($use3,1);
            }
            if($branch>100){//判断加减金额
               $achievements                        = '+'.(($branch-100)*$money);
            }
            if($branch<100){//判断加减金额
                $achievements                       = '-'.((100-$branch)*$money);
            }
            if($achievements =='-0' || $achievements == '+0'){
                $achievements=0;
            }
            $user_info[$key]['Achievements']['count_money'] = $achievements;//绩效增减金额

            $user_info[$key]['Extract']             = $this->salary_kpi_month($val['id'],$que['p.month']); //目标任务 完成 提成

            $user_info[$key]['tax_counting']        = $user_info[$key]['salary'][0]['standard_salary']/10*$user_info[$key]['salary'][0]['performance_salary']+($user_info[$key]['Achievements']['count_money'])-$user_info[$key]['insurance_Total'];//计税工资

            //个人所得税
            if($user_info[$key]['tax_counting'] <= 5000){
                $counting                           = '0';
            }else{
                $cout                               = $user_info[$key]['tax_counting']-5000;

                if($cout <= 3000){
                    $counting                       = $cout*0.03;
                }
                if($cout > 3000 && $cout <= 12000){
                    $counting                       = $cout*0.10-210;
                }
                if($cout > 12000 && $cout <= 25000){
                    $counting                       = $cout*0.20-1410;
                }
                if($cout > 25000 && $cout <= 35000){
                    $counting                       = $cout*0.25-2660;
                }
                if($cout > 35000 && $cout <= 55000){
                    $counting                       = $cout*0.30-4410;
                }
                if($cout > 55000 && $cout <= 80000){
                    $counting                       = $cout*0.35-7160;
                }
                if($cout > 80000){
                    $counting                       = $cout*0.45-15160;
                }
                $counting                           = ((int)($counting*100))/100;
            }
            $user_info[$key]['personal_tax']        = $counting;//个人所得税
            $user_info[$key]['Labour']              = ($user_info[$key]['salary'][0]['standard_salary']/10*$user_info[$key]['salary'][0]['basic_salary']-500)*0.01;//工会会费

            $Year_end                               = ($user_info[$key]['bonus'][0]['annual_bonus'])/12;

            if($Year_end < 1500){
                $price1                             = $Year_end*0.03;
            }
            if($Year_end > 1500 && $Year_end < 4500){
                $price1                             = $Year_end*0.1-105;
            }
            if($Year_end > 4500 && $Year_end < 9000){
                $price1                             = $Year_end*0.2-555;
            }
            if($Year_end > 9000 && $Year_end < 35000){
                $price1                             = $Year_end*0.25-1055;
            }
            if($Year_end > 35000 && $Year_end < 55000){
                $price1                             = $Year_end*0.3-2755;
            }
            if($Year_end > 55000 && $Year_end < 80000){
                $price1                             = $Year_end*0.35-5505;
            }
            if($Year_end>80000){
                $price1                             = $Year_end*0.45-13505;
            }
            $price2                                 = ((int)($price1*100))/100;//年终奖计税

            $user_info[$key]['yearend']             = $price2;//年终奖计税
            // 提成 + 奖金+带团补助+年终奖+
            $user_info[$key]['welfare']             = $user_info[$key]['Extract']['total']+$user_info[$key]['bonus'][0]['bonus']+$user_info[$key]['bonus'][0]['extract']+$user_info[$key]['bonus'][0]['annual_bonus']+$user_info[$key]['subsidy'][0]['housing_subsidy']+$user_info[$key]['subsidy'][0]['foreign_subsidies']+$user_info[$key]['subsidy'][0]['computer_subsidy'];

            //应发工资 = 岗位工资-考勤扣款
            $user_info[$key]['Should'] = $user_info[$key]['salary'][0]['standard_salary']-$user_info[$key]['attendance'][0]['withdrawing']+$achievements+$user_info[$key]['Extract']['total']+$user_info[$key]['bonus'][0]['bonus']+$user_info[$key]['bonus'][0]['annual_bonus']-$price2+$user_info[$key]['subsidy'][0]['housing_subsidy']+$user_info[$key]['subsidy'][0]['foreign_subsidies']+$countmoney;

            //实发工资=岗位工资-考勤扣款+绩效增减+提成+奖金-代扣代缴+带团补助+年终奖-年终奖计税+住房补贴+外地补贴+电脑补贴-五险一金-个人所得税-工会会费+其他补款-大额医保
            $user_info[$key]['real_wages']          = $user_info[$key]['salary'][0]['standard_salary']-$user_info[$key]['attendance'][0]['withdrawing']+$achievements+$user_info[$key]['Extract']['total']+$user_info[$key]['bonus'][0]['bonus']-$user_info[$key]['summoney']+$user_info[$key]['bonus'][0]['extract']+$user_info[$key]['bonus'][0]['annual_bonus']-$price2+$user_info[$key]['subsidy'][0]['housing_subsidy']+$user_info[$key]['subsidy'][0]['foreign_subsidies']+$user_info[$key]['subsidy'][0]['computer_subsidy']-$user_info[$key]['insurance_Total']-$counting-$user_info[$key]['Labour']+$countmoney-$user_info[$key]['insurance'][0]['big_price'];

        }
//        print_r($user_info);die;
        return $user_info;
    }

    /**
     * countmoney 部门合计
     */
    private function countmoney($archives){
        $where['status']                                    = 0;
        $where['archives']                                  = $archives;
        $where = array_filter($where);
        $info1                                              =  M('account')->where($where)->group('departmentid')->order('employee_member ASC')->select();//个人数据

        foreach($info1 as $k => $v){//去除编码空的数据
            if($v['employee_member'] == ""){//去空
                unset($info1[$k]);
            }else{
                $query['departmentid']                      = $v['departmentid'];
                $list                                       = $this->salary_excel_sql($archives);//获取薪资信息
                foreach($list as $key =>$val){
                    if($val['department'][0]['id']==$v['departmentid']){
                        $sum[$k]['name']                    = '合计';
                        $sum[$k]['department']              = $val['department'][0]['department'];//部门
                        $sum[$k]['standard_salary']         += $val['salary'][0]['standard_salary'];//标准薪资
                        $sum[$k]['basic']                   += $val['salary'][0]['standard_salary']/10*$val['salary'][0]['basic_salary'];//基本薪资
                        $sum[$k]['withdrawing']             += $val['attendance'][0]['withdrawing'];//考勤扣款
                        $sum[$k]['performance_salary']      += $val['salary'][0]['standard_salary']/10*$val['salary'][0]['performance_salary'];//绩效薪资
                        $sum[$k]['count_money']             += $val['Achievements']['count_money'];//绩效增减
                        $sum[$k]['total']                   += $val['Extract']['total'];//业绩提成
                        $sum[$k]['bonus']                   += $val['bonus'][0]['bonus'];// 奖金
                        $sum[$k]['housing_subsidy']         += $val['subsidy'][0]['housing_subsidy'];//住房补贴
                        $sum[$k]['Other']                   += $val['Other'];//其他补款
                        $sum[$k]['Should']                  += $val['Should'];//应发工资
                        $sum[$k]['care']                    += $val['insurance'][0]['medical_care_base']*$val['insurance'][0]['medical_care_ratio'];//医疗保险
                        $sum[$k]['pension']                 += $val['insurance'][0]['pension_base']*$val['insurance'][0]['pension_ratio'];//养老保险
                        $sum[$k]['unemployment']            += $val['insurance'][0]['unemployment_base']*$val['insurance'][0]['unemployment_ratio'];// 失业保险
                        $sum[$k]['accumulation']            += $val['insurance'][0]['accumulation_fund_base']*$val['insurance'][0]['accumulation_fund_ratio'];//公积金
                        $sum[$k]['insurance_Total']         += $val['insurance_Total'];//个人保险合计
                        $sum[$k]['tax_counting']            += $val['tax_counting'];//计税工资
                        $sum[$k]['personal_tax']            += $val['personal_tax'];//个人所得税
                        $sum[$k]['summoney']                += $val['summoney'];//税后扣款
                        $sum[$k]['Labour']                  += $val['Labour'];//工会会费
                        $sum[$k]['real_wages']              += $val['real_wages'];// 实发工资
                    }
                }
            }
        }
        return $sum;
    }

    /**
     * summoney 部门合计
     */
    private function summoney($sum){
        $cout['name']                                = '总合计';
        foreach($sum as $key => $val){
            $cout['standard_salary']                 += $val['standard_salary'];//标准薪资
            $cout['basic']                           += $val['basic'];//基本薪资
            $cout['withdrawing']                     += $val['withdrawing'];//考勤扣款
            $cout['performance_salary']              += $val['performance_salary'];//绩效薪资
            $cout['count_money']                     += $val['count_money'];//绩效增减
            $cout['total']                           += $val['total'];//业绩提成
            $cout['bonus']                           += $val['bonus'];//奖金
            $cout['housing_subsidy']                 += $val['housing_subsidy'];//住房补贴
            $cout['Other']                           += $val['Other'];//其他补款
            $cout['Should']                          += $val['Should'];//应发工资
            $cout['care']                            += $val['care'];//医疗保险
            $cout['pension']                         += $val['pension'];//养老保险
            $cout['unemployment']                    += $val['unemployment'];//失业保险
            $cout['accumulation']                    += $val['accumulation'];//公积金
            $cout['insurance_Total']                 += $val['insurance_Total'];//个人保险合计
            $cout['tax_counting']                    += $val['tax_counting'];//计税工资
            $cout['personal_tax']                    += $val['personal_tax'];//个人所得税
            $cout['summoney']                        += $val['summoney'];//税后扣款
            $cout['Labour']                          += $val['Labour'];//工会会费
            $cout['real_wages']                      += $val['real_wages'];//实发工资

        }
        return $cout;
    }

/**
 * 导出 excel
 */
//    public function excel(){
//        //引入PHPExcel库文件
//        Vendor('PHPExcel.Classes.PHPExcel');
//        //创建对象
//        $excel = new PHPExcel();
//        //Excel表格式,这里简略写了8列
//        $letter = array('A','B','C','D','E','F','F','G');
//        //表头数组
//        $tableheader = array('序号','客栈名称','客栈地址','座机','手机','QQ','邮箱');
//        //填充表头信息
//        for($i = 0;$i < count($tableheader);$i++) {
//            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
//        }
//        //表格数组
//        $data = array(
//            array('1','B','C','D','E','F','G'),
//            array('2','B','C','D','E','F','G'),
//            array('3','B','C','D','E','F','G'),
//            array('4','B','C','D','E','F','G'),
//            array('5','B','C','D','E','F','G'),
//            array('6','B','C','D','E','F','G'),
//            array('7','B','C','D','E','F','G'),
//        );
//        //填充表格信息
//        for ($i = 2;$i <= count($data) + 1;$i++) {
//            $j = 0;
//            foreach ($data[$i - 2] as $key=>$value) {
//                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
//                $j++;
//            }
//        }
//        //创建Excel输入对象
//        $write = new PHPExcel_Writer_Excel5($excel);
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type:application/force-download");
//        header("Content-Type:application/vnd.ms-execl");
//        header("Content-Type:application/octet-stream");
//        header("Content-Type:application/download");;
//        header('Content-Disposition:attachment;filename="客栈信息.xls"');
//        header("Content-Transfer-Encoding:binary");
//        $write->save('php://output');
//    }

}