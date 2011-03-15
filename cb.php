<?php

//CDN
$CDN = 'http://a.tbcdn.cn/';
//$CDN = 'http://114.80.174.242/';

/**
 * combo.php
 * 只处理\?\?.+的情况
 * 拔赤 - lijine00333@163.com
 */

//抓取文件
function get_contents($url){
    $ch =curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $str =curl_exec($ch);
    curl_close($ch);
    if ($str !==false) {
        return $str;
    }else {
        return '';
    }  
}

//得到扩展名
function get_extend($file_name) { 
	$extend =explode("." , $file_name); 
	$va=count($extend)-1;
	return $extend[$va];
} 

/**
 * begin
 */

//cdn上存在的各种可能的文件类型
$header = array(
    'js' => 'Content-Type: application/x-javascript',
    'css' => 'Content-Type: text/css',
    'jpg' => 'Content-Type: image/jpg',
    'gif' => 'Content-Type: image/gif',
    'png' => 'Content-Type: image/png',
    'jpeg' => 'Content-Type: image/jpeg',
    'swf' => 'Content-Type: application/x-shockwave-flash'
);
//文件类型
$type = '';

//文件完整路径数组
$files = array();
//文件相对路径数组
$tmp_files = array();

//前缀
$prefix = $_SERVER['SCRIPT_NAME'];
$split_a= explode("??",$_SERVER['REQUEST_URI']);
$tmp_files = explode(",",$split_a[1]);

if(preg_match('/,/',$split_a[1])){//多文件
	$_tmp = explode(',',$split_a[1]);
	foreach($_tmp as $v){
		$files[] = $prefix.$v;
	}
}else{//单文件
	$files[] = $prefix.$split_a[1];
}

$R_files = array();

foreach ($files as $k) {
	$k = preg_replace(
		array('/^\//','/\?.+$/','/-min\./'),
		array('','','.'),
		$k);
	//最后可能是一个逗号
	if(!preg_match('/(\.js|\.css)$/',$k)){
		continue;
	}

	while(preg_match('/[^\/]+\/\.\.\//',$k)){

		$k = preg_replace(
			array('/[^\/]+\/\.\.\//'),
			array(''),
			$k,1);
	}

    if(empty($type)) {
		$type = get_extend($k);
    }
	//文件存在
    if(file_exists($k)) {
		$R_files[] = file_get_contents($k);
    }else{
		//文件不存在
		try{
			$R_files[] = '/***** http://a.tbcdn.cn/'.$k.' *****/';
			$R_files[] = join('',file($CDN.$k));
		}catch(Exception $e){}
    }
}
//添加过期头
header("Expires: " . date("D, j M Y H:i:s", strtotime("now + 10 years")) ." GMT");
//文件类型
header($header[$type]);
//拼装文件
$result = join("\n",$R_files);
//输出文件
echo $result;
//echo 1;
?>
