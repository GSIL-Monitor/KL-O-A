<include file="Index:header2" />

<style>
    .file {
        position: relative;
        display: inline-block;
        background: #D0EEFF;
        border: 1px solid #99D3F5;
        border-radius: 4px;
        padding: 8px 15px;
        overflow: hidden;
        color: #1E88C7;
        text-decoration: none;
        text-indent: 0;
        line-height: 20px;
    }
    .file input {
        position: absolute;
        font-size: 100px;
        right: 0;
        top: 0;
        opacity: 0;
    }
    .file:hover {
        background: #AADFFD;
        border-color: #78C3F3;
        color: #004974;
        text-decoration: none;
    }
</style>
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>文件详情</h1>
                    <ol class="breadcrumb">
                        <li><a href="{:U('Files/index')}"><i class="fa fa-home"></i> 首页</a></li>
                        <li><a href="{:U('Approval/Approval_Index')}">文件审批</a></li>
                        <li><a href="">文件详情</a></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <div class="tip" style="padding: 5px 12px;">
                                        <form method="post" action="{:U('Approval/Approval_file')}" enctype="multipart/form-data">
                                            <a href="javascript:;" class="file" style="float:left;background-color:#00acd6;color:#FFFFFF;"><i class="fa fa-upload"></i> 上传修改后文件
                                                <input type="file" name="file" id="approval_file">
                                                <input type="hidden" name="user_id" value="{$approval_file['id']}">
                                                <input type="hidden" name="style" value="1">
                                            </a>
                                            <button type="submit" style="float:left;width:6em;height:2.4em;background-color:#00acd6;border-radius:6px;font-size:1.2em;color:#ffffff;margin-left:1em;margin-top:-0.1em;line-height:0em;">保 存</button>
                                            </form>
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">

                                        <table class="table table-bordered">
                                            <tr class="orders" style="text-align:center;">
                                                <th class="sorting" style="text-align:center;width:10em;"><b>ID</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>上传者</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>文件名称</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>修改后文件名称</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>修改人姓名</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>修改时间</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>文件大小</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>文件格式</b></th>
                                                <th class="sorting" style="text-align:center;width:10em;"><b>上传时间</b></th>
                                            </tr>
                                            <foreach name="approval_file" item="f">
                                                <tr style="text-align:center;">
                                                    <td >{$f['file']['account_id']}</td>
                                                    <td>{$f['file']['account_name']}</td>
                                                    <td>
                                                        <a href="{$f['file']['file_url']}">
                                                            <?php if(!empty($f['file']['file_name']) && !empty($f['file']['file_format'])){echo $f['file']['file_name'].'.'.$f['file']['file_format'];}?>
                                                        </a>
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
                                                    <td>{$f['file']['file_size']}</td>
                                                    <td>{$f['file']['file_format']}</td>
                                                    <td><?php if(!empty($f['file']['createtime'])){echo date('Y-m-d',$f['file']['createtime']);}?></td>
                                                </tr>
                                            </foreach>
                                        </table>

                                </div><!-- /.box-body -->
                              
                            </div><!-- /.box -->

                        </div><!-- /.col -->
                     </div>

                </section><!-- /.content -->
            </aside><!-- /.right-side -->


<include file="Index:footer2" />

