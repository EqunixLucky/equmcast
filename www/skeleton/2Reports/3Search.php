<?
    $sql = "
SELECT
     '%JOB' AS jobid
    ,a.data
    ,a.printdate
    ,a.captdate
    ,(c.startuser || ' - ' || d.username) AS username
    ,b.filename
    ,CASE 
     WHEN a.status = 0 THEN 'PRINTED'
     WHEN a.status >= 9999 THEN 'NOT FOUND'
     WHEN a.status = 1 THEN 'CONFIRMED'
     WHEN a.status > 1 AND a.status < 9999 THEN 'MULTI PRINTED'
     END AS Status
FROM tjob_%JOB AS a
    LEFT JOIN tjobbatch AS b ON a.idbatch = b.idbatch
    LEFT JOIN tjobact AS c ON c.idjob = b.idjob AND a.printdate BETWEEN c.startdate AND c.stopdate
    LEFT JOIN equfw.musers AS d ON c.startuser = d.idlogin
WHERE TRUE %WHERE
ORDER BY 1,3 DESC;";
	
    // N : Numeric, right justify
    // F : Numeric with thousand separator, right justify
    // T : Text, left justify
    $header = array(0=>'Job #_T',1=>'Data_T',2=>'Print Date_T',3=>'Capture Date_T',4=>'User_T',5=>'File Name_T',6=>'Status_T',);
    
    function getSearch($params)
    {
	global $db, $log, $sid,$sql,$header;
	
	$rowAll = array();
	$jobArray = array();
	$totalResult = 0;
	
	if($params['searchJob'] != 0) $jobArray = array($params['searchJob']);
	else $jobArray = $params['arrJob'];
	
	$total = 0;
	
	foreach($jobArray as $jobid)
	{
	    $where = '';
	    if($params['searchRcData'] != '')
	    {
		$where .= " AND a.data ~* '" . $params['searchRcData'] . "' ";
	    }
	    if($params['searchUser'] != '0')
	    {
		$where .= " AND c.startuser = '" . $params['searchUser'] . "' ";
	    }
	    if($params['searchDate'] != '')
	    {
		list($d,$m,$y) = explode('/',$params['searchDate']);
		$tgl = "$y-$m-$d";
		$where .= " AND TO_CHAR(a.printdate,'YYYY-MM-DD') = '" . $tgl . "' ";
	    }
	    switch($params['searchStatus'])
	    {
		case '0':
		    $where .= ' AND a.status = 0 ';
		    break;
		case '10':
		    $where .= ' AND a.status = 1 ';
		    break;
		case '200':
		    $where .= ' AND (a.status > 1 AND a.status < 9999) ';
		    break;
		case '16':
		    $where .= ' AND a.status >= 9999 ';
		    break;
	    }
	    
	    $q = str_replace('%WHERE',$where,$sql);
	    $q = str_replace('%JOB',$jobid,$q);
	    $rows = $db->getResults($q,ARRAY_N);
	    $total += count($rows);
	    foreach($rows as $row)
	    {
		if($totalResult >= $params['searchMax']) break;
		$totalResult++;
		array_push($rowAll,$row);
	    }
	    //if($totalResult >= $params['searchMax']) break;
	}
	
	
	
	
	$result = array(
	    'header' => $header,
	    'rows'   => $rowAll,
	    'sql'    => $jobArray,
	    'total'  => $total,
	);
    
        return array(
		'rspcode' => 0,	     
		'errmsg'  => '',
		'result'  => $result
	); 
	
    }

?>
