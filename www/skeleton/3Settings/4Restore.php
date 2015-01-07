<?php

function getBackupFile($params)
{
	global $db, $sid, $log;
	
	$d = dir(DIR_RESTORE);

	$result = array();
	$result['backlist'] = array();

	while (false !== ($entry = $d->read()))
	{
		if(!is_dir(DIR_RESTORE . "/" . $entry)) continue;
		
		$d1 = dir(DIR_RESTORE . "/" . $entry);
		while (false !== ($entry1 = $d1->read()))
		{
			if (!preg_match('/bz2$/', $entry1)) continue;
			$log->l('INFO', "getBackupFile: $entry1 loginid:{$sid->loginId}");
			$fname = str_replace(array('.bz2'),array(''), $entry1);
			$file = array();
			list($type,$date,$file['hash']) = explode('_',$fname);
			$file['date'] = date('Y-m-d H:i:s',strtotime($date));
			switch($type)
			{
			case 'emvback':
				$file['type'] = 'Full backup';
				break;
			case 'emvinc':
				$file['type'] = 'Incremental backup';
				break;
			case 'emvarc':
				$file['type'] = 'Archive';
				break;
			default:
				$file['type'] = $type;
				break;
			}
			$file['fullname'] = DIR_RESTORE . "/" . $entry . "/" . $entry1;
			array_unshift($result['backlist'], $file);
		}
		$d1->close();
	}
	$d->close();
	
	$rspcode = 0;
	$errmsg = '';
	return array(
		'rspcode' 	=> $rspcode,
		'errmsg' 	=> $errmsg,
		'result'	=> $result,
	);
}

function restore($params)
{
	global $db, $sid, $log;
	
	//$valid = $db->getVar("select count(*) from equfw.musers where idlogin='{$sid->loginId}' and password=md5('{$params['password']}');");
	$valid = 1;
	if($valid > 0)
	{
		$result = array();
		$errmsg = '';
		//$filename = DIR_UPLOADTMP . "{$params['fullname']}.bz2";
		$filename = "{$params['fullname']}";
		//$result['status'] = 'Restore failed';
		if(is_file($filename))
		{
			$filenoext = str_replace('.bz2','',basename($params['fullname']));
			list($type,$date,$hash) = explode('_',$filenoext);
			switch($type)
			{
			case 'emvback':
				$tmp = "bunzip2 -kc {$filename} | psql template1 postgres";
				//shell_exec('dropdb -U ' . DBUSER . ' ' . DBNAME);
				shell_exec('psql -U postgres -c "SELECT pg_terminate_backend (pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = \'' . DBNAME . '\';"');
				shell_exec('psql -U postgres -c "drop database ' . DBNAME . ';"');
				shell_exec($tmp);
				$result['status'] = 'Restored';
				break;
			case 'emvinc':
				$tmp = "bunzip2 -kc {$filename} | psql template1 postgres";
				$inc_back_sig = $db->getVar("select value from msetting where item = 'INC_BACK_SIG';");
				if($inc_back_sig == $hash)
				{
					shell_exec($tmp);
					incRestore($date);
					$result['status'] = 'Restored';
				}
				else
				{
					$result['status'] = 'Restore Failed. Hash code mismatch';
				}
				break;
			case 'emvarc':
				$tmp = "bunzip2 -kc {$filename} | psql template1 postgres";
				shell_exec($tmp);
				checkarchive();
				$result['status'] = 'Restored';
				break;
			default:
				$result['status'] = 'Restore failed';
				break;
			}
		}
	}
	else
	{
		$result['status'] = 'Password not valid';
	}
	
	return array(
		'rspcode' 	=> 0,
		'errmsg' 	=> $errmsg,
		'result'	=> $result,
	);
}

function incRestore($dateshort)
{
	global $db, $sid, $log;
	
	$datelong = date('Y-m-d H:i:s',strtotime($dateshort));
	
	$drop = array();
	
	$sql = '';
	
	$sql .= "delete from tjoblist where idjob in (select idjob from tjoblist_{$dateshort});\n";
	$sql .= "insert into tjoblist (select * from tjoblist_{$dateshort});\n";
	$drop[] = "tjoblist_{$dateshort}";
	
	$sql .= "delete from tjobbatch where idjob in (select idjob from tjobbatch_{$dateshort});\n";
	$sql .= "insert into tjobbatch (select * from tjobbatch_{$dateshort});\n";
	$drop[] = "tjobbatch_{$dateshort}";
	
	$sql .= "delete from tjobact where idjob in (select idjob from tjobact_{$dateshort});\n";
	$sql .= "insert into tjobact (select * from tjobact_{$dateshort});\n";
	$drop[] = "tjobact_{$dateshort}";
	
	$sql .= "delete from equfw.musers;\n";
	$sql .= "insert into equfw.musers (select * from equfw.musers_{$dateshort});\n";
	$drop[] = "equfw.musers_{$dateshort}";
	
	$sql .= "delete from msetting;\n";
	$sql .= "insert into msetting (select * from msetting_{$dateshort});\n";
	$drop[] = "msetting_{$dateshort}";
	
	$sql .= "delete from mdevice;\n";
	$sql .= "insert into mdevice (select * from mdevice_{$dateshort});\n";
	$drop[] = "mdevice_{$dateshort}";
	
	$sql .= "delete from mjobtype;\n";
	$sql .= "insert into mjobtype (select * from mjobtype_{$dateshort});\n";
	$drop[] = "mjobtype_{$dateshort}";
	
	$rows = $db->getResults("select idjob from tjoblist_{$dateshort}", ARRAY_A);
	foreach($rows as $row)
	{
		$sql .= "drop table IF EXISTS tjob_{$row['idjob']};\n";
		$sql .= "create table tjob_{$row['idjob']}  as select * from tjob_{$row['idjob']}_{$dateshort};\n";
		$drop[] = "tjob_{$row['idjob']}_{$dateshort}";
		
		$sql .= "drop table IF EXISTS tjob_{$row['idjob']}_dup;\n";
		$sql .= "create table tjob_{$row['idjob']}_dup as select * from tjob_{$row['idjob']}_dup_{$dateshort};\n";
		$drop[] = "tjob_{$row['idjob']}_dup_{$dateshort}";
	}
	
	$dbquery = $db->query($sql);
	
	$sqldrop = '';
	foreach($drop as $tbl)
	{
		$sqldrop .= "drop table IF EXISTS {$tbl};\n";
	}
	$dbdrop = $db->query($sqldrop);
	
}

function checkarchive()
{
	global $db, $sid, $log;
	
	$rows = $db->getResults("select idjob from tjoblist where status = 4;", ARRAY_A);
	
	foreach($rows as $row)
	{
		$dbcount = $db->getVar("select count(*) as jml from tjob_{$row['idjob']};");
		if(is_numeric($dbcount))
		{
			if($dbcount > 0)
				$dbupdate = $db->query("update tjoblist set status=3 where idjob={$row['idjob']}");
		}
	}
}

?>