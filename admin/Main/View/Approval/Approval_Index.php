<include file="Index:header2" />


            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>文件审批</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Files/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="{:U('Approval/Approval_Index')}">文件审批</a></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <div class="tip">
                                        <a href="javascript:;"  class="btn btn-danger Approval_file_delete" style="padding:6px 12px;"><i class="fa fa-trash-o"></i> 删除</a>
<!--                                        <a href="{:U('Approval/Approval_Upload')}" class="btn btn-info btn-sm"><i class="fa fa-upload"></i> 上传文件</a>-->
                                    </div>
                                    <a class="btn btn-info btn-sm" id="salary_create_file" onclick="javascript:salry_opensearch('searchtext',700,300,'创建文件');" style="float:right;margin:1em 2em 0em 0em;background-color:#f4543c"><b>+</b> 创建文件</a>
                                </div><!-- /.box-header -->
                                <div class="box-body">

                                <table class="table table-bordered dataTable"  style="margin-top:10px;">
                                    <tr role="row" class="orders" >
                                    	<th style="text-align:center;width:6em;"/>
                                            <input type="checkbox" id="Approval_check"/>
                                        </th>
                                        <th style="text-align:center;width:6em;"><b> ID </b></th>
                                        <th style="text-align:center;width:10em;"><b>拟稿人姓名</b></th>
                                        <th style="text-align:center;width:10em;"><b>原文件名称</b></th>
                                        <th style="text-align:center;width:10em;"><b>创建时间</b></th>
                                        <th style="text-align:center;width:10em;"><b>修改后文件名称</b></th>
                                        <th style="text-align:center;width:10em;"><b>修改人姓名</b></th>
                                        <th style="text-align:center;width:10em;"><b>修改时间</b></th>
                                        <th style="text-align:center;width:5em;"><b>文件格式</b></th>
                                        <th style="text-align:center;width:5em;"><b>文件大小</b></th>
                                        <th style="text-align:center;width:5em;"><b>状态</b></th>
                                        <th style="text-align:center;width:10em;"><b>操作</b></th>
                                    </tr>
                                    <foreach name="file" item="f">
                                    <tr>
                                    	<td align="center">
                                            <input type="checkbox" class="Approval_check" value="{$f['file']['id']}"/>
                                        </td>
                                        <td style="text-align:center;">{$f['file']['id']}</td>
                                        <td style="text-align:center;color:#3399FF;">{$f['file']['account_name']}</td>
                                        <td style="text-align:center;" >
                                            <a href="{$f['file']['file_url']}">
                                                <?php if(!empty($f['file']['file_name']) && !empty($f['file']['file_format'])){echo $f['file']['file_name'].'.'.$f['file']['file_format'];}?>
                                            </a>
                                        </td>
                                        <td style="text-align:center;">
                                            <?php if(!empty($f['file']['createtime'])){echo date('Y-m-d H:i:s',$f['file']['createtime']);}?>
                                        </td>
                                        <td style="text-align:center;">
                                            <a href="{$f['flie_update']['file_url']}">
                                                <?php if(!empty($f['flie_update']['file_name']) && !empty($f['flie_update']['file_format'])){echo $f['flie_update']['file_name'].'.'.$f['flie_update']['file_format'];}?>
                                            </a>
                                        </td>
                                        <td style="text-align:center;">{$f['flie_update']['account_name']}</td>
                                        <td style="text-align:center;">
                                            <?php if(!empty($f['flie_update']['update_time'])){echo date('Y-m-d H:i:s',$f['flie_update']['update_time']);}?>
                                        </td>

                                        <td style="text-align:center;">{$f['file']['file_format']}</td>
                                        <td style="text-align:center;">{$f['file']['file_size']}</td>
                                        <td style="text-align:center;">
                                            <?php if($f['file']['status']==1){echo "待批注";}elseif($f['file']['status']==2){echo "待批准";}elseif($f['file']['status']==3){echo "已批准";}elseif($f['file']['status']==4){echo "审批未通过";}?>
                                        </td>
                                        <td style="text-align:center;">
                                            <a href="{:U('Approval/Approval_Update',array('id'=>$f['file']['id']))}">
                                                查看详情
                                            </a>
                                            <a href="{:U('Approval/Approval_Upload',array('type'=>3,'id'=>$f['file']['id']))}"
                                               style="<?php if(($f['file']['file_leader_id']=='' || $f['file']['file_leader_id']==0) && $type==1){echo '';}else{echo 'display:none';} ?>"> | 添加审批人</a>

                                        </td>
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
    <script src="__HTML__/js/public.js?v=1.0.6" type="text/javascript"></script>

    <form action="{:U('Salary/salaryindex')}" method="post">

        <div class="form-group col-md-5" style="float: left;">
            创建文件名称： <input type="text" class="form-control" style="margin-top:1em;width:20em;" name="create_file">
        </div>
        <div class="form-group" style="width:8em;float: right;">
            创建人：
            <input type="text" class="form-control" style="margin-top:1em;" name="file_name" value="<?php echo $_SESSION['username']?>" disabled="disabled">
        </div><br><br><br><br>
        <div class="form-group" style="margin:0em 0em 0em 1em;">
            <a>文件描述：</a><br>
            <textarea rows="5" cols="65" style="margin-top:1em;"> </textarea>
        </div>


    </form>
</div>

<include file="Index:footer2" />

<script type="text/javascript">

    // 文件选择
    $(document).ready(function(e) {
        //选择
        $('#Approval_check').on('ifChecked', function() {
            $('.Approval_check').iCheck('check');
        });
        $('#Approval_check').on('ifUnchecked', function() {
            $('.Approval_check').iCheck('uncheck');
        });
    });


    // 删除选择文件
    $('.Approval_file_delete').click(function(){
        var id = '';
        $(".Approval_check:checkbox:checked").each(function(){
            var content = $(this).val();
                id += content+',';
            $(this).parents('tr').remove();
        });

        $.ajax({
            url:"{:U('Ajax/Ajax_file_delete')}",
            type:"POST",
            data:{'fileid':id},
            dataType:"json",
            success:function(date){
                if(date.sum==1){
                    alert(date.msg);
                }else{
                    alert(date.msg);
                }
            }
        });
    });


    function salry_opensearch(obj,w,h,t){ //自制弹窗
        var url ="{:U('Approval/Approval_Upload')}";
        art.dialog({
            content:$('#'+obj).html(),
            lock:true,
            title: t,
            width:w,
            height:h,
            okValue: t,
            button:[
                {name:'确定',
                    class:'aui_state_highlight',
                    callback:function(){
                        window.location.href=url;
                        return false;
                    }
                },
            ],
            cancelValue:'取消',
            cancel: function () {
            }

        }).show();
        $('.aui_buttons').find("button").first().css("background-color","#00acd6");
    }
</script>
