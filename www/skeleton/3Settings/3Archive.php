<?php

	function archive($params)
	{
		global $db, $sid, $log;
		
		$result = '';
		
		$from = '';
		if($params[0] != '')
		{
			list($d,$m,$y) = explode('-',$params[0]);
			$from = "{$y}-{$m}-{$d}";
		}
		
		$to = '';
		if($params[1] != '')
		{
			list($d,$m,$y) = explode('-',$params[1]);
			$to = "{$y}-{$m}-{$d}";
		}
		
		if($from == '' && $to == '')
		{
			echo 'Please select date';
		}
		else
		{
			$where = ' status = 3 ';
			if($from != '') $where .= " and TO_CHAR(creadate,'YYYY-MM-DD') >= '{$from}'";
			if($to != '') $where .= " and TO_CHAR(creadate,'YYYY-MM-DD') <= '{$to}'";
			
			$rows = $db->getResults("select idjob from tjoblist where {$where}", ARRAY_A);
			$idjob = array();
			$shell = "pg_dump -U " . DBUSER . " " . DBNAME . " ";
			$totjob = 0;
			foreach($rows as $row)
			{
				$totjob++;
				$shell .= " -t tjob_{$row['idjob']} ";
				$shell .= " -t tjob_{$row['idjob']}_dup ";
				$idjob[] = $row['idjob'];
			}
			
			if($totjob > 0)
			{
				//==================================================
				$filename = "emvarc_" . date('YmdHis') . ".bz2";
				//==================================================
				header( 'Content-Type: application/octet-stream' ); 
				header( 'Content-Disposition: attachment; filename="'. $filename .'"' ); 
				//==================================================
				$shell .= " | bzip2 -c";
				$finish = false;
				$oldfilesize = 0;
				$filesize = 0;
				$samesize = 0;
				$fp = popen($shell,'r');
				while(!$finish)
				{
					$oldfilesize = $filesize;
					$data = fpassthru($fp);
					$filesize = strlen($data);
					if($filesize == $oldfilesize)
					{
						if($samesize < 3)
							$samesize++;
						else
							$finish = true;
					}
					sleep(1);
				}
				//==================================================
				$sqldrop = '';
				foreach($idjob as $job)
				{
					$sqldrop .= "update tjoblist set status=4 where idjob = {$job};\n";
					$sqldrop .= "drop table IF EXISTS tjob_{$job};\n";
					$sqldrop .= "drop table IF EXISTS tjob_{$job}_dup;\n";
				}
				$dbdrop = $db->query($sqldrop);
			}
			else
			{
				echo 'No data to archive or data already archived';
			}
		}
		
	}

?>