<?php
	define('DATADIR', '/home/equmcast/data/');
	
	$mcastArr = array(
		"privaddr" 	=> "",
		"sessid" 	=> "",
		"filename"	=> "",
		"size"		=> 0,
		"blocks"	=> 0,
		"totalsection"	=> 0,
		"currsection"	=> 0,
		"loop"		=> 0,
		"transfereta"	=> "00:00:00",
		"totaleta"	=> "00:00:00",
		"duration"	=> "0",
		"deltasec"	=> 0,
		"tlastsec"	=> 0,
		"startsec"	=> 0,
		"clients"	=> array()	
	);

	$fp = popen("/bin/equmcast -R 2000000 ".DATADIR.$argv[1]." 2>&1", "r");
	$flog = fopen(DATADIR."logs/mcast.log", "w");
	
	$mcastArr["filename"] = $argv[1];
	
	while(!feof($fp))
	{
		$log = fread($fp, 1024);
		fwrite($flog, $log);
		flush();

		if(preg_match("/Using private multicast address ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)\s+Group ID\:\s+([0-9A-E]+)/", $log, $logmatch))
		{
			$mcastArr["privaddr"] 	= $logmatch[1];
			$mcastArr["sessid"] 	= $logmatch[2];
		}	
		else if(preg_match("/Received REGISTER from client 0x([0-9A-E]+)/" , $log, $logmatch))
		{
			preg_match("/([0-9A-E]{2})([0-9A-E]{2})([0-9A-E]{2})([0-9A-E]{2})/", $logmatch[1], $ipaddr);
			$client = array(
				"id" 		=> $logmatch[1],
				//"ip" 		=> hexdec($ipaddr[1]).".".hexdec($ipaddr[2]).".".hexdec($ipaddr[3]).".".hexdec($ipaddr[4]),
				"ip" 		=> sprintf("%03d", hexdec($ipaddr[3])).sprintf("%03d", hexdec($ipaddr[4])),
				"status" 	=> 0,	// 0:IDLE, 1:COMPLETED, 2:PARTIAL
				"naks"		=> 0,
				"progress"	=> 0
			);
			array_push($mcastArr["clients"], $client);
		}
		/*else if(preg_match("/File\s+ID\:\s+[0-9]+\s+Name\:\s+([0-9a-zA-Z\.]+)/", $log, $logmatch))
		{
			$mcastArr["filename"] = $logmatch[1];
		}*/
		else if(preg_match("/Bytes\:\s+([0-9]+)\s+Blocks\:\s+([0-9]+)\s+Sections\:\s+([0-9]+)/", $log, $logmatch))
		{
			$mcastArr["size"]		= intval($logmatch[1]);
			$mcastArr["blocks"]		= intval($logmatch[2]);
			$mcastArr["totalsection"]	= intval($logmatch[3]);
		}
		else if(preg_match("/Starting pass ([0-9]+)/", $log, $logmatch))
		{
			$mcastArr["loop"]	= intval($logmatch[1]);
			foreach($mcastArr["clients"] as &$client)
			{
				if($mcastArr["loop"] > 1) $client["progress"] = ($mcastArr["blocks"] - $client["naks"]) * 100 / $mcastArr["blocks"];
				if($client["naks"] != 0) $client["status"] = 2;
				$client["naks"] = 0;
			}
		}	
		else if($mcastArr["loop"] < 2 && preg_match("/Sending section ([0-9]+)/", $log, $logmatch))
		{
			$mcastArr["currsection"] = intval($logmatch[1]);
			if($mcastArr["tlastsec"] > 0)
			{
				$mcastArr["deltasec"] += time() - $mcastArr["tlastsec"] ;
				$mcastArr["transfereta"] = date("H:i:s", $mcastArr["startsec"] + ($mcastArr["deltasec"]) * $mcastArr["totalsection"] / $mcastArr["currsection"]);
			}
			else $mcastArr["startsec"] = time();
			
			$mcastArr["tlastsec"] = time();
		}
		else if(preg_match("/Got\s+([0-9]+)\s+NAKs for section\s+[0-9]+\s+from client 0x([0-9A-E]+)/", $log, $logmatch))
		{
			foreach($mcastArr["clients"] as &$client)
			{
				if($client["id"] == $logmatch[2])
				{
					$client["naks"] += intval($logmatch[1]);
					//$client["status"] = ($mcastArr["loop"] < 2) ? 0 : 2;
				}
			}
		}
		else if(preg_match("/Got COMPLETE from client 0x([0-9A-E]+)/", $log, $logmatch))
		{
			foreach($mcastArr["clients"] as &$client)
			{
				if($client["id"] == $logmatch[1])
				{
					$client["naks"] = 0;
					$client["status"] = 1;
				}
			}
		}
		else if(preg_match("/Total elapsed time\:\s+([0-9\.]+)\s+seconds/", $log, $logmatch))
		{
			$mcastArr["duration"] = $logmatch[1];
		}
		else continue;

		file_put_contents(DATADIR."mcast.stat", json_encode($mcastArr));
		//echo json_encode($mcastArr)."\n\n";
	}
	fclose($flog);
	fclose($fp);
?>
