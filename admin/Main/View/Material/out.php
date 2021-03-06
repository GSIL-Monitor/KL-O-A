<include file="Index:header2" />





            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>物资出库申请</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="{:U('Material/stock')}"><i class="fa fa-gift"></i> 物资库存</a></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                         <!-- right column -->
                        <div class="col-md-12">
                            <!-- general form elements disabled -->
                            <form method="post" action="{:U('Material/out')}" name="myform" id="myform">
                            <div class="box box-warning">
                                <div class="box-header">
                                    <h3 class="box-title">物资出库申请</h3>
                                    <div class="pull-right box-tools">
                                        <if condition="rolemenu(array('Material/out_record'))">
                                        <a href="{:U('Material/out_record')}" class="btn btn-info btn-sm"><i class="fa fa-bars"></i> 出库记录</a>
                                        </if>
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    
                                        <input type="hidden" name="dosubmit" value="1" />
                                        <input type="hidden" name="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                                        <if condition="$row"><input type="hidden" name="id" value="{$row.id}" /></if>
                                        <!-- text input -->
                                        <div class="form-group col-md-3">
                                            <label>出库类型</label>
                                            <select  class="form-control" name="info[type]" id="intotype" onChange="selecttype();">
                                                <option value="0" <?php if ($row['type']==0) echo ' selected'; ?> >正常出库</option>
                                                <option value="1" <?php if ($row['type']==1) echo ' selected'; ?> >物资报废</option>
                                                <option value="2" <?php if ($row['type']==2) echo ' selected'; ?> >赠送物资</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label>团号</label>
                                            <input type="text" name="info[order_id]" id="order_id" value="{$row.order_id}"  class="form-control" />
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label>领取人</label>
                                            <input type="text" name="info[receive_liable]" id="receive_liable" value="{$row.receive_liable}"  class="form-control" />
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label>库房负责人</label>
                                            <input type="text" name="info[storehouse_liable]" id="storehouse_liable" value="{$row.storehouse_liable}"  class="form-control" />
                                        </div>
                                       
                                        
                                      
                                        
                                        <div class="form-group">&nbsp;</div>

                                    
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                            
                            
                            
                            <div class="box box-warning">
                                <div class="box-header">
                                    <h3 class="box-title">物资清单</h3>
                                </div>
                                <div class="box-body">
                                    <div class="content">
                                        <div class="content" style="padding-top:0px;">
                                            <div id="material">
                                                <div class="userlist">
                                                    <div class="unitbox material_name">物资名称</div>
                                                    <div class="unitbox" style="width:50px;">库存</div>
                                                    <div class="unitbox">数量</div>
                                                    <div class="unitbox">参考单价</div>
                                                    <div class="unitbox longinput">合计</div>
                                                    <div class="unitbox longinput">备注</div>
                                                </div>
                                                
                                                <div class="userlist" id="material_id">
                                                    <span class="title">1</span>
                                                    <input type="hidden" class="form-control material_id" name="material[0][material_id]" value="0">
                                                    <input type="text" class="form-control material_name" name="material[0][material]">
                                                    <span class="stock">0</span>
                                                    <input type="text" class="form-control amount" name="material[0][amount]">
                                                    <input type="text" class="form-control unit_price" name="material[0][unit_price]">
                                                    <input type="text" class="form-control longinput totalsum" name="material[0][total]" readonly>
                                                    <input type="text" class="form-control longinput" name="material[0][remarks]">
                                                    <!-- <a href="javascript:;" class="btn btn-danger btn-flat" onclick="delbox('material_id')">删除</a> -->
                                                </div>
                                               
                                            </div>
                                            <div id="material_val">0</div>
                                            
                                            <a href="javascript:;" class="btn btn-success btn-sm" style="margin-top:15px;" onClick="add_material()"><i class="fa fa-fw fa-plus"></i> 新增物资</a> 
                                            
                                            <div class="form-group">&nbsp;</div>
                                        </div>
                                       
                                    </div>
                                </div>
                            </div>
                            </form>
                            
                            <div id="formsbtn">
                            	<button type="button" class="btn btn-success btn-lg" id="lrpd" onClick="onsubmint()">申请出库</button>
                            </div>
                            
                        </div><!--/.col (right) -->
                    </div>   <!-- /.row -->
                </section><!-- /.content -->
                
            </aside><!-- /.right-side -->
			
  </div>
</div>

<include file="Index:footer2" />
<script type="text/javascript"> 
	
	
	$(document).ready(function() {	
		keywords();
		total();
	})
	
	//关键字联想
	function keywords(){
		var keywords = <?php echo $keywords; ?>;
		$(".material_name").autocomplete(keywords, {
			 matchContains: true,
			 highlightItem: false,
			 formatItem: function(row, i, max, term) {
			 	 return '<span style=" display:none">'+row.pinyin+'</span>'+row.material;
			 },
			 formatResult: function(row) {
				 return row.material;
			 }
		});	
	}
	
	//新增物资
	function add_material(){
		var i = parseInt($('#material_val').text())+1;

		var html = '<div class="userlist" id="material_'+i+'"><span class="title"></span><input type="hidden" class="form-control material_id" name="material['+i+'][material_id]" value="0"><input type="text" class="form-control material_name" name="material['+i+'][material]"><span class="stock">0</span><input type="text" class="form-control amount" name="material['+i+'][amount]"><input type="text" class="form-control unit_price" name="material['+i+'][unit_price]"><input type="text" class="form-control longinput totalsum" name="material['+i+'][total]" value="" readonly><input type="text" class="form-control longinput" name="material['+i+'][remarks]"><a href="javascript:;" class="btn btn-danger btn-flat" onclick="delbox(\'material_'+i+'\')">删除</a></div>';
		$('#material').append(html);	
		$('#material_val').html(i);
		orderno();
		total();
		keywords();
	}
	
	//编号
	function orderno(){
		$('#mingdan').find('.title').each(function(index, element) {
            $(this).text(parseInt(index)+1);
        });
		$('#material').find('.title').each(function(index, element) {
            $(this).text(parseInt(index)+1);
        });	
	}
	
	//移除
	function delbox(obj){
		$('#'+obj).remove();
		orderno();
	}
	
	//更新价格与数量
	function total(){
		$('.userlist').each(function(index, element) {
            $(this).find('.unit_price').blur(function(){
				var cost = $(this).val();
				var amount = $(element).find('.amount').val();
				$(element).find('.totalsum').val(accMul(cost,amount));	
			});
			
			$(this).find('.material_name').blur(function(){
				//获取物资数据
				var mm = $(this).parent().find('.material_name').val();
				if(mm){
					$.ajax({
		               type: "GET",
		               url: "<?php echo U('Ajax/material'); ?>",
					   dataType:'json', 
		               data:{keywords:mm},
		               success:function(data){
		                   if(data.id){
								var acost = data.cost;
								$(element).find('.unit_price').val(acost);
								$(element).find('.material_id').val(data.id);
								$(element).find('.stock').text(data.stock);
						   }
		               }
		           });
				}	
			})
			
			$(this).find('.amount').blur(function(){
				var amount = parseInt($(this).val());
				var cost = $(element).find('.unit_price').val();
				var stock = parseInt($(element).find('.stock').text());
				if(amount > stock){
					alert('库存不足');
					$(this).val(stock)
					$(element).find('.totalsum').val(accMul(cost,stock));
				}else{
					$(element).find('.totalsum').val(accMul(cost,amount));
				}
			});
			
        });		
	}
	
	function onsubmint(){
		
		var type = 0;
		$('.material_id').each(function(index, element) {	
			if($(this).val()==0){
				type += 1;
			}
		});
		$('.amount').each(function(index, element) {	
			if($(this).val()==0){
				type += 1;
			}
		});
		if(type==0){
			$('#myform').submit();
		}else{
			alert('请确认物资和物资出库数量是否正确');	
		}
		
	}
	
	

</script>	

     


