<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


// include pear
//require_once("DB.php");

/**
* Database Update class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* @package application
*/
class ilDBUpdate
{
	/**
	* db update file
	*/
	var $DB_UPDATE_FILE;

	/**
	* current version of db
	* @var	integer	db version number
	*/
	var $currentVersion;

	/**
	* current version of file
	* @var	integer	fiel version number
	*/
	var $fileVersion;

	/**
	* constructor
	*/
	function ilDBUpdate($a_db_handler = 0,$tmp_flag = false)
	{
		// workaround to allow setup migration
		if ($a_db_handler)
		{
			$this->db =& $a_db_handler;
			
			if ($tmp_flag)
			{
				$this->DB_UPDATE_FILE = "./sql/dbupdate.php";			
			}
			else
			{
				$this->DB_UPDATE_FILE = "../sql/dbupdate.php";
			}
		}
		else
		{
			global $mySetup;
			$this->db = $mySetup->db;	
			$this->DB_UPDATE_FILE = "./sql/dbupdate.php";
		}
   
		$this->readDBUpdateFile();
		$this->getFileVersion();
		$this->getCurrentVersion();
	}
	
    /**
	* destructor
	* 
	* @return boolean
	*/
	function _DBUpdate()
	{
		$this->db->disconnect();
	}

	function readDBUpdateFile()
	{
		if (!file_exists($this->DB_UPDATE_FILE))
		{
			$this->error = "no_db_update_file";
			$this->filecontent = array();
			return false;
		}
		
		$this->filecontent = @file($this->DB_UPDATE_FILE);
		return true;
	}

	function getCurrentVersion()
	{
		$q = "SELECT value FROM settings ".
			 "WHERE keyword = 'db_version'";
		$r = $this->db->query($q);
			
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
			
		$this->currentVersion = (integer) $row->value;

		return $this->currentVersion;
	}

	function setCurrentVersion ($a_version)
	{
		{
			$q = "UPDATE settings SET ".
				 "value = '".$a_version."' ".
				 "WHERE keyword = 'db_version'";
		}

		$this->db->query($q);
		$this->currentVersion = $a_version;
		
		return true;
	}

	function getFileVersion()
	{
		//go through filecontent and search for last occurence of <#x>
		reset($this->filecontent);
		$regs = array();
		foreach ($this->filecontent as $row)
		{
			if (ereg("^<#([0-9]+)>", $row, $regs))
			{
				$version = $regs[1];
			}
		}

		$this->fileVersion = (integer) $version;
		return $this->fileVersion; 
	}
	
	/**
	* execute a query
	* @param	object	DB 
	* @param	string	query
	* @return	boolean
	*/
	function execQuery($db,$str)
	{
		$sql = explode("\n",trim($str));
		for ($i=0; $i<count($sql); $i++)
		{
			$sql[$i] = trim($sql[$i]);
			if ($sql[$i] != "" && substr($sql[$i],0,1)!="#")
			{
				//take line per line, until last char is ";"
				if (substr($sql[$i],-1)==";")
				{
					//query is complete
					$q .= " ".substr($sql[$i],0,-1);
					$r = $db->query($q);
					if (DB::isError($r))
					{
						$this->error = $r->getMessage();
						return false;
					}
					unset($q);
				} //if
				else
				{
					$q .= " ".$sql[$i];
				} //else
			} //if
		} //for
		if ($q != "")
			echo "incomplete_statement: ".$q."<br>";
		return true;
	}

	function applyUpdate()
	{
		$f = $this->fileVersion;
		$c = $this->currentVersion;

		if ($c < $f)
		{
			$msg = array();
			for ($i=($c+1); $i<=$f; $i++)
			{
				if ($this->applyUpdateNr($i) == false)
				{
					$msg[] = array(
						"msg" => "update_error: ".$this->error,
						"nr" => $i
					);
					$this->updateMsg = $msg;
					return false;
				}
				else
				{
					$msg[] = array(
						"msg" => "update_applied",
						"nr" => $i
					);
				}
			}

			$this->updateMsg = $msg;
		}
		else
		{
			$this->updateMsg = "no_changes";
		}
				
		return true;
	}

	/**
	 * apply an update
	 * @param int nr number what patch to apply
	 * @return bool
	 * @access private
	 */
	function applyUpdateNr($nr)
	{
		global $ilDB,$ilErr,$ilUser,$ilCtrlStructureReader,$ilModuleReader;

		//search for desired $nr
		reset($this->filecontent);

		//init
		$i = 0;

	    //go through filecontent
		while (!ereg("^<#".$nr.">", $this->filecontent[$i]) && $i<count($this->filecontent))
		{
			$i++;
		}

		//update not found
		if ($i == count($this->filecontent))
		{
			$this->error = "update_not_found";
			return false;
		}

		$i++;

		//update found, now extract this update to a new array
		$update = array();
		while ($i<count($this->filecontent) && !ereg("^<#".($nr+1).">", $this->filecontent[$i]))
		{
			$update[] = trim($this->filecontent[$i]);
			$i++;
		}

		//now you have the update, now process it
		$sql = array();
		$php = array();
		$mode = "sql";

		foreach ($update as $row)
		{
			if (ereg("<\?php", $row))
			{
				if (count($sql)>0)
				{
					if ($this->execQuery($this->db, implode("\n", $sql)) == false)
					{
						$this->error = "dump_error: ".$this->error;
						return false;
					}
					$sql = array();
				}
				$mode = "php";
			}
			elseif (ereg("\?>", $row))
			{
				if (count($php)>0)
				{
					eval(implode("\n", $php));
					$php = array();
				}
				$mode = "sql";

			}
			else
			{
				if ($mode == "sql")
				{
					$sql[] = $row;
				}

				if ($mode == "php")
				{
					$php[] = $row;
				}
			} //else
		} //foreach

		if ($mode == "sql" && count($sql) > 0)
		{
			if ($this->execQuery($this->db, implode("\n", $sql)) == false)
			{
				$this->error = "dump_error: ".$this->error;
				return false;
			}
		}
	
		//increase db_Version number
		$this->setCurrentVersion($nr);
		//$this->currentVersion = $ilias->getSetting("db_version");
		
		return true;
		
	}
	
	function getDBVersionStatus()
	{
		if ($this->fileVersion > $this->currentVersion)
			return false;
		else
			return true;
	}
	
	function getTables()
	{
		$a = array();
	
		$query = "SHOW TABLES";	
		$res = $this->db->query($query);
		while ($row = $res->fetchRow())
		{
			$status = $this->getTableStatus($row[0]);
			$a[] = array(
				"name" => $status["Table"],
				"table" => $row[0],
				"status" => $status["Msg_text"]
			);
		}
		return $a;
	}
	
	function getTableStatus($table)
	{
		$a = array();
	
		$query = "ANALYZE TABLE ".$table;	
		$res = $this->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row;
	}
	
	function optimizeTables($tables)
	{
		$msg = array();
		foreach ($_POST["tables"] as $key => $value)
		{
			$query = "OPTIMIZE TABLE ".$key;	
			$res = $this->db->query($query);
			$msg[] = "table $key: ok";
		}	
		return $msg;
	}

} // END class.DBUdate
?>
