<?

include_once('db.php');

class session
{
	public $loginId, $userName, $acl;
	public $logStart; 
	public $logStop; 
	public $db;

	private $retryMax;
	private $retry;
	private $logout;

	var $resp = array(
		'rspcode' => 0,
		'errmsg'  => '',
		'result'  => 'OK',
	);
	
	function __construct() 
	{
		global $db, $log;
		
		if (!is_object($log)) $log = new logger(LOG_NAME);
		if (!is_object($db)) $db = new db(DBCONN);
		
		$this->userId = NULL;
		$this->logStart = time();
		$this->logStop = NULL;
		$this->retry = 0;
		$this->retryMax = 3;
		
	}

	function l($level, $info)
	{
		return $this->logger->l($level, $info);
	}

	//function auth($user, $pass), $user dan $pass sudah dilakukan escape string
	// authorisasi user
	//return:
	// TRUE berarti authorized
	// FALSE berarti not authorized
	public function auth($user, $pass)
	{
		global $db;
		//$dbIsDiff = $this->dbIsSessionDiff();
		$haveShow = FALSE;
		if ($this->bypass ==FALSE) 
		{
			if ($user!='' && $pass!='' && $this->retry==0) { //|| $dbIsDiff==FALSE) {
				$haveShow = TRUE;
				$user='';
				$pass='';
				$this->retry++;
				$this->bypass = TRUE;
				//display warning close all window
				//$this->display(DISPLAY_WARNING_CLOSE_ALL_WINDOW);	
				//exit();
			}
		}
		
		if ($haveShow == FALSE)
		{
			//jika userId yang sama diinput, retry diincrement,jika tidak dibalikin
			if ((!is_null($this->userId)) && !empty($user) && eregi($user,$this->userId))
				$this->retry++;
			else {
				$this->userId = $user;
				$this->retry = 2;
			}
		
			//authorisasi 
			$isUserValid = $this->dbIsUserIdValid($user,$pass);
			if (is_array($isUserValid)) {

				if ($isUserValid['status']==USER_ACTIVE) {
					$endP = $this->dbCheckLoginPeriode(); //check login periode
					$this->dbSessionCheck(); //check double login
					if ($endP >= 0 ) {
						
						//insert ke table session
						$this->logStop = $endP;	
						$this->dbSessionInsert(date('Y-m-d H:i:s',$this->logStart),
								       date('Y-m-d H:i:s',$this->logStop));
						
						//ambil data group
						$this->dbGetGroup();
						
						doUserLogPage($user,$_SERVER['REMOTE_ADDR'],'login');
						
						$this->retry=0;
						return TRUE;
					}


					//display warning bahwa user bukan berada pada masa login
					$this->display(DISPLAY_WARNING_NOT_IN_LOGPERIOD);
					exit();
				}
				else
				{	
					//display warning bahwa user telah diblock
					$this->display(DISPLAY_WARNING_ACCOUNT_ALREADY_LOCKED);
					exit();
				}

			}

			if ($this->retry>$this->retryMax) {

				$this->dbLockUser($user);
				$this->retry=0;
				$this->bypass = FALSE;
				//display warning account di lock
				$this->display(DISPLAY_WARNING_ACCOUNT_LOCKED);	
				exit();
			}
		}

//		$this->retry = 0;
		header('HTTP/1.0 401 Unauthorized');
		header("WWW-Authenticate: Basic realm=\"". time() . "\"");
		
		//display cancel:
		$this->display(DISPLAY_CANCEL);
		return FALSE;
	}

	//function display($path)
	// menampilkan message ke user
	public function display($path) 
	{
		if (eregi($path,DISPLAY_WARNING_ACCOUNT_LOCKED))
		{
			global $oSID;
			global $db;
			$q = sprintf("update sessions set status=%d, logouttime='%s' where id='%s' and userid='{$this->userId}' and status=%d;",
					'LOCKED', date('Y-m-d H:i:s'),session_id(),SESSION_ACTIVE);
			$db->query($q);

			$id = session_id();
			session_unset();
			session_destroy();
			echo('<html><head><title>Warning Page</title></head><body>'.
				'Your account is locked by Administrator now.<br>'.
				'Please contact your Administrator to unlock.</body></html>');
			return;
		}

		if (eregi($path,DISPLAY_WARNING_CLOSE_ALL_WINDOW))
		{
			echo('<html><head><title>Warning Page</title></head><body>Tutup semua window browser anda, '.
				'kemudian jalankan kembali untuk mulai login.</body></html>');
			return;
		}
				
		if (eregi($path,DISPLAY_CANCEL)) {
			//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=\"/smile/?cancel=1\">" ;
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=/smile/?cancel=1\">";
			return;
		}

		if (eregi($path,DISPLAY_WARNING_NOT_IN_LOGPERIOD)) {
			echo('<html><head><title>Warning page</title></head><body>You cannot login outside the permitted login periode.</body></html>');
			return;
		}

		if (eregi($path,DISPLAY_WARNING_ACCOUNT_ALREADY_LOCKED))
		{
			echo('<html><head><title>Warning page</title></head><body>Your account already locked.. contact your administrator to unlock.</body></html>');
			return;
		}

		if (eregi($path,DISPLAY_UNAUTHORIZED)) {
			echo('<html><head><title>Unauthorized</title></head><body>' .
				'You have not enough priviledge to enter this page</body></html>');
			return;
		}


		session_destroy();
		echo("<html><head><title>.....</title></head><body>"); 
		var_dump($path);
		echo("Default::>tutup browser utk login lagi</body></html>");
		exit();
		//header('location: ./logout'.$path.'/');
	}


	//function dbIsUserIdValid($userid,$pass)
	// checking apakah user id dan password valid, dan status user tidak blocked
	//return:
	// NULL : userid atau password tidak valid
	// ARRAY: valid
	private function dbIsUserIdValid($userid,$pass)
	{
		global $db;
		
		if ($userid=='' || $pass=='') return NULL;
		
		$q = "select userid,pass,fullname,status from useraccess ".
		     "where upper(userid)='{$userid}';";

		$hasil = $db->getRow($q,ARRAY_A);
		if (is_null($hasil)) return NULL;
		
		if (crypt($pass, $hasil['pass'])!==$hasil['pass']) return NULL;
		
		$this->userId = $userid;
		$this->userFullName = $hasil['fullname'];

		return $hasil;

	}	

	//function dbIsInLoginPeriode()
	// checking apakah user berada dalam periode boleh login
	//return:
	// >=0 berarti di dlm login periode, dgn nilai return berupa time logout
	// < 0 berarti di luar login periode
	private function dbCheckLoginPeriode()
	{
        	global $db;

		$day = date("w"); //minggu = 0, senin = 1, ..., sabtu=6

		$q = "select day$day from loginperiode where upper(userid)='{$this->userId}';";
		$hasil = $db->getVar($q);
		if (is_null($hasil)) return -1;

		$periodelist = explode(';',$hasil);
		foreach($periodelist as $p)
		{
			if ($p=='') continue;
			$period=explode(',',$p);
			$h = floor($period[0]/60);
			$h = $h<10?'0'.$h:$h;
			$m = $period[0]%60;
			$m = $m<10?'0'.$m:$m;
			$start = date('Y-m-d ').$h. ':'.$m.':00';
			$start = strtotime($start);
			$stop = $start+($period[1]*60);
		        //debugging only: echo date('Y-m-d H:i:s',$this->logStart). " --> start: " .date('Y-m-d H:i:s',$start)." stop: ".date('Y-m-d H:i:s',$stop)."<br>";
			if (time()>= $start && time() <$stop) return $stop;			
		}
		
		return -1;	
	}


	//function dbLockUser($userid)
	// melakukan update table useraccess, status = locked
	//return:
	// TRUE : sukses
	// FALSE: gagal
	private function dbLockUser($userid)	
	{
		global $db;
		$q = "UPDATE equfw.musers SET status=".USER_LOCKED." where upper(userid)='{$userid}';";
		return $db->query($q);
	}

	//function dbIsSessionDiff()
	// checking ada session id double?
	//return:
	// TRUE : yes different
	// FALSE : no, same
	/*	
	private function dbIsSessionDiff()
	{
		global $db;
		return TRUE;

		$q = "select id,status from sessions where id='".session_id()."' order by regdate desc limit 1;";
		$res = $db->getRow($q,ARRAY_A);
		if (!$res) return TRUE;
		if ($res['status'] != SESSION_ACTIVE) return TRUE;
		if ($res['status'] != SESSION_ACTIVE && $res['status'] != SESSION_ENDPERIODE)
		{
			$q='update sessions set status='.SESSION_ACTIVE.' where id=\''.session_id().'\';';
			$db->query($q);
		} 
		return FALSE;
	}
	*/

	//function dbCheckSession()
	// check apakah ada session lain yg lagi aktif
	//return:
	// -
	private function dbSessionCheck()
	{
		global $db;

		$q = "select id,logouttime from sessions where upper(userid)='{$this->userId}' and status=".SESSION_ACTIVE.";";

		$sessData = $db->getRow($q,ARRAY_A);
		if (is_null($sessData['id']) || empty($sessData['id'])) return;
		if (eregi($sessData['id'], session_id())) return;
		
		//berikut dijalankan jika ternyata ada session yg aktif

		//delete file
		//if (file_exists(TMP_SESS.'/sess_'.$sessData['id']))
		//	unlink(TMP_SESS.'/sess_'.$sessData['id']);

		//update status yg lama
		if ($this->logStart>strtotime($sessData['logouttime']))
		{
			$status = SESSION_ENDPERIODE;
			$q = "update sessions set status={$status} where id='{$sessData['id']}' and 
				upper(userid)='{$this->userId}' and status=".SESSION_ACTIVE.';'; 
		}
		else {
			$status = SESSION_ENDDOUBLE;
			$q = "update sessions set status=".$status.",logouttime='".date('Y-m-d H:i:s',$this->logStart).
				"' where id='".$sessData['id']."' and status=".SESSION_ACTIVE.';'; 
		}

		$db->query($q);
	}


	//function dbSessionInsert($logstart,$defaultlogouttime) 
	// insert data session
	//return:
	// -
	private function dbSessionInsert($logstart,$defaultlogouttime)
	{
		global $db;
		$q = "insert into sessions (id, regdate, remotehost, remoteport, userid,".
			"logouttime,status) values('". session_id()."','".$logstart."','".
			$_SERVER['REMOTE_ADDR']."',".$_SERVER['REMOTE_PORT'].",'".$this->userId."','".
			$defaultlogouttime."',"	. SESSION_ACTIVE .");";
		$db->query($q);
	}


	//function dbGetGroup()
	// retrieve data group from table
	//return:
	// -1    : user is not assigned to any group
	// other : number of data group retrieved
	private function dbGetGroup()
	{
		global $db;
		$q = "select userid, grouplist from usergroup where upper(userid)='".$this->userId."';";

		$row = $db->getRow($q,ARRAY_A);
		if (!$row) 
			$hasil='common;';
		else
		{
			$hasil = $row['grouplist'];
			$hasil.='common;';
		}
		$this->group = explode(';',$hasil);
		$i=0;
		foreach ($this->group as $groupitem)
		{
			$groupitem = trim($groupitem);
			if ($groupitem=='') continue;
			$q = "select grouppath,groupattr from grouppath where groupname='".$groupitem."';";
			$attr = $db->getResults($q,ARRAY_N);
			
			if (is_null($attr)) return -1;

			foreach($attr as $row)
			{
				$i++;
                                if (isset($this->groupPath[$row[0]]))
                                {
                                        $this->groupPath[$row[0]].=$row[1];
                                }
                                else
				$this->groupPath[$row[0]]= $row[1]; //group['grouppath']='groupattr'
			}
		}
		return $i;	
	}
	
	
	function login($params)
	{
		global $db, $log;
		
		if (!$db->connected())
		{
			return array(
				'rspcode' => 1,
				'errmsg'  => "Cannot access to database, please restart Server!",
				'result'  => '',
			);
		}

		$ret = $db->getRow
		("
			SELECT username, idlogin, acl FROM equfw.musers	
			WHERE
				idlogin = '{$params['username']}' AND
				password = md5('{$params['password']}') AND
				status >= 100
			LIMIT 1;
		");
		list($this->username, $this->loginId, $this->acl) = $ret;
		$this->acls = explode(';', $this->acl);

		if (isset($this->loginId))
		{
			$log->l('INFO', "Login as {$this->loginId}, acl: {$this->acl}");

			$rspcode 	= 0;
			$errmsg  	= 'OK';
			$result  	= array(
				'loginid' 	=> $this->loginId,
				'loginname'	=> $this->username,
				'url'		=> '/'
			);

		}
		else
		{
			$log->l('ERRO', "Cannot Login, check user/pass/status, status should be > 99");
			$rspcode	= 13;
			$errmsg 	= 'Username or Password is not correct, please try again!';
			$result 	= '';
		}
		
		return array(
			'rspcode' 	=> $rspcode,
			'errmsg'	=> $errmsg,
			'result'        => $result,
		);
	}

	function logout($param)
	{
		global $log;
		$log->l('INFO', "{$this->loginId} has logout!");
		unset($this->loginId);

		return array(
			'rspcode' 	=> 0,
			'errmsg'	=> 'OK',
			'result'        => array( 'url' => '/'),
		);
	}

	function changepassword($params)
	{
		global $db, $log;
				
		$retVal = $db->query("
			UPDATE  equfw.musers
			SET
				password = md5('{$params['password']}'),
				username = '{$params['username']}'
			WHERE
				idlogin = '{$this->loginId}'"
		);
		
		$rspcode = 0;
		$errmsg  = "{$this->loginId} Successfully Change Password";
		$result  = 'OK';
		
		if (!$retVal)
		{
			$rspcode = 0;
			$errmsg  = "{$this->loginId} FAIL Change Password";
			$result  = '';
			$loglvl  = 'ERRO';
		}
		$log->l($loglvl, $errmsg);
		
		return array(
			'rspcode'	=> $rspcode,
			'errmsg'	=> $errmsg,
			'$result'	=> $result
		);
	}
	
	function heartbeat($param)
	{
		$d = date('l jS \of F Y h:i:s A');
		
		
		$resp['result'] = "-";
		
		return $resp;
	}

}

session_name('EqunixSPOE');
session_start();

if (!isset($_SESSION['id'])) $_SESSION['id'] = new session();
$sid = &$_SESSION['id'];

?>
