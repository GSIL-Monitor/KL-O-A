
    <?php if($audit_yusuan && $costacc){ ?>
        <div class="content" style="padding-top:0px;">
            <table class="table table-striped" id="font-14-p">
                <thead>
                    <tr>
                        <th width="">费用项</th>
                        <th width="">单价</th>
                        <th width="">数量</th>
                        <th width="">合计</th>
                        <th width="">类型</th>
                        <th width="">备注</th>
                        <th width="">借款</th>
                    </tr>
                </thead>
                <tbody>
                    <foreach name="costacc" key="k" item="v">
                    <tr class="userlist" id="supplier_id_103">
                        <td width="16.66%">{$v.title}</td>
                        <td width="16.66%">&yen; {$v.unitcost}</td>
                        <td width="16.66%">{$v.amount}</td>
                        <td width="16.66%">&yen; {$v.total}</td>
                        <td width="16.66%"><?php echo $kind[$v['type']]; ?></td>
                        <td>{$v.remark}</td>
                        <td width="80" id="td_{$v.id}">
                            <a href="javascript:;" class="btn btn-info btn-sm" onclick="add_jiekuan({$v.id})">借款</a>
                            <input type="hidden" name="id" value="{$v.id}">
                            <input type="hidden" name="total" value="{$v.total}" id="total_{$v.id}">
                        </td>
                    </tr>
                    </foreach>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-size:16px; color:#ff3300;">&yen; {$budget.budget}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" action="{:U('Finance/public_save')}" name="jiekuanform" id="jiekuanform" onsubmit="return submitBefore()" >
            <div class="content">
                <input type="hidden" name="savetype" value="2">
                <input type="hidden" name="info[op_id]" value="{$op.op_id}" />
                <input type="hidden" name="info[costacc_ids]" id="ids">
                <input type="hidden" id="qianzi" value="0">
                <input type="hidden" id="jk_sum">
                <div style="width:100%; float:left;">
                    
                    <div class="form-group col-md-6">
                        <label>借款单位：</label>
                        <input type="text" name="info[rolename]" class="form-control" value="<?php echo $list['rolename']?$list['rolename']:session('rolename'); ?>" readonly />
                    </div>

                    <div class="form-group col-md-6">
                        <label>团号：</label>
                        <input type="text" name="info[group_id]" class="form-control" value="<?php echo $list['group_id']?$list['group_id']:$op['group_id']; ?>" readonly />
                        <input type="hidden" name="info[op_id]" value="<?php echo $list['op_id']?$list['op_id']:$op['op_id']; ?>" />
                    </div>

                    <div class="form-group col-md-6">
                        <label>借款金额：</label>
                        <input type="text" name="info[sum]" id="jiekuanjine" class="form-control" value="{$list.sum}" onblur="todaxie($(this).val())" />
                    </div>

                    <div class="form-group col-md-6">
                        <label>人民币(大写)：</label>
                        <input type="text" name="info[sum_chinese]" id="daxie" class="form-control" value="{$list.sum_chinese}" />
                    </div>

                    <div class="form-group col-md-12" id="jk_type">
                        <label>支付方式：</label>
                        <input type="radio" name="type" value="1" <?php if ($list['type']== 1) echo "checked"; ?> /> &nbsp;支票 &emsp;&emsp;
                        <input type="radio" name="type" value="2" <?php if ($list['type']== 2) echo "checked"; ?> /> &nbsp;现金 &emsp;&emsp;
                        <input type="radio" name="type" value="3" <?php if ($list['type']== 3) echo "checked"; ?> /> &nbsp;汇款 &emsp;&emsp;
                        <input type="radio" name="type" value="4" <?php if ($list['type']== 4) echo "checked"; ?> /> &nbsp;其他 &emsp;&emsp;
                    </div>

                    <div class="form-group col-md-12">
                        <label>用途说明：</label>
                        <textarea class="form-control"  name="info[description]">{$list.description}</textarea>
                    </div>
                    <div class="form-group col-md-12 zp_show hk_show">
                        <label>受款单位：</label>
                        <input type="text" name="info[payee]" class="form-control zhipiao huikuan" value="{$list.payee}">
                    </div>

                    <div class="form-group col-md-6 hk_show">
                        <label>开户行名称：</label>
                        <input type="text" name="info[bank_name]" class="form-control huikuan" value="{$list.bank_name}">
                    </div>

                    <div class="form-group col-md-6 hk_show">
                        <label>账号：</label>
                        <input type="text" name="info[card_num]" class="form-control huikuan" value="{$list.card_num}">
                    </div>

                    <div class="form-group col-md-6" id="jkr_qianzi">
                        <label>借款人：</label>
                        <input type="button" onclick="show_qianzi()" value="签字">
                    </div>

                </div>
            </div>
            <div style="width:100%; text-align:center;">
                <!--<a  href="javascript:;" class="btn btn-info btn-lg" onClick="javascript:save('design','<?php /*echo U('Op/public_save'); */?>');">保存</a>-->
                <input type="submit" class="btn btn-info btn-lg" value="提交">
            </div>
        </form>

    <?php }else{ ?>
            <div class="content" style="margin-left:15px;">该项目尚未做预算！</div>
    <?php }  ?>

    <script>
        $(function () {
            $('.hk_show').hide();

            $('#jk_type').find('ins').each(function (index,ele) {
                $(this).click(function () {
                    var type = $(this).prev('input').val();

                    if(type ==1){ //支票
                        $('.huikuan').removeAttr('required');
                        $('.hk_show').hide();
                        $('.zp_show').show();
                        $('.zhipiao').attr('required','true');
                    }else if(type == 3){ //汇款
                        $('.hk_show').show();
                        $('.huikuan').attr('required','true');
                    }else{
                        $('.huikuan').removeAttr('required');
                        $('.hk_show').hide();
                    }
                })
            })
        })

        function add_jiekuan(id) {
            var arr_ids         = $('#ids').val();
            var jiekuanjine     = $('#jiekuanjine').val();
            var total           = $('#total_'+id).val();
            var sum             = accAdd(jiekuanjine,total);  //数据相加
            $('#jiekuanjine').val(sum);
            $('#jk_sum').val(sum);
            todaxie(sum);       //转换为大写

            var aid             = '['+id+'],';
            arr_ids             += aid;

            $('#ids').val(arr_ids);

            var html            = '';
            html +='<a href="javascript:;" class="btn btn-sm" onclick="del_jiekuan('+id+')">取消</a>'+
                '<input type="hidden" name="id" value="'+id+'">'+
                '<input type="hidden" name="total" value="'+total+'" id="total_'+id+'">';
            $('#td_'+id+'').html(html);
        }

        function del_jiekuan(id){
            var jiekuanjine     = $('#jiekuanjine').val();
            var total           = $('#total_'+id).val();
            var sum             = accSub(jiekuanjine,total);  //数据相减
            $('#jiekuanjine').val(sum);
            $('#jk_sum').val(sum);
            todaxie(sum);       //转换为大写

            var aid             = '['+id+'],';
            var aids            = $('#ids').val();
            var aaa             = aids.replace(aid,'');
            $('#ids').val(aaa);

            var html            = '';
            html +='<a href="javascript:;" class="btn btn-info btn-sm" onclick="add_jiekuan('+id+')">借款</a>'+
                '<input type="hidden" name="id" value="'+id+'">'+
                '<input type="hidden" name="total" value="'+total+'" id="total_'+id+'">';
            $('#td_'+id+'').html(html);
        }

        function todaxie(num) {
            $.ajax({
                type: "post",
                url: "<?php echo U('Ajax/numTrmb'); ?>",
                dataType:'json',
                data: {num:num},
                success:function(data){
                    if(data){
                        $('#daxie').val(data);
                    }
                }
            });
        }

        function show_qianzi() {
            var html = '';
            html += '<label>借款人：</label>'+
                '<input type="text" name="password" class="" placeholder="请输入签字密码"  />&emsp;'+
                '<input type="button" value="确定" onclick="check_pwd()">';
            $('#jkr_qianzi').html(html);
        }

        function check_pwd() {
            var pwd = $('input[name="password"]').val();
            $.ajax({
                type: 'POST',
                url : "{:U('Ajax/check_pwd')}",
                data: {pwd:pwd},
                success:function (msg) {
                    if (msg.stu ==1){
                        var html = '';
                        html += '<label>借款人：</label>'+
                            '<input type="hidden" name="info[jk_file]" value="'+msg.file_url+'">'+
                            '<img width="100" src="/'+msg.file_url+'" alt="">';
                        $('#jkr_qianzi').html(html);
                        $('#qianzi').val('1');
                    }else{
                        art_show_msg(msg.message);
                        return false;
                    }
                }
            })
        }

        function submitBefore() {
            var isqianzi = $('#qianzi').val();
            var ying_jk  = $('#jk_sum').val();
            var shi_jk   = $('#jiekuanjine').val();
            if (isqianzi == 1){
                if (shi_jk > ying_jk){
                    art_show_msg('本次借款超出所选项目实际预算,请重新核实借款信息');
                    return false;
                }else{
                    $('#jiekuanform').submit();
                }
            }else{
                art_show_msg('请完善借款信息');
                return false;
            }
        }

        /*function qianzi() {
            art.dialog.open("<?php echo U('Finance/sign_jk',array('opid'=>$op['op_id'])); ?>",{
                lock:true,
                title: '借款人签字',
                width:600,
                height:300,
                okValue: '提交',
                fixed: true,
                ok: function () {
                    this.iframe.contentWindow.gosubmint();
                    return false;
                },
                cancelValue:'取消',
                cancel: function () {
                }
            });
        }*/

    </script>