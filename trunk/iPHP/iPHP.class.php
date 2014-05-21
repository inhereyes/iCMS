<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package common
* @$Id: iPHP.php 2330 2014-01-03 05:19:07Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');

class iPHP{
	public static $pagenav      = NULL;
	public static $offset       = NULL;
	public static $break        = true;
	public static $dialogTitle  = 'iPHP';
	public static $dialogCode   = false;
	public static $dialogLock   = false;
	public static $dialogObject = 'parent.';
	public static $iTPL         = NULL;
	public static $iTPLMode     = null;

	public static function iTPL(){
        $iTPL                    = new iTemplate();
        $iTPL->template_dir      = iPHP_TPL_DIR;
        #$iTPL->def_template_dir = self::_def_tpl();
        $iTPL->compile_dir       = iPHP_TPL_CACHE;
        $iTPL->left_delimiter    = '<!--{';
        $iTPL->right_delimiter   = '}-->';
        $iTPL->register_modifier("date", "get_date");
        $iTPL->register_modifier("cut", "csubstr");
        $iTPL->register_modifier("htmlcut","htmlSubString");
        $iTPL->register_modifier("count","cstrlen");
        $iTPL->register_modifier("html2txt","HtmToText");
        //$iTPL->register_modifier("pinyin","GetPinyin");
        $iTPL->register_modifier("unicode","getunicode");
        $iTPL->register_modifier("small","gethumb");
        $iTPL->register_modifier("thumb","small");
        $iTPL->register_modifier("random","random");
        self::$iTPL = $iTPL;
        return $iTPL;
	}
    public static function tpl_vars($key=null){
        return self::$iTPL->get_template_vars($key);
    }
    public static function clear_compiled_tpl($file = null){
    	self::$iTPL->clear_compiled_tpl($file);
    }
    public static function assign($key,$value) {
        self::$iTPL->assign($key,$value);
    }
    public static function append($key, $value=null, $merge=false) {
        self::$iTPL->append($key,$value,$merge);
    }
    public static function clear($key) {
        self::$iTPL->clear_assign($key);
    }
    public static function display($tpl){
    	self::$iTPL->display($tpl);
    }
    public static function fetch($tpl){
    	return self::$iTPL->fetch($tpl);
    }
    public static function pl($tpl) {
        if(self::$iTPLMode=='html') {
            return self::$iTPL->fetch($tpl);
        }else {
            self::$iTPL->display($tpl);
            //echo iFS::sizeUnit(xdebug_memory_usage());
            //echo iFS::sizeUnit(xdebug_peak_memory_usage());            
        }
    }
	public static function PG($key){
		return isset($_POST[$key])?$_POST[$key]:$_GET[$key];
	}
	public static function router($key,$static=false){
		if($static) return $key;

		$path   = iPHP_APP_CORE.'/iURL.define.php';
		$router = self::import($path,true);

		if(is_array($key)){
			if(is_array($key[1])){
				$url = $router[$key[0]];
				preg_match_all('/\{(\w+)\}/i',$url, $matches);
				$url = str_replace($matches[0], $key[1], $url);
			}else{
				$url = preg_replace('/\{\w+\}/i',$key[1], $router[$key[0]]);
			}
			$key[2] && $url = $key[2].$url;
		}else{
			$url = $router[$key];
		}
		return $url;
	}	
	// 获取客户端IP
	public static function getIp($format=0) {
	    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	        $onlineip = getenv('HTTP_CLIENT_IP');
	    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	        $onlineip = getenv('REMOTE_ADDR');
	    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	        $onlineip = $_SERVER['REMOTE_ADDR'];
	    }
	    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	    $ip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	    if($format) {
	        $ips = explode('.', $ip);
	        for($i=0;$i<3;$i++) {
	            $ips[$i] = intval($ips[$i]);
	        }
	        return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]);
	    } else {
	        return $ip;
	    }
	}
	//设置COOKIE
	public static function setCookie($name, $value = "", $time = 0) {
	    $cookiedomain	= iPHP_COOKIE_DOMAIN;
	    $cookiepath		= iPHP_COOKIE_PATH;
	    $cookietime		= ($time?$time:iPHP_COOKIE_TIME);
	    $name 			= iPHP_COOKIE_PRE.'_'.$name;
	    $_COOKIE[$name] = $value;
	    setcookie($name, $value,time()+$cookietime,$cookiepath, $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
	}
	//取得COOKIE
	public static function getCookie($name) {
	    $name	= iPHP_COOKIE_PRE.'_'.$name;
	    if (isset($_COOKIE[$name])) {
	        return $_COOKIE[$name];
	    }
	    return FALSE;
	}
    public static function getUniCookie($s){
		$s = str_replace('\\\u','\\u',self::getCookie($s));
		$u = json_decode('["'.$s.'"]');
		return $u[0];
    }
    public static function import($path,$r=false){
		$key	= str_replace(iPATH,'iPHP://',$path);
		if($r){
			if(!isset($GLOBALS['_iPHP_REQ'][$key])){
				$GLOBALS['_iPHP_REQ'][$key] = include $path;
			}
			return $GLOBALS['_iPHP_REQ'][$key];
		}

      	if(isset($GLOBALS['_iPHP_REQ'][$key])) return;
      	
		$GLOBALS['_iPHP_REQ'][$key] = true;
		require $path;
    }
	public static function loadClass($name,$msg=''){
		if (!class_exists($name)){
		    $path = iPHP_CORE.'/i'.$name.'.class.php';
			$msg && self::throwException($msg,1010);
		    self::import($path);
	    }
	}
	
    public static function app($app = NULL,$args = NULL){
    	$app_dir	= $app_name = $app;
    	if(is_array($app)){
    		$app_dir	= $app[0];
    		$app_name	= $app[1];
    	}
    	self::import(iPHP_APP.'/'.$app_dir.'/'.$app_name.'.app.php');
    	$app_name	= $app_name.'App';
    	if($args){
			return new $app_name($args);
		}
		return new $app_name();
    }
    public static function appFun($fun = NULL){
    	$fun_dir	= $fun_name = $fun;
    	if(is_array($fun)){
    		$fun_dir	= $fun[0];
    		$fun_name	= $fun[1];
    	}
    	self::import(iPHP_APP.'/'.$fun_dir.'/'.$fun_name.'.tpl.php');
    }
    public static function appClass($class = NULL,$args = NULL){
    	$class_dir	= $class_name = $class;
    	if(is_array($class)){
    		$class_dir	= $class[0];
    		$class_name	= $class[1];
    	}
    	self::import(iPHP_APP.'/'.$class_dir.'/'.$class_name.'.class.php');
    	
    	if($args==="break") return;

    	if($args){
			return new $class_name($args);
		}
		return new $class_name();
    }
	public static function throwException($msg, $code,$name='',$h404=true) {
		if(!headers_sent() && $h404){
			header("HTTP/1.1 404 Not Found");
		}
	    trigger_error('<B>iPHP '.$name.' Fatal Error:</B>'.$msg. '(' . $code . ')',E_USER_ERROR);
	}
	public static function page_p2num($path,$page=false){
		$page===false && $page	= $GLOBALS['page'];
		if($page<2){
			return str_replace(array('_{P}','&p={P}'),'',$path);
		}
		return str_replace('{P}',$page,$path);
	}
	public static function page($iurl){
		if(isset($GLOBALS['iPage'])) return;

		$GLOBALS['iPage']['url']  = $iurl->pageurl;
		$GLOBALS['iPage']['html'] = array('enable'=>true,'index'=>$iurl->href,'ext'=>$iurl->ext);
	}
    public static function lang($string='') {
    	if(empty($string)) return false;

		$keyArray  = explode(':',$string);
		$count     = count($keyArray);
		list($app,$do,$key,$msg) = $keyArray;   
		 	
		$fname     = $app.'.lang.php';
		$path      = iPHP_APP_CORE.'/lang/'.$fname;

		if(!@is_file($path)) return false;

		$langArray = self::import($path,true);
	
		switch ($count) {
			case 1:return $langArray;
			case 2:return $langArray[$do];
			case 3:return $langArray[$do][$key];
			case 4:return $langArray[$do][$key][$msg];
		}
    }
	//检查验证码
	public static function seccode($seccode,$type='F') {
	    $_seccode		= self::getCookie('seccode');
	    $cookie_seccode = empty($_seccode)?'':authcode($_seccode, 'DECODE');
	    if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
	        return false;
	    }else {
	        return true;
	    }
	}
	public static function http404($v,$code="",$b=true){
		if(empty($v)){
			header("X-iPHP-ECODE:".$code);
			header("HTTP/1.1 404 Not Found");
			//self::gotourl(iPHP_URL_404);
			$b && exit();
		}
	}
	public static function andSQL($vars,$field,$not='') {
	    if(is_array($vars)) {
	        $ids=implode(',',$vars);
	        $sql=$not=='not'?" AND $field NOT IN ($ids)":" AND $field IN ($ids) ";
	    }else {
	        $vars=addslashes($vars);
	        $sql=$not=='not'?" AND $field<>'$vars'  ":" AND $field='$vars' ";
	    }
	    return $sql;
	}
	public static function str2time($str="0") {
		$correct     = 0;
		$str OR $str ='now';
		$time        = strtotime($str);
		(int)iPHP_TIME_CORRECT && $correct = (int)iPHP_TIME_CORRECT*60;
	    return $time+$correct;
	}
    public static function json($a,$break=true,$ret=false){
    	$callback	= $_GET['callback'];
    	header("Access-Control-Allow-Origin: ".__HOST__);
    	$json	= json_encode($a);
    	$callback && $json	=$callback.'('.$json.')';
    	if($ret){
    		return $json;
    	}
    	echo $json;
    	$break && exit();
    }
    public static function code($code=0,$msg='',$forward='',$format=''){
    	strstr($msg,':') && $msg = self::lang($msg);
    	$a = array('code'=>$code,'msg'=>$msg,'forward'=>$forward);
    	if($format=='json'){
    		self::json($a);
    	}
        return $a;
    }
    public static function msg($info,$ret=false) {
    	list($label,$icon,$content)= explode(':#:',$info);
    	$msg = '<div class="iPHP-msg"><span class="label label-'.$label.'">';
    	$icon && $msg.= '<i class="fa fa-'.$icon.'"></i> ';
    	if(strstr($content,':')){
    		$lang = self::lang($content);
    		$lang && $content = $lang;
    	}
    	$msg.= $content.'</span></div>';       
    	if($ret) return $msg;
    	echo $msg;
    }
	public static function js($js="js:",$ret=false) {
        $A		= explode(':',$js);
        switch ($A[0]){
        	case 'js':
				$A[1] 		&& $code	= $A[1];
				$A[1]=="0"	&& $code	= self::$dialogObject.'history.go(-1);';
				$A[1]=="1"	&& $code	= self::$dialogObject.'location.reload();';
        	break;
        	case 'url':	
				$A[1]=="1" && $A[1]	= __REF__;
	        	$code	= self::$dialogObject."location.href='".$A[1]."';";
        	break;
        	case 'src':	$code	= self::$dialogObject."$('#iPHP_FRAME').attr('src','".$A[1]."');";break;
        	default:	$code	= '';
        }
        
        if($ret) return $code;
        
        echo '<script type="text/javascript">'.$code.'</script>';
        self::$break && exit();
    }
	public static function alert($msg,$js=null,$s=3) {
		self::$dialogLock	= true;
		self::dialog('warning:#:warning:#:'.$msg,$js,$s);
    }
	public static function OK($msg,$js=null,$s=3) {
		self::$dialogLock	= true;
		self::dialog('success:#:check:#:'.$msg,$js,$s);
    }
	public static function dialog($info=array(),$js='js:',$s=3,$buttons=null,$update=false) {
		$info    = (array)$info;
		$title   = $info[1]?$info[1]:'提示信息';
		$content = $info[0];
        strstr($content,':#:') && $content=self::msg($content,true);
		$content = addslashes($content);
		$dialog  = "var dialog = ".self::$dialogObject."$.dialog({
		    id: 'iPHP_DIALOG',width: 360,height: 150,fixed: true,
		    title: '".self::$dialogTitle." - {$title}',content: '{$content}',";
		$autoFun = 'dialog.close();';
		$fun     = self::js($js,true,$obj);
		if($fun){
      		$dialog.='cancelValue: "确定",cancel: function(){'.$fun.'return true;},';
      		$autoFun = $fun.'dialog.close();';
		}
        if(is_array($buttons)) {
            foreach($buttons as $key=>$val) {
            	$val['url'] && $fun 	= self::$dialogObject."location.href='{$val['url']}';";
            	$val['src'] && $fun 	= self::$dialogObject."$('#iPHP_FRAME').attr('src','".$val['src']."');return false;";
                $val['top'] && $fun 	= "top.window.open('{$val['url']}','_blank');";
                $val['id']	&& $id		= "id: '".$val['id']."',";
                $buttonA[]="{{$id}value: '".$val['text']."',callback: function () {".$fun."}}";
                $val['next'] && $autoFun = $fun;
            }
            $button	= implode(',',$buttonA);
      	}
		$dialog.="});";
        if($update){
        	$dialog	= "var dialog = ".self::$dialogObject."$.dialog.get('PHP_DIALOG');";
			$dialog.="dialog.content('{$content}');";
			$autoFun = $fun;
        }
		$button	&& $dialog.="dialog.button(".$button.");";
        self::$dialogLock && $dialog.='dialog.lock();';
        $s<=30	&& $timeount	= $s*1000;
        $s>30	&& $timeount	= $s;
        $s===false && $timeount	= false;
        if($timeount){
        	$dialog.='window.setTimeout(function(){'.$autoFun.'},'.$timeount.');';
        }else{
        	$update && $dialog.=$autoFun;
        }
		echo self::$dialogCode?$dialog:'<script type="text/javascript">'.$dialog.'</script>';
        self::$break && exit();
    }
    public static function UTF8toUni($c) {
        switch(strlen($c)) {
            case 1:
                return ord($c);
            case 2:
                $n = (ord($c[0]) & 0x3f) << 6;
                $n += ord($c[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($c[0]) & 0x1f) << 12;
                $n += (ord($c[1]) & 0x3f) << 6;
                $n += ord($c[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($c[0]) & 0x0f) << 18;
                $n += (ord($c[1]) & 0x3f) << 12;
                $n += (ord($c[2]) & 0x3f) << 6;
                $n += ord($c[3]) & 0x3f;
                return $n;
        }
    }
    public static function pinyin($str,$split="",$pn=true) {
        if(!isset($GLOBALS["iPHP.PY"])) {
            $GLOBALS["iPHP.PY"]=unserialize(gzuncompress(iFS::read(iPHP_PATH.'/pinyin.table')));
        }
        preg_match_all('/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/',trim($str),$match);
        $s = $match[0];
        $c = count($s);
        for ($i=0;$i<$c;$i++) {
            $uni	= strtoupper(dechex(self::UTF8toUni($s[$i])));
            if(strlen($uni)>2) {
				$pyArr = $GLOBALS["iPHP.PY"][$uni];
				$py    = is_array($pyArr)?$pyArr[0]:$pyArr;
                $pn && $py=str_replace(array('1','2','3','4','5'), '', $py);
                $zh && $split && $R[]=$split;
				$R[]  = strtolower($py);
				$zh   = true;
				$az09 = false;
            }else if(preg_match("/[a-z0-9]/i",$s[$i])) {
                $zh && $i!=0 && !$az09 && $split && $R[]=$split;
				$R[]  = $s[$i];
				$zh   = true;
				$az09 = true;
            }else {
                $sp=true;
                if($split) {
                    if($s[$i]==' ') {
                        $R[]=$sp?'':$split;
                        $sp=false;
                    }else {
                        $R[]=$sp?$split:'';
                        $sp=true;
                    }
                }else {
                    $R[]='';
                }
				$zh   = false;
				$az09 = false;
            }
        }
        return str_replace(array('Üe','Üan','Ün','lÜ','nÜ'),array('ue','uan','un','lv','nv'),implode('',(array)$R));
    }
	//翻页函数
	public static function pagenav($total,$displaypg=20,$unit="条记录",$url='',$target='') {
	    $displaypg	= intval($displaypg);
	    $page		= $GLOBALS["page"]?intval($GLOBALS["page"]):1;
	    $lastpg		= ceil($total/$displaypg); //最后页，也是总页数
	    $page		= min($lastpg,$page);
	    $prepg		= (($page-1)<0)?"0":$page-1; //上一页
	    $nextpg		= ($page==$lastpg ? 0 : $page+1); //下一页
	    self::$offset	= ($page-1)*$displaypg;
	    self::$offset<0 && self::$offset=0;
	    $url OR $url= $_SERVER["REQUEST_URI"];
	    $urlA	= parse_url($url);
	    parse_str($urlA["query"], $query);
	    $query['totalNum']	= $total;
	    $query['page']		= "";
	    $urlA["query"]		= http_build_query($query);
	    $url	= $urlA["path"].'?'.$urlA["query"];
	    self::$pagenav="<ul><li><a href='{$url}1' target='_self'>首页</a></li>";
	    self::$pagenav.=$prepg?"<li><a href='{$url}$prepg' target='_self'>上一页</a></li>":'<li class="disabled"><a href="#">上一页</a></li>';
	    $flag=0;
	    for($i=$page-2;$i<=$page-1;$i++) {
	        if($i<1) continue;
	        self::$pagenav.="<li><a href='{$url}$i' target='_self'>$i</a></li>";
	    }
	    self::$pagenav.='<li class="active"><a href="#">'.$page.'</a></li>';
	    for($i=$page+1;$i<=$lastpg;$i++) {
	        self::$pagenav.="<li><a href='{$url}$i' target='_self'>$i</a></li>";
	        $flag++;
	        if($flag==4) break;
	    }
	    self::$pagenav.=$nextpg?"<li><a href='{$url}$nextpg' target='_self'>下一页</a></li>":'<li class="disabled"><a href="#">下一页</a></li>';
	    self::$pagenav.="<li><a href='{$url}$lastpg' target='_self'>末页</a></li>";
	    self::$pagenav.="<li> <span class=\"muted\">共{$total}{$unit}，{$displaypg}{$unit}/页 共{$lastpg}页</span></li>";
	    for($i=1;$i<=$lastpg;$i=$i+5) {
	        $s=$i==$page?' selected="selected"':'';
	        $select.="<option value=\"$i\"{$s}>$i</option>";
	    }
	    if($lastpg>200) {
	        self::$pagenav.="<li> <span class=\"muted\">跳到 <input type=\"text\" id=\"pageselect\" style=\"width:24px;height:12px;margin-bottom: 0px;line-height: 12px;\" /> 页 <input class=\"btn btn-small\" type=\"button\" onClick=\"window.location='{$url}'+$('#pageselect').val();\" value=\"跳转\" style=\"height: 22px;line-height: 18px;\"/></span></li>";
	    }else {
	        self::$pagenav.="<li> <span class=\"muted\">跳到 <select id=\"pageselect\" style=\"width:48px;height:20px;margin-bottom: 3px;line-height: 16px;padding: 0px\" onchange=\"window.location='{$url}'+this.value\">{$select}</select>页</span></li>";
	    }
	    self::$pagenav.='</ul>';
	    //(int)$lastpg<2 &&UCP::$pagenav='';
	}
	public static function total($tnkey,$sql,$type=null){
    	$tnkey	= substr($tnkey,8,16);
    	$total	= (int)$_GET['totalNum'];
    	if(empty($total) && $type!='G'){
//    		$total	= (int)self::getCookie($tnkey);
    		$total	= (int)iCache::get('iTotalNum/'.$tnkey);
		}
    	if(empty($total) || $GLOBALS['removeTotal']){
        	$total	= iDB::getValue($sql);
        	//echo iDB::$last_query;
        	if($type!='G'){
        		iCache::set('iTotalNum/'.$tnkey,$total,3600);
        		//self::setCookie($tnkey,$total,3600);
        	}
        }
        return $total;
	}
	public static function gotourl($URL=''){
	    $URL OR $URL=__REF__;
	    if(headers_sent()){
	         echo '<meta http-equiv=\'refresh\' content=\'0;url='.$URL.'\'><script type="text/javascript">window.location.replace(\''.$URL.'\');</script>';
	   	}else {
	        header("Location: $URL");
	    }
		exit;
	}
    //获取文件夹列表
    public static function folder($dir='',$type=NULL) {
    	$dir	= trim($dir,'/');
    	$sDir	= $dir;
    	$_GET['dir'] && $gDir	= trim($_GET['dir'],'/');
    	
    	
    	
//    	print_r('$dir='.$dir.'<br />');
//    	print_r('$gDir='.$gDir.'<br />');

    	//$gDir && $dir	= $gDir;
    	
        //strstr($dir,'.')!==false	&& self::alert('What are you doing?','',1000000);
        //strstr($dir,'..')!==false	&& self::alert('What are you doing?','',1000000);
		
		
        $sDir_PATH	= iFS::path_join(iPATH,$sDir);
        $iDir_PATH	= iFS::path_join($sDir_PATH,$gDir);

//    	print_r('$sDir_PATH='.$sDir_PATH."\n");
//    	print_r('$iDir_PATH='.$iDir_PATH."\n");
    	
		strpos($iDir_PATH,$sDir_PATH)===false && self::alert("对不起!您访问的目录有问题!");
		
        if (!is_dir($iDir_PATH)) {
            return false;
        }

		$url	= buildurl(false,'dir');
        if ($handle = opendir($iDir_PATH)) {
            while (false !== ($rs = readdir($handle))) {
//				print_r('$rs='.$rs."\n");
            	$filepath	= iFS::path_join($iDir_PATH,$rs);
				$filepath	= rtrim($filepath,'/');
//				print_r('$filepath='.$filepath."\n");
                $sFileType 	= filetype($filepath);
//				print_r('$sFileType='.$sFileType."\n");
				$path		= str_replace($sDir_PATH, '', $filepath);
                if ($sFileType	=="dir" && !in_array($rs,array('.','..','admincp'))) {
                    $dirArray[]	= array('path'=>$path,'name'=>$rs,'url'=>$url.urlencode($path));
                }
                if ($sFileType	=="file" && !in_array($rs,array('..','.iPHP'))) {
                	$filext		= iFS::getExt($rs);
	                $fileinfo	= array(
	                		'path'=>$path,
	                		'dir'=>dirname($path),
	                        'url'=>iFS::fp($path,'+http'),
	                        'name'=>$rs,
	                        'modified'=>get_date(filemtime($filepath),"Y-m-d H:i:s"),
	                        'md5'=>md5_file($filepath),
	                        'ext'=>$filext,
	                        'size'=>iFS::sizeUnit(filesize($filepath))
	                ); 
	                if($type){
	                	 in_array(strtolower($filext),$type) && $fileArray[]	= $fileinfo;
	                }else{
	                	$fileArray[]	= $fileinfo;
	                }
                }
            }
        }
		$a['DirArray']  = (array)$dirArray;
		$a['FileArray'] = (array)$fileArray;
		$a['pwd']       = str_replace($sDir_PATH, '', $iDir_PATH);
		$a['pwd']       = trim($a['pwd'],'/');
		$pos            = strripos($a['pwd'],'/');
		$a['parent']    = ltrim(substr($a['pwd'],0,$pos), '/');
		$a['URI']       = $url;
//    	print_r($a);
//    	exit;
        return $a;
    }
}

function buildurl($url=false,$str='') {
	$url	OR $url	= $_SERVER["REQUEST_URI"];
	$urlA	= parse_url($url);
	parse_str($urlA['query'], $query1);
	parse_str($str, $query2);
	$query         = array_merge($query1,$query2);
	$urlA['query'] = http_build_query($query); 
	$nurl          = glue_url($urlA);
	return $nurl?$nurl:$url;
}
function glue_url($parsed) {
    if (!is_array($parsed)) return false;

	$uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
	$uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
	$uri .= isset($parsed['host']) ? $parsed['host'] : '';
	$uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
	$uri .= isset($parsed['path']) ? $parsed['path'] : '';
	$uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
	$uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
	return $uri;
} 


function bitscale($a) {
	$a['th']==0 && $a['th']=9999;
	if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
		$a['h'] = ceil($a['h'] * ($a['tw']/$a['w']));
		$a['w'] = $a['tw'];
	}else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
		$a['w'] = ceil($a['w'] * ($a['th']/$a['h']));
		$a['h'] = $a['th'];
	}
	return $a;
}

function format_date($date,$isShowDate=true){
    $limit = time() - $date;
    if($limit < 60){
        return '刚刚';
    }
    if($limit >= 60 && $limit < 3600){
        return floor($limit/60) . '分钟之前';
    }
    if($limit >= 3600 && $limit < 86400){
        return floor($limit/3600) . '小时之前';
    }
    if($limit >= 86400 and $limit<259200){
        return floor($limit/86400) . '天之前';
    }
    if($limit >= 259200 and $isShowDate){
        return get_date($date,'Y-m-d');
    }else{
        return '';
    }
}
// 格式化时间
function get_date($timestamp=0,$format='') {
	$correct = 0;
	$format OR $format            = iPHP_DATE_FORMAT;
	$timestamp OR $timestamp      = time();
	(int)iPHP_TIME_CORRECT && $correct = (int)iPHP_TIME_CORRECT*60;
    return date($format,$timestamp+$correct);
}
//中文长度
Function cstrlen($str) {
    return csubstr($str,'strlen');
}
//中文截取
function csubstr($str,$len,$end=''){
	$len!='strlen' && $len=$len*2;
    //获取总的字节数  
    $ll = strlen($str);
    //字节数  
    $i = 0;  
    //显示字节数  
    $l = 0;       
    //返回的字符串  
    $s = $str;  
    while ($i < $ll)  {
        //获取字符的asscii  
        $byte = ord($str{$i});  
        //如果是1字节的字符  
        if ($byte < 0x80)  {
            $l++;
            $i++;
        }elseif ($byte < 0xe0){  //如果是2字节字符
            $l += 2;  
            $i += 2;  
        }elseif ($byte < 0xf0){   //如果是3字节字符
            $l += 2;  
            $i += 3;  
        }else{  //其他，基本用不到
            $l += 2;  
            $i += 4;  
        }
        if($len!='strlen'){
	        //如果显示字节达到所需长度  
	        if ($l >= $len){
	            //截取字符串
	            $s = substr($str, 0, $i);  
	            //如果所需字符串字节数，小于原字符串字节数  
	            if($i < $ll){
	                //则加上省略符号  
	                $s = $s . $end; break;  
	            }
	            //跳出字符串截取 
	            break;  
	        }
        }
    }
    //返回所需字符串 
    return $len!='strlen'?$s:$l;
}

//截取HTML
function htmlSubString($content,$maxlen=300,$suffix=FALSE) {
	$content   = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
	$wordrows  = 0;
	$outstr    = "";
	$wordend   = false;
	$beginTags = 0;
	$endTags   = 0;
    foreach($content as $value) {
        if (trim($value)=="") continue;

        if (strpos(";$value","<")>0) {
            if (!preg_match("/(<[^>]+?>)/si",$value) && cstrlen($value)<=$maxlen) {
                $wordend=true;
                $outstr.=$value;
            }
            if ($wordend==false) {
                $outstr.=$value;
                if (!preg_match("/<img([^>]+?)>/is",$value)&& !preg_match("/<param([^>]+?)>/is",$value)&& !preg_match("/<!([^>]+?)>/is",$value)&& !preg_match("/<br([^>]+?)>/is",$value)&& !preg_match("/<hr([^>]+?)>/is",$value)&&!preg_match("/<\/([^>]+?)>/is",$value)) {
                    $beginTags++;
                }else {
                    if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                        $endTags++;
                    }
                }
            }else {
                if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                    $endTags++;
                    $outstr.=$value;
                    if ($beginTags==$endTags && $wordend==true) break;
                }else {
                    if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<[br|BR]([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)&& !preg_match("/<\/([^>]+?)>/is",$value)) {
                        $beginTags++;
                        $outstr.=$value;
                    }
                }
            }
        }else {
            if (is_numeric($maxlen)) {
                $curLength=cstrlen($value);
                $maxLength=$curLength+$wordrows;
                if ($wordend==false) {
                    if ($maxLength>$maxlen) {
                        $outstr.=csubstr($value,$maxlen-$wordrows,FALSE,0);
                        $wordend=true;
                    }else {
                        $wordrows=$maxLength;
                        $outstr.=$value;
                    }
                }
            }else {
                if ($wordend==false) $outstr.=$value;
            }
        }
    }
    while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)) {
        $outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html",$outstr);
    }
    if (strpos(";".$outstr,"[html_")>0) {
        $outstr=str_replace("[html_&lt;]","<",$outstr);
        $outstr=str_replace("[html_&gt;]",">",$outstr);
    }
    if($suffix&&cstrlen($outstr)>=$maxlen)$outstr.="．．．";
    return $outstr;
}
//去掉多余的空标签
function strip_empty_html($matches) {
    $arr_tags1=explode(" ",$matches[1]);
    if ($arr_tags1[0]==$matches[2]) {
        return "";
    }else {
        $matches[0]=str_replace("<","[html_&lt;]",$matches[0]);
        $matches[0]=str_replace(">","[html_&gt;]",$matches[0]);
        return $matches[0];
    }
}

function sechtml($string) {
	$search  = array("/\s+/","/<(\/?)(script|iframe|style|object|html|body|title|link|meta|\?|\%)([^>]*?)>/isU","/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU");
	$replace = array(" ","&lt;\\1\\2\\3&gt;","\\1\\2",);
	$string  = preg_replace ($search, $replace, $string);
    return $string;
}
//HTML TO TEXT
function HtmToText($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = HtmToText($val);
        }
    } else {
		$search  = array ("'<script[^>]*?>.*?</script>'si","'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'","'&(quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i","'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i","'&(copy|#169);'i","'&#(\d+);'e");
		$replace = array ("", "", "\\1", "\"", "&", "<", ">", " ", chr(161), chr(162), chr(163), chr(169), "chr(\\1)");
		$string  = preg_replace ($search, $replace, $string);
    }
    return $string;
}
function HTML2JS($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = HTML2JS($val);
        }
    } else {
        $string = str_replace(array("\n","\r","\\","\""), array(' ',' ',"\\\\","\\\""), $string);
    }
    return $string;
}
function dhtmlspecialchars($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val);
        }
    } else {
    	$string = str_replace(array("\0","%00"),'',$string);
        $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
                str_replace(array('&', '"',"'", '<', '>'), array('&amp;', '&quot;','&#039;', '&lt;', '&gt;'), $string));
    }
    return $string;
}
function unhtmlspecialchars($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = unhtmlspecialchars($val);
        }
    } else {
        $string = str_replace (array('&amp;','&#039;','&quot;','&lt;','&gt;'), array('&','\'','\"','<','>'), $string );
    }
    return $string;
}

function random($length, $numeric = 0) {
    if($numeric) {
        $hash = sprintf('%0'.$length.'d', rand(0, pow(10, $length) - 1));
    } else {
		$hash  = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max   = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[rand(0, $max)];
        }
    }
    return $hash;
}
function get_avatar($uid,$size=0) {
	$nuid = abs(intval($uid));
	$nuid = sprintf("%08d", $nuid);
	$dir1 = substr($nuid, 0, 3);
	$dir2 = substr($nuid, 3, 2);
	$avatar	= 'avatar/'.$dir1.'/'.$dir2.'/'.$uid.".jpg";
	if ($size) {
		$avatar.= '_'.$size.'x'.$size.'.jpg';
	}
	return $avatar;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length   = 4;
	$key           = md5($key ? $key : iPHP_KEY);
	$keya          = md5(substr($key, 0, 16));
	$keyb          = md5(substr($key, 16, 16));
	$keyc          = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
	$cryptkey      = $keya.md5($keya.$keyc);
	$key_length    = strlen($cryptkey);
	
	$string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	
	$result        = '';
	$box           = range(0, 255);
	
	$rndkey        = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
		$j       = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp     = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a       = ($a + 1) % 256;
		$j       = ($j + $box[$a]) % 256;
		$tmp     = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result  .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
function strEX($haystack, $needle) {
    return !(strpos($haystack, $needle) === FALSE);
}
function array_diff_values($N, $O){
 	$diff['+'] = array_diff($N, $O);
 	$diff['-'] = array_diff($O, $N);
    return $diff;
}
function _int($n) {
    return 0-$n;
}
function getdirname($path=null){
	if (!empty($path)) {
		if (strpos($path,'\\')!==false) {
			return substr($path,0,strrpos($path,'\\')).'/';
		} elseif (strpos($path,'/')!==false) {
			return substr($path,0,strrpos($path,'/')).'/';
		}
	}
	return './';
}
function getunicode($string){
	if(empty($string)) return;

	$array = (array)$string;
	$json  = json_encode($array);
	return str_replace(array('["','"]'), '', $json);
}
function iPHP_ERROR_HANDLER($errno, $errstr, $errfile, $errline){
    $errno = $errno & error_reporting();
    if($errno == 0) return;
    defined('E_STRICT') OR define('E_STRICT', 2048);
    defined('E_RECOVERABLE_ERROR') OR define('E_RECOVERABLE_ERROR', 4096);
    $html="<pre>\n<b>";
    switch($errno){
        case E_ERROR:              $html.="Error";                  break;
        case E_WARNING:            $html.="Warning";                break;
        case E_PARSE:              $html.="Parse Error";            break;
        case E_NOTICE:             $html.="Notice";                 break;
        case E_CORE_ERROR:         $html.="Core Error";             break;
        case E_CORE_WARNING:       $html.="Core Warning";           break;
        case E_COMPILE_ERROR:      $html.="Compile Error";          break;
        case E_COMPILE_WARNING:    $html.="Compile Warning";        break;
        case E_USER_ERROR:         $html.="User Error";             break;
        case E_USER_WARNING:       $html.="User Warning";           break;
        case E_USER_NOTICE:        $html.="User Notice";            break;
        case E_STRICT:             $html.="Strict Notice";          break;
        case E_RECOVERABLE_ERROR:  $html.="Recoverable Error";      break;
        default:                   $html.="Unknown error ($errno)"; break;
    }
    $html.=":</b> $errstr\n";
    if(function_exists('debug_backtrace')){
        //print "backtrace:\n";
        $backtrace = debug_backtrace();
        array_shift($backtrace);
        foreach($backtrace as $i=>$l){
            $html.="[$i] in function <b>{$l['class']}{$l['type']}{$l['function']}</b>";
            $l['file'] && $html.=" in <b>{$l['file']}</b>";
            $l['line'] && $html.=" on line <b>{$l['line']}</b>";
            $html.="\n";
        }
    }
    $html.="\n</pre>";
    $html	= str_replace('\\','/',$html);
    $html	= str_replace(iPATH,'iPHP://',$html);
	header('HTTP/1.1 500 Internal Server Error');
	header('Status: 500 Internal Server Error');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
    $_GET['frame'] OR exit($html);
    $html	= str_replace("\n",'<br />',$html);
    iPHP::$dialogLock	= true;
    iPHP::dialog(array("warning:#:warning-sign:#:".$html,'系统错误!可发邮件到 idreamsoft@qq.com 反馈错误!我们将及时处理'),'js:1',30);
    exit;
}