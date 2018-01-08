<include file="Index:header2" />

            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>个人业绩排行榜</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li class="active">个人业绩排行榜</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">个人业绩排行榜（按已完成结算统计）</h3>
                                </div>
                                <div class="box-body">
                                	
                                    <div class="btn-group" id="catfont">
                                        <a href="{:U('Chart/pplist')}" class="btn btn-info">个人业绩排行榜</a>
                                        <a href="{:U('Chart/tplist')}" class="btn btn-default">团队总体业绩排行榜</a>
                                        <a href="{:U('Chart/tpavglist')}" class="btn btn-default">团队人均排行榜</a>
                                    </div>
                                    
                                    
                                	<table id="example2" class="table table-bordered table-hover" style="margin-top:10px;">
                                        <thead>
                                            <tr role="row" class="orders" >
                                            	<th width="40">序号</th>
                                                <th>姓名</th>
                                                <th width="12%">所在部门</th>
                                                <th width="12%" class="orderth">累计收入(元)</th>
                                                <th width="12%" class="orderth">累计毛利(元)</th>
                                                <th width="12%" class="orderth">累计毛利率(%)</th>
                                                <th width="12%" class="orderth">当月收入(元)</th>
                                                <th width="12%" class="orderth">当月毛利(元)</th>
                                                <th width="12%" class="orderth">当月毛利率(%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <foreach name="lists" item="row" key="k">                      
                                            <tr>
                                            	<td class="orderNo"><?php echo $k+1; ?></td>
                                                <td>{$row.create_user_name}</td>
                                                <td>{$row.rolename}</td>
                                                <td>{$row.zsr}</td>
                                                <td>{$row.zml}</td>
                                                <td>{$row.mll}</td>
                                                <td>{$row.ysr}</td>
                                                <td>{$row.yml}</td>
                                                <td>{$row.yll}</td>
                                            </tr>
                                            </foreach>	
                                        </tbody>	
                                        
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

        <include file="Index:footer2" />
        <script type="text/javascript">
		$('#example2').dataTable({
			"bPaginate": false,
			"bLengthChange": false,
			"bFilter": false,
			"bSort": true,
			"bInfo": false,
			"aaSorting" : [[3, "desc"]],
			"bAutoWidth": true,
			"aoColumnDefs": [{ "bSortable": false, "aTargets": [ 0,1,2] }]
		});
		
		$(document).ready(function(e) {
			$('.orderth').click(function(){
				$('.orderNo').each(function(index, element) {
					$(this).text(index+1);
				});	
			})
		});
        </script>
        