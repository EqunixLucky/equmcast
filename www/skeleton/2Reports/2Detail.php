<?
    $sql = array(
	1 => "SELECT a.idbatch,b.filename,a.printdate,a.captdate,a.data,a.status FROM tjob_%JOB AS a LEFT JOIN tjobbatch AS b ON a.idbatch = b.idbatch WHERE TRUE %WHERE ORDER BY 1;",
        2 => "SELECT a.data,a.status FROM tjob_%JOB AS a WHERE TRUE %WHERE ORDER BY 1;",
    );
	
    // N : Numeric, right justify
    // F : Numeric with thousand separator, right justify
    // T : Text, left justify
    $header = array(
	1 => array(0=>'Batch #_N',1=>'File Name_T',2=>'Print Date_T',3=>'Capture Date_T',4=>'Data_T',5=>'Status_T'),
	2 => array(0=>'Data_T',1=>'Status_T'),

    );
    
    $report = array(
	1 => array("id"=>"1","prompt"=>"Full Detail"),
	2 => array("id"=>"2","prompt"=>"Short Detail"),
    );
    
    $qq = '';
    
    function reportInit()
    {
	global $report;
	
	$rspcode = 0;
	$errmsg = '';
	
	return array(
		'rspcode' => $rspcode,	     
		'errmsg'  => $errmsg,
		'result'  => $report);
    }
    
    function getRows($params,$job=0)
    {
	global $db, $log, $sid,$sql,$header, $qq;
	
	$where = '';
	if($params['detailDate'] != '')
	{
	    list($d,$m,$y) = explode('/',$params['detailDate']);
	    $tgl = "$y-$m-$d";
            $where .= " AND TO_CHAR(a.printdate,'YYYY-MM-DD') = '" . $tgl . "' ";
	}
	switch($params['detailStatus'])
	{
	    case '0':
		$where .= ' AND a.status = 0 ';
		break;
	    case '100':
		$where .= ' AND a.status = 1 ';
		break;
	    case '-1':
		$where .= ' AND a.status = -1 ';
		break;
	    case '16':
		$where .= ' AND a.status >= 9999 ';
		break;
	}
	
	$q = str_replace('%WHERE',$where,$sql[$params['id']]);
	$q = str_replace('%JOB',$job,$q);
	$qq = $q;
	$rows = $db->getResults($q,ARRAY_N);
	return $rows;
    }

    function downloadReport($params)
    {
	global $sid,$header,$report,$qq;
        $jobIdArray = array();
	$jobNameDisp = '';
	$jobStatDisp = '';

        if($params['detailJobId'] == 0)
        {
	    $jobNameDisp = 'All Job';
            $jobname = 'all-all';
            $jobIdArray = explode(',',$params['jobIdList']);
        }
        else
        {
	    $jobNameDisp = $params['jobName'];
            $jobname = strtolower(str_replace(' ','',$params['jobName']));
            $jobname = strtolower(str_replace('-','_',$jobname));
            $jobIdArray[0] = $params['detailJobId'];
        }
        
	$tgl = 'alldate';
        if($params['detailDate'] != '')
	{
	    list($d,$m,$y) = explode('/',$params['detailDate']);
	    $tgl = "$y-$m-$d";
	}
	
	$stat = 'allstat';
	$jobStatDisp = 'All Status';
	switch($params['detailStatus'])
	{
	    case '0':
		$stat = 'printed';
		$jobStatDisp = 'Printed';
		break;
	    case '100':
		$stat = 'confirmed';
		$jobStatDisp = 'Confirmed';
		break;
	    case '-1':
		$stat = 'unused';
		$jobStatDisp = 'Unused';
		break;
	    case '16':
		$stat = 'notfound';
		$jobStatDisp = 'Not Found';
		break;
	}
	
	//$filename = $jobname . '_' . $report[$params['id']]['prompt'] . '_' . $tgl . '_' . $stat . '.csv';
	$filename = strtolower(str_replace(' ','_',$jobname . '_' . $report[$params['id']]['prompt'] . '_' . $tgl . '_' . $stat));
	$filename = strtolower(preg_replace("/[^a-z|0-9]/i", "_", $filename)) . '.csv';
	
	$fnamewrite = '../download/' . $filename;
	
	$fh = fopen($fnamewrite,'w');
	
	// SET REPORT TITLE
	fputcsv($fh,array('DTL001 - Job Detail'));
	fputcsv($fh,array('Detail Type :',$report[$params['id']]['prompt']));
	fputcsv($fh,array('Job No. :',$jobNameDisp));
	fputcsv($fh,array('Job Status :',$jobStatDisp));
	if($params['detailDate'] != '') fputcsv($fh,array('Date :',$params['detailDate']));

	$arrHead = array();
	for($a=0;$a < count($header[$params['id']]);$a++)
	{
	    $arrHead[$a] = preg_replace("/_(.+)/", "", $header[$params['id']][$a]);
	}
	fputcsv($fh,$arrHead);
	
        $x = '';
	$csvrows = 0;
        foreach($jobIdArray as $id)
        {
	    $x .= "[ $id | ";
            $rows = getRows($params,$id);
	    $x .= count($rows) . '],';
	    if(count($rows) > 0)
	    {
		foreach($rows as $row)
		{
		    $csvrows++;
		    fputcsv($fh,$row);
		}
	    }
        }
	if($csvrows == 0) fputcsv($fh,array('No Data'));
	
	fclose($fh);
        
        return array(
		'rspcode' => 0,	     
		'errmsg'  => '',
		'result'  => $filename,
		'sql'	  => $qq,
	); 
	
    }
    


?>
