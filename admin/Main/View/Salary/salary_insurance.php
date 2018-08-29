
<!--提成/奖金/补助-->
<div class="box">
    <div class="box-header">
        <div class="box-tools pull-left">
            <h3 style="color:blue"><b>&nbsp;&nbsp;&nbsp;五险一金</h3>
            <h4 style="margin:-1.8em 0em 0em 8em">操作事项</h4>
            <select name="some" class="btn btn-info" style="margin:-3em 0em 0em 17em;" id="salary_insurance">
                <option value="0" <?php if($type=="" || $type==0 ){echo 'selected';}?>>选择操作</option>
                <option value="1" onclick="salary_hide(1)" <?php if($type==8){echo 'selected';}?>>录入提成/奖金</option>
                <option value="2" onclick="salary_hide(2)" <?php if($type==9){echo 'selected';}?>>变动各项补助</option>
                <option value="3" onclick="salary_hide(3)" <?php if($type==10){echo 'selected';}?>>变动各项补助</option>
            </select>
        </div>
    </div>
    <div class="box-body">
        <div class="btn-group"><br>

            <div id="table_salary_percentage1" >
                <div style="float: left;margin-left: 2em;">
                    <label>选择人员：</label>
                    <a href="javascript:;" class="btn btn-info btn-sm" onclick="javascript:opensearch('searchtext_2',700,160);"><i class="fa fa-search"></i> 搜索</a> (提示: 选择不到人员或基本信息不完整、错误时，请在“员工管理”->"人员管理"页面添加或编辑信息)<br><br>
                    <table class="table table-bordered"  style="margin-top:10px;">
                        <tr role="row" class="orders">
                            <th class="sorting" data="op_id"  style="width:8em;">ID</th>
                            <th class="sorting" data="group_id" style="width:12em;">员工姓名</th>
                            <th class="sorting" data="group_id" style="width:12em;">员工编号</th>
                            <th class="sorting" data="group_id" style="width:12em;">员工部门</th>
                            <th class="sorting" data="project" style="width:13em;">员工岗位</th>
                            <th class="sorting" data="number" style="width:12em;">提成</th>
                            <th class="sorting" data="number" style="width:12em;">奖金</th>
                            <th class="sorting" data="shouru" style="width:12em;">年终奖</th>
                            <th class="sorting" data="shouru" style="width:10em;">操作</th>
                        </tr>

                        <foreach name="rows" item="lst">
                            <tr>
                                <td class="salary_table_extract">{$lst.aid}</td>
                                <td>{$lst.nickname}</td>
                                <td>{$lst.employee_member}</td>
                                <td>{$lst.department}</td>
                                <td>{$lst.post_name}</td>
                                <td><input type="text" style="float:left;" class="form-control salary_bonus_extract" value="{$lst.extract}" /></td>
                                <td><input type="text" style="float:left;" class="form-control salary_bonus_bonus" value="{$lst.bonus}" /></td>
                                <td><input type="text" style="float:left;" class="form-control salary_bonus_yearend" value="{$lst.annual_bonus}" /></td>
                                <td> <input type="button" class="form-control salary_bonus_butt1" value="添加" style="background-color:#00acd6;font-size:1em;" /></td>
                            </tr>
                        </foreach>
                    </table>
                    <div class="box-footer clearfix">
                        <div class="pagestyle">{$page2}</div>
                    </div>

                </div>
            </div>

            <div id="table_salary_percentage2" style="display:none;" >
                <div style="float: left;margin-left: 2em;">
                    <label>选择人员：</label>
                    <a href="javascript:;" class="btn btn-info btn-sm" onclick="javascript:opensearch('searchtext_1',700,160);"><i class="fa fa-search"></i> 搜索</a> (提示: 选择不到人员或基本信息不完整、错误时，请在“员工管理”->"人员管理"页面添加或编辑信息)<br><br>
                    <table class="table table-bordered" style="margin-top:10px;">
                        <tr role="row" class="orders">
                            <th class="sorting" data="op_id"  style="width:6em;">ID</th>
                            <th class="sorting" data="group_id" style="width:7em;">员工姓名</th>
                            <th class="sorting" data="group_id" style="width:7em;">员工编号</th>
                            <th class="sorting" data="group_id" style="width:7em;">员工部门</th>
                            <th class="sorting" data="project" style="width:8em;">员工岗位</th>
                            <th class="sorting" data="number" style="width:8em;">原住房补助</th>
                            <th class="sorting" data="number" style="width:11em;">现住房补助</th>
                            <th class="sorting" data="shouru" style="width:8em;">原外地补贴</th>
                            <th class="sorting" data="shouru" style="width:11em;">现外地补贴</th>
                            <th class="sorting" data="number" style="width:8em;">原电脑补贴</th>
                            <th class="sorting" data="number" style="width:12em;">现电脑补贴</th>
                            <th class="sorting" data="shouru" style="width:10em;">操作</th>

                        </tr>
                        <foreach name="rows" item="lst">
                            <tr>
                                <td class="salary_table_extract1">{$lst.aid}</td>
                                <td>{$lst.nickname}</td>
                                <td>{$lst.employee_member}</td>
                                <td>{$lst.department}</td>
                                <td>{$lst.post_name}</td>
                                <td>{$lst.housing_subsidy}</td>
                                <td><input type="text" style="float:left;" class="form-control salary_subsidy_housingt" value="{$lst.housing_subsidy}" /></td>
                                <td>{$lst.foreign_subsidies}</td>
                                <td><input type="text" style="float:left;" class="form-control salary_subsidy_foreign" value="{$lst.foreign_subsidies}" /></td>
                                <td>{$lst.computer_subsidy}</td>
                                <td><input type="text" style="float:left;" class="form-control salary_subsidy_computer" value="{$lst.computer_subsidy}" /></td>
                                <td><input type="button" class="form-control salary_subsidy_butt1" value="添加" style="background-color:#00acd6;font-size:1em;" /></td>
                            </tr>
                        </foreach>

                    </table>

                    <div class="box-footer clearfix">
                        <div class="pagestyle">{$page2}</div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>