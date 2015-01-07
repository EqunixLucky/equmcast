<?php

define('LOGPANIC',		0);
define('LOGERROR',		1);
define('LOGWARN',		2);
define('LOGINFO',		3);
define('LOGDEBUG',		4);
define('LOGLEVEL',		99);

if (!defined('DIR_LOG')) define('DIR_LOG', '/tmp/');

class logger
{
	private $LogFile;
	
	function __construct($File)
	{
		$this->LogFile = DIR_PREFIX_PHP . DIR_LOG . $File;
	}

	// Implements file-based logging
	// - Level
	// - Message
	function l($Level, $Message)
	{
		$LogMessage = date("ymd+His") . substr((string)microtime(), 1, 4) . "|$Level|$Message\n";
		
		$fname = $this->LogFile . date('Ymd') . '.log';
		//$fh = fopen($this->LogFile, 'a+');
		$fh = fopen($fname, 'a+');
		if (!$fh) return;
		fputs($fh, $LogMessage);
		fclose($fh);
		
		return TRUE;
	}
}

$log = new logger(LOG_NAME);

?>
