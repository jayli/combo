<?php

//CDN
$CDN = 'http://a.tbcdn.cn/';
//$CDN = 'http://114.80.174.242/';

/**
 * combo.php
 * 只处理\?\?.+的情况
 * apache conf
 *	 <VirtualHost *:80>
 *		ServerAdmin webmaster@dummy-host.example.com
 *		DocumentRoot "D:/dev/a.tbcdn.cn/"
 *		ServerName a.tbcdn.cn
 *
 *		RewriteEngine On
 *		RewriteCond %{QUERY_STRING} ^\?.*\.(js|css)$ [NC]
 *		RewriteRule ^/(.*)$ /cb.php?%{REQUEST_URI} [QSA,L,NS]
 *
 *		RewriteCond D:/dev/a.tbcdn.cn/%{REQUEST_FILENAME} !-F
 *		RewriteRule ^/(.+)$ http://a.tbcdn.cn/$1 [QSA,P,L]
 *
 *		<Directory D:/dev/a.tbcdn.cn/>
 *		Order deny,allow
 *			Allow from All
 *		</Directory>
 *		<IfModule expires_module>
 *			ExpiresActive On
 *			ExpiresDefault "access plus 10 years"
 *		</IfModule>
 *	</VirtualHost>
 *
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
		array('/^\//'),
		array(''),
		$k);

    if(empty($type)) {
		$type = get_extend($k);
    }
	//文件存在
    if(file_exists($k)) {
		$R_files[] = file_get_contents($k);
    }else{
		//文件不存在
		$R_files[] = '/***** http://a.tbcdn.cn/'.$k.' *****/';
		$R_files[] = join('',file($CDN.$k));
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
?>
