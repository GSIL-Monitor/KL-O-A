<include file="Index:header2" />

            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>报销单</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="javascript:;"><i class="fa fa-gift"></i> {$_pagetitle_}</a></li>
                        <li class="active">报销单</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">报销单列表</h3>
                                    <div class="box-tools pull-right">
                                    	 <a href="javascript:;" class="btn btn-info btn-sm" onclick="javascript:opensearch('searchtext',600,160);"><i class="fa fa-search"></i> 搜索</a>
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                
                                <table class="table table-bordered dataTable fontmini" id="tablelist" style="margin-top:10px;">
                                    <tr role="row" class="orders" >
                                        <th class="sorting" width="180" data="b.bxd_id">报销单号</th>
                                        <th class="sorting" width="" data="b.group_ids">团号</th>
                                        <th class="sorting" data="b.jkd_ids">借款单号</th>
                                        <th class="sorting" width="100" data="b.bx_user">报销人</th>
                                        <th class="sorting" width="80" data="b.sum">报销金额</th>
                                        <th class="sorting" width="60" data="b.type">报销方式</th>
                                        <th class="sorting" width="80" data="b.zhuangtai">审批状态</th>
                                        <if condition="rolemenu(array('Finance/jiekuandan_info'))">
                                            <th width="40" class="taskOptions">详情</th>
                                        </if>
                                        <if condition="rolemenu(array('Finance/del_jkd'))">
                                            <th width="40" class="taskOptions">删除</th>
                                        </if>
                                    </tr>
                                    <foreach name="lists" item="row"> 
                                    <tr>
                                        <td>{$row.bxd_id}</td>
                                        <td>{$row.group_ids}</td>
                                        <td>{$row.jkd_ids}</td>
                                        <!--<td>
                                            <div class="">
                                                <if condition="rolemenu(array('Finance/jiekuandan_info'))">
                                                    <a href="{:U('Finance/jiekuandan_info',array('bxid'=>$row['id']))}" title="{$row.project}">{$row.project}</a>
                                                    <else />
                                                    <a href="javascript:;" title="{$row.project}">{$row.project}</a>
                                                </if>
                                            </div>
                                        </td>-->
                                        <td>{$row.bx_user}</td>
                                        <td>{$row.sum}</td>
                                        <td>{$jk_type[$row[type]]}</td>
                                        <td>{$row.zhuangtai}</td>
                                        <if condition="rolemenu(array('Finance/jiekuandan_info'))">
                                            <td class="taskOptions">
                                                <a href="javascript:;" title="详情" class="btn btn-info btn-smsm"><i class="fa fa-bars"></i></a>
                                            </td>
                                        </if>
                                        <if condition="rolemenu(array('Finance/del_jkd'))">
                                            <td class="taskOptions">
                                                <button onClick="javascript:ConfirmDel()" title="删除" class="btn btn-warning btn-smsm"><i class="fa fa-times"></i></button>
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
            
            
            <div id="searchtext">
                <form action="" method="get" id="searchform">
                <input type="hidden" name="m" value="Main">
                <input type="hidden" name="c" value="Finance">
                <input type="hidden" name="a" value="baoxiao_lists">

                <!--<div class="form-group col-md-6">
                    <input type="text" class="form-control" name="title" placeholder="项目名称">
                </div>-->

                <div class="form-group col-md-12">
                    <input type="text" class="form-control" name="oid" placeholder="团号">
                </div>
                
                <div class="form-group col-md-6">
                    <input type="text" class="form-control" name="bxdid" placeholder="报销单号">
                </div>
               	
                <div class="form-group col-md-6">
                    <input type="text" class="form-control" name="ou" placeholder="报销人">
                </div>
                
                </form>
            </div>

<include file="Index:footer2" />
