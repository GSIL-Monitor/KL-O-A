<include file="Index:header2" />



            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        {$_pagetitle_}
                        <small>{$_pagedesc_}</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="{:U('ScienceRes/res')}"><i class="fa fa-gift"></i> {$_pagetitle_}</a></li>
                        <li class="active">{$_action_}</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                         <!-- right column -->
                        <div class="col-md-12">
                            <!-- general form elements disabled -->
                            <form method="post" action="{:U('ScienceRes/addres')}" name="myform" id="myform">
                            <div class="box box-warning">
                                <div class="box-header">
                                    <h3 class="box-title">{$_action_}</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    
                                        <input type="hidden" name="dosubmit" value="1" />
                                        <input type="hidden" name="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                                        <if condition="$row"><input type="hidden" name="id" value="{$row.id}" /></if>
                                        <!-- text input -->
                                        
                                        <div class="form-group col-md-12">
                                            <label>资源名称</label>
                                            <input type="text" name="info[title]" id="title" value="{$row.title}"  class="form-control" />
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label>资源类型</label>
                                            <select  class="form-control"  name="info[kind]" required>
                                            <foreach name="kinds" item="v">
                                                <option value="{$v.id}" <?php if ($row && ($v['id'] == $row['kind'])) echo ' selected'; ?> >{$v.name}</option>
                                            </foreach>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label>联系人</label>
                                            <input type="text" name="info[contacts]" id="contacts"   value="{$row.contacts}" class="form-control" />
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label>联系人职务</label>
                                            <input type="text" name="info[contacts_tel]" id="contacts_tel"   value="{$row.contacts_tel}" class="form-control" />
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label>所在地区</label>
                                            <input type="text" name="info[diqu]" id="diqu"   value="{$row.diqu}" class="form-control" />
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>地址</label>
                                            <input type="text" name="info[address]" id="address"   value="{$row.address}" class="form-control" />
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>电话</label>
                                            <input type="text" name="info[tel]" id="tel" value="{$row.tel}"  class="form-control" />
                                        </div>
                                        
                                        <div class="form-group col-md-12">
                                            <label><a href="javascript:;" onClick="selectkinds()">选择适用项目类型</a> <span style="color:#999999">(选择后您可以点击删除)</span></label>
                                            <div id="pro_kinds_text">
                                            
                                            <foreach name="deptlist" item="v">
                                                 <span class="unitbtns" title="点击删除该选项"><input type="hidden" name="business_dept[]" value="{$v.id}"><button type="button" class="btn btn-default btn-sm">{$v.name}</button></span>
                                            </foreach>
                                            
                                            </div>
                                        </div>
                                        
                                        <div class="form-group col-md-12">
                                            <label>介绍</label>
                                            <?php 
											 echo editor('content',$row['content']); 
											 ?>
                                        </div>
                                        
                                        
                                        <div class="form-group">&nbsp;</div>
                                        

                                    
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                            <div id="formsbtn">
                            	<button type="submit" class="btn btn-info btn-lg" id="lrpd">保存</button>
                            </div>
                            </form>
                        </div><!--/.col (right) -->
                    </div>   <!-- /.row -->
                </section><!-- /.content -->
                
            </aside><!-- /.right-side -->

  </div>
</div>
            
<include file="Index:footer2" />
<script type="text/javascript"> 

	$(document).ready(function() {	

		
		
		closebtns();
		

	});

	
	//选择适用项目类型
	function selectkinds() {
		art.dialog.open('<?php echo U('Product/select_kinds'); ?>',{
			lock:true,
			title: '选择适用项目类型',
			width:600,
			height:400,
			okValue: '提交',
			fixed: true,
			ok: function () {
				var origin = artDialog.open.origin;
				var data = this.iframe.contentWindow.gosubmint();
				var i=0;
				var str = "";
				for (i=0; i<data.length; i++) {
				    str = '<span class="unitbtns" title="点击删除该选项"><input type="hidden" name="business_dept[]" value="'+data[i].id+'"><button type="button" class="btn btn-default btn-sm">'+data[i].kind+'</button></span>';
                    	    $('#pro_kinds_text').append(str);
				}
				closebtns();
			},
			cancelValue:'取消',
			cancel: function () {
			}
		});	
	}
	
	
	function closebtns(){
	    $('.unitbtns').each(function(index, element) {
              $(this).click(function(){
		       $(this).remove();
          	  })  
          });	
	}

</script>	
