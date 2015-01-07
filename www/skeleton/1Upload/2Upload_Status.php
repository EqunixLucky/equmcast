<?

define('DATADIR', '/home/equmcast/data');

function sendstatus($params)
{
	$resp = array(
		'rspcode'	=> 0,
		'errmsg'	=> '',
		'result'	=> json_decode(file_get_contents(DATADIR."/mcast.stat"))
	);
	
	return $resp;
}

function abort($params)
{
	$resp = array(
		'rspcode'	=> 0,
		'errmsg'	=> '',
		'result'	=> ''
	);
	
	//exec("killall equmcast > /dev/null 2>&1 &");
	
	return $resp;
}

?>
