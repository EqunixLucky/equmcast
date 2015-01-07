<?

define('MAGIC_CODE', 'EQNX');

define('EMV_ACK', 		'A');
define('EMV_DEV_ISBUSY', 	'B');
define('EMV_DEV_ISOPEN', 	'E');
define('EMV_PING', 		'Z');
define('EMV_DEV_START', 	'S');
define('EMV_DEV_STOP',		's');
define('EMV_END',		'X');
define('EMV_DEV_OPEN',		'O');
define('EMV_DEV_CLOSE',		'o');
define('EMV_DEV_STATUS', 	'T'); // params:{devid, numberoftrx}
define('EMV_DEV_ACTIVITY', 	'V');

define('EMV_STATPRINT', 	'F'); //{jobid, counter print, lastprint, prints}
define('EMV_STATCAM', 		'H');
define('EMV_SENDJOB', 		'J');  // sendjob, startprint, stopprint, stopjob
define('EMV_STOPJOB', 		'j');
define('EMV_LISTENCAM',		'L');
define('EMV_UNLISTENCAM',	'l');
define('EMV_FETCHMSG', 		'M');
define('EMV_PUSHMSG', 		'm');
define('EMV_LASTPRINT',		'N');
define('EMV_DEV_PROGRESS', 	'P');

define('TCPACK_SUCC', 		0);
define('TCPACK_ERRCONN', 	1);
define('TCPACK_ERRPING', 	2);
define('TCPACK_ERRSIZE', 	3);
define('TCPACK_ERRSEND', 	4);
define('TCKACK_ERRMAGIC', 	5);
define('TCPACK_ERRACK', 	6);
define('TCPACK_ERRRST', 	7);
define('XX', 	8);


/********************************************************************
status returned from LINX printer....
1. Jetstatus (idle, priting, paused)
2. 

//*******************************************************************/

class serviceComm
{
	var $tcp;
	var $tcpAck;
	var $run;
	var $rspParam;
	
	function __construct($hostname = '127.0.0.1', $port = 6000)
	{
		global $db, $log, $sid;
		
		$this->run = FALSE;
		$this->tcp = fsockopen($hostname, $port, $errno, $errstr, 60);		
		
		if (!$this->tcp)
		{
			$log->l('ERRO', "serviceComm: Please Start the EMVD.");
		
			//$log->l('ERRO', "Server emvd not run yet, restarting...");
			//exec("~/bin/emvd &", $out, $retval);
			//sleep(1);
			//$this->tcp = fsockopen($hostname, $port, $errno, $errstr, 60);		
			//if (!$this->tcp)
			//{
			//	$log->l('ERRO', "FAIL connect to server $hostname:$port! errno=$errno, errString=$errstr");
			//	return TCPACK_ERRCONN;
			//}
			//$log->l('INFO', "Successfully restart the Server EMVD.");
			//
			//if ($this->sendTcp(EMV_PING) == TCPACK_SUCC)
			//	$log->l('INFO',"Successfully connected to the Service!");
			//else
			//	return FALSE;
		}
		else
		{
			$this->run = TRUE;
		}
		//$log->l('INFO', "Server EMVD Ready for service.");
		
		return TRUE;
	}
	
	function ping()
	{
		global $log;
		
		if ($this->sendTcp(EMV_PING) == TCPACK_SUCC)
			$log->l('INFO',"Successfully connected to the Service!");
		else
			return FALSE;
		
		return TRUE;
	}
	
	function sendTcp($cmd, $params = array(0))
	{
		global $db, $log, $sid;
		
		$this->rspParam = array(0, '');
		if (!$this->run)
		{
			$log->l('ERRO', "serviceComm: NOT RUNNING.");
			return TCPACK_ERRCONN;
		}
		$sparam = implode(',', $params);
		$size = 5 + strlen($sparam);
		if ($size > 255)
		{
			$log->l('ERRO', "CMDSIZE EXCEED MAX ALLOWED!!!!");
			return TCPACK_ERRSIZE;
		}
		else
			$strCmd = chr($size) . MAGIC_CODE . $cmd . $sparam;
	
		$ret = fwrite($this->tcp, $strCmd, $size+1);
	
		if (!$ret)
		{
			$log->l('ERRO',"Cannot send to Server!");
			return TCPACK_ERRSEND;
		}
		
		$c = fread($this->tcp, 1);
		$size = ord($c);
		if ($size>0) $msg = fread($this->tcp, $size);
		else $msg = '';
		
		if (substr($msg, 0, 4) != MAGIC_CODE)
		{
			$log->l('ERRO', "Magic Code is not sync!, untrusted condition! EXIT");
			fclose($this->tcp);
			return TCKACK_ERRMAGIC; 
		
		}
		else if (substr($msg, 4, 1) != EMV_ACK)
		{
			$log->l('ERRO', "Malformat response. Ack not found!");
			fclose($this->tcp);
			return TCPACK_ERRACK; 
		}	
		
		list($this->retval, $rsp) = explode(';', substr($msg, 5), 2);
		$ret = array();
		$arsp = explode(',', $rsp);
		foreach($arsp as $arspx) { list($key, $val) = explode(':', $arspx); $ret[$key] = $val; }
		$this->rspArray = $ret;
		
		$errlvl = ($this->retval == 0) ? 'INFO':'ERRO';
		$log->l($errlvl, "CommResp= {$this->retval}=>'{$rsp}'");
	
		$this->tcpAck = ($this->retval == 0);
		
		//return ($this->tcpAck) ? TCPACK_SUCC : TCPACK_ERRRST;
		return ($this->tcpAck);
	}
	
	// return TRUE or FALSE
	function openDevice($devid)
	{
		global $db, $log, $sid;
		//{devid, devtype, factorymodel, ipaddr, port, keycode}
		$devArr = $db->getRow(
			"SELECT devid, model, ipaddr, port FROM mdevice WHERE status = 1 AND devid = $devid;"
		);
		
		if (!is_array($devArr))	
		{
			$this->rspParam = "Device is not found in Database";
			return FALSE;
		}		
		$log->l('INFO', "OpenDevice: $devid connected.");

		return $this->sendTcp(EMV_DEV_OPEN, $devArr);
	}

	// return TRUE or FALSE
	function closeDevice($devid)
	{
		global $db, $log, $sid;
		
		$log->l('INFO', "CloseDevice: $devid.");
		return $this->sendTcp(EMV_DEV_CLOSE, array($devid));
	}
	
	// return TRUE or FALSE
	function startDevice($devid, $dirJob, $dirArch, $idjob)
	{
		global $db, $log, $sid;
		
		$log->l('INFO', "StartDevice: $devid.");
		//if ($this->sendTcp(EMV_DEV_ISBUSY, array($devid)) == TCPACK_SUCC)
		//{
		//	$log->l('ERRO', "Dev: $devid cannot Started because already START.");
		//	return FALSE;
		//}
// remark by jul 20140613
//		$this->sendTcp(EMV_DEV_STOP, array($devid));
		return $this->sendTcp(EMV_DEV_START, array($devid, $dirJob, $dirArch, $idjob));
	}
	
	function endDevice()
	{
		global $db, $log, $sid;
		
		$log->l('INFO', "endDevice");
		return $this->sendTcp(EMV_END, array(0));
	}
	// return TRUE or FALSE
	function stopDevice($devid)
	{
		global $db, $log, $sid;
		
		$log->l('INFO', "StopDevice: $devid.");
		//if ($this->sendTcp(EMV_DEV_ISBUSY, array($devid)) != TCPACK_SUCC)
		//{
		//	$log->l('ERRO', "Dev: $devid cannot Stopped because already STOP.");
		//	return FALSE;
		//}
		return $this->sendTcp(EMV_DEV_STOP, array($devid));
	}

	// return TRUE or FALSE
	function getDevAct($devid)
	{
		global $db, $log, $sid;
		
		if ($this->sendTcp(EMV_DEV_ACTIVITY, array($devid)))
			return $this->rspArray;
		else
			return array('act' => 0, 'err' => 0);
	}

	
	function isBusy($devid)
	{
		global $db, $log, $sid;
		
		$retval = $this->sendTcp(EMV_DEV_ISBUSY, array($devid));
		$log->l('INFO',"ISBUSY: {$this->retval} ". implode(':', $this->rspArray));
		if ($this->retval == 102) return FALSE;
		if ($this->retval == 108) return TRUE;
		if ($this->retval == 0) return FALSE;
		if ($this->rspArray['status'] == 'NOTFOUND') return FALSE;
		
		return $retval;
	}


	function isOpen($devid)
	{
		global $db, $log, $sid;
		
		return $this->sendTcp(EMV_DEV_ACTIVITY, array($devid));
	}
	
	function getProgress($devid)
	{
		global $log;
		
		$retval = $this->sendTcp(EMV_DEV_PROGRESS, array($devid));
		$log->l('INFO', "getProgress, ". implode(':', $this->rspArray));
		return $retval;
	}
	
	function getStatDevice($devid)
	{
		global $db, $log, $sid;
		
		$this->sendTcp(EMV_DEV_STATUS, array($devid));
		
		return $this->rspArray;
	}
	
	function close()
	{
		fclose($this->tcp);
	}
}

?>
