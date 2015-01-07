<?
	function reportinit($params) {
		global $db, $log, $sid;
		
		$where = '';
		
		if($params['uploadDate'] != '')
		{
			list($d,$m,$y) = explode('/',$params['uploadDate']);
			$tgl = "$y-$m-$d";
			$where .= " AND TO_CHAR(a.creadate,'YYYY-MM-DD') = '" . $tgl . "' ";
		}
		
		if($params['uploadUser'] != '0') $where .= " AND a.creauser = '" . $params['uploadUser'] . "' ";
		
		if($params['uploadFileName'] != '') $where .= " AND a.file LIKE '%" . $params['uploadFileName'] . "%' ";
		
		if($params['uploadType'] != '0') $where .= " AND a.upltype = '" . $params['uploadType'] . "' ";
		
		if($params['uploadJob'] != '0') $where .= " AND a.idjob = '" . $params['uploadJob'] . "' ";
		
		$sql = "SELECT  a.id
			       ,TO_CHAR(a.creadate, 'YYYY-MM-DD HH24:MI:SS') AS creadate
			       ,b.username
			       ,a.file
			       ,a.numrow
			       ,a.firstdata
			       ,a.idreff
			       ,(CASE a.upltype WHEN 'UPLD' THEN 'UPLOAD'
						WHEN 'SPLT' THEN 'SPLIT'
						WHEN 'ABDN' THEN 'ABANDON'
				 END
				) AS upltype
			       ,a.idjob
			FROM t_upload a
			       LEFT JOIN equfw.musers b ON a.creauser = b.idlogin
			WHERE TRUE $where
			ORDER BY a.creadate, a.id;";
		
		$rows = $db->getResults($sql,ARRAY_N);
		
		return array(
			'rspcode' 	=> 0,
			'errmsg' 	=> '',
			'result'	=> $rows,
		);
	}
?>