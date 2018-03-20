<include file="Index:header2" />

<script type="text/javascript">
    window.onload = function(i){
        $('#in_group').hide();
        $('#dept').hide();
    }
</script>

            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>发起工单</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="javascript:;"><i class="fa fa-gift"></i> 工单计划</a></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <form method="post" action="{:U('Worder/new_worder')}" name="myform" id="myform">
                <input type="hidden" name="dosubmint" value="1">
                    <div class="row">
                         <!-- right column -->
                        <div class="col-md-12">


                            
                            <div class="box box-warning">
                                <div class="box-header">
                                    <h3 class="box-title">工单计划</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="content">
                                    	
                                        <div class="form-group col-md-12">
                                            <label>工单名称：</label><input type="text" name="info[worder_title]" class="form-control" />
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label>工单内容：</label><textarea class="form-control"  name="info[worder_content]"></textarea>
                                        </div>
                                        
                                        <div class="form-group col-md-6">
                                            <label>工单类型：</label>
                                            <select  class="form-control"  name="info[worder_type]" required>
                                            <foreach name="worder_type" key="k" item="v">
                                                <option value="{$k}">{$v}</option>
                                            </foreach>
                                            </select> 
                                        </div>
                                        
                                        <div class="form-group col-md-6">
                                            <label>发起人员：</label>
                                            <input type="text" class="form-control" name="info[ini_user_name]" value="{:session('nickname')}" readonly>
                                        </div>

                                        <div class="form-group col-md-6"></div>

                                        <!-- ************************************start-->
                                        <div class="col-md-12">

                                            <div class="box box-success">
                                                <div class="box-header">
                                                    <h3 class="box-title">工单受理组/人</h3>
                                                </div>
                                                <div class="box-body" style="padding-top:20px;">

                                                    <div class="form-group col-md-12" id="addti_btn">
                                                        <a href="javascript:;" class="btn btn-success btn-sm" onClick="task(1)" style="margin-right:10px;"><i class="fa fa-fw  fa-plus"></i> 添加工单受理组/人</a>
                                                    </div>

                                                    <div id="task_timu">
                                                        <foreach name="days" key="k" item="v">

                                                            <!--<div class="tasklist" id="task_a_{$v.id}">
                                                                <a class="aui_close" href="javascript:;" onClick="del_timu('task_a_{$v.id}')">×</a>
                                                                <div class="form-group col-md-6 pd">
                                                                    <label>工单受理组/人<span class="tihao">{$k+1}</span>：</label>
                                                                    <select name="info[{$v.id}][exe_dept_id]" id="group" onchange="check_group()" class="form-control">
                                                                        <option value="" disabled selected>请选择受理组</option>
                                                                        <foreach name="group" item="v">
                                                                            <option value="{$v.id}">{:tree_pad($v['level'])}{$v.role_name}</option>
                                                                        </foreach>
                                                                    </select>
                                                                </div>

                                                                <div id="in_group">
                                                                    <div class="form-group col-md-6">
                                                                        <label>项目类型：</label>
                                                                        <select name="info[{$v.id}][dept_id]" id="pro_tit" onchange="show_dept()" class="form-control">
                                                                            <option value="" disabled selected>请选择项目类型</option>

                                                                        </select>
                                                                    </div>

                                                                    <div id="dept">
                                                                        <div class="form-group col-md-6">
                                                                            <label>工单类型：</label><input type="text" name="info[{$v.id}][type]" class="form-control" readonly />
                                                                        </div>

                                                                        <div class="form-group col-md-6">
                                                                            <label>完成所需时间：</label><input type="text" name="info[{$v.id}][use_time]" class="form-control" readonly />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>-->
                                                        </foreach>
                                                    </div>

                                                    <div style="display:none" id="task_val">0</div>

                                                    <div id="daysbox"></div>
                                                    <div class="form-group">&nbsp;</div>
                                                    <div class="form-group">&nbsp;</div>

                                                </div>
                                            </div><!-- /.box -->

                                        </div><!--/.col (right) -->
                                        <!-- ******************************************************end-->

                                        <div class="kjss ">
                                                <input type="hidden" name="bkpr" id="bkpr" value="">
                                                <!--<input type="hidden" name="kpr" id="kpr" value="">-->
                                                <!--<input type="text" name="month" class="form-control monthly" placeholder="月份" style="width:100px; margin-right:10px;" />-->
                                                <input type="text" class="form-control keywords_bkpr" placeholder="请输入执行部门"  style="width:100%; margin-right:10px;"/>
                                                <!--<input type="text" class="form-control keywords_kpr" placeholder="考评人"  style="width:180px;"/>-->
                                                <!--<button class="btn btn-info btn-sm" style="float:left;"><i class="fa fa-search"></i></button>-->
                                        </div>


                                        <div class="form-group col-md-12"></div>

                                        <div class="form-group col-md-12">
                                            <label>上传文件附件：</label>
                                            {:upload_m('uploadfile','files',$attr,'上传文件附件')}
                                        </div>

                                    </div>
                                    
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                            
                           
                            <div style="width:100%; text-align:center;">
                            <button type="submit" class="btn btn-info btn-lg" id="lrpd">发起工单</button>
                            </div>
                        </div><!--/.col (right) -->
                    </div>   <!-- /.row -->
                    </form>
                </section><!-- /.content -->
                
            </aside><!-- /.right-side -->
			
  </div>
</div>

<include file="Index:footer2" />

    <script type="text/javascript">

        function task(obj){
            var i = parseInt($('#task_val').text())+1;

            var header = '<div class="tasklist" id="task_ti_'+i+'"><a class="aui_close" href="javascript:;" onClick="del_timu(\'task_ti_'+i+'\')">×</a><div class="col-md-12 pd"><label class="titou"><strong>工单受理组/人<span class="tihao">'+i+'</span></strong>:</label>';
            var days = '<div class="form-group col-md-12 pd"> <select name="exe_info['+i+'][exe_dept_id]" id="group_'+i+'" onchange="check_group('+i+')" class="form-control"> <option value="" disabled selected>请选择受理组</option> <foreach name="group" item="v"> <option value="{$v.id}">{:tree_pad($v[\'level\'])}{$v.role_name}</option> </foreach> </select> </div> <div id="in_group'+i+'" style="display: none"> <div class="form-group col-md-12"> <label>项目类型：</label> <select name="exe_info['+i+'][wd_id]" id="pro_tit'+i+'" onchange="show_dept('+i+')" class="form-control"> <option value="" disabled selected>请选择项目类型</option> </select> </div> <div id="dept'+i+'"> <div class="form-group col-md-6"> <label>工单类型：</label><input type="text" name="exe_info['+i+'][type]" id= "type'+i+'" class="form-control" readonly /> </div> <div class="form-group col-md-6"> <label>完成所需时间：</label><input type="text" name="exe_info['+i+'][use_time]" id="use_time'+i+'" class="form-control" readonly /> </div></div></div>';
            var footer = '</div>';
            var html = header+days+footer;

            $('#task_timu').append(html);
            $('#task_val').html(i);
            //重编题号
            $('.tihao').each(function(index, element) {
                var no = index*1+1;
                $(this).text(no);
            });
        }

        //移除题目
        function del_timu(obj){
            $('#'+obj).remove();
            $('.tihao').each(function(index, element) {
                var no = index*1+1;
                $(this).text(no);
            });
        }

        //获取所有用户组
        function check_group(a){
            var id = $("#group_"+a+"").val();
            $.ajax({
                type:"POST",
                url:"{:U('Ajax/member')}",
                data:{id:id},
                success:function(msg){
                    if (msg == 0){
                        $("#in_group"+a+"").hide();
                    }else {
                        $("#in_group"+a+"").show();
                        $("#pro_tit"+a+"").empty();
                        var count = msg.length;
                        var i= 0;
                        var b="";
                        b+='<option value="" disabled selected>请选择项目类型</option>';
                        for(i=0;i<count;i++){
                            b+="<option value='"+msg[i].id+"'>"+msg[i].pro_title+"</option>";
                        }
                        $("#pro_tit"+a+"").append(b);
                    }
                }
            })
        }

        //获取该用户组的工单项列表
        function show_dept(a){
            var pro_tit = 'pro_tit'+a;
            var id = $("#pro_tit"+a+"").val();
            $.ajax({
                type:"POST",
                url:"{:U('Ajax/dept')}",
                data:{id:id},
                success:function(msg){
                    $("#type"+a+"").val(msg.type_res);
                    $("#use_time"+a+"").val(msg.use_time+"个工作日");
                }
            })
        }

        //
        $(document).ready(function(e) {
            var keywords = <?php echo $userkey; ?>;

            $(".keywords_bkpr").autocomplete(keywords, {
                matchContains: true,
                highlightItem: false,
                formatItem: function(row, i, max, term) {
                    return '<span style=" display:none">'+row.pinyin+'</span>'+row.text;
                },
                formatResult: function(row) {
                    return row.user_name;
                }
            }).result(function(event, item) {
                $('#bkpr').val(item.id);
            });

        })
    </script>