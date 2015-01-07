<?

define('DATADIR', '/home/equmcast/data');

function getflist($params)
{
	$resp = array(
		'rspcode'	=> 0,
		'errmsg'	=> '',
		'result'	=> array()
	);
	
	if($dirhandler = opendir(DATADIR))
	{
		while(($file = readdir($dirhandler)) !== FALSE)
			if($file != "." && $file != ".." && $file != "mcast.stat" && $file != "scripts" && $file != "logs")
			{
				$fnode = array(
					"name"	=> $file,
					"size"	=> filesize(DATADIR."/".$file)
				);
				array_push($resp["result"], $fnode);
			}
	}
	
	closedir($dirhandler);
	return $resp;
}

function sendstart($params)
{
	$resp = array(
		'rspcode'	=> 0,
		'errmsg'	=> '',
		'result'	=> ''
	);
	
	exec(DATADIR."/scripts/sendFile ".$params["filename"]." > /dev/null 2>&1 &");
	
	return $resp;
}

?>
