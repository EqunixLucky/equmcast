<?
/* 
 * dbaccess.php
 * version 20060224 - Julyanto
 * version 20140111 - Julyanto 
 */

include_once('logger.php');
 
define('OBJECT','OBJECT',TRUE);
define('ARRAY_A','ARRAY_A',TRUE);
define('ARRAY_N','ARRAY_N',TRUE);

class db
{
	var $dbname;
	var $colInfo;
	var $affectedRows;
	var $resultRows;

	function __construct($connStr)
	{
		global $log;

		if (preg_match('/dbname=([a-z0-9]+) /i', $connStr, $m)) $this->dbname = $m[1];

		$this->dbh = pg_connect($connStr);
		if (!$this->dbh) 
		{
			$log->l("ERRO", "Can't Connect: $connStr");
			return FALSE;
		}

		return TRUE;
	}
	
	function __destruct()
	{
		
		
	}

	function connected()
	{
		return ($this->query("SELECT 1;")*1 == 1);
	}
	
	function log($lvl=LOGERROR, $str = "")
	{
		global $log;

		$this->lastError = pg_last_error();
		//if (empty($lastError)) return TRUE;

		$logStr = "DB|$str|{$this->lastError}\n{$this->lastQuery}";
		$log->l($lvl, $logStr);
	
		return TRUE;
	}

	function query($query)
	{
		$query = trim(preg_replace('/\s+/', ' ', $query));

		$this->num_queries++;
		$this->lastQuery = $query;
		if (! $this->result = pg_query($this->dbh, $query))
		{
			$this->log("ERRO", "***");
			return FALSE;
		}
		
		$this->affectedRows = pg_affected_rows($this->result);
		$this->resultRows   = pg_num_rows($this->result);

		if (preg_match('/^(CREATE|DROP)/', strtoupper($query)))
			return true;
		elseif (preg_match('/^(INSERT|UPDATE|DELETE)/', strtoupper($query)))
			return ($this->affectedRows != 0);
		else
			return ($this->resultRows != 0);
			
	}

	function getVar($query)
	{
		if (!$this->query($query)) return FALSE;
		$row = pg_fetch_object($this->result, 0);
		if ($row) $values = array_values(get_object_vars($row));
		pg_free_result($this->result);

		// If there is a value return it else return NULL
		return (isset($values[0]) && $values[0]!=='')?$values[0]:NULL;
	}

	function getRow($query, $output = ARRAY_N)
	{
		if (!$this->query($query)) return FALSE;

		$row = pg_fetch_object($this->result, 0);
		pg_free_result($this->result);

		if     ($output == ARRAY_A)
			return $row?get_object_vars($row):NULL;
		elseif ($output == ARRAY_N)
			return $row?array_values(get_object_vars($row)):NULL;
		else 
			return $row?$row:NULL;

	}

	function getResults($query, $output = ARRAY_N)
	{

		if (!$this->query($query)) return FALSE;

		$i=0;
		unset($this->colInfo);
		while ($i < pg_num_fields($this->result))
		{
			$this->colInfo[$i]->name = pg_field_name($this->result,$i);
			$this->colInfo[$i]->type = pg_field_type($this->result,$i);
			$this->colInfo[$i]->size = pg_field_size($this->result,$i);
			$i++;
		}
	
		$i=0;
		$rows = array();
		while ($row = @pg_fetch_object($this->result, $i))
		{
			if ($output == OBJECT)
				$rows[$i] = $row;
			elseif ($output == ARRAY_A)
				$rows[$i] = get_object_vars($row);
		 	elseif ($output == ARRAY_N)
			{
				$row = get_object_vars($row);
				$rows[$i] = array_values($row);
			}

			$i++;
		}
		pg_free_result($this->result);
		$this->resultRows = $i;

		if ($i==0) return NULL; else return $rows;
	}
	

	/****************************************************
		attr = array (
			'tblhdr = array (
				'table' => '',
				'tr' => '',
				'td' => '',
			),
			'tbldata' = array (
				'tr' => '',
				'td' => '',
			);
		);

	*****************************************************/
	function getTable($query, $attr = NULL)
	{
		$aResult = $this->getResult($query);
		
		// create table header
		$fields = "";
		foreach ($this->colInfo as $f)
		{
			$fields .= "<td {$attr[tblhdr]->td} >{$f->name}</td>\n";
		}
		$html = "<table {$attr[tblhdr]->table} ><tr {$attr[tblhdr]->tr} >$fields</tr>";

		foreach ($aResult as $row)
		{
			$line = '';
			foreach ($row as $col)
			{
				$line .= "<td {$attr[tbldata]->td} >$col</td>\n";
			}

			$lines .= "<tr {$attr[tbldata]->tr} >\n$line</tr>\n";
		}

		return $html;
	}


	function insertArray($tblname, $arr)
	{
		$fields   = '';
		$contents = '';
		foreach ($arr as $key => $val)
		{
			$fields   .= "$key`";
			$contents .= "'$val'`";
		}
		$fields   =  preg_replace('/`/',', ', substr($fields, 0, strlen($fields)-1));
		$contents =  preg_replace('/`/',', ', substr($contents, 0, strlen($contents)-1));

		$retVal = $this->query("INSERT INTO $tblname ($fields) VALUES ($contents);");

		return $retVal;
	}

	/***  END OF DB ACCESS CLASS  ***/
}

$db = new db(DBCONN);

?>
