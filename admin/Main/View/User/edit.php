		<include file="Index:header" />
        
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <include file="Index:menu" />

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>修改资料</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="{:U('User/index')}">用户管理</a></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                         <!-- right column -->
                        <div class="col-md-6">
                            <!-- general form elements disabled -->
                            <div class="box box-warning">
                                <div class="box-header">
                                    <h3 class="box-title">编辑角色</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    	<form method="post" action="{:U('User/edit')}" name="basic_validate" id="basic_validate" enctype="multipart/form-data" novalidate />
                               			<input type="hidden" name="dosubmit" value="1" />
                                        <input type="hidden" name="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                                		<input type="hidden" name="id" value="{$row.id}" />
                                        <!-- text input -->
                                        <?php if(C('RBAC_SUPER_ADMIN') == cookie('username')){?>
                                        <div class="form-group">
                                            <label>角色</label>
                                            <select class="form-control" name="roles">
                                                <foreach name="roles" item="v">
                                                <option value="{$v.id}" <?php if($row['roleid']==$v['id']){ ?> selected <?php } ?>>{$v.remark}</option>
                                                </foreach>
                                            </select>
                                        </div>
                                        <?php }else{ ?>
                                        <input type="hidden" name="roles" value="{$row.roleid}">
                                        <?php } ?>
                                        
                                        
                                        <div class="form-group">
                                            <label>用户名</label>
                                            <input type="text" class="form-control" value="{$row.username}" disabled="">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>使用者</label>
                                            <input type="text" name="info[nickname]" class="form-control" value="{$row.nickname}"/>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>邮箱</label>
                                            <input type="text" name="info[email]" class="form-control" value="{$row.email}" id="email"/>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>手机号</label>
                                            <input type="text" name="info[mobile]" class="form-control" value="{$row.mobile}"/>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>角色状态</label>
                                            <select class="form-control" name="info[status]">
                                                <option <?php if($row === false or $row['status']==0){ echo 'selected';}?> value="0">启用</option>
                                                <option <?php if($row !== false and $row['status'] == 1){ echo 'selected';}?> value="1">禁用</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<button type="submit" class="btn btn-success">保存</button>
                                        </div>

                                    </form>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div><!--/.col (right) -->
                    </div>   <!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
		
        <include file="Index:footer" />
        
        <script type="text/javascript">
		$(document).ready(function(){
			$.formValidator.initConfig({autotip:true,formid:"basic_validate",onerror:function(msg){}});
			$("#username").formValidator({onshow:"4-20位字符，可由英文、数字组成",onfocus:"4-20位字符，可由英文、数字组成"}).inputValidator({min:4,max:20,onerror:"4-20位字符，可由英文、数字组成"}).regexValidator({regexp:"ps_username",datatype:"enum",onerror:"4-20位字符，可由中文、英文、数字组成"}).ajaxValidator({
				type : "get",
				url : "op.php",
				data :"m=User&a=public_checkname_ajax",
				datatype : "html",
				async:'false',
				success : function(data){
					if( data == "1" ) {
						return true;
					} else {
						return false;
					}
				},
				buttons: $("#dosubmit"),
				onerror : "该用户名不可用。",
				onwait : "请稍候..."
			});
			/*
			$("#email").formValidator({onshow:"请输入邮箱",onfocus:"请输入邮箱"}).inputValidator({min:4,max:20,onerror:"请输入邮箱"}).regexValidator({regexp:"email",datatype:"enum",onerror:"请输入邮箱"}).ajaxValidator({
				type : "get",
				url : "op.php",
				data :"m=User&a=public_checkmail_ajax",
				datatype : "html",
				async:'false',
				success : function(data){
					if( data == "1" ) {
						return true;
					} else {
						return false;
					}
				},
				buttons: $("#dosubmit"),
				onerror : "该邮箱已被注册。",
				onwait : "请稍候..."
			});
			*/
			$("#password_1").formValidator({onshow:"6-20位字符（字符、数字、符号）组成",onfocus:"6-20位字符（字符、数字、符号）组成"}).inputValidator({min:6,max:20,onerror:"6-20位字符（字符、数字、符号）组成"});
			$("#password_2").formValidator({onshow:"请再次输入密码",onfocus:"请再次输入密码",oncorrect:"&nbsp;"}).inputValidator({min:1,onerror:"确认密码不能为空"}).compareValidator({desid:"password_1",operateor:"=",onerror:"与上面密码不一致，请重新输入"});
	
		});
	</script>
