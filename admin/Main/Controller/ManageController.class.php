<?php
namespace Main\Controller;
use Think\Controller;
use Sys\P;
ulib('Page');
use Sys\Page;
class ManageController extends BaseController {

    /**
     * Manage_month 月度经营报表
     * F 京区业务中心 G 京外业务中心 L 南京项目部
     * M 武汉项目部 N 沈阳项目部 P 长春项目部 B 市场部
     */
    public function Manage_month(){
        $mod            = D('Manage');
        // 月度统计人员 数额 占比
        $number         = $mod->month();
        $this->number   = $number;
        $this->display();
    }
    //季度经营报表
    public function Manage_quarter(){
        $mod            = D('Manage');
        // 月度统计人员 数额 占比
        $number         = $mod->month();
        $this->number   = $number;
        $this->display();
    }
    //年度经营报表
    public function Manage_year(){
        $mod            = D('Manage');
        // 月度统计人员 数额 占比
        $number         = $mod->month();
        $this->number   = $number;
        $this->display();
    }

 }
