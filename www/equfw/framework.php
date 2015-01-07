<?php

function getSetting($item)
{
	global $db, $sid, $log;
	$ret = '';
	$sql = "SELECT value from msetting WHERE item='{$item}'";
	$rows = $db->getResults($sql,ARRAY_A);
	foreach($rows as $row) $ret = $row['value'];
	return $ret;
}

function setSetting($item,$value)
{
	global $db, $sid, $log;
	$sql = "UPDATE msetting SET value='{$value}' WHERE item='{$item}'";
	$ret = $db->query($sql);
	return $ret;
}

//daniel
function getUserList()
{
	global $db, $log, $sid;
	
	$rspcode = 0;
	$errmsg = '';
	$rows = $db->getResults(
		"select idlogin, username from equfw.musers where username <> ''  order by username;",
		ARRAY_A
	);
	
	return array(
		'rspcode' => $rspcode,	     
		'errmsg'  => $errmsg,
		'result'  => $rows);

}

//daniel
function logUploadFile($params)
{
	global $db, $log, $sid;
	
	$filename1 = DIR_UPLOADTMP . "/{$sid->loginId}_{$params['filename']}_job";
	$filename2 = DIR_UPLOADTMP . "/ALL_{$params['filename']}_job";
	$filename =  (file_exists($filename1)) ? $filename1 : $filename2;
	
	$firstdata = '';
	
	$linecount = 0;
	$linecount = 0;
	$firstdata = '';
	$lines = file($filename);
	$firstdata = $lines[0];
	$linecount = count($lines);
	
	$sql = "INSERT INTO t_upload
		(file, creauser, numrow, firstdata, upltype)
		VALUES
		('{$params['filename']}','{$sid->loginId}', $linecount, '$firstdata', 'UPLD');";
	$ret = $db->query($sql);
	
	return array(
		'rspcode' 	=> 0,
		'errmsg' 	=> '',
		'result'	=> $filename,
	);
}

function getJobList2($params)
{
	global $db, $log;
	
	$all = false;
	if(isset($params['all']))
	{
		if($params['all'] == '1') $all = true;
	}
	
//               result['joblist'] = [{'idjob': 1, 'jobname':'xxx', 'progress': 39, 'batchlist':['111','222','333']}, {'jobid': 2, 'jobname':'yyy', 'progress': 69, 'batchlist':['aaa', 'bbb', 'ccc']}];
	
	$where = ' AND status = 1 ';
	if($all == true)
	{
		$where = '';
	}
	$rows = $db->getResults("SELECT * FROM tjoblist WHERE TRUE $where ORDER BY idjob;", ARRAY_A);
	
	foreach($rows as $key => $row)
	{
		$rows[$key]['batchlist'] = $db->getResults("SELECT idbatch, filename, stoptime - startime as duration, rownum, (100 * rowprint / rownum) as progress, rowbad, lastprint FROM tjobbatch WHERE status=1 AND idjob={$row['idjob']} ORDER BY 1", ARRAY_A);
	}
	
	$result['joblist'] = $rows;
	$log->l('INFO', "joblist: ". json_encode($result['joblist']));
	$rspcode = 0;
	$errmsg = '';
	return array(
		'rspcode' 	=> $rspcode,
		'errmsg' 	=> $errmsg,
		'result'	=> $result,
	);
}

?>