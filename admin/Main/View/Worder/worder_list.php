<include file="Index:header2" />

            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        工单管理
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="{:U('Rbac/index')}"><i class="fa fa-gift"></i> {$_pagetitle_}</a></li>
                        <li class="active">{$_action_}</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">工单计划列表</h3>
                                    <div class="box-tools pull-right">
                                    	 <a href="javascript:;" class="btn btn-info btn-sm" onclick="javascript:opensearch('search_worder',800,160);"><i class="fa fa-search"></i> 搜索</a>
                                         <a href="{:U('Worder/new_worder')}" class="btn btn-sm btn-danger"><i class="fa fa-plus"></i> 新建工单</a>
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                
                                <div class="btn-group" id="catfont">
                                    <!--<a href="{:U('Worder/worder_list',array('pin'=>0))}" class="btn <?php /*if($pin==0){ echo 'btn-info';}else{ echo 'btn-default';} */?>">所有工单</a>
                                    <a href="{:U('Worder/worder_my',array('pin'=>1))}" class="btn <?php /*if($pin==1){ echo 'btn-info';}else{ echo 'btn-default';} */?>">我的工单</a>-->
                                    <a href=javascript:; class="btn <?php if($pin==0){ echo 'btn-info';}else{ echo 'btn-default';} ?>">所有工单</a>
                                    <a href=javascript:; class="btn <?php if($pin==1){ echo 'btn-info';}else{ echo 'btn-default';} ?>">我的工单</a>
                                </div>
                                
                                <table class="table table-bordered dataTable fontmini" id="tablelist" style="margin-top:10px;">
                                    <tr role="row" class="orders" >
                                        <th class="sorting" width="80" data="id">id</th>
                                        <th class="sorting" data="worder_title">工单标题</th>
                                        <th class="sorting" width="160" data="worder_content">工单内容</th>
                                        <th class="sorting" data="worder_type">工单类型</th>
                                        <th class="sorting" data="init_user_name">发起人姓名</th>
                                        <!--<th class="sorting" data='init_dept_name'>发起人部门</th>-->
                                        <th class="sorting" width="80"  data="exe_user_name">执行人姓名</th>
                                        <!--<th class="sorting" width="80" data="exe_dept_name">执行人部门</th>-->
                                        <th class="sorting" width="80" data="status">工单状态</th>
                                        <th class="sorting" >工单创建时间</th>
                                        <th class="sorting" >工单完成时间</th>
                                        <if condition="rolemenu(array('Worder/exe_worder'))">
                                        <th width="40" class="taskOptions">执行</th>
                                        </if>
                                        
                                        <if condition="rolemenu(array('Worder/del_worder'))">
                                        <th width="40" class="taskOptions">删除</th>
                                        </if> 
                                    </tr>
                                    <foreach name="lists" item="row"> 
                                    <tr>
                                        <td>{$row.id}</td>
                                        <td>{$row.worder_title}</td>
                                        <td>{$row.worder_content}</td>
                                        <td></td><!--类型-->
                                        <td>{$row.ini_user_name}</td>
                                        <td>{$row.exe_user_name}</td>
                                        <td></td><!--工单状态-->
                                        <td>{$row.create_time|date='Y-m-d H:i:s',###}</td>
                                        <td>{$row.complete_time|date='Y-m-d H:i:s',###}</td>
                                        <if condition="rolemenu(array('Worder/exe_worder'))">
                                        <td class="taskOptions">
                                        <a href="{:U('Worder/exe_worder',array('opid'=>$row['op_id']))}" title="执行" class="btn btn-info btn-smsm"><i class="fa fa-pencil"></i></a>
                                        </td>
                                        </if>
                                        <if condition="rolemenu(array('Worder/del_worder'))">
                                        <td class="taskOptions">
                                        <button onClick="javascript:ConfirmDel('{:U('Worder/del_worder',array('id'=>$row['id']))}')" title="删除" class="btn btn-warning btn-smsm"><i class="fa fa-times"></i></button>
                                        </td>
                                        </if>
                                    </tr>
                                    </foreach>					
                                </table>
                                </div><!-- /.box-body -->
                                 <div class="box-footer clearfix">
                                	<div class="pagestyle">{$pages}</div>
                                </div>
                            </div><!-- /.box -->

                        </div><!-- /.col -->
                     </div>

                </section><!-- /.content -->
            </aside><!-- /.right-side -->
            
            
            <div id="search_worder">
                <form action="" method="get" id="searchform">
                <input type="hidden" name="m" value="Main">
                <input type="hidden" name="c" value="Worder">
                <input type="hidden" name="a" value="worder_list">

                <div class="form-group col-md-12">
                    <input type="text" class="form-control" name="worder_title" placeholder="工单名称">
                </div>

                <div class="form-group col-md-12">
                    <input type="text" class="form-control" name="worder_content" placeholder="工单内容">
                </div>
                
                </form>
            </div>

<include file="Index:footer2" />
