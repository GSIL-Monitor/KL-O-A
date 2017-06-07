<?php
// 加载参数类
import ('P', COMMON_PATH . 'Common/'); 
use App\P;

/**
 * @brief  载入第三方类库
 * @param  string  $class   要加载的类名（含路径）
 * @return
 */
function ulib ($class) {
    import($class, THINK_PATH . '../ulib/');
}

/*redis*/
function redis($key,$val=null){
	//global $redis_server;
	//if (!$redis_server) {
	   $redis_server = new \Redis();	
	   $redis_server->connect( C('REDIS_HOST'), C('REDIS_PORT'));
		//$redis_server->auth(ccc);
	//}
	if($val){
		$redis_server->set($key, $val);	
		
	}else{
		 $val = $redis_server->get($key);
	}
	$redis_server->close();
	return $val;
}


/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function password($password, $encrypt='') {
    $pwd = array();
    $pwd['encrypt'] =  $encrypt ? $encrypt : create_randomstr();
    $pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function create_randomstr($lenth = 6) {
    return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}

/**
* 产生随机字符串
* @param    int        $length  输出长度
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function random($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * 判断菜单是否有权限显示
 * @text String 如"Index/index"
 * @return 1为有权限显示、0为无权限显示
 */
function rolemenu($obj){
	
	
	$menu = array();
	//默认样式
	$style =  0;	
	//判断是否为开发者权限
	if(session(C('ADMIN_AUTH_KEY'))){
		$style = 1;
	}else{
		
		foreach($obj as $bb=>$unit){
			$text = strtoupper($unit);
			if($_SESSION['_ACCESS_LIST']){
				if(is_array($_SESSION['_ACCESS_LIST']['MAIN'])){
					foreach($_SESSION['_ACCESS_LIST']['MAIN'] as $k=>$v){
						foreach($v as $a=>$b){
							$menu[] = $k.'/'.$a;
						}
					}
					if(in_array($text,$menu)){
						$style = 1;	
					}
				}
			}
		}
		
	}

	return $style;	
}

//用户关系递归
function Userrelation($id = 0) { 
    global $str; 
	$db = M('admin');
	$guanxibiao = $db->field('id,parentid')->where(array('parentid'=>$id))->select();
    if($guanxibiao){
		foreach ($guanxibiao as $row){
            $str .= $row['id']. ",";
            Userrelation($row['id']);
		}
    } 
    return $str; 
} 

//角色关系递归
function Rolerelation($id = 0,$type = 0) { 
    global $str; 
	$db = M('role');
	$guanxibiao = $db->field('id,pid')->where(array('pid'=>$id))->select();
    if($guanxibiao){
		foreach ($guanxibiao as $row){
            $str .= $row['id']. ",";
            Rolerelation($row['id']);
		}
    } 
	
	if($type==1){
		 $str .= $id. ",";
	}
	
	$role = trim($str,',');
	
	//返回用户ID
	$user = array();
	$user = M('account')->where(array('roleid'=>array('in',$role)))->Getfield('id',true);	
	if($type==0){
		$user[] = cookie('userid');
	}
	$data = implode(',',$user);
		
	
	//if($type){
		
	//}else{
		//返回角色ID
		//$data = $role;	
	//}
    return $data; 
} 

//渠道关系递归
function Dealerrelation($id = 0) { 
    global $str; 
	$db = M('dealer');
	$guanxibiao = $db->field('id,parentid')->where(array('parentid'=>$id))->select();
    if($guanxibiao){
		foreach ($guanxibiao as $row){
            $str .= $row['id']. ",";
            Dealerrelation($row['id']);
		}
    } 
    return $str; 
} 

/**
* 状态输出
* @param    int        $status  状态
* @return   String     $status对应的显示状态
*/
function statustr($status){
	if($status==1){
		return '<font color="#009900">正常</font>';
	}else{
		return '<font color="#cc0000">异常</font>';
	}
}


function merge_node($node, $access, $pid = 0) {
	$arr = array();
	foreach ($node as $v) {
		if (is_array($access)) {
	        $v['access'] = in_array($v['id'], $access) ? 1 : 0;	
	    }
	    if ($v['pid'] == $pid) {
		    $v['child'] = merge_node($node, $access, $v['id']);
			$arr[] = $v;	
		}
	}
	return $arr;
}

//
function ck ($str, $val, $yes = ' checked="checked" ', $no = ''){
    if (is_int($str)) return $str == $val ? $yes : $no;
    if (empty($str) && $val == "0" ) return $yes;
    return strpos($str, $val) === 0 ? $yes : $no;
}

function hide ($str, $val, $yes = ' style="display:none;" ', $no = ''){
    if (empty($str) && $val == "0" ) return $yes;
    return strpos($str, $val) === 0 ? $yes : $no;
}

function sel ($str, $val, $yes = ' selected ', $no = ''){
    if (is_int($str)) return $str == $val ? $yes : $no;
    if (empty($str) && $val =="0" ) return $yes;
    return $str == $val ? $yes : $no;
}

function ison ($str, $val, $yes = 'active', $no =''){
    return ck($str, $val, $yes, $no);
}


function P($var, $stop = true){
	header("Content-Type: text/html;charset=utf-8"); 
    echo '<pre>';
	print_r($var);
	echo '</pre>';
	if ($stop) die();	
}


/**
 * 编辑器
**/
function editor($editor_name, $default = '', $editor_id = '') {
	$str = '';
	if(!defined('EDITOR_INIT')) {
		$str .= '<script type="text/javascript" src="' .__ROOT__. '/admin/assets/comm/ckeditor/ckeditor.js"></script>';
				
		define('EDITOR_INIT', 1);
	}     
	if (empty($editor_id)) $editor_id = preg_replace("/\[\]/", "_", $editor_name);
	return $str.'<textarea class="ckeditor" name="'.$editor_name.'" id="'.$editor_id.'" >'.$default.'</textarea>';
}

function upload_image($name,$uptext = '上传图片', $default = '', $multi = true) {
    $str = '';    
	if (!defined('INIT_UPLOAD_IMAGE')) {
		  $str .= '<script type="text/javascript" src="' .__ROOT__. '/admin/assets/comm/upfile.js"></script>';
		  //$str .= '<link rel="stylesheet" href="'. __ASSETS__. 'css/upload_img.css" />';
		  define('INIT_UPLOAD_IMAGE', 1);
	}
	
	$show = '';
	$values = array();
	if (!empty($default)) {
		if (preg_match('/^(\d+,?)+$/', $default)) {
		    $db = M('attachment');
		    $rs = $db->where("id in (".$default.")")->select();
			$i = 0;
			foreach($rs as $line) {
			    $values[$i]['id'] = $line['id'];
				$values[$i]['thumb'] =  dirname($line['filepath']). "/thumb_80_60_" . basename($line['filepath']);
				$values[$i]['imgurl'] = $line['filepath'];
				$i++;
			}
		} else {
			$i = 0;
			foreach(explode(",", $default) as $img) {
				if (empty($img)) continue;
		        $values[$i]['id'] = '';
				if (strpos($img, 'http://') === false) {
				    $values[$i]['thumb'] = dirname($img). "/thumb_80_60_" . basename($img);
				} else {
					$values[$i]['thumb'] = $img;
				}
				$values[$i]['imgurl'] = $img;
				$i++;
			}
		}
	}
	
	$close = $multi ? '<div class="closeimg"><a href="javascript:;" onclick="javascript:g_remove_img(this);" class="iclose"></a></div>' : '';
	$arr = $multi ? '[]' : '';
	foreach($values as $row) {
		$show .= '<div class="oneimg">'.$close.'<div class="imgdiv"><div class="outline"><img src="'.$row['thumb'].'"  height="60" alt="点击查看大图" onclick="g_open_big(\''.$row['imgurl'] .'\');" /></div></div><div style="display:none"><input type="checkbox" name="'.$name.'[id]'.$arr.'" value="'.$row['id'].'" checked="checked"/><input type="checkbox" name="'.$name.'[imgurl]'.$arr.'" value="'.$row['imgurl'].'" checked="checked"  /><input type="checkbox" name="'.$name.'[thumb]'.$arr.'" value="'.$row['thumb'].'" checked="checked"/></div></div>';	
		
	}
	
	$str .= '
			<table rules="none" border="0" cellpadding="0" cellspacing="0" class="upload_table">
			<tr>
				<td align="left">
				<div>
				<a href="javascript:;" class="btn btn-info btn-sm" onclick="javascript:g_upload_image(\''.U('Attachment/img_upload'). '\',\''. $name .'\','. $multi.');">'.$uptext.'</a>
				<label style="margin:0px;display:inline-block;"><small>&nbsp;&nbsp;单个文件最大上传限制20M'. ($multi?' (可多选)':'') .'</small></label>
				</div>
				</td>
			</tr>
			<tr>
				<td>
				<div id="'.$name.'_show" class="imgs_show">'.$show.'
				</div>
				</td>
			</tr>
		</table>';
	
	return $str;
}


/**
 * 生成缩略图函数
 * @param  $img 图片路径
 * @param  $width  缩略图宽度
 * @param  $height 缩略图高度
 * @param  $autocut 是否自动裁剪 默认不裁剪，当高度或宽度有一个数值为0是，自动关闭
 * @param  $smallpic 无图片是默认图片路径
*/ 
function thumb($img, $width = 80, $height = 60 ,$autocut = 1, $nopic = 'images/nopic.jpg') {

	if(empty($img)) return __ASSETS__ . DS . $nopic;   //判断原图路径是否输入

	if(!extension_loaded('gd') || strpos($img, '://')) return $img;
    
	$root_path =  __ROOT__ . DS ;
	if (strpos($img, $root_path) === 0) {
	    $img_replace = substr_replace($img, '', 0, strlen($root_path));
	} else {
	    $img_replace = $img;
	}
	
	if(!file_exists($img_replace)) return  __ASSETS__ . DS . $nopic; //判断原图是否存在

	$newimg = dirname($img_replace).'/thumb_'.$width.'_'.$height.'_'.basename($img_replace);   //缩略图路径

	if(file_exists($newimg)) return $newimg;  //如果缩略图存在则直接输入
	
	$image = new \Think\Image(); 
	$image->open($img_replace);
    
	if ($autocut) {
        $image->thumb($width, $height,\Think\Image::IMAGE_THUMB_CENTER)->save($newimg);
	} else {
        $image->thumb($width, $height)->save($newimg);
	}
    
	return $newimg;
}


/**
 * @brief 导出Excel
 * @param array $data
 * @param array $title
 * @param string $filename
 */
function exportexcel($data=array(),$title=array(),$filename='export'){
    ini_set('max_execution_time', 500);

    $cols = array();
	 for($i='A'; $i!='YZ'; $i++) {
		 $cols[] = $i;
	 }
     
     ulib('PHPExcel');

    $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
    $cacheSettings = array();
    \PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);

    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->getDefaultColumnDimension()->setWidth(20);
    

    $n = 1;
    if (!empty($title)) {
        $j = 0;
        foreach($title as $v) {
            $sheet->setCellValue($cols[$j] . '1', $v);
            $j++;
        }
        $n = 2;
    }

    foreach($data as $k => $v) {
        if (is_array($v)) {
            for($i = 0; $i < count($v); $i++) {
                $sheet->setCellValueExplicit($cols[$i].$n, current($v), \PHPExcel_Cell_DataType::TYPE_STRING);
                //$sheet->setCellValue($cols[$i].$n, current($v));
                each($v);
            }
        } else {
            $sheet->setCellValueExplicit($cols[0].$n, $v, \PHPExcel_Cell_DataType::TYPE_STRING);
            //$sheet->setCellValue($cols[0].$n, $v);
        }
        $n++;
    }

    ob_end_clean();
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
     
}



/**
 * @brief 使用模板导出Excel
 * @param array $data
 * @param array $title
 * @param string $filename
 */
function model_exportexcel($data=array(),$filename='export',$model){
    ini_set('max_execution_time', 500);

    ulib('PHPExcel'); //导入thinkphp第三方类库
	//创建一个读Excel模板的对象
	$objReader= \PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel = $objReader->load ("$model");//读取模板，模版放在根目录
	//获取当前活动的表
	$objActSheet=$objPHPExcel->getActiveSheet();
	$objActSheet->setTitle($filename);//设置excel的标题
	
	foreach($data as $k=>$v){
		$objActSheet->setCellValue($k,$v);
	}
	
	//导出
	$filename = iconv('utf-8',"gb2312",$filename);//转换名称编码，防止乱码
	//header ( 'Content-Type: application/vnd.ms-excel;charset=utf-8' );
	//header ( 'Content-Disposition: attachment;filename="' . $filename . '.xls"' ); //”‘.$filename.’.xls”
   // header ( 'Cache-Control: max-age=0');
	
	ob_end_clean();
	header("Content-type:application/octet-stream");
	header("Accept-Ranges:bytes");
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=".$filename.".xls");
	header("Pragma: no-cache");
	header("Expires: 0");

	
	$objWriter = \PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel5' ); //在内存中准备一个excel2003文件
	$objWriter->save ('php://output');
     
}


/**
 * @brief 导入Excel
 * @param array $file
 */
function importexcel($filePath){
	
	ulib('PHPExcel');
	
	$PHPExcel = new \PHPExcel(); 
	
	/**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/ 
	$PHPReader = new \PHPExcel_Reader_Excel2007(); 
	if(!$PHPReader->canRead($filePath)){ 
		$PHPReader = new \PHPExcel_Reader_Excel5(); 
		if(!$PHPReader->canRead($filePath)){ 
			echo 'no Excel'; 
			return ; 
		} 
	} 
	
	$PHPExcel = $PHPReader->load($filePath); 
	/**读取excel文件中的第一个工作表*/ 
	$currentSheet = $PHPExcel->getSheet(0); 
	/**取得最大的列号*/ 
	$allColumn = $currentSheet->getHighestColumn(); 
	/**取得一共有多少行*/ 
	$allRow = $currentSheet->getHighestRow(); 
	/**从第二行开始输出，因为excel表中第一行为列名*/ 
	
	$data = array();
	for($currentRow = 1;$currentRow <= $allRow;$currentRow++){ 
		/**从第A列开始输出*/ 
		$cont = array();
		for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){ 
			$val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/ 
			$cont[] = $val;
			/**如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出*/ 
			//$cont[] = iconv('utf-8','gb2312', $val);
		} 
		$data[] = $cont;
	} 
	
	return $data;
     
}



/**
 * @brief 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function send_mail ($to,  $name,  $subject = '', $body = '', $attachment = null)
{

    $config = C('EMAIL_CONFIG');

	 ulib('PHPMailer.PHPMailerAutoload');
    
    $mail             = new \PHPMailer(); //PHPMailer对象
    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();  // 设定使用SMTP服务
    $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';                 // 使用安全协议
    $mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
    $mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
    $mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
    $mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
    $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
    $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
    $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if(is_array($attachment)){ // 添加附件
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}


/*邮件模板*/
function mailmode($mail,$url){
	return '<p>您收到这封邮件，是因为chengli@btte.net（Email：'.$mail.'）在我们网站为您的Email开通了子账户权限。</p><p>您可以通过我们的网站实时查看到被授权应用的统计数据。</p><p>如果您并不需要访问我们的网站，请忽略这封邮件。您不需要退订或进行其他进一步的操作。</p><p>----------------------------------------------------------------------</p><p>帐号激活说明</p><p>----------------------------------------------------------------------</p><p>您是我们网站的新用户，我们需要对您的地址有效性进行验证以避免垃圾邮件或地址被滥用。</p><p>您只需点击下面的链接，并补全相关的注册信息，即可激活您的帐号：</p><p><a target="_blank" href="'.$url.'" _act="check_domail">'.$url.'</a></p><p>(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)</p><p>感谢您的访问，祝您使用愉快！</p><p>此致</p><p>友盟管理团队</p><p>-----------------------------</p><p>友盟（<a target="_blank" href="http://www.umeng.com" _act="check_domail">www.umeng.com</a>）<br>最专业的移动平台分析工具</p>';
}



/**
 * @brief  拉伸合并后的节点
 */
function sort_node($nodes, &$arr) {
    foreach ($nodes as $row) {
        if (isset($row['child'])) {
            $child = $row['child'];
            unset($row['child']);
        } else {
            $child = false;
        }
        $arr[] = $row;
        if ($child) {
            array_merge($arr, sort_node($child, $arr));
        }
    }
}

/**
 * @brief  获取角色列表
 */
function get_roles($sort = true) {

    global $__roles_list;
    global $__roles_list_unsort;

    if (count($__roles_list) > 0) {
        return  $sort == true ? $__roles_list : $__roles_list_unsort;
    } else {
        $db = M('role');
        $where = "id>3";
        //p($page);
        //$this->pagetitle = '角色';
        $allroles = $db->where($where)->order('pid,id')->select();

        $role_by_id  = array();
        foreach ($allroles as $row) {
            $role_by_id[$row['id']] = $row;
        }
        $__roles_list_unsort = $role_by_id;
        $roles = merge_node($role_by_id, null);
        sort_node($roles, $__roles_list);
    }
    return  $sort == true ? $__roles_list : $__roles_list_unsort;
}

/**
 * @brief  获取物资分类列表
 */
function get_material_kinds($sort = true) {

    global $__prj_kind_list;
    global $__prj_kind_list_unsort;

    if (count($__prj_kind_list) > 0) {
        return  $sort == true ? $__prj_kind_list : $__prj_kind_list_unsort;
    } else {
        $db = M('material_kind');

        $allroles = $db->order('pid,id')->select();

        $kind_by_id  = array();
        foreach ($allroles as $row) {
            $kind_by_id[$row['id']] = $row;
        }
        $__prj_kind_list_unsort = $kind_by_id;
        $kinds = merge_node($kind_by_id, null);
        sort_node($kinds, $__prj_kind_list);
    }
    return  $sort == true ? $__prj_kind_list : $__prj_kind_list_unsort;
}

/**
 * @brief  获取项目分类列表
 */
function get_project_kinds($sort = true) {

    global $__prj_kind_list;
    global $__prj_kind_list_unsort;

    if (count($__prj_kind_list) > 0) {
        return  $sort == true ? $__prj_kind_list : $__prj_kind_list_unsort;
    } else {
        $db = M('project_kind');

        $allroles = $db->order('pid,id')->select();

        $kind_by_id  = array();
        foreach ($allroles as $row) {
            $kind_by_id[$row['id']] = $row;
        }
        $__prj_kind_list_unsort = $kind_by_id;
        $kinds = merge_node($kind_by_id, null);
        sort_node($kinds, $__prj_kind_list);
    }
    return  $sort == true ? $__prj_kind_list : $__prj_kind_list_unsort;
}


/**
 * @brief  获取线路分类列表
 */
function get_product_kinds($sort = true) {

    global $__prj_kind_list;
    global $__prj_kind_list_unsort;

    if (count($__prj_kind_list) > 0) {
        return  $sort == true ? $__prj_kind_list : $__prj_kind_list_unsort;
    } else {
        $db = M('product_kind');

        $allroles = $db->order('pid,id')->select();

        $kind_by_id  = array();
        foreach ($allroles as $row) {
            $kind_by_id[$row['id']] = $row;
        }
        $__prj_kind_list_unsort = $kind_by_id;
        $kinds = merge_node($kind_by_id, null);
        sort_node($kinds, $__prj_kind_list);
    }
    return  $sort == true ? $__prj_kind_list : $__prj_kind_list_unsort;
}

/**
 * @brief  根据项目分类ID取分类名称
 */
function get_prj_kind_name($id, $isfull = true, $sep = ' > ') {

    $kinds = get_project_kinds(false);
    $name = '';
    if (array_key_exists($id, $kinds)) {
        $name = $kinds[$id]['name'];

        if (!$isfull) return $name;
        $pid = $kinds[$id]['pid'];
        while ($pid  > 0) {
            $name = $kinds[$pid]['name'] . $sep . $name;
            $pid = $kinds[$pid]['pid'];
        }
    }
    return $name;
}

/**
 * @brief  补足树形前缀
 */
function tree_pad($level, $usespace = false) {
    if ($level <= 1) {
        return '';
    }

    if ($level == 2) {
        return '├';
    }

    $tmpstr = '';

    for ($i=1; $i < $level - 1; $i++) {
        if ($usespace) {
            $tmpstr .= '│&nbsp;&nbsp;';
        } else {
            $tmpstr .= '│<span style="color:white;">─</span>';
        }
    }
    $tmpstr .= '├';
    return $tmpstr;
}

/**
 * @brief  根据roleid取角色名称
 */
function get_role_name($id, $isfull = true, $sep = ' > ') {

    $roles = get_roles(false);
    $name = '';
    if (array_key_exists($id, $roles)) {
        $name = $roles[$id]['role_name'];

        if (!$isfull) return $name;
        $pid = $roles[$id]['pid'];
        while ($pid  > 4) {
            $name = $roles[$pid]['role_name'] . $sep . $name;
            $pid = $roles[$pid]['pid'];
        }
    }
    return $name;
}


/**
 * @brief  检查当前控制器和方法名称，返回指定的CSS class名
 * @param string  $str 目标值，如 User/index （判断控制器+方法）  User （仅判断控制器）
 * @param string  $css 可选css类名
 * return 匹配时返回 $css  否则为空串
 */
function on ($str, $css = ' active') {
    $tmp = explode('/', $str);
    if (count($tmp) > 1) {
        return  (CONTROLLER_NAME == $tmp[0] && ACTION_NAME == $tmp[1]) ? $css : '';
    } else {
        return  (CONTROLLER_NAME == $tmp[0]) ? $css : '';
    }
}


/**
 * @brief  检查当前用户资源权限配置，生成where过滤条件
 * @param string $tb 
 * @param string $priv = v,d,u
 * return 返回 生成的where条件
 */
function get_priv_condition($tb, $priv = 'v', $short_name = null) {
    
    if (session(C('ADMIN_AUTH_KEY')) || session('userid') == 1) return '1';
    if (!$short_name) $short_name = $tb;
    $roleid = M('role_user')->where('user_id='.session('userid'))->getField('role_id');
    $str = " (select substring(GROUP_CONCAT(${$priv} order by resid desc),1,1)
            from oa_rights where roleid=${roleid} and restable = '${tb}'
            and ({$short_name}.id = oa_rights.resid or oa_rights.resid = 0)) ";
            
    return $str;

}


function priv_where($req_type, $short_name = null) {
    
    if (session(C('ADMIN_AUTH_KEY')) || session('userid') == 1) return '1';
    $cfg  = M('audit_field')->where("req_type=$req_type")->find();
    $tb   = $cfg['table'];
    $priv = $cfg['priv'];
    if (!$short_name) $short_name = C('DB_PREFIX') . $tb;
    $roleid = M('role_user')->where('user_id='.session('userid'))->getField('role_id');
    $str = " (select substring(GROUP_CONCAT(${priv} order by resid desc),1,1)
    from oa_rights where roleid=${roleid} and restable = '${tb}'
    and (${short_name}.id = oa_rights.resid or oa_rights.resid = 0)) ";

    return $str;

}


function fsize($size) {

    $mod = 1024;

    $units = explode(' ','b kb mb gb tb pb');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    
    return round($size, 1) . ' ' . $units[$i];
}

function open_req($req_type, $req_id) {
    return "open_req('" . U('Rights/audit_req', array('req_type'=>$req_type, 'id'=>$req_id)) ."')";
}

function open_audit($audit_id) {
    return "open_audit('" . U('Rights/audit_apply', array('id'=>$audit_id)) ."')";
}



function opid($day=''){
	if($day){
		$date = date('Ymd',strtotime($day));
	}else{
		$date = date('Ymd',time());
	}
	$lastid = M('op')->where(array('op_id'=>array('like',$date.'%')))->order('id DESC')->find();
	if($lastid){
		$opid = 	$lastid['op_id']+1;
	}else{
		$opid = 	($date*10000)+1;
	}
	return $opid;
}


function op_record($info){
	$data = array();
	$data = $info;
	$data['uname'] = cookie('name');
	$data['op_time'] = time();
	$isok = M('op_record')->add($data);
	if($isok){
		return true;	
	}else{
		return false;	
	}
}


//汇总项目预算
function opcost($opid){
	//汇总项目预算
	$cost = $costlist   = M('op_cost')->where(array('op_id'=>$opid))->sum('total');
	$sumcost = $cost ? '&yen'.$cost : '';
	return $sumcost;
}


//二维数组去重
function unique_arr($array,$field='name'){
	if($array){
		foreach($array as $k=>$v){
			if($v['id']){
				$unitid[$v['id']]['id']   = $v['id'];	
				$unitid[$v['id']]['name'] = $v[$field];	
			}
		}
	}
    return $unitid;
}

//获取任意日期的月第一天和最后一天
function month_phase($date){
	
	if(strlen($date)==6) $date = $date.'01'; 
	
	$data = array();
	$data['start'] = strtotime(date('Y-m-01', strtotime($date)).' 00:00:00');
	$data['end']   = strtotime(date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month -1 day')).' 23:59:59');
	$data['month'] = date('Ym', strtotime($date));
	
	//上个月
	$data['prevmonth'] = date('Ym', ($data['start']-(86400*3)));
	
	//下个月
	$data['nextmonth'] = date('Ym', ($data['end']+(86400*3)));
	
	return $data;
}


//角色下所有的用户
function Roleinuser($id = 0,$check='role') { 
	global $$check;
	$db = M('role');
	$guanxibiao = $db->field('id,pid')->where(array('pid'=>$id))->select();
    if($guanxibiao){
		foreach ($guanxibiao as $row){
            $roid[] = $row['id'];
            Roleinuser($row['id'],$check);
		}
    } 
	//返回用户ID
 	$roid[] = $id;
	$userlist = M('account')->where(array('roleid'=>array('in',implode(',',$roid))))->Getfield('id',true);	
    return implode(',',$userlist); 
} 


//项目数量统计
function op_sum($date,$type=1,$dept){
	
	$db  = M();
	$day = month_phase($date);	
	//京区校外
	$where = array();
	if($dept) $where['o.create_user'] = array('in',Roleinuser($dept));
	if($type==1){
		$where['o.create_time'] = array('between',array($day['start'],$day['end']));	
	}else if($type==2){
		$where['o.create_time'] = array('between',array($day['start'],$day['end']));	
		$where['o.group_id']    = array('neq','');
	}else if($type==3){
		$where['a.audit_time']  = array('between',array($day['start'],$day['end']));	
		$where['b.audit']       = 1;
	}
	$sum = $db->table('__OP__ as o')->join('__OP_SETTLEMENT__ as b on b.op_id = o.op_id','LEFT')->join('__AUDIT_LOG__ as a on a.req_id = b.id and a.req_type = 801','LEFT')->where($where)->count();
	return $sum;
}



//项目提成统计
function op_tc($date,$type=1,$dept){
	
	$db  = M();
	$day = month_phase($date);
	
	if($type==1){
		$keywords = '计调提成';	
	}else if($type==2){
		$keywords = '研发提成';	
	}
	//京区校外
	$where = array();
	if($dept) $where['o.create_user'] = array('in',Roleinuser($dept));
	$where['a.audit_time']  = array('between',array($day['start'],$day['end']));	
	$where['b.audit']       = 1;
	$where['c.status']      = 2;
	$where['c.title']       = array('like','%'.$keywords.'%');
	
	$sum = $db->table('__OP_COSTACC__ as c')->join('__OP__ as o on o.op_id = c.op_id')->join('__OP_SETTLEMENT__ as b on b.op_id = c.op_id','LEFT')->join('__AUDIT_LOG__ as a on a.req_id = b.id and a.req_type = 801','LEFT')->where($where)->sum('c.total');
	return $sum;
}

//项目提成统计
function ticheng($year,$type=1){
		
	$db   = M();
	
	$jqxw    = array();
	$jqxn    = array();
	$jwyw    = array();
	$cgly    = array();
	$zong    = array();
	
	for($i=1;$i<=12;$i++){	
		$date = $year.'-'.$i.'-01';
		
		//京区校外
		$jqxwsum = op_tc($date,$type,33);
		$jqxw[]  = $jqxwsum ? floatval($jqxwsum) : 0;
		
		//京区校内
		$jqxnsum = op_tc($date,$type,35);
		$jqxn[]  = $jqxnsum ? floatval($jqxnsum) : 0;
		
		//京外业务
		$jwywsum = op_tc($date,$type,18);
		$jwyw[]  = $jwywsum ? floatval($jwywsum) : 0;
		
		//常规旅游
		$cglysum = op_tc($date,$type,19);
		$cgly[]  = $cglysum ? floatval($cglysum) : 0;
		
		//总计
		$zongsum = op_tc($date,$type,0);
		$zong[]  = $zongsum ? floatval($zongsum) : 0;
		
	}
	
	$rs = array();
	$rs[0]['name'] = '京区校内';
	$rs[0]['data'] = $jqxn;
	$rs[1]['name'] = '京区校外';
	$rs[1]['data'] = $jqxw;
	$rs[2]['name'] = '京外业务';
	$rs[2]['data'] = $jwyw;
	$rs[3]['name'] = '常规业务';
	$rs[3]['data'] = $cgly;
	$rs[4]['name'] = '总计';
	$rs[4]['data'] = $zong;
	
	return json_encode($rs);
	
	
}

//项目收入
function op_income($date,$type=1,$dept=0,$kind=0){
	
	$db  = M();
	$day = month_phase($date);	
	//京区校外
	
	if($type==1){
		$field = 'b.shouru';	
	}else{
		$field = 'b.maoli';	
	}
	
	$sum = 0;
	//结算的项目
	$where = array();
	if($dept) $where['o.create_user'] = array('in',Roleinuser($dept));
	if($kind) $where['o.kind']        = $kind;
	$where['a.audit_time']  = array('between',array($day['start'],$day['end']));	
	$where['b.audit']       = 1;
	$sum += $db->table('__OP__ as o')
			->join('__OP_SETTLEMENT__ as b on b.op_id = o.op_id','LEFT')
			->join('__AUDIT_LOG__ as a on a.req_id = b.id and a.req_type = 801','LEFT')
			->where($where)->sum($field);
	
	//未结算的项目
	$where = array();
	if($dept) $where['o.create_user'] = array('in',Roleinuser($dept));
	$where['a.audit_time']  = array('between',array($day['start'],$day['end']));	
	$where['b.audit']       = 1;
	$where['s.audit']       = array('neq','1');
	
	$sum += $db->table('__OP__ as o')
			->join('__OP_SETTLEMENT__ as s on s.op_id = o.op_id','LEFT')
			->join('__OP_BUDGET__ as b on b.op_id = o.op_id','LEFT')
			->join('__AUDIT_LOG__ as a on a.req_id = b.id and a.req_type = 800','LEFT')
			->where($where)
			->sum($field);
	
	
	
		   
	return $sum;
}


//业务部门收入
function business($bumen,$year,$type=1){
	$kind = M('project_kind')->where(array('name'=>array('like',$bumen.'-%')))->order('id ASC')->select();
	$html = array();
	foreach($kind as $k=>$v){
		$unitdata = array();
		for($i=1;$i<=12;$i++){	
			$sum = op_income($year.'-'.$i.'-01',$type,0,$v['id']);
			$unitdata[]  = $sum ? intval($sum) : 0;
		}
		if($k==0){
			$html[] = '{name:\''.trim(trim($v['name'],$bumen),'-').'\',data:['.implode(',',$unitdata).']}';
		}else{
			$html[] = '{name:\''.trim(trim($v['name'],$bumen),'-').'\',data:['.implode(',',$unitdata).'],visible: false}';
		}
		
	}
	
	return '['.implode(',',$html).']';
	
}




//项目收入
function op_cycle($date,$dept=0,$kind=0){
	
	$db  = M();
	$day = month_phase($date);	
	//京区校外
	
	
	$sum = 0;
	//结算的项目
	$where = array();
	//if($dept) $where['o.create_user'] = array('in',Roleinuser($dept));
	if($kind) $where['o.kind']        = $kind;
	$where['a.audit_time']  = array('between',array($day['start'],$day['end']));	
	$where['b.audit']       = 1;
	
	$field = '(a.audit_time-o.create_time)/86400 as days';
	$lists = $db->field($field)->table('__OP__ as o')
			->join('__OP_SETTLEMENT__ as b on b.op_id = o.op_id','LEFT')
			->join('__AUDIT_LOG__ as a on a.req_id = b.id and a.req_type = 801','LEFT')
			->where($where)->select();
	
	
	$i=0;
	foreach($lists as $k=>$v){
		$sum += $v['days'];
		$i++;
	}
	
	
		   
	return round($sum/$i);
}


//业务部门结算周期
function cycle($bumen,$year,$type=1){
	$kind = M('project_kind')->where(array('name'=>array('like',$bumen.'-%')))->order('id ASC')->select();
	$html = array();
	foreach($kind as $k=>$v){
		$unitdata = array();
		for($i=1;$i<=12;$i++){	
			$sum = op_cycle($year.'-'.$i.'-01',0,$v['id']);
			$unitdata[]  = $sum ? intval($sum) : 0;
		}
		if(array_sum($unitdata)){
			$html[] = '{name:\''.trim(trim($v['name'],$bumen),'-').'\',data:['.implode(',',$unitdata).']}';
		}else{
			$html[] = '{name:\''.trim(trim($v['name'],$bumen),'-').'\',data:['.implode(',',$unitdata).'],visible: false}';
		}
		
	}
	
	return '['.implode(',',$html).']';
	
}

?>


