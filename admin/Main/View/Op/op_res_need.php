<script type="text/javascript">
    $(document).ready(function() {
        $("#res_need_table").hide();
        $('#after_lession').hide();

        var op_kind     = <?php echo $op_kind;?>;
        if (op_kind == 60) {
            $('#res_need_table').html('');
        }else{
            $('#after_lession').html('');
        }
    })
</script>

    <form method="post" action="<?php echo U('Op/public_save'); ?>" id="res_need_table">
    <input type="hidden" name="dosubmint" value="1">
    <input type="hidden" name="opid" value="{$op.op_id}">
    <input type="hidden" name="savetype" value="11">
    <input type="hidden" name="info[ini_user_id]" value="{:session('userid')}" readonly>
    <input type="hidden" name="info[ini_user_name]" value="{:session('nickname')}" readonly>
        <div class="row">
            <!-- right column -->
            <div class="form-group col-md-12">

                        <div class="content">

                            <div class="form-group col-md-4">
                                <label>需求部门：</label><input type="text" name="info[department]" value="{$resource['department']}" class="form-control" />
                            </div>

                            <div class="form-group col-md-4">
                                <label>客户单位：</label><input type="text" name="info[client]" value="<?php echo $resource['client']?$resource['client']:$op['customer'] ?>" class="form-control" />
                            </div>

                            <div class="form-group col-md-4">
                                <label>业务人员：</label><input type="text" name="info[service_name]" value="{$op['sale_user']}" class="form-control" readonly />
                            </div>

                            <div class="form-group col-md-4">
                                <label>实施对象：</label><input type="text" name="info[imp_obj]" value="<?php echo $resource['imp_obj']?$resource['imp_obj']:$apply_to[$op['apply_to']] ; ?>" class="form-control" />
                            </div>

                            <div class="form-group col-md-4">
                                <label>活动人数：</label><input type="text" name="info[number]" value="{$resource['number']}" class="form-control" />
                            </div>

                            <div class="form-group col-md-4">
                                <label>实施时间：</label><input type="text" name="info[in_time]" value="<?php echo $resource['in_time']?date('Y-m-d H:i',$resource['in_time']): date('Y-m-d H:i:s',$confirm['dep_time']); ?>" class="form-control inputdatetime" />
                            </div>

                            <div class="form-group col-md-4">
                                <label>活动时长（天）：</label><input type="text" name="info[use_time]" value="<?php echo $resource['use_time']?$resource['use_time']:$confirm['days'] ; ?>" class="form-control" />
                            </div>

                            <div class="form-group col-md-4">
                                <label>活动地点：</label><input type="text" name="info[addr]" value="<?php echo $resource['addr']?$resource['addr']:$confirm['address']; ?>" class="form-control" />
                            </div>

                            <!--<div class="form-group col-md-4">
                                <label>提交时间：</label><input type="text" name="info[money]" value="{$resource['money']}" class="form-control" />
                            </div>-->

                            <div class="form-group col-md-4">
                                <!--<label>接收人员：</label><input type="text" class="form-control" name="info[exe_user_name]"  value="{$resource['exe_user_name']}" id="exe_u_name" />
                                <input type="hidden" name="info[exe_user_id]" id="exe_u_id"  value="{$resource['exe_user_id']}" />-->
                                <label>接收人员：</label><input type="text" class="form-control" name="info[exe_user_name]"  value="{$men['nickname']}" id="exe_u_name" />
                                <input type="hidden" name="info[exe_user_id]" id="exe_u_id"  value="{$men['id']}" />
                            </div>

                            <div class="form-group col-md-12">
                                <h2 class="res_need_h2">院所、场馆、场地</h2>
                            </div>

                            <div class="form-group col-md-12">
                                <label>活动需求：</label>
                                <foreach name="act_need" key="k" item="v">
                                <span class="checkboxs_100"><input type="checkbox" name="act_need[]" <?php if(in_array($v,$act_needs)){ echo 'checked';} ?>  value="{$v}">&nbsp; {$v}</span>&#12288;&#12288;
                                </foreach>
                            </div>

                            <div class="form-group col-md-12">
                                <label>资源需求：</label><input type="text" name="info[res_need]" value="{$resource['res_need']}" class="form-control" />
                            </div>

                            <div class="form-group col-md-12">
                                <label>具体需求描述：</label><textarea class="form-control" value="{$resource['res_special_need']}"  name="info[res_special_need]">{$resource['res_special_need']}</textarea>
                            </div>

                            <div class="form-group col-md-12">
                                <h2 class="res_need_h2" >课程与活动</h2>
                            </div>

                            <div class="form-group col-md-6">
                                <label>课程活动名称：</label><input type="text" name="info[les_name]" value="{$resource['les_name']}" class="form-control" />
                            </div>

                            <div class="form-group col-md-6">
                                <label>学科（如动物、植物、微生物、天文、地质等）：</label><input type="text" name="info[subject]" value="{$resource['subject']}" class="form-control" />
                            </div>

                            <div class="form-group col-md-12">
                                <label>具体需求描述（课程描述、是否有动手活动及预算；集合时间、集合地点等）：</label><textarea class="form-control"  name="info[les_special_need]">{$resource['les_special_need']}</textarea>
                            </div>


                            <div class="form-group col-md-12">
                                <h2 class="res_need_h2" >小课题</h2>
                            </div>

                            <div class="form-group col-md-12">
                                <div>小课题领域</div>
                                <foreach name="task_field" key="k" item="v">
                                    <span class="checkboxs_255"><input type="checkbox" name="task_field[]" <?php if(in_array($v,$task_fields)){ echo 'checked';} ?>  value="{$v}">&nbsp; {$v}</span>&#12288;&#12288;
                                </foreach>
                            </div>

                            <div class="form-group col-md-12">
                                <div>资源方性质 :</div>
                                <span class="checkboxs_255"><input type="radio" name="info[task_type]" <?php if($resource['task_type']==1){ echo 'checked';} ?>  value="1">&nbsp; 中科院院内</span>&#12288;&#12288;
                                <span class="checkboxs_255"><input type="radio" name="info[task_type]" <?php if($resource['task_type']==2){ echo 'checked';} ?>  value="2">&nbsp; 中科院院外</span>&#12288;&#12288;
                                <span class="checkboxs_255"><input type="radio" name="info[task_type]" <?php if($resource['task_type']==3){ echo 'checked';} ?>  value="3">&nbsp; 均可</span>&#12288;&#12288;
                            </div>

                            <div class="form-group col-md-12" id="is_custom">
                                <label>是否制定资源方：</label>
                                <span class="checkboxs_400"><input type="radio" name="info[custom]" <?php if($resource['custom']==1){ echo 'checked';} ?>  value="1">&nbsp; 是<span id="custom">，资源方名称：<input type="text" name="info[res_name]" value="{$resource['res_name']}" style="border: none; border-bottom: solid 1px;"></span></span>&#12288;&#12288;
                                <span class="checkboxs_255"><input type="radio" name="info[custom]" <?php if($resource['custom']==0){ echo 'checked';} ?>  value="0">&nbsp; 否</span>&#12288;&#12288;
                            </div>

                            <div class="form-group col-md-12">
                                <label>具体需求描述（课题周期、是否参赛、预算价格等）：</label><textarea class="form-control"  name="info[task_special_need]">{$resource['task_special_need']}</textarea>
                            </div>



                        </div>

                <div style="width:100%; text-align:center;">
                    <button type="submit" class="btn btn-info btn-lg" id="lrpd">提交</button>
                </div>
            </div><!--/.col (right) -->
        </div>
    </form>

<form method="post" action="<?php echo U('Op/public_save'); ?>" id="after_lession">
    <input type="hidden" name="dosubmint" value="1">
    <input type="hidden" name="opid" value="{$op.op_id}">
    <input type="hidden" name="savetype" value="11">
    <input type="hidden" name="info[ini_user_id]" value="{:session('userid')}" readonly>
    <input type="hidden" name="info[ini_user_name]" value="{:session('nickname')}" readonly>
    <div class="row">
        <!-- right column -->
        <div class="form-group col-md-12">

            <div class="content">

                <div class="form-group col-md-8">
                    <label>学校名称：</label><input type="text" name="info[client]" value="<?php echo $resource['client']?$resource['client']:$op['customer'] ?>" class="form-control" />
                </div>

                <div class="form-group col-md-4">
                    <label>上课地点：</label><input type="text" name="info[addr]" value="<?php echo $resource['addr']?$resource['addr']:$confirm['address']; ?>" class="form-control" />
                </div>

                <div class="form-group col-md-4">
                    <label>课程周期：</label><input type="text" name="info[use_time]" value="<?php echo $resource['use_time']; ?>" class="form-control" />
                </div>

                <div class="form-group col-md-4">
                    <label>上课时间：</label><input type="text" name="info[lession_time]" value="<?php echo $resource['lession_time']?$resource['lession_time']:$confirm['days'] ; ?>" class="form-control" />
                </div>

                <div class="form-group col-md-4">
                    <label>课程名称：</label><input type="text" name="info[lession_name]" value="{$resource['lession_name']}" class="form-control" />
                </div>

                <div class="form-group col-md-4">
                    <label>面向年级：</label><input type="text" name="info[lession_grade]" value="{$resource['lession_grade']}" class="form-control" />
                </div>

                <div class="form-group col-md-4">
                    <label>接收人员：</label><input type="text" class="form-control" name="info[exe_user_name]"  value="{$men['nickname']}" id="exe_u_name" />
                    <input type="hidden" name="info[exe_user_id]" id="exe_u_id"  value="{$men['id']}" />
                </div>

                <div class="form-group col-md-4">
                    <label>填表人：</label><input type="text" name="info[service_name]" value="{$op['sale_user']}" class="form-control" readonly />
                </div>

                <div class="form-group col-md-12" id="is_handson">
                    <label>动手实践：</label>
                    <span class="checkboxs_400"><input type="radio" name="info[handson]" <?php if($info['handson']==1){ echo 'checked';} ?>  value="1">&nbsp; 是<span id="handson">，费用标准：<input type="text" name="info[lession_price]" style="border: none; border-bottom: solid 1px;"></span></span>&#12288;&#12288;
                    <span class="checkboxs_255"><input type="radio" name="info[handson]" <?php if($info['handson']==0){ echo 'checked';} ?>  value="0">&nbsp; 否</span>&#12288;&#12288;
                </div>

                <div class="form-group col-md-12">
                    <label>如有更多需求,请具体描述：</label><textarea class="form-control"  name="info[lession_special_need]">{$resource['lession_special_need']}</textarea>
                </div>

            </div>

            <div style="width:100%; text-align:center;">
                <button type="submit" class="btn btn-info btn-lg" id="lrpd">提交</button>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function(e){
        var keywords = <?php echo $userkey; ?>;
        $("#exe_u_name").autocomplete(keywords, {
            matchContains: true,
            highlightItem: false,
            formatItem: function(row, i, max, term) {
                return '<span style=" display:none">'+row.pinyin+'</span>'+row.text;
            },
            formatResult: function(row) {
                return row.text;
            }
        }).result(function (event, item) {
            $("#exe_u_id").val(item.id);
        });
    });

</script>