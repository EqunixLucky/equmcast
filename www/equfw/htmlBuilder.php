<?

class htmlBuilder
{
	private $menuTree, $skeleton, $pageContent, $tabMenu;
    	
	function __construct($skeleton)
	{	
		$this->skeleton = DIR_PREFIX_PHP . "/{$skeleton}";
		if (!is_dir($this->skeleton)) { echo "this {$this->skeleton} is not directory!\n"; return FALSE; }
		
		$this->menuTree = array();
		$d = dir($this->skeleton);
		while (FALSE !== ($menudir = $d->read()))
		{
			if ($menudir == '.' || $menudir == '..') continue;
			$subdir = "{$this->skeleton}/{$menudir}";
			if (!is_dir($subdir))
			{
			    
				continue;
			}
		    
			$sdir = dir($subdir);
			$menu = array();
			while (($file = $sdir->read()) !== FALSE)
			{
				if (!preg_match('/^tab(.*)\.html$/', $file)) continue;
								
				$fname 	 = "{$this->skeleton}/{$menudir}/{$file}";				
				$exts 	 = explode('.', $file);
				$ext 	 = end($exts);
				$tabMenu = preg_replace('/^tab/', '', $exts[0]);
				$ext     = $tabMenu;
				$content = file_get_contents($fname);
				$content = $this->buildPageContent($content);
				$mdir    = explode('/', $subdir);
				$mdirext = end($mdir);
				$menu[$ext] = str_replace('%DIR_CURRENT', "/static/apps/{$mdirext}", $content);
				
			}	
			$sdir->close();
			$this->menuTree[$menudir] = $menu;
		}
		$d->close();		
		ksort($this->menuTree);
		
		return TRUE;
	}
	

	function buildStructure()
	{
		$this->tabMenu    = '';
		$this->mainMenu    = "\t\t<div id='menu' >\n";
		$this->pageContent = '';
		foreach ($this->menuTree as $key1 => $val1)
		{
			$idx = substr($key1, 0, 1);
			if (isset($val1['img']))
			{
			    $this->mainMenu    .= "<img class='menuButton' src='data:image/png;base64,{$val1['img']}' id='{$key1}' >\n";
			    unset($val1['img']);
			}
			else
			    $this->mainMenu    .= "\t\t\t<img class='menuButton' src='static/apps/{$key1}/menu_{$idx}.png' id='{$key1}' >\n";
			
			$tabMenu 	    = '';
			
			ksort($val1);
			foreach ($val1 as $key2 => $val2)
			{
				$prompt = str_replace('_', ' ', $key2);
				$prompt = substr($prompt, 1);
				$tabMenu .= "\t\t\t<li class='nav' id='tab_{$key1}_{$key2}' ><a><strong>$prompt</strong></a></li>\n";
			
				$content = $val2;
				$this->pageContent .= "\n\t\t<div id='page_tab_{$key1}_{$key2}' class='mainContent' >\n$content\n\t\t</div>";
			}
   
			$this->tabMenu    .= "\n\t\t<ul id='page_{$key1}' class='tab-nav' >\n$tabMenu\t\t</ul>";
		}
		
		$this->tabMenu .= "\n\t\t<div class='clearfix' > </div>\n";
		$this->mainMenu .= "\t\t</div>\n";
	}
	
	function buildPageContent($content)
	{
		global $db, $log, $sid;
		
		if (preg_match('/%JSONQUERY\{(.*)\}/', $content, $arr))
		{
		    
			$sql = $arr[1];
			$rows = $db->getResults($sql, ARRAY_A);
			$jsonqres = json_encode($rows);
			
			//$log->l('INFO', "buildPageContent: $sql, $jsonqres");
			$content = preg_replace('/%JSONQUERY\{(.*)\}/', $jsonqres, $content);
		}
		if (preg_match('/%ACL\:(.*)\{(.*)\}/', $content, $arr))
		{
			$aclList = $arr[1];
			$code = '';
			
			//$log->l('INFO', "htmlBuilder: $aclList -> $arr[2]");
			if (isset($sid->acls))
			    foreach($sid->acls as $acl)
				if (!empty($acl) && strstr($aclList, $acl)) $code = $arr[2];
			
			$content = preg_replace('/%ACL\:(.*)\{(.*)\}/', $code, $content);
			
		}
		//str_replace('%DIR_CURRENT', "/static/apps/{$mdirext}", $content);
		
		return $content;
	}
	
	function output()
	{
		global $sid;
		
		$this->buildStructure();
		
		$strrpl = array(
			'%USERNAME'	=> $sid->username,
			'%MAINMENU'	=> $this->mainMenu,
			'%TABMENU'	=> $this->tabMenu,
			'%PAGECONTENT'  => $this->pageContent,
		);
		$index = file_get_contents("{$this->skeleton}/index.html");
		
		return str_replace(array_keys($strrpl), array_values($strrpl), $index);
	}
	
	//
	//function parseContent($content)
	//{
	//	$pagetab = simplexml_load_string($content);
	//	$arr = json_decode(json_encode($pagetab), TRUE);
	//
	//	
	//	// process event
	//	// process hook to event
	//	// pagemenu....
	//	
	//	print_r($arr);
	//}
}

?>
