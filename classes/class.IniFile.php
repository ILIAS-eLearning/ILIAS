<?php

/**
 * INIFile Parser
 *
 * Description:
 *
 * A Simpe Ini File Implementation to keep settings 
 * in a simple file instead of in a DB
 *
 * Usage Examples: 
 * $ini = new IniFile("./ini.ini");
 * Read entire group in an associative array
 * $grp = $ini->read_group("MAIN");
 * //prints the variables in the group
 * if ($grp)
 * for(reset($grp); $key=key($grp); next($grp))
 * {
 * echo "GROUP ".$key."=".$grp[$key]."<br>";
 * }
 * //set a variable to a value
 * $ini->setVariable("NEW","USER","JOHN");
 * //Save the file
 * $ini->save_data();
 * 
 * @package ilias
 * @author Mircho Mirev <?@?.com>
 * @author Peter Gabriel <peter@gabriel-online.net>
 * @version $Id$
 */
class IniFile
{
    /**
	 * name of file
	 * @var string
	 */	
    var $INI_FILE_NAME = "";
    
    /**
	 * error var
	 * @var string
	 */
    var $ERROR = "";
    
    /**
	 * sections in ini-file
	 * @var array
	 */
    var $GROUPS = array();
    
    /**
	 * actual section
	 * @var string
	 */
    var $CURRENT_GROUP = "";
	
    /**
	 * constructor
	 * @access public
	 */
    function INIFile($iniFileName)
	{
		//check if a filename is given
    	if(empty($iniFileName))
		{
			$this->error("INIFile::constructor: no ini file given");
			return false;
		}
		
		//check if file exists
		if(!file_exists($iniFileName))
		{
			$this->error("INIFile::constructor: This file does not exist!");
			return false;
		}
		else
		{
			$this->INI_FILE_NAME = $iniFileName;

			//parse the file
			if ($this->parse()==false)
				return false;
		}
		
		return true;
	}
    
    /**
	 * load and parse an inifile
	 * @access private
	 */
    function parse() {
//        $inidata = parse_ini_file($this->INI_FILE_NAME);
    	$fp = @fopen($this->INI_FILE_NAME, "r+");
		if ($fp == false)
		{
			$this->error("file_not_accessible");
			return false;
		}
    	$contents = fread($fp, filesize($this->INI_FILE_NAME));
    	$ini_data = split("\n",$contents);
		
    	while(list($key, $data) = each($ini_data))
    	{
			if (substr($data,0,1) != ";")
			{
				$this->parse_data($data);
			}
    	}
    	fclose($fp);
		return true;
    }
	
    /**
	 * parse data
	 * @access private
	 */
    function parse_data($data)
	{
		if(ereg("\[([[:alnum:]]+)\]",$data,$out))
		{
    		$this->CURRENT_GROUP= trim($out[1]);
    	}
    	elseif (!empty($data))
    	{
    		$split_data = split("=", $data);
    		$this->GROUPS[$this->CURRENT_GROUP][trim($split_data[0])]=trim($split_data[1]);
    	}
    }
    
    /**
	 * save ini-file-data
	 * @access private
	 */
    function save()
    {
    	$fp = fopen($this->INI_FILE_NAME,"w");
    	
    	if(empty($fp))
    	{
    		$this->Error("Cannot create file $this->INI_FILE_NAME");
    		return false;
    	}
    	
    	$groups = $this->readGroups();
    	$group_cnt = count($groups);
    	
    	for($i=0; $i<$group_cnt; $i++)
    	{
    		$group_name = $groups[$i];
			//prevent empty line at beginning of ini-file
			if ($i==0)
				$res = sprintf("[%s]\n",$group_name);
			else
				$res = sprintf("\n[%s]\n",$group_name);
    		fwrite($fp, $res);
    		$group = $this->readGroup($group_name);
    		for(reset($group); $key=key($group);next($group))
    		{
    			$res = sprintf("%s = %s\n",$key,$group[$key]);
    			fwrite($fp,$res);
    		}
    	}
    	
    	fclose($fp);
    }
    
    /**
	 * returns number of groups	
	 */
	function getGroupCount()
	{
		return count($this->GROUPS);
	}
	
    /**
	 * returns an array with the names of all the groups
	 */
    function readGroups()
    {
    	$groups = array();
    	for(reset($this->GROUPS);$key=key($this->GROUPS);next($this->GROUPS))
    		$groups[]=$key;
    	return $groups;
    }
	
    /**
	 * checks if a group exists
	 */
    function groupExists($group_name)
    {
    	$group = $this->GROUPS[$group_name];
    	if (empty($group)) return false;
    	else return true;
    }
    
    /**
	 * returns an associative array of the variables in one group	
	 */
    function readGroup($group)
    {
    	$group_array = $this->GROUPS[$group];
    	if(!empty($group_array)) 
    		return $group_array;
    	else 
    	{
    		$this->Error("Group $group does not exist");
    		return false;
    	}
    }
	
    /**
	 * adds a new group
	 */
    function addGroup($group_name)
    {
    	$new_group = $this->GROUPS[$group_name];
    	if(empty($new_group))
    	{
    		$this->GROUPS[$group_name] = array();
    	}
    	else $this->Error("Group $group_name exists");
    }
    
    /**
	 * reads a single variable from a group
	 */
    function readVariable($group, $var_name)
    {
    	$var_value = trim($this->GROUPS[$group][$var_name]);
    	if(!empty($var_value))
    		return $var_value;
    	else
    	{
    		$this->Error("$var_name does not exist in $group");
    		return false;
    	}
    }
	
    /**
	 * sets a variable in a group
	 */
    function setVariable($group, $var_name, $var_value)
    {
    	if ($this->groupExists($group))
    		$this->GROUPS[$group][$var_name]=$var_value;
    }	
    
    /**
	 * error handling
	 */
    function error($errmsg)
    {
    	$this->ERROR = $errmsg;
    	return true;
    }
	
} //end class

?>