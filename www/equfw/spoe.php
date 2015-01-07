<?php
error_reporting(E_ALL);

define('DIR_PREFIX_PHP',	'../');
define('DIR_EQUFW', 		'equfw');
define('DIR_STATIC', 		'static');
define('DIR_APPS', 		'apps');
define('DIR_DOWNLOAD', 		'download');
define('DIR_RESTORE', 		'/media');


include_once('../config.php');
include_once('htmlBuilder.php');
include_once('logger.php');
include_once('serviceComm.php');

/*************** Path resolving... *******************************************************/
//$pathArray = explode('/', strtolower($_SERVER['REQUEST_URI'])); array_shift($pathArray);
$pathArray = explode('/', $_SERVER['REQUEST_URI']); array_shift($pathArray);
$mainPath = array_shift($pathArray);
while (strlen(MAINPATH)>0 && strstr(MAINPATH, $mainPath)) $mainPath = array_shift($pathArray);
if ($mainPath == 'static' ) staticProceed($pathArray);

//include_once('db.php');
include_once('session.php');

//daniel - berisi fungsi2 umum yang dipanggil oleh backend
include_once('framework.php');

$headers = getallheaders();
$ajax = isset($headers['X-Requested-With']) && ($headers['X-Requested-With'] == 'XMLHttpRequest');
if (isset($_POST['req'])) { $params = $_POST['req']; $ajax = 'AJAX'; }
//$log->l('INFO', "pathInfo: '{$_SERVER['REQUEST_URI']}', mainPath: '$mainPath'");

//include_once("serviceComm.php");
//$sc = new serviceComm();
//if (! $sc->ping())
//		exit("<div style='text-align: center;font-size: 250%' ><br>Sorry, the ServiceComm is OFF, please contact Support (021-68881838)</div>");

$rspCode = 0;
$errMsg  = 'OK';

switch($mainPath)
{
case 'info': phpinfo(); exit(0); break;
case 'ui':
	$uipath = '../skeleton/';
	// yes, sure this isn't any break here...
case 'ajax':
	if (!$ajax)
	{
		$log->l('ERRO', 'Ajax Path is called by NOT AJAX caller!!');
		$result = '';
		break;
	}

	$funcClass = array_shift($pathArray);
	$pageName = '.';
	if(strpos($funcClass, '.') !== false)
		list($pageName, $className) = explode('.', $funcClass);
	else
		$className = $funcClass;

	$funcName = array_shift($pathArray);
	$parameters = implode("', '", $pathArray);
	if (!isset($params)) $params = array();
	$sparam = implode(',', $params);
	$log->l('INFO', "ajaxCall: ($pageName/$className)->{$funcName}($sparam)");
	
	//daniel - tambahan pengecekan untuk prefix 'ajax' karena untuk prefix 'ajax' $uipath tidak di-set
	//ori => include("$uipath/$pageName/{$className}.php");
	if (isset($uipath))
	{
		include("$uipath/$pageName/{$className}.php");
		$retval = call_user_func($funcName, $params);
	}
	else if ($className == 'common')
	{
		$retval = call_user_func_array($funcName, $params);
	}
	else
	{
		include("$pageName/{$className}.php");
		$retval = call_user_func(array(new $className, $funcName), $params);
	}

	if (!is_array($retval)) $retval = array( 'rspcode' => 2, 'errmsg' => 'return not valid!', 'result' => $retval);
	echo json_encode($retval);
	break;

case 'dynobj':
	$log->l('ERRO', 'not yet defined path!!!');
	echo json_encode(
		array(
			'result' 	=> 'result: DYNOBJ is called by fault!',
			'rspcode' 	=> 4,
			'errmsg' 	=> 'DYNOBJ is called by fault!',
		)
	);
	break;

case 'fileUpload':
	$sparam = json_encode($params);
	$fname = $_POST['Filename'];
	if (strlen($fname)>80)
	{
		$fname = substr($fname, 0, 80);
		$log->l('ERRO', "File: '{$_POST['Filename']}' too long, truncated into: '$fname' max fname: 80");
	}
	$log->l('INFO', "param: $sparam loginID:{$sid->loginId} tmp:{$_FILES['Filedata']['tmp_name']} filename:{$fname}");
	
	$prefix_ = 'ALL';
	
	//daniel
	//tambahan utk zip dab bz2 file
	$ext = strtolower(end(explode('.',$_FILES['Filedata']['tmp_name'])));
	if($ext = 'zip')
	{
		$filename = "../../uploads/tmp/{$fname}";
		copy($_FILES['Filedata']['tmp_name'], $filename);
		$filedest = "../../uploads/tmp/{$prefix_}_" . basename($filename, ".zip") . "_job";
		shell_exec("gunzip -c {$filename} > {$filedest}");
		shell_exec("rm {$filename}");
	}
	if($ext = 'bz2')
	{
		$filename = "../../uploads/tmp/{$fname}";
		copy($_FILES['Filedata']['tmp_name'], $filename);
		//$filedest = "../../uploads/tmp/" . basename($filename, ".bz2");
		//shell_exec("mv {$filename} {$filedest}");
	}
	else
	{
		//$prefix_ = (isset($sid->loginId)) ? $sid->loginId : 'ALL';
		$filename = "../../uploads/tmp/{$prefix_}_{$fname}_job";
		copy($_FILES['Filedata']['tmp_name'], $filename);
		echo "OK";
	}
	break;

case 'download':
	//daniel - utk cover proses backup
	$uipath = '../skeleton/';
	$funcClass = array_shift($pathArray);
	$pageName = '.';
	if(strpos($funcClass, '.') !== false)
		list($pageName, $className) = explode('.', $funcClass);
	else
		$className = $funcClass;
	$funcName = array_shift($pathArray);
	//$parameters = implode("', '", $pathArray);
	$parameters = $pathArray;
	include("$uipath/$pageName/{$className}.php");
	$retval = call_user_func($funcName, $parameters);
	break;
	
default:
	/*if (!isset($sid->loginId) && ($mainPath !== 'login'))
	{
		$log->l('INFO', "loginId is NOT SET, mainPath:{$mainPath}");
		include('login.php');
	}
	else*/if (method_exists($sid, $mainPath) && isset($params))
	{
		$log->l('INFO', "Call session->{$mainPath}(". implode(',',$params) .");");
		echo json_encode(call_user_func(array($sid, $mainPath), $params));
	}
	else
	{	
		//file_put_contents('ledstat', $_SERVER['QUERY_STRING']);
		file_put_contents('ledstat', $_GET['LED0'].';'.$_GET['LED1'].';'.$_GET['LED2']);
		$GLOBALS['LED0'] = $_GET['LED0'];
		$tmpl = new htmlBuilder(DIR_SKELETON);
		echo $tmpl->output();
	}
}
exit();

function staticProceed($pathArray)
{
	global $log, $sid;
	
	if (!is_object($log)) $log = new logger(LOG_NAME);
	
	$contentType = array(
		'css' 	=> 'text/css',
		'css'	=> 'text/css',
		'csv'	=> 'text/csv',
		'html'	=> 'text/html',
		'jpg'	=> 'image/jpg',
		'png'	=> 'image/png',
		'gif'	=> 'image/gif',
		'js'	=> 'application/javascript',
		'ico'	=> 'image/x-icon',
		'swf'   => 'application/x-shockwave-flash',
	);
	
	$subdir  = array_shift($pathArray);
	$restdir = implode('/', $pathArray);
	
	switch($subdir)
	{
	case DIR_EQUFW:
		$path =  "../{$subdir}/{$restdir}";
		break;
	//daniel - untuk folder images
	case DIR_IMAGE:
		$path =  "../static/{$subdir}/{$restdir}";
		break;
	case DIR_APPS:
		$path =  '../' . DIR_SKELETON . "/{$restdir}";
		break;
	case DIR_DOWNLOAD:
		$path =  "../". DIR_DOWNLOAD ."/{$restdir}";
		break;
	}
	
	if (strpos($path, '?')) list($file, $qstring) = explode('?', $path); else $file = $path;
	$exts = explode('.', end($pathArray));
	$ext  = end($exts);
	if (strpos(end($exts), '?')) list($ext, $qstring) = explode('?', end($exts)); else $ext = end($exts);
	
	if (array_key_exists($ext, $contentType)) $cntType = $contentType[$ext];
	else
	{
		$cntType = "text/$ext";
		$errlog = "Extension: '$ext' has no associated headers, please add! path=$path";
	}
	
	header("Content-Type: {$cntType}");
		
	if (!file_exists($file))
	{
		if (!isset($errlog)) $errlog = '';
		$errlog .= " File: $path not available !!!";
		echo "<PRE>ERROR, FILE:$path NOT FOUND</PRE>";
	}
	else
		readfile($file);
		
	if (isset($errlog)) $log->l('ERRO', $errlog);
	
	exit();
}

?>
