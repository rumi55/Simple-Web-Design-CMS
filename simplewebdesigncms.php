<?php
//
// ###################################################
//
// Simple Web Design CMS
//
// Release: 20091109
//
// ###################################################
//
// Set multiple usernames with password or just one username with a password:
$users = array(
	'username'=>'password',
//	'username2'=>'password2',
//	'username3'=>'password3',
);

// Disable extentions for editing (e.g. .gif .jpg .png .ico):
$ext_not_for_edit = array('.gif','.jpg','.png','.ico');

// Set path to document root:
$path = $_SERVER['DOCUMENT_ROOT']; // Your document root.

// Set maximum upload size in bytes:
$upload_max = 1000000; // 1 MB.

/*
 * Below this line not for editing purposes.
 */

// ###################################################

error_reporting(0);
ini_set('default_charset','UTF-8');

if(isset($_GET['echo'])){
	switch($_GET['echo']){
		case 'css': echo get_css(); break;
		case 'img': echo get_img(); break;
	}
	exit;
}
if(isset($_GET['logout'])){
	setcookie('swdc','',time() - 3600);
	header('Location: simplewebdesigncms.php');
	exit;
}
if(!check_login($users)){
	echo get_html_login();
	exit;
}
if(substr($path,-1) == '/'){
	$path = substr($path,0,-1);
}
if(isset($_GET['path'])){
	$path = $_GET['path'];
}
if($path == ''){
	$path = '/';
}
$path = str_replace('//','/',$path);
$mmkdir = false;
if(isset($_POST['mkdir'])){
	if(!file_exists($path.'/'.$_POST['mkdir'])){
		mkdir($path.'/'.$_POST['mkdir'],0777,true);
		$mmkdir = true;
	}
}
$mmkfile = false;
if(isset($_POST['mkfile'])){
	if(!file_exists($path.'/'.$_POST['mkfile'])){
		file_put_contents($path.'/'.$_POST['mkfile'],'');
		chmod($path.'/'.$_POST['mkfile'],0777);
		$mmkfile = true;
	}
}
$mfile = false;
if(isset($_FILES['file']) && !empty($_FILES['file'])
	&& $_FILES['file']['error'] == 0 && $_FILES['file']['size'] <= $upload_max){
	$upload_file = $path.'/'.basename($_FILES['file']['name']);
	if(move_uploaded_file($_FILES['file']['tmp_name'],$upload_file)){
		chmod($upload_file,0777);
		$mfile = true;
	}
}
$mdeld = false;
$mdelf = false;
if(isset($_GET['del'])){
	if(file_exists($path.'/'.$_GET['del'])){
		chmod($path.'/'.$_GET['del'],0777);
		clearstatcache();
		if(is_dir($path.'/'.$_GET['del'])){
			del_dir($path.'/'.$_GET['del']);
			$mdeld = true;
		}else{
			unlink($path.'/'.$_GET['del']);
			$mdelf = true;
		}
	}
}

clearstatcache();
echo get_html_head($path);

if($mmkdir){echo '<span style="color:#f00;">Folder is made!</span><hr />';}
if($mmkfile){echo '<span style="color:#f00;">File is made!</span><hr />';}
if($mfile){echo '<span style="color:#f00;">File is uploaded!</span><hr />';}
if($mdeld){echo '<span style="color:#f00;">Folder is gone!</span><hr />';}
if($mdelf){echo '<span style="color:#f00;">File is gone!</span><hr />';}

if(isset($_GET['path']) && isset($_GET['file'])
	&& file_exists($_GET['path'].'/'.$_GET['file'])){
	$medit = false;
	if(isset($_POST['edit'])){
		$edit = $_POST['edit'];
		if(get_magic_quotes_gpc() == 1){
			$edit = stripslashes($edit);
		}
		if(file_put_contents($_GET['path'].'/'.$_GET['file'],
				htmlspecialchars_decode($edit,ENT_NOQUOTES))){
			chmod($_GET['path'].'/'.$_GET['file'],0777);
			clearstatcache();
			$medit = true;
		}
	}
	if($medit){echo '<span style="color:#f00;">File is saved!</span><hr />';}
	if(in_array(substr($_GET['file'],strrpos($_GET['file'],'.')),$ext_not_for_edit)){
		echo '<span style="color:#f00;">Not for edit!</span><hr />';
		echo '<table><tr><td>'.
		'<img src="simplewebdesigncms.php?echo=img&type=file" alt="Current Edit:" />'.
		'</td><td>'.
		'- '.$_GET['path'].'/'.$_GET['file'].' | '.get_rights($_GET['path'].'/'.$_GET['file']).' | '.
		'</td><td>'.
		'<a href="simplewebdesigncms.php?path='.$path.'" title="back">'.
		'<img src="simplewebdesigncms.php?echo=img&type=back" alt="Back" />'.
		'</a></td></tr></table>'.
		'<hr />';
	}else{
		echo '<table><tr><td>'.
		'<img src="simplewebdesigncms.php?echo=img&type=file" alt="Current Edit:" />'.
		'</td><td>'.
		'- '.$_GET['path'].'/'.$_GET['file'].' | '.get_rights($_GET['path'].'/'.$_GET['file']).' | '.
		'</td><td>'.
		'<a href="simplewebdesigncms.php?path='.$path.'" title="Back">'.
		'<img src="simplewebdesigncms.php?echo=img&type=back" alt="Back" />'.
		'</a></td></tr></table>'.
		'<hr />';
		echo '<form action="simplewebdesigncms.php?path='.
		$_GET['path'].'&file='.$_GET['file'].'" method="post">';
		echo '<input type="submit" name="save" value="Save File" />';
		echo '<br />';
		echo '<textarea name="edit" rows="100" cols="130">';
		echo htmlspecialchars(file_get_contents($_GET['path'].'/'.$_GET['file']),
			ENT_NOQUOTES,'UTF-8');
		echo PHP_EOL.'</textarea>';
		echo '</form><hr />';
	}
}else{
	echo show_dir($path);
	echo '<hr />';
}

echo get_html_foot();

function check_login($users){
	foreach($users as $user => $pass){
		if(isset($_COOKIE['swdc']) && $_COOKIE['swdc'] == md5($user.$pass)){
			return true;
		}elseif(isset($_POST['u']) && isset($_POST['p'])
			&& $_POST['u'] == $user && $_POST['p'] == $pass){
			setcookie('swdc',md5($_POST['u'].$_POST['p']));
			return true;
		}
	}
	return false;
}
function show_dir($path){
	$files = scandir($path);
	if(empty($files) || count($files) == 2){
		return '<span style="color:#f00;">Directory is empty!</span>';
	}
	$out = '<div id="show_dir"><table><tr>'.
	'<th colspan="2">Folder / File<hr /></th>'.
	'<th colspan="3">Permissions<hr /></th>'.
	'<th>Size<hr /></th>'.
	'<th>Last Modified<hr /></th>'.
	'<th>Goto<hr /></th>'.
	'<th>Delete<hr /></th>'.
	'</tr>';
	foreach($files as $file){
		if($file == '.' || $file == '..') continue;
		if(is_dir($path.'/'.$file)){
			$out .= '<tr><td>'.
			'<img src="simplewebdesigncms.php?echo=img&type=folder" alt="Folder" />'.
			'</td><td><a href="simplewebdesigncms.php?path='.
			$path.'/'.$file.'">'.
			$file.'/</a></td><td>'.
			get_rights_per($path.'/'.$file).'</td><td>'.
			get_rights_oct($path.'/'.$file).'</td><td>'.
			get_rights($path.'/'.$file).'</td><td class="size">'.
			get_file_size($path.'/'.$file).'</td><td>'.
			get_last_mod($path.'/'.$file).'</td><td>'.
			'<a href="simplewebdesigncms.php?path='.
			$path.'/'.$file.'" title="Goto Folder">'.
			'<img src="simplewebdesigncms.php?echo=img&type=forward" alt="Goto Folder" />'.
			'</a></td><td>'.
			'<a href="simplewebdesigncms.php?path='.
			$path.'&del='.
			$file.'" title="Delete Folder">'.
			'<img src="simplewebdesigncms.php?echo=img&type=del" alt="Delete Folder" />'.
			'</a></td></tr>';
		}else{
			$out .= '<tr><td>'.
			'<img src="simplewebdesigncms.php?echo=img&type=file" alt="File" />'.
			'</td><td>'.
			'<a href="simplewebdesigncms.php?path='.
			$path.'&file='.
			$file.'">'.
			'- '.$file.'</a></td><td>'.
			get_rights_per($path.'/'.$file).'</td><td>'.
			get_rights_oct($path.'/'.$file).'</td><td>'.
			get_rights($path.'/'.$file).'</td><td class="size">'.
			get_file_size($path.'/'.$file).'</td><td>'.
			get_last_mod($path.'/'.$file).'</td><td>'.
			'<a href="simplewebdesigncms.php?path='.
			$path.'&file='.
			$file.'" title="Goto Edit File">'.
			'<img src="simplewebdesigncms.php?echo=img&type=edit" alt="Goto Edit File" />'.
			'</a></td><td>'.
			'<a href="simplewebdesigncms.php?path='.
			$path.'&del='.
			$file.'" title="Delete File">'.
			'<img src="simplewebdesigncms.php?echo=img&type=del" alt="Delete File" />'.
			'</a></td></tr>';
		}
	}
	$out .= '</table></div>';
	return $out;
}
function del_dir($path){
	foreach(scandir($path) as $file){
		if($file == '.' || $file == '..') continue;
		if(is_dir($path.'/'.$file)){
			del_dir($path.'/'.$file);
//			rmdir($path.'/'.$file);
		}else{
			unlink($path.'/'.$file);
		}
	}
	if(is_dir($path)) rmdir($path);
}
function get_rights($file){
	$out = '';
	if(is_readable($file)){
		$out .= ' <span style="color:#0f0;">readable</span>';
	}else{
		$out .= ' <span style="color:#f00;">not readable</span>';
	}
	if(is_writable($file)){
		$out .= ' <span style="color:#0f0;">writable</span>';
	}else{
		$out .= ' <span style="color:#f00;">not writable</span>';
	}
	if(is_executable($file)){
		$out .= ' <span style="color:#0f0;">executable</span>';
	}else{
		$out .= ' <span style="color:#f00;">not executable</span>';
	}
	return $out;
}
function get_rights_oct($file){
	return substr(sprintf('%o', fileperms($file)), -4);
}
function get_rights_per($file){
	$perms = fileperms($file);
	if(($perms & 0xC000) == 0xC000){
		// Socket
		$info = 's';
	}elseif(($perms & 0xA000) == 0xA000){
		// Symbolic Link
		$info = 'l';
	}elseif(($perms & 0x8000) == 0x8000){
		// Regular
		$info = '-';
	}elseif(($perms & 0x6000) == 0x6000){
		// Block special
		$info = 'b';
	}elseif(($perms & 0x4000) == 0x4000){
		// Directory
		$info = 'd';
	}elseif(($perms & 0x2000) == 0x2000){
		// Character special
		$info = 'c';
	}elseif(($perms & 0x1000) == 0x1000){
		// FIFO pipe
		$info = 'p';
	}else{
		// Unknown
		$info = 'u';
	}

	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

	return $info;
}
function get_last_mod($file){
	return date('Y-m-d H:i:s',filemtime($file));
}
function get_file_size($file){
	return bytes2format(filesize($file));
}
function bytes2format($bytes){
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes).' '.$units[$pow];
}
function get_html_login(){
	$html = ''.
//	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
	'<html xmlns="http://www.w3.org/1999/xhtml"><head>'.
	'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
	'<title>Login @ Simple Web Design CMS</title>'.
	'<link href="simplewebdesigncms.php?echo=css" rel="stylesheet" type="text/css" />'.
	'</head><body>'.
	'<div id="head"><h1>Login @ Simple Web Design CMS</h1></div>'.
	'<div id="content">'.
	'<form action="simplewebdesigncms.php" method="post">'.
	'<table><tr><td>'.
	'Username:</td><td><input type="text" name="u" value="" />*</td>'.
	'</tr><tr><td>'.
	'Password:</td><td><input type="password" name="p" value="" />*</td>'.
	'</tr><tr><td>'.
	'&nbsp;</td><td><input type="submit" value="Login" />'.
	'</td></tr></table>'.
	'</form>'.
	get_html_foot();
	return $html;
}
function get_html_head($path){
	$html = ''.
//	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
	'<html xmlns="http://www.w3.org/1999/xhtml"><head>'.
	'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
	'<title>Simple Web Design CMS</title>'.
	'<link href="simplewebdesigncms.php?echo=css" rel="stylesheet" type="text/css" />'.
	'</head><body>'.
	'<div id="head">'.
	'<table><tr><td valign="top">'.
	'<a href="simplewebdesigncms.php" title="Home">'.
	'<img src="simplewebdesigncms.php?echo=img&type=home" alt="Home" /></a>'.
	'</td><td valign="top">'.
	'<a href="simplewebdesigncms.php?logout=true" title="Logout">'.
	'<img src="simplewebdesigncms.php?echo=img&type=exit" alt="Logout" /></a>'.
	'</td><td>'.
	'<form action="simplewebdesigncms.php?path='.$path.'" method="post">'.
	'<input type="text" name="mkdir" value="" />'.
	'<input type="submit" value="Create New Folder" />'.
	'</form>'.
	'</td><td>'.
	'<form action="simplewebdesigncms.php?path='.$path.'" method="post">'.
	'<input type="text" name="mkfile" value="" />'.
	'<input type="submit" value="Create New File" />'.
	'</form>'.
	'</td><td>'.
	'<form action="simplewebdesigncms.php?path='.$path.'" method="post" enctype="multipart/form-data">'.
	'<input type="file" name="file" value="" />'.
	'<input type="submit" value="Upload File" />'.
	'</form>'.
	'</td></tr></table>'.
	'</div>'.
	'<div id="content">'.
	'<hr />'.
	'<table><tr><td>'.
	'<img src="simplewebdesigncms.php?echo=img&type=folder_open" alt="Current Directory:" />'.
	'</td><td>'.
	$path.' | '.get_rights($path).' | '.
	'</td><td>'.
	'<a href="simplewebdesigncms.php?path='.substr($path,0,strrpos($path,'/')).'" title="One Dir Up">'.
	'<img src="simplewebdesigncms.php?echo=img&type=back" alt="One Dir Up" />'.
	'</a></td></tr></table>'.
	'<hr />';
	return $html;
}
function get_html_foot(){
	$html = '</div>'.
	'<div id="foot">'.
	'<a href="http://jhwd.nl" target="_blank">Developer</a>'.
	'</div>'.
	'</body></html>';
	return $html;
}
function get_css(){
	$css = '
body{
background-color:#ddd;
font-family:arial;
font-size:14px;
}

a:link{
color:#000;
text-decoration:underline;
}
a:visited{
color:#000;
text-decoration:underline;
}
a:hover{
color:#000;
cursor:pointer;
text-decoration:none;
}
a:active{
color:#f00;
cursor:pointer;
text-decoration:none;
}
a:focus,a:hover,a:active{
outline:none;
}

img{
border-style:none;
border-width:0;
}

#head,#foot,#content{
padding:10px;
}
#content{
background-color:#fff;
border:1px solid #000;
}

th{
font-size:80%;
background-color:#ddd;
}
#show_dir table{
width:100%;
}
#show_dir td{
font-size:80%;
}
#show_dir tr:hover{
background-color:#eee;
}

td.size{
text-align:right;
}
';
	return $css;
}
function get_img(){
	if(isset($_GET['type'])){
		switch($_GET['type']){
			case 'home':
				return base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAqUExURQMDA2ZmZrW1tc7Ozt7e3u/v74WFhZmZmTNmmQAAAP8AAP///zMzMwAAAJ22yHsAAAAOdFJOU/////////////////8ARcDcyAAAALRJREFUeNpi4EUDAAHEAGPw8PKysQEJgACCCrBxcfHwsrECeQABxADlc3Jy8bCxAlUABBADlM/ExMnFCVIBEEAMUD43N0gEqAIggBhgfFZWoAiQBxBADHA+KwtQhIcXIICAAgxQPgsLMwMPL0AAgbQA+ezsjDxAAaAZAAEEE+DggAoABBBYgJUFIgByB0AAgQWYWRAqAAIIJMDMzAwSAFJAAYAAAgrwIAFeXoAAYkD3PkCAAQA8zAmjVYYlDQAAAABJRU5ErkJggg==');
			case 'exit':
				return base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAJUExURf///wCZAAAAAKJH7DwAAAADdFJOU///ANfKDUEAAAB0SURBVHjaYmBCAwABxMDEiAKYAAIIKMDAAGYygBETQACBBODSIAGAAIKogEIgiwkggKAqGOBaAAIIWQVYACCAMGwBCCAMAYAAQhYAGcYEEEAoAiAzAAIIbCgCMDIBBBCGCoAAwjADIIAwbAEIIAZ07wMEGACRrQETW54UHgAAAABJRU5ErkJggg==');
			case 'edit':
				return base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAB4UExURWpqma6u4pp0CaZ8EJoAAO11XbOmAMIyB/XCAPnHBlZeDz4uBuCmGc07KZSaBMClAcgyDq4ZBOFQKfBgO885JccpFfK/AMI0CbslB552Cf2jjH5qFGtQENOFANGeAIiHB8dZFf/MAKyhAX5+sAAAAMzM/5mZzP///+KxjpYAAAAodFJOU////////////////////////////////////////////////////wC+qi4YAAAAnUlEQVR42mJQRwMAAcSAzBERVVcHCCBkAV4pYQl1gABCEuDnYRUSFwQIIISAMhunggA7C0AAwQWUVfkUOWRZ1AECiAHOV1VTEpNTVwcIIAY4n1FNDcQBCCAGNL46QADBBGB8dYAAApNMjHC+OkAAQQTk4Xx1gAAC0UySzNJcMOsBAggkoMItwwx3H0AAMagAATOShwACiAHd+wABBgD82SHUrvKTuAAAAABJRU5ErkJggg==');
			case 'del':
				return base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAYUExURZlmZv+ZM/9mAMwzM8wAAP8AAJkAAAAAAJHQzOoAAAAIdFJOU/////////8A3oO9WQAAAJFJREFUeNpiYEcDAAHEwM7AwgDnsbCzAwQQAzsLIysblAuiAQIIKMvCChEB89kBAgisnAUkAuGzAwQQRD9QBMpnBwggqIEsMHPYAQIIrgImAhBACDOgIgABxIBQDyEBAggowMzEAlHNCiIAAoiBnRnuMLAIQAABBeB8MAAIIKAWJD5QCUAAMaD7FiCAMAQAAgwAYLoGdQu5RxIAAAAASUVORK5CYII=');
			case 'file':
				return base64_decode('R0lGODlhFAAWAMIAAP///8z//5mZmTMzMwAAAAAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAADUDi6vPEwDECrnSO+aTvPEddVIriN1wWJKDG48IlSRG0T8kwJvIBLOkvvxwoCfDnjkaisIIHNZdL4LAarUSm0iY12uUwvcdArm3mvyG3N/iUAADs=');
			case 'folder':
				return base64_decode('R0lGODlhFAAWAMIAAP/////Mmcz//5lmMzMzMwAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAACACwAAAAAFAAWAAADVCi63P4wyklZufjOErrvRcR9ZKYpxUB6aokGQyzHKxyO9RoTV54PPJyPBewNSUXhcWc8soJOIjTaSVJhVphWxd3CeILUbDwmgMPmtHrNIyxM8Iw7AQA7');
			case 'folder_open':
				return base64_decode('R0lGODlhGwAWAMIAAP/////Mmcz//7u7u5lmMzMzMwAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAACACwAAAAAGwAWAAADZSi63P4wykmrbSbrfJcZYAga3SeeGxeZZxuSEOu6sCOjc8rdb8AbgaCQkKEJdcJhoYhKOp+EpeAGfFoDUZisenVmPb2uVwoecMXBL+NzRqvXbfEbQ6jb7/cCOabv+/0qEjqDNQ8JADs=');
			case 'back':
				return base64_decode('R0lGODlhFAAWAMIAAP///8z//5mZmWZmZjMzMwAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAADSxi63P4jEPJqEDNTu6LO3PVpnDdOFnaCkHQGBTcqRRxuWG0v+5LrNUZQ8QPqeMakkaZsFihOpyDajMCoOoJAGNVWkt7QVfzokc+LBAA7');
			case 'forward':
				return base64_decode('R0lGODlhFAAWAMIAAP///8z//5mZmWZmZjMzMwAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAADThi63P6EhPFcvETUhe/cQYdplehRkHmRThGa31O4LwbORaZIYIDjugZt8SsGfUNkcfnjMJ8zhQAKVQyoTykB22wIttTed9lrJcuKM/qRAAA7');
		}
	}
}
?>
