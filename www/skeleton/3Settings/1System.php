<?php

	function init()
	{
		global $db, $sid, $log;
		
		$MAX_LINE_PER_BATCH = getSetting('MAX_LINE_PER_BATCH');
		$result['MAX_LINE_PER_BATCH'] = $MAX_LINE_PER_BATCH;
		
		$MAX_FILE_PER_JOB = getSetting('MAX_FILE_PER_JOB');
		$result['MAX_FILE_PER_JOB'] = $MAX_FILE_PER_JOB;
		
		$MAX_JOB_RUN = getSetting('MAX_JOB_RUN');
		$result['MAX_JOB_RUN'] = $MAX_JOB_RUN;
		
		$rspcode = 0;
		$errmsg = '';
		return array(
			'rspcode' 	=> $rspcode,
			'errmsg' 	=> $errmsg,
			'result'	=> $result,
			    );
	}
	
	function save($params)
	{
		$rspcode = 0;
		$errmsg = '';
		$result = 'New setting has been saved';
		
		if(is_numeric($params['MAX_LINE_PER_BATCH']) && is_numeric($params['MAX_FILE_PER_JOB']) && is_numeric($params['MAX_JOB_RUN']))
		{
			$res = setSetting('MAX_LINE_PER_BATCH',$params['MAX_LINE_PER_BATCH']);
			$res = setSetting('MAX_FILE_PER_JOB',$params['MAX_FILE_PER_JOB']);
			$res = setSetting('MAX_JOB_RUN',$params['MAX_JOB_RUN']);
		}
		else
		{
			$rspcode = 1;
			$errmsg = 'All input must be numeric.';
			$result = 'Fail to safe new settings';
		}
		
		return array(
			'rspcode' 	=> $rspcode,
			'errmsg' 	=> $errmsg,
			'result'	=> $result,
			    );
	}

?>