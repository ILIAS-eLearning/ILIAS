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
			$this->error("no_file_given");
			return false;
		}
		
		$this->INI_FILE_NAME = $iniFileName;

		return true;
    }
    
    function read()
    {
        //check if file exists
		if(!file_exists($this->INI_FILE_NAME))
		{
			$this->error("file_does_not_exist");
			return false;
		}
		else
		{
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
    function parse()
	{
		//use php4 function parse_ini_file
		$this->GROUPS = parse_ini_file($this->INI_FILE_NAME, true);
		
		//check if groups are filled
		if ($this->GROUPS == false)
		{
			$this->error("file_not_accessible");
			return false;
		}

		//set current group
		$temp = array_keys($this->GROUPS);
		$this->CURRENT_GROUP = $temp[count($temp)-1];

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

	function setContent($data)
	{
		$this->GROUPS = $data;
		return true;
	}
    
    /**
	 * save ini-file-data to filesystem
	 * @access private
	 */
    function write()
    {
    	$fp = @fopen($this->INI_FILE_NAME,"w");
    	
    	if(empty($fp))
    	{
    		$this->error("Cannot create file $this->INI_FILE_NAME");
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
    		$result = fwrite($fp, $res);
			$group = $this->readGroup($group_name);
    		for(reset($group); $key=key($group);next($group))
    		{
    			$res = sprintf("%s = %s\n",$key,$group[$key]);
    			$result = fwrite($fp,$res);
    		}
    	}
    	
    	fclose($fp);

		return true;
    }
    
	
	/**
	* returns the content of IniFile
	* @access public
	* @param void
	* @return string content
	*/
	function show()
	{
    	$groups = $this->readGroups();
    	$group_cnt = count($groups);
    	
		//clear content
		$content = "";
		
		// go through all groups
    	for($i=0; $i<$group_cnt; $i++)
    	{
    		$group_name = $groups[$i];
			//prevent empty line at beginning of ini-file
			if ($i==0)
				$content = sprintf("[%s]\n",$group_name);
			else
				$content .= sprintf("\n[%s]\n",$group_name);

			$group = $this->readGroup($group_name);
    		
			//go through group an display all variables
			for(reset($group); $key=key($group);next($group))
    		{
    			$content .= sprintf("%s = %s\n",$key,$group[$key]);
			}
    	}
		return $content;
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
    	if (!is_array($group))
		{
			 return false;
		}
    	else
		{
			return true;
		}
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