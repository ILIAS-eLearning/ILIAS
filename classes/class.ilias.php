<?php
/**
* ILIAS base class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @version $Id$
* @author Sascha Hofmann <shofmann@databay.de>
*
* @extends PEAR
* @package ilias-core
* @access public
*/
class ILIAS extends PEAR
{
	/**
	* ini file
	* @var string
	*/
 	var $INI_FILE = "./ilias.ini.php";

	/**
	* database connector
	* @var string
	* @access public
	*/
	var $dsn = "";

	/**
	* database handle
	* @var object database
	* @access private
	*/
	var $db;
	
	/**
	* template path
	* @var string
	* @access private
	*/
	var $tplPath = "";

	/**
	* user account
	* @var object user
	* @access public
	*/
	var $account;

	/**
	* auth parameters
	* @var array
	* @access private
	*/
	var $auth_params = array();
		
	/**
	* auth handler
	* @var object auth
	* @access public
	*/
	var $auth;
	
	/**
	* operation list
	* @var array
	* @access private
	*/
	var $operations;
	
	/**
	* type definition
	* @var array
	* @access private
	*/	
	var $typedefinition = array(
								"grp"  => "'frm','le','crs','file','rolf'",
								"cat"  => "'cat','frm','le','grp','crs','file','rolf'",
								"frm"  => "'rolf'",
								"le"   => "'rolf'",
								"crs"  => "'le','frm','grp','file','rolf'",
								"file" => "'rolf'",
								"adm"  => "'usrf','rolf','objf','lngf'",
								"usrf" => "'user'",
								"rolf" => "'role','rolt'",
								"objf" => "'type'",
								"lngf" => "'lang'"
							);
	/**
	* system settings
	* @var array
	* @access public
	*/
	var $ini = array();
	
	/**
	* Error Handling
	* @var object Error
	* @access public
	*/
	var $error_obj;

	/**
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function ILIAS()
	{
		$this->PEAR();

		// get settings from ini file
		$this->ini = new IniFile($this->INI_FILE);
		$this->ini->read();

		//check for error
		if ($this->ini->ERROR != "")
		{
			header("Location: ./setup.php?error=".$this->ini->ERROR);
		}
		
		// build dsn of database connection and connect
		$this->dsn = $this->ini->readVariable("db","type").
					 "://".$this->ini->readVariable("db", "user").
					 ":".$this->ini->readVariable("db", "pass").
					 "@".$this->ini->readVariable("db", "host").
					 "/".$this->ini->readVariable("db", "name");
		
		$this->db = new DBx($this->dsn);
			
		// build option string for PEAR::Auth
		$this->auth_params = array(
									'dsn'		  => $this->dsn,
									'table'       => $this->ini->readVariable("auth", "table"),
									'usernamecol' => $this->ini->readVariable("auth", "usercol"),
									'passwordcol' => $this->ini->readVariable("auth", "passcol")
									);
		// set tplPath
		$this->tplPath = TUtil::setPathStr($this->ini->readVariable("server", "tpl_path"));
		
		// We use MySQL as storage container
		$this->auth = new Auth("DB",$this->auth_params,"",false);

		// Error Handling
		$this->error_obj = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK,array($this->error_obj,'errorHandler'));
	}

	/**
	* destructor
	* @access	private
	* @return boolean
	*/
	function _ILIAS()
	{
		if ($this->ini->readVariable("db", "type") != "")
		{
			$this->db->disconnect();
		}
		
		return true;
		}

	/**
	* read string value from settingstable
	* @access	public
	* @param	string	keyword
	* @return	string	value
	*/
	function getSettingsStr($key)
	{
		$sql = "SELECT value_str FROM settings WHERE keyword='".$key."'";
		$r = $this->db->query($sql);
		if ($r->num_rows()>0)
		{
			$row = $r->fetchRow();
			return $row[0];
		}
		else
		{
			return false;
		}
	}

	/**
	* read integer value from settingstable
	* @access	public
	* @param	string		keyword
	* @return	integer		value
	*/
	function getSettingsInt($key)
	{
		$sql = "SELECT value_int FROM settings WHERE keyword='".$key."'";
		$r = $this->db->query($sql);

		if ($r->numRows()>0)
		{
			$row = $r->fetchRow();
			return $row[0];
		}
		else
		{
			return false;
		}
	}

	/**
	* write integer value to settingstable
	* @access	public
	* @param	string		keyword
	* @param	integer		value
	* @return	integer		value
	*/
	function setSettingsInt($key, $value)
	{
		$sql = "DELETE FROM settings WHERE keyword='".$key."'";
		$r = $this->db->query($sql);

		$sql = "INSERT INTO settings (keyword, value_int) VALUES ('".$key."','".$value."')";
		$r = $this->db->query($sql);
		return true;
	}
	
	/**
	* skin system: get all available skins from template directory
	* @access public
	* @return array
	* @version 0.1
	* @author Peter Gabriel <pgabriel@databay.de>
	*/
	function getSkins()
	{
		$skins = array();
		
		//open directory for reading and search for subdirectories
		$tplpath = $this->ini->readVariable("server", "tpl_path");
		if ($dp = @opendir($tplpath))
		{
			while (($file = readdir($dp)) != false)
			{
				//is the file a directory?
				if (is_dir($tplpath."/".$file) && $file != "." && $file != ".." && $file != "CVS")
				{
					$skins[] = array(
						"name" => $file
					);
				}
			} // while
		}
		else
		{
			return false;
		}
		$this->skins = $skins;
		return true;
	}
	
} // END class.ILIAS
?>