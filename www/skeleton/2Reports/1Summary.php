<?
    $sql = array(
	1 => "SELECT 
    c.idjob
   ,c.jobname
   ,d.username
   ,TO_CHAR(c.creadate,'YYYY-MM-DD HH24:MI')
   ,COUNT(CASE WHEN (a.status >= 0 AND a.status < 9999) then 1 else null end) as printed
   ,COUNT(CASE WHEN a.status = -1 then 1 else null end) as unused
   ,COUNT(CASE WHEN a.status < 9999 then 1 else null end) as totaldata
   ,COUNT(CASE WHEN a.status = 1 then 1 else null end) as confirmed
   ,(COUNT(CASE WHEN a.status = 1 then 1 else null end)*10000/COUNT(CASE WHEN (a.status >= 0 AND a.status <= 1) then 1 else null end))/100
FROM tjoblist AS c
    LEFT JOIN tjob_%JOB a ON (a.printdate >= c.creadate OR a.printdate <= c.creadate)
    LEFT JOIN equfw.musers d ON d.idlogin = c.creauser
WHERE c.idjob=%JOB %WHERE
GROUP BY 1,2,3,4;",
	2 => "SELECT b.idjob,a.jobname,b.idbatch,b.rownum,b.rowprint FROM tjobbatch AS b LEFT JOIN tjoblist a ON a.idjob = b.idjob WHERE TRUE %WHERE ORDER BY b.idjob,b.idbatch;", 
	//3 => "SELECT COALESCE(TO_CHAR(a.printdate,'YYYY-MM-DD HH24:MI:SS'),'') AS printdate,b.filename,a.data FROM tjob_%JOB AS a LEFT JOIN tjobbatch AS b ON b.idbatch = a.idbatch ORDER BY 1,2,3;",
	4 => "SELECT idjob,COALESCE(TO_CHAR(MIN(startime),'YYYY-MM-DD HH24:MI:SS'),'') AS start,COALESCE(TO_CHAR(MAX(finitime),'YYYY-MM-DD HH24:MI:SS'),'') AS finish,filename,SUM(rowprint) AS printed,SUM(rowvalid) AS camok,SUM(rowbad) AS camnok FROM tjobbatch WHERE TRUE %WHERE GROUP BY idjob,filename ORDER BY idjob,filename;",
	5 => "SELECT a.idjob,b.jobname,COALESCE(TO_CHAR(b.creadate,'YYYY-MM-DD HH24:MI:SS'),'') AS creadate,a.filename,SUM(a.rownum) AS rownum,SUM(a.rowprint) AS rowprint ,(SUM(a.rownum) - SUM(a.rowprint)) AS rowleft FROM tjobbatch AS a LEFT JOIN tjoblist AS b ON a.idjob = b.idjob WHERE TRUE %WHERE GROUP BY 1,2,3,4 ORDER BY 1,2,3,4;",
    );
	
    // N : Numeric, right justify
    // F : Numeric with thousand separator, right justify
    // T : Text, left justify
    $header = array(
	1 => array(0=>'Job #_N',1=>'Job Name_T',2=>'Operator_T',3=>'Created_T',4=>'Total Printed_F',5=>'Unused_F',6=>'Total Data_F',7=>'Confirmed_F',8=>'Confirmed (%)_F'),
	2 => array(0=>'Job #_N',1=>'Job Name_T',2=>'Batch #_N',3=>'Row Num._N',4=>'Row Print_N'),
	//3 => array(0=>'Date_T',1=>'File Name_T',2=>'RC Data_T'),
	4 => array(0=>'Job #_N',1=>'Start_T',2=>'Finish_T',3=>'File Name_T',4=>'Printed_N','Cam OK_N','Cam Not OK_N'),
	5 => array(0=>'Job #_N',1=>'Job Name_T',2=>'Create Date_T',3=>'File Name_T',4=>'Row Num._N','Row Print_N','Row Left_N'),
	
    );
    
    $input = array(
	1 => '#filterRepDate1,#filterRepDate2,#filterRepJob,#filterRepUser',
	2 => '#filterRepJob,#filterRepJobStat',
	//3 => '#filterRepDate1,#filterRepDate2,#filterRepJob',
	4 => '#filterRepDate1,#filterRepDate2',
	5 => '#filterRepDate1,#filterRepDate2',
    );
    
    $report = array(
	1 => array("id"=>"1","prompt"=>"SUM 001 - Succes Rate","input"=>$input[1]),
	2 => array("id"=>"2","prompt"=>"SUM 002 - Job Status"  ,"input"=>$input[2]),
	//3 => array("id"=>"3","prompt"=>"Job Detail"  ,"input"=>$input[3]),
	4 => array("id"=>"4","prompt"=>"SUM 003 - Job Summary" ,"input"=>$input[4]),
	5 => array("id"=>"5","prompt"=>"SUM 004 - Job Info"    ,"input"=>$input[5]),
    );
    
    function reportinit()
    {
	global $input,$report;
	
	$rspcode = 0;
	$errmsg = '';
	
	return array(
		'rspcode' => $rspcode,	     
		'errmsg'  => $errmsg,
		'result'  => $report);
    }
    
    function getRows($params,$jobid)
    {
	global $db, $log, $sid,$sql,$header,$input;
	
	$where = '';
	if($jobid != 0)
	{
	    switch($params['id'])
	    {
		case 1:
		    $where .= " AND c.idjob=" . $jobid . " ";
		    break;
		case 4:
		    $where .= " AND idjob=" . $jobid . " ";
		    break;
		default:
		    $where .= " AND b.idjob=" . $jobid . " ";
		    break;
	    }
	}
	
	if($params['filterRepUser'] != '0') $where .= " AND b.startuser='" . $params['filterRepUser'] . "' ";
	
	if($params['filterRepJobStat'] != '0') $where .= " AND b.status=" . $params['filterRepJobStat'] . " ";
	
	if($params['filterRepDate1'] != '' OR $params['filterRepDate2'] != '')
	{
		if($params['filterRepDate1'] == '') $params['filterRepDate1'] = 'NOW()';
		
		if($params['filterRepDate2'] == '') $params['filterRepDate2'] = $params['filterRepDate1'];
		
		switch($params['id'])
		{
		    case 1:
			$where .= " AND c.creadate BETWEEN {$params['filterRepDate1']} AND {$params['filterRepDate2']} ";
			break;
		    case 3:
			$where .= " AND a.printdate BETWEEN {$params['filterRepDate1']} AND {$params['filterRepDate2']} ";
			break;
		    case 4:
			$where .= " AND startime BETWEEN {$params['filterRepDate1']} AND {$params['filterRepDate2']} ";
			break;
		}
	}
	
	$q = str_replace('%WHERE',$where,$sql[$params['id']]);
	$q = str_replace('%JOB',$jobid,$q);
	$rows = $db->getResults($q,ARRAY_N);
	
	//custom column
	//switch($params['id'])
	//{
	//	case 1:
	//		$tmpRows = array();
	//		foreach($rows as $row)
	//		{
	//			$tmpRow = $row;
	//			$persen = 0;
	//			if($row[4] > 0)
	//				$persen = $row[7] / $row[4] * 100;
	//			$tmpRow[7] = number_format($persen,2,',','.');
	//			array_push($tmpRows,$tmpRow);
	//		}
	//		$rows = $tmpRows;
	//		break;
	//}
	
	return $rows;
    }

    function downloadReport($params)
    {
	global $sid,$header,$report;
	
	//$filename = $report[$params['id']]['prompt'] . '_' . date('YmdHis') . '.csv';
	$filename = strtolower(str_replace(' ','_',$report[$params['id']]['prompt'] . '_' . date('YmdHis') . '.csv'));

	$fnamewrite = '../download/' . $filename;

	
	$fh = fopen($fnamewrite,'w');
	
	// SET REPORT TITLE
	fputcsv($fh,array($report[$params['id']]['prompt']));
    	if($params['filterRepJob'] != '0') fputcsv($fh,array('Job :',$params['jobName']));
    	if($params['filterRepUser'] != '0') fputcsv($fh,array('Operator :',$params['userName']));
    	if($params['filterRepJobStat'] != '0') fputcsv($fh,array('Status :',$params['jobStat']));
	
	$arrHead = array();
	for($a=0;$a < count($header[$params['id']]);$a++)
	{
	    $arrHead[$a] = preg_replace("/_(.+)/", "", $header[$params['id']][$a]);
	}
	fputcsv($fh,$arrHead);
	
	$arrjob = explode(',',$params['jobIdList']);
	$rows = array();
	foreach($arrjob as $idjob)
	{
	    $arrtmp = getRows($params,$idjob);
	    if(count($arrtmp) > 0) foreach($arrtmp as $tmp) fputcsv($fh,$tmp);
	}
	
	//if(count($rows) > 0)
	//{
	//    foreach($rows as $row)
	//    {
	//	fputcsv($fh,$row);
	//    }
	//}
	
	fclose($fh);
        
        return array(
		'rspcode' => 0,	     
		'errmsg'  => '',
		'result'  => $filename
	); 
	
    }
    
    function reportquery($params)
    {
	global $header,$sql;
	$arrjob = explode(',',$params['jobIdList']);
	$rows = array();
	foreach($arrjob as $idjob)
	{
	    $arrtmp = getRows($params,$idjob);
	    if(count($arrtmp) > 0) foreach($arrtmp as $tmp) array_push($rows,$tmp);
	}
	
	$rspcode = 0;
	$errmsg = '';
	$result = array(
	    'header' => $header[$params['id']],
	    'rows'   => $rows,
	    'sql'    => '',
	);
        
        return array(
		'rspcode' => $rspcode,	     
		'errmsg'  => $errmsg,
		'result'  => $result
	); 
    }


?>
