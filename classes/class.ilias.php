<?php
/**
* ILIAS base class
*
* class to setup ILIAS
*
* @author Sascha Hofmann <shofmann@databay.de>
* @package ILIAS
* @access public
* @version $Id$
*/

class ILIAS extends PEAR
{
    /**
    *  database connector
    *  @var string
    *  @access public
	*/
    var $dsn = "";

    /**
    *  database handle
    *  @var object
    *  @access private
    */
    var $db = "";
	
    /**
    *  template path
    *  @var string
    *  @access private
    */
    var $tplPath = "";

    /**
    *  user account
    *  @var object
    *  @access public
	*/
    var $account = "";

    /**
    *  auth parameters
    *  @var array
    *  @access private
    */
    var $auth_params = array();
		
    /**
    *  auth handler
    *  @var object
    *  @access public
    */
    var $auth = "";
	
    /**
    *  operation list
    *  @var array
    *  @access private
    */
    var $operations = "";
	
    /**
    *  type definition
    *  @var array
    *  @access private
    */	
	var $typedefinition = array(
								"grp"  => "'grp','frm','le'",
								"cat"  => "'cat','frm','le','grp'",
								"frm"  => "",
								"le"   => "",
								"crs"  => "'le','frm','grp'",
								"adm"  => "'usrf','rolf','objf'",
								"usrf" => "'user'",
								"rolf" => "'role'",
								"objf" => "'type'"
							);
    /**
    *  system settings
    *  @var array
    *  @access public
    */
	var $ini = array();

	/**
    * constructor
	* 
	* setup ILIAS global object
	* 
	* @param void
    * @return boolean
    */
    function ILIAS()
    {
		// get settings from ini file
		$this->ini = parse_ini_file("ilias.ini",true);
		
        // build dsn of database connection and connect
		$this->dsn = $this->ini["db"]["type"].
					 "://".$this->ini["db"]["user"].
					 ":".$this->ini["db"]["pass"].
					 "@".$this->ini["db"]["host"].
					 "/".$this->ini["db"]["name"];
		
		$this->db = DB::connect($this->dsn,true);
            
		if (DB::isError($this->db)) {
			die($this->db->getMessage());
		}
			
		// build option string for PEAR::Auth
		$this->auth_params = array(
									'dsn'		  => $this->dsn,
									'table'       => $this->ini["auth"]["table"],
									'usernamecol' => $this->ini["auth"]["usercol"],
									'passwordcol' => $this->ini["auth"]["passcol"]
									);
		// set tplPath
		$this->tplPath = TUtil::setPathStr($this->ini["server"]["tpl_path"]);
		
		// We use MySQL as storage container
		$this->auth = new Auth("DB",$this->auth_params,"",false);
		
		return true;
	}

    /**
    * destructor
	* 
	* @param void
    * @return boolean
    */
    function _ILIAS()
	{
		if ($this->ini["db"]["type"] != "")
		{
			$this->db->disconnect();
        }
		
		return true;
    }
}
?>