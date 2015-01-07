<?php

	function init()
	{
		global $db, $sid, $log;
	
		$last_inc_back = $db->getVar("select value from msetting where item = 'LAST_INC_BACK';");
		$result = '-';
		if($last_inc_back)
			$result = $last_inc_back;
		
		$rspcode = 0;
		$errmsg = '';
		return array(
			'rspcode' 	=> $rspcode,
			'errmsg' 	=> $errmsg,
			'result'	=> $result,
			    );
	}
	
	function fullBack()
	{
		global $db, $sid, $log;
		$chunklen = 1024;
		//==================================================
		//header('Content-Type: text/plain; filename="' . $filename . '" ');
		header( 'Content-Type: application/octet-stream' ); 
		header( 'Content-Disposition: attachment; filename="'. $filename .'"' ); 
		//==================================================
		$longdate = date('Y-m-d H:i:s');
		$shortdate = date('YmdHis'); 
		//==================================================
		$inc_back_sig = $db->getVar("select value from msetting where item = 'INC_BACK_SIG';");
		$hash = md5($shortdate);
		$retdb = $db->query("update msetting set value = '{$hash}' where item = 'INC_BACK_SIG';");
		//==================================================
		$last_inc_back = $db->getVar("select value from msetting where item = 'LAST_INC_BACK';");
		$retdb = $db->query("update msetting set value = '{$longdate}' where item = 'LAST_INC_BACK';");
		//==================================================
		sleep(1);
		//==================================================
		$filename = "emvback_{$shortdate}_{$hash}.bz2";
		//==================================================
		//header('Content-Type: text/plain; filename="' . $filename . '" ');
		header( 'Content-Type: application/octet-stream' ); 
		header( 'Content-Disposition: attachment; filename="'. $filename .'"' ); 
		//==================================================
		$fp = popen("pg_dump --clean --create -U " . DBUSER . " " . DBNAME . " | bzip2 -c",'r');
		//$fp = popen("pg_dump -c -U " . DBNAME . " " . DBUSER . "",'r');
		$data = '';
		$offset = 0;
		$oldfilesize = 0;
		$filesize = 0;
		$samesize = 0;
		$finish = false;
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
		pclose($fp);
		echo $data;
	}

	function incBack()
	{
		global $db, $sid, $log;
		
		$result = '';
		$inc_back_sig = $db->getVar("select value from msetting where item = 'INC_BACK_SIG';");
		if(!is_null($inc_back_sig))
		{
			$drop = array();
			$shell = "pg_dump -U " . DBUSER . " " . DBNAME . " ";
			$date = $db->getVar("select value from msetting where item = 'LAST_INC_BACK';");
			$lastdate = date('Y-m-d H:i:s');
			$retdb = $db->query("update msetting set value = '" . $lastdate . "' where item = 'LAST_INC_BACK';");
			//==================================================
			$datTbl = $lastdate;
			$datTbl = str_replace('-','',$datTbl);
			$datTbl = str_replace(' ','',$datTbl);
			$datTbl = str_replace(':','',$datTbl);
			//==================================================
			sleep(1);
			//==================================================
			$dbret = $db->query("create table tjoblist_{$datTbl} as select * from tjoblist where creadate >= '{$date}'");
			if($dbret)
			{
				$shell .= " -t tjoblist_{$datTbl} ";
				$drop[] = "tjoblist_{$datTbl}";
			}
			//==================================================
			$dbret = $db->query("create table tjobbatch_{$datTbl} as select * from tjobbatch where idjob in (select idjob from tjoblist where creadate >= '{$date}')");
			if($dbret)
			{
				$shell .= " -t tjobbatch_{$datTbl} ";
				$drop[] = "tjobbatch_{$datTbl} ";
			}
			//==================================================
			$dbret = $db->query("create table tjobact_{$datTbl} as select * from tjobact where idjob in (select idjob from tjoblist where creadate >= '{$date}')");
			if($dbret)
			{
				$shell .= " -t tjobact_{$datTbl} ";
				$drop[] = "tjobact_{$datTbl} ";
			}
			//==================================================
			$rows = $db->getResults("select idjob from tjoblist where creadate >= '{$date}'", ARRAY_A);
			foreach($rows as $row)
			{
				$dbret = $db->query("create table tjob_{$row['idjob']}_{$datTbl} as select * from tjob_{$row['idjob']}");
				if($dbret)
				{
					$shell .= " -t tjob_{$row['idjob']}_{$datTbl} ";
					$drop[] = "tjob_{$row['idjob']}_{$datTbl} ";
				}
				//==================================================
				$dbret = $db->query("create table tjob_{$row['idjob']}_dup_{$datTbl} as select * from tjob_{$row['idjob']}_dup");
				if($dbret)
				{
					$shell .= " -t tjob_{$row['idjob']}_dup_{$datTbl} ";
					$drop[] = "tjob_{$row['idjob']}_dup_{$datTbl} ";
				}
			}
			//==================================================
			$dbret = $db->query("create table equfw.musers_{$datTbl} as select * from equfw.musers");
			if($dbret)
			{
				$shell .= " -t equfw.musers_{$datTbl} ";
				$drop[] = "equfw.musers_{$datTbl} ";
			}
			//==================================================
			$dbret = $db->query("create table msetting_{$datTbl} as select * from msetting");
			if($dbret)
			{
				$shell .= " -t msetting_{$datTbl} ";
				$drop[] = "msetting_{$datTbl} ";
			}
			//==================================================
			$dbret = $db->query("create table mdevice_{$datTbl} as select * from mdevice");
			if($dbret)
			{
				$shell .= " -t mdevice_{$datTbl} ";
				$drop[] = "mdevice_{$datTbl} ";
			}
			//==================================================
			$dbret = $db->query("create table mjobtype_{$datTbl} as select * from mjobtype");
			if($dbret)
			{
				$shell .= " -t mjobtype_{$datTbl} ";
				$drop[] = "mjobtype_{$datTbl} ";
			}
			//==================================================
			$filename = "emvinc_{$datTbl}_{$inc_back_sig}.bz2";
			//$filename = "emvinc_{$datTbl}_{$inc_back_sig}";
			//==================================================
			//header('Content-Type: text/plain; filename="' . $filename . '" ');
			header( 'Content-Type: application/octet-stream' ); 
			header( 'Content-Disposition: attachment; filename="'. $filename .'"' ); 
			//==================================================
			$shell .= " | bzip2 -c";
			$fp = popen($shell,'r');
			$data = '';
			$offset = 0;
			$oldfilesize = 0;
			$filesize = 0;
			$samesize = 0;
			$finish = false;
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
			pclose($fp);
			//==================================================
			$sqldrop = '';
			foreach($drop as $tbl)
			{
				$sqldrop .= "drop table {$tbl};\n";
			}
			$dbdrop = $db->query($sqldrop);
			//==================================================
			echo $data;
			//==================================================
		}
	}
	
	
	
	
	
	
	
	

?>