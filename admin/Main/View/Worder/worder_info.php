<include file="Index:header2" />

    <aside class="right-side">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>工单详情</h1>
            <ol class="breadcrumb">
                <li><a href="{:U('Index/index')}"><i class="fa fa-home"></i> 首页</a></li>
                <li><a href="{:U('Worder/worder_list')}"><i class="fa fa-gift"></i> 工单管理</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        
            <div class="row">
                 <!-- right column -->
                <div class="col-md-12">
                     
                     
                     
                     <div class="box box-warning" style="margin-top:15px;">
                        <div class="box-header">
                            <h3 class="box-title">
                             工单信息
                            </h3>
                            <?php /*if($row['contract_id']){ */?><!--
                            <h3 class="box-title pull-right" style="font-weight:normal; color:#333333;"><span class="green">工单编号：{$row.contract_id}</span></h3>
                            --><?php /*} */?>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="content">
                            	<div class="form-group col-md-12">
                                    <h2 style="font-size:16px; color:#ff3300; border-bottom:2px solid #dedede; padding-bottom:10px;">工单信息</h2>
                                </div>
                                <div class="form-group col-md-12">
                                <table width="100%" id="font-14" rules="none" border="0" cellpadding="0" cellspacing="0" style="margin-top:-15px;">
                                    <tr>
                                        <td colspan="2">工单名称：{$info.worder_title}</td>
                                        <td>工单类型 : {$info.type}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">工单内容：{$info.worder_content}</td>
                                    </tr>
                                    <tr>
                                        <td width="33.33%">工单发起者姓名：{$info.ini_user_name}</td>
                                        <td width="33.33%">工单发起者职务：{$info.ini_dept_name}</td>
                                        <td width="33.33%">工单发起时间：{$info.create_time|date='Y-m-d H:i:s',###}</td>

                                    </tr>
                                    <tr>
                                        <td width="33.33%">工单执行者姓名：{$info.exe_user_name}</td>
                                        <td width="33.33%">工单执行者职务：{$info.exe_dept_name}</td>
                                        <if condition="$info.response_time neq 0">
                                            <td width="33.33%">工单响应时间：{$info.response_time|date='Y-m-d H:i:s',###}</td>
                                            <else />
                                            <td width="33.33%">工单响应时间：<span class="red">未响应</span></td>
                                        </if>
                                    </tr>
                                    <tr>
                                    	<td width="33.33%">工单状态：{$info.sta}</td>
                                        <td width="33.33%">工单计划完成时间：{$info.plan_complete_time|date='Y-m-d H:i:s',###}</td>
                                        <if condition="$info.complete_time neq 0">
                                            <td width="33.33%">工单实际完成时间：{$info.complete_time|date='Y-m-d H:i:s',###}</td>
                                            <else />
                                            <td width="33.33%">工单实际完成时间：未完成</td>
                                        </if>
                                    </tr>
                                    <if condition="$info['exe_reply_content'] neq null">
                                        <tr><td colspan="3">工单执行人响应工单回复：{info.exe_reply_content}</td></tr>
                                    </if>
                                    <if condition="$info['exe_reply_content'] neq null">
                                        <tr><td colspan="3">工单执行人完成工单回复：{info.exe_reply_content}</td></tr>
                                    </if>
                                </table>
                                </div>
                                
                                <div class="form-group col-md-12">
                                    <h2 style="font-size:16px; color:#ff3300; border-bottom:2px solid #dedede; padding-bottom:10px;">工单相关文件</h2>
                                </div>
                                <div class="form-group col-md-12">
                                	<div id="showimglist">
                                        <foreach name="atts" key="k" item="v">
											<?php if(isimg($v['filepath'])){ ?>
                                            <a href="{$v.filepath}" target="_blank" style="margin-right:10px;"><div class="fileext"><?php echo isimg($v['filepath']); ?></div></a>
                                            <?php }else{ ?>
											<a href="{$v.filepath}" target="_blank" style="margin-right:10px;"><img src="{:thumb($v['filepath'],100,100)}" style="margin-right:15px; margin-top:15px;"></a>
											<?php } ?>
                                        </foreach>
                                    </div>
                                </div>

                            </div>
                            
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div><!--/.col (right) -->
                
                
                <div class="col-md-12">

                    <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">工单确认信息</h3>
                            <h3 class="box-title pull-right" style="font-weight:normal; color:#333333;">
                                <?php  if($info['assign_name']){ ?>
                                    负责人：{info.assign_name}
                                <?php  }else{ ?>
                                    <?php  if(rolemenu(array('Worder/assign_user'))){ ?>
                                        <a href="javascript:;" onclick="javascript:assign('{:U('Worder/assign_user',array('opid'=>$op['op_id']))}','指派项目线路行程负责人');" style="color:#09F;">指派负责人</a>
                                    <?php  }else{ ?>
                                        暂未指派负责人
                                    <?php  } ?>

                                <?php  } ?>
                            </h3>
                        </div>
                        <div class="box-body" style="padding-top:20px;" id="form_tip">
                        	
                            <?php /*if(rolemenu(array('Worder/assign_user')) and $info['exe_user_id'] == cookie('userid')){ */?>
                            <?php if(rolemenu(array('Worder/assign_user'))){ ?>

                           	<form method="post" action="{:U('Worder/assign_user')}" name="myform" id="save_huikuan">
                            <input type="hidden" name="do_exe" value="1">
                            <input type="hidden" name="id" value="{$row.id}">
                            <input type="hidden" name="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                                
                            <div class="form-group col-md-12" style="margin-top:10px;">
                                <div class="checkboxlist" id="applycheckbox" style="margin-top:10px;">
                                <input type="radio" name="info[status]" value="1" <?php if($row['status']==1){ echo 'checked';} ?> > 确认通过
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="info[status]" value="-1" <?php if($row['status']==-1){ echo 'checked';} ?> > 拒绝该工单

                                </div>
                            </div>

                            <!--<div class="form-group col-md-4" style="margin-top:10px;"></div>-->

                            <div class="form-group col-md-12">
                            	<div style="border-top:1px solid #dedede; margin-top:15px; padding-top:20px;">
                                    <label>工单类型</label>
                                    <select class="form-control" name="info[wd_id]" >
                                        <option value="" disabled selected>选择工单类型</option>
                                        <foreach name="dept_list" item="v">
                                            <option value="{$v.id}" <?php if($row['gbs']==1){ echo 'selected';} ?> >{$v.pro_title}</option>
                                        </foreach>
                                        <!--<option value="1" <?php /*if($row['gbs']==1){ echo 'selected';} */?> >已返回综合部</option>
                                        <option value="2" <?php /*if($row['gbs']==2){ echo 'selected';} */?> >已返回财务部</option>-->
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label>审核意见</label>
                                <textarea class="form-control" name="info[exe_reply_content]" >{$row.exe_reply_content}</textarea>
                            </div>

                            <div class="form-group col-md-12"  style="margin-top:50px; padding-bottom:20px; text-align:center;">
                                <button class="btn btn-success btn-lg">确认提交</button>
                            </div>
                           	</form>
                            
                            <?php }else{ ?>
							<div class="content">
                                <table width="100%" id="font-14" rules="none" border="0" cellpadding="0" cellspacing="0">
                                   
                                    <tr>
                                        <td width="33.33%">确认状态：{$row.strstatus}</td>
                                        <td width="33.33%">确认者：{$row.confirm_user_name}</td>
                                        <td width="33.33%">确认时间：{$row.confirm_time|date='Y-m-d H:i:s',###}</td>
                                        
                                    </tr>
                                    <tr>
                                        <td width="33.33%">是否盖章： {$row.strseal}</td>
                                        <td width="33.33%">工单编号：{$row.contract_id}</td>
                                        <td width="33.33%">返回状态：{$row.gbstatus}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">审核意见：{$row.confirm_remarks}</td>
                                    </tr>
                                </table>
                            </div>	
							<?php } ?>
                            
                            <div class="form-group">&nbsp;</div>
                                   
                        </div>
                    </div><!-- /.box -->
                </div><!--/.col (right) -->
                
                
                <?php if(rolemenu(array('Finance/save_huikuan'))){ ?>
                <div class="col-md-12">
                    <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">项目回款</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        	<?php if($huikuanlist){ ?>
                            <form method="post" action="{:U('Finance/save_huikuan')}" name="myform" id="save_huikuan">
                            <input type="hidden" name="dosubmint" value="1">
                            <input type="hidden" name="info[op_id]" value="{$op.op_id}">
                            <input type="hidden" name="info[name]" value="{$op.project}">
                            <input type="hidden" name="info[cid]" value="{$row.id}">
                            <input type="hidden" name="settlement" value="{$settlement.id}" />
                            <input type="hidden" name="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                            <div class="content" >
                                <div style="width:100%; float:left;">
                                	<div class="form-group col-md-12">
                                        <label>回款计划：</label>
                                        <select class="form-control" name="info[payid]">
                                            <foreach name="huikuanlist" key="k" item="v">
                                                <option value="{$v.id}">{$row.contract_id} / 第{$v.no}笔 / {$v.amount}元 / {$v.remark}</option>
                                            </foreach>
                                        </select>
                                    </div>
                                	
                                    <div class="form-group col-md-4">
                                        <label>本次回款金额：</label>
                                        <input type="text" name="info[huikuan]" id="renshu" class="form-control" value=""/>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label>收款方式：</label>
                                        <select class="form-control" name="info[type]">
                                            <option value="">选择</option>
                                            <option value="转账">转账</option>
                                            <option value="支票">支票</option>
                                            <option value="现金">现金</option>
                                            <option value="其他">其他</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label>收款日期：</label>
                                        <input type="text" name="info[huikuan_time]" class="form-control inputdate" value=""/>
                                    </div>
                                    
                                    <div class="form-group col-md-12">
                                        <label>备注：</label>
                                        <input type="text" name="info[remark]" id="remark" class="form-control" value=""/>
                                    </div>
                                    
                                    <div class="form-group col-md-12"  style="margin-top:50px; padding-bottom:20px; text-align:center;">
                                        <button class="btn btn-success btn-lg">保存并提交审核</button>
                                    </div>
                                </div>
                                
                            </div>
                            
                            </form>  
                            <?php }else{ ?>
                            <?php if($pays){ ?>
                            <div class="content" ><span style="padding:20px 0; float:left; clear:both; text-align:center; text-align:center; width:100%;">已全部回款</span></div>
                            <?php }else{ ?>
                            <div class="content" ><span style="padding:20px 0; float:left; clear:both; text-align:center; text-align:center; width:100%;">尚未制定回款计划</span></div>
                            <?php }} ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <?php if($huikuan){ ?>
                <div class="col-md-12">    
                     <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">回款记录</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="content" style="padding-top:0px;">
                                 <table class="table table-striped" id="font-14-p">
                                    <thead>
                                        <tr>
                                            <th width="120">回款金额</th>
                                            <th width="120">回款方式</th>
                                            <th width="180">申请时间</th>
                                            <th>回款备注</th>
                                            
                                            <th width="120">审批状态</th>
                                            <th width="120">审批者</th>
                                            <th width="">审批说明</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <foreach name="huikuan" key="k" item="v">
                                            <tr class="userlist">
                                                <td>&yen; {$v.huikuan}</td>
                                                <td>{$v.type}</td>
                                                <td>{$v.create_time|date='Y-m-d H:i:s',###}</td>
                                                <td>{$v.remark}</td>
                                                
                                                <td>{$v.showstatus}</td>
                                                <td>{$v.show_user}</td>
                                                <td>{$v.show_reason}</td>
                                            </tr> 
                                        </foreach>
                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                    </div>
                </div> 
                <?php } ?>           
                
            </div>   <!-- /.row -->
            
        </section><!-- /.content -->
        
    </aside><!-- /.right-side -->
			
  </div>
</div>

<include file="Index:footer2" />

<script type="text/javascript">
    //指派责任人
    function assign(url,title){
        art.dialog.open(url,{
            lock:true,
            title: title,
            width:800,
            height:500,
            okValue: '提交',
            ok: function () {
                this.iframe.contentWindow.gosubmint();
                return false;
            },
            cancelValue:'取消',
            cancel: function () {
            }
        });
    }
</script>