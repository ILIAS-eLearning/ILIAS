<?php
/**
* ILIAS base class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @extends PEAR
* @package ilias-core
* @todo review the concept how the object type definition is loaded. We need a concept to
* edit the definitions via webfrontend in the admin console.
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
			exit;
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
		
		//********XML***********************************************
		//objects-typedefinition in XML
		$data = file("objects.xml");
		$data = implode($data,"");
		$this->objDef = databay_XML2OBJ($data);
		unset($data);
		//debug for xml-objectdefinitions
		//echo $this->objDef->ChildNodes[0]->countElements("object");
		//echo nl2br(htmlspecialchars(databay_OBJ2XML($this->objDef)));		
	
	}

	function getObjDefinition($a_objname)
	{
		for ($i=0; $i<count($this->objDef->ChildNodes[0]->ChildNodes); $i++)
		{
			$obj = $this->objDef->ChildNodes[0]->ChildNodes[$i];
			if ($obj->getAttr("NAME") == $a_objname) {
				break;
			}
		}
		$data["name"] = $obj->getAttr("NAME");
		$data["subobjects"] = array();
		$data["properties"] = array();
		$data["actions"] = array();
		foreach ($obj->ChildNodes as $row)
		{
			if ($row->Name == "SUBOBJ")
			{
			    $data["subobjects"][] = array(
					"attrs" => $row->getAttrs(),
					"name" => $row->Data
				);
			}
			if ($row->Name == "PROPERTY")
			{
			    $data["properties"][] = array( 
						"attrs" => $row->getAttrs(),
						"name" => $row->Data
				);
			}
			if ($row->Name == "ACTION")
			{
			    $data["actions"][] = $row->Data;
			}
		}
		return $data;
	}
	
	/**
	* Destructor
	* @access	private
	* @return	boolean
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
	* read one value from settingstable
	* @access	public
	* @param	string	keyword
	* @return	string	value
	*/
	function getSetting($a_keyword)
	{
		$query = "SELECT value FROM settings WHERE keyword='".$a_keyword."'";
		$res = $this->db->query($query);

		if ($res->numRows() > 0)
		{
			$row = $res->fetchRow();
			return $row[0];
		}
		else
		{
			return false;
		}
	}

	/**
	* read all values from settingstable
	* @access	public
	* @return	array	value
	*/
	function getAllSettings()
	{
		$query = "SELECT * FROM settings";
		$res = $this->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$rueckgabe[$row[keyword]] = $row[value];
		}
		
		return $rueckgabe;
	}

	/**
	* write one value to settingstable
	* @access	public
	* @param	string		keyword
	* @param	string		value
	* @return	integer		value
	*/
	function setSetting($key, $value)
	{
		$sql = "DELETE FROM settings WHERE keyword='".$key."'";
		$r = $this->db->query($sql);

		$sql = "INSERT INTO settings (keyword, value) VALUES ('".$key."','".$value."')";
		$r = $this->db->query($sql);
		return true;
	}

	/**
	* skin system: get all available skins from template directory
	* @access	public
	* @return	array
	* @author	Peter Gabriel <pgabriel@databay.de>
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
	
	/**
	* skin system: get all available styles from current templates
	* @access	public
	* @param	string
	* @return	array
	* @author	Peter Gabriel <pgabriel@databay.de>
	*/
	function getStyles($skin)
	{
//		if (is_array($this->styles))
//		{
//			return true;
//		}
		$styles = array();

		//open directory for reading and search for subdirectories
		$tplpath = $this->ini->readVariable("server", "tpl_path")."/".$skin;
		if ($dp = @opendir($tplpath))
		{

			while (($file = readdir($dp)) != false)
			{
				//is the file a stylesheet?
				if (strpos($file, ".css") > 0)
				{
					$styles[] = array(
						"name" => substr($file,0,-4)
					);
				}
			} // while
		}
		else
		{
			return false;
		}
		$this->styles = $styles;
		return true;
	}
	
	/**
	* get first available stylesheet from skindirectory
	* @param string
	* @access public
	*/
	function getFirstStyle($skin)
	{
		if (!is_array($this->styles))
		{
			$this->getStyles($skin);
		}
		return $this->styles[0]["name"];
	}
	
	/**
	* check if a templatename exists on the server
	* @param string
	*/
	function checkTemplate($name)
	{
		return file_exists($this->tplPath."/".$name);
	}
} // END class.ILIAS
?>