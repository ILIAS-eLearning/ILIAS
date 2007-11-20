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

require_once "./Services/Language/classes/class.ilObjLanguage.php";

/**
* Class ilObjLanguageExt
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilObjLanguageExt.php $
*
* @package ilias-core
*/
class ilObjLanguageExt extends ilObjLanguage
{
	
	/**
	* parameters and content defined in the language file
	*
	* @var		string
	* @access	private
	*/
	var $lang_file_param = array();
	var $lang_file_content = array();
	var $lang_file_comments = array();


	/**
	* Constructor
	*/
	function ilObjLanguageExt($a_id = 0, $a_call_by_reference = false)
	{
		$this->ilObjLanguage($a_id, $a_call_by_reference);
	}
	
	
	/**
	* Set the local status of the language
	*
	* @param   boolean       local status (true/false)
	*/
	function setLocal($a_local = true)
	{
		if ($this->isInstalled())
		{
			if ($a_local == true)
			{
				$this->setDescription("installed_local");
			}
			else
			{
                $this->setDescription("installed");
			}
			$this->update();
		}
	}

	
	/**
	* Get the full language description
	*
	* @return   string       description
	*/
	function getLongDescription()
	{
		return $this->lng->txt($this->desc);
	}
	
	/**
	* Get the language files path
	*
	* @return   string       path of language files folder
	*/
	function getLangPath()
	{
		return $this->lang_path;
	}

	/**
	* Get the customized language files path
	*
	* @return   string       path of customized language files folder
	*/
	function getCustLangPath()
	{
		return $this->cust_lang_path;
	}

	/**
	* Get all translation entries from the database
	*
	* @param    array       list of modules
	* @param    string       search pattern
	* @return   array       module#:#topic => value
	*/
	function getAllTranslations($a_modules = array(), $a_pattern = '')
	{
		return $this->_getTranslations($this->key, $a_modules, NULL, $a_pattern);
	}
	
	
	/**
	* Get only the changed translation entries from the database
	* which differ from the original language file.
	* The language file has to be read first.
	*
	* @param    array       list of modules
	* @param    string       search pattern
	* @return   array       module#:#topic => translation
	*/
	function getChangedTranslations($a_modules = array(), $a_pattern = '')
	{
		$translations = $this->_getTranslations($this->key, $a_modules, NULL, $a_pattern);
		$changes = array();
		
		foreach ($translations as $key => $value)
		{
			if ($this->lang_file_content[$key] != $value)
			{
				$changes[$key] = $value;
			}
		}
		
		return $changes;
	}


	/**
	* Get only the unchanged translation entries from the database
	* which are equal to the original language file.
	* The language file has to be read first.
	*
	* @param    array       list of modules
	* @param    array       search pattern
	* @return   array       module#:#topic => translation
	*/
	function getUnchangedTranslations($a_modules = array(), $a_pattern = '')
	{
		$translations = $this->_getTranslations($this->key, $a_modules, NULL, $a_pattern);
		$unchanged = array();

		foreach ($translations as $key => $value)
		{
			if ($this->lang_file_content[$key] == $value)
			{
				$unchanged[$key] = $value;
			}
		}

		return $unchanged;
	}


	/**
	* Get all translation entries from the database
	* for wich the original language file has a comment.
	* The language file has to be read first.
	*
	* @param    array       list of modules
	* @param    array       search pattern
	* @return   array       module#:#topic => translation
	*/
	function getCommentedTranslations($a_modules = array(), $a_pattern = '')
	{
		$translations = $this->_getTranslations($this->key, $a_modules, NULL, $a_pattern);
		$commented = array();

		foreach ($translations as $key => $value)
		{
			if ($this->lang_file_comments[$key] != "")
			{
				$commented[$key] = $value;
			}
		}

		return $commented;
	}


	/**
	* Get the whole language file content
	* The language file has to be read first.
	*
	* @return   array      key => value
	*/
	function getLangFileContent()
	{
		return $this->lang_file_content;
	}
	
	/**
	* Get all language file comments
	* The language file has to be read first.
	*
	* @return   array      key => comment
	*/
	function getLangFileComments()
	{
		return $this->lang_file_comments;
	}

	/**
	* Get a parameter value from the original language file header
	*
	* Parameters are @version, @author, ...
	* The language file has to be read first.
	*
	* @param    string  	parameter name
	* @return   string  	parameter value
	*/
	function getLangFileParam($a_param)
	{
		return $this->lang_file_param[$a_param];
	}
	
	
	/**
	* Get the value of a single language file entry
	*
	* This entry may differ from the current database value
	* due to a local language file or to online translations.
	* The language file has to be read first.
	*
	* @param    string      module name
	* @param    string      topic indentifier
	* @return   string      language file value
	*/
	function getLangFileValue($a_module, $a_topic)
	{
		return $this->lang_file_content[$a_module.$this->separator.$a_topic];
	}


	/**
	* Import a language file into the ilias database
	*
	* @param    string  	handling of existing values
	*						('keepall','keeknew','replace','delete')
	*/
	function importLanguageFile($a_file, $a_mode_existing = 'keepnew')
	{
		global $ilDB;
		
		switch($a_mode_existing)
		{
			// keep all existing entries
			case 'keepall':
				$to_keep = $this->getAllTranslations();
				break;

			// keep existing online changes
			case 'keepnew':
			    // read the original language file
			    $this->readLanguageFile();
				$to_keep = $this->getChangedTranslations();
				break;

 			// replace all existing definitions
			case 'replace':
			    $to_keep = array();
			    break;

           // delete all existing entries
			case 'delete':
				$query = "DELETE FROM lng_data WHERE lang_key='".$this->key."'";
				$ilDB->query($query);
				$query = "DELETE FROM lng_modules WHERE lang_key='".$this->key."'";
				$ilDB->query($query);
				$to_keep = array();
				break;
				
			default:
			    return;
		}
		
		// read the new language file and process content
		$this->readLanguageFile($a_file);
		$to_save = array();
		foreach ($this->lang_file_content as $key => $value)
		{
			if (!isset($to_keep[$key]))
			{
				$to_save[$key] = $value;
			}
		}
		$this->_saveTranslations($this->key, $to_save);
	}


	/**
	* Read the language file content and parameters into class arrays
	*/
	function readLanguageFile($a_lang_file = '')
	{
		$this->lang_file_param = array();
		$this->lang_file_content = array();
		$this->lang_file_comments = array();

		if ($a_lang_file == '')
		{
			$a_lang_file = $this->lang_path . "/ilias_" . $this->key . ".lang";
		}
		$content = file($a_lang_file);
		
		$in_header = true;
		foreach ($content as $dummy => $line)
		{
			if ($in_header)
			{
				// check header end
				if (trim($line) == "<!-- language file start -->")
				{
					$in_header = false;
					continue;
				}
				else
				{
					// get header params
					$pos_par = strpos($line, "* @");
					
					if ($pos_par !== false)
					{
				        $pos_par += 3;
						$pos_space = strpos($line, " ", $pos_par);
						$pos_tab = strpos($line, "\t", $pos_par);
						$pos_white = min($pos_space, $pos_tab);
					
						$param = substr($line, $pos_par, $pos_white-$pos_par);
						$value = trim(substr($line, $pos_white));
						
						$this->lang_file_param[$param] = $value;
					}
				}
			}
			else
			{
				// separate the lang file entry
				$separated = explode($this->separator, trim($line));
				
				if (count($separated) == 3)
				{
					$key = $separated[0].$this->separator.$separated[1];
					$value = $separated[2];

					// cut off comment
					$pos = strpos($value, $this->comment_separator);
					if ($pos !== false)
					{
						$this->lang_file_comments[$key]
							= substr($value , $pos + strlen($this->comment_separator));
							
						$value = substr($value , 0 , $pos);
					}
					$this->lang_file_content[$key] = $value;
				}
			}
		}
	}


	//
	// STATIC FUNCTIONS
	//

	/**
	* Get al modules of a language
	*
	* @access   static
	* @param    string      language key
	* @return   array       list of modules
	*/
	function _getModules($a_lang_key)
	{
		global $ilDB;
		
		$q = "SELECT module FROM lng_modules WHERE ".
			" lang_key = ".$ilDB->quote($a_lang_key).
			" order by module";
		$set = $ilDB->query($q);

		while ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$modules[] = $rec["module"];
		}
		return $modules;
	}

	/**
	* Get the translations of specified topics
	*
	* @access   static
	* @param    string      language key
	* @param    array       list of modules
	* @param    array       list of topics
	* @param    array       search pattern
	* @return   array       "module#:#topic" => translation
	*/
	function _getTranslations($a_lang_key, $a_modules = array(), $a_topics = array(), $a_pattern = '')
	{
		global $ilDB, $lng;

		if (is_array($a_modules))
		{
			for ($i = 0; $i < count($a_modules); $i++)
			{
				$a_modules[$i] = $ilDB->quote($a_modules[$i]);
			}
            $modules_list = implode(',', $a_modules);
		}
		if (is_array($a_topics))
		{
			for ($i = 0; $i < count($a_topics); $i++)
			{
				$a_topics[$i] = $ilDB->quote($a_topics[$i]);
			}
			$topics_list = implode(',', $a_topics);
		}

		$q = "SELECT * FROM lng_data WHERE".
			" lang_key =".$ilDB->quote($a_lang_key);
		if ($modules_list)
		{
			$q .= " AND module in (". $modules_list. ")";
		}
		if ($topics_list)
		{
			$q .= " AND identifier in (". $topics_list. ")";
		}
		if ($a_pattern)
		{
			$q .= " AND value like ". $ilDB->quote("%".$a_pattern."%");
		}
		$q .= " ORDER BY module, identifier";
		$set = $ilDB->query($q);

		$trans = array();
		while ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$trans[$rec["module"].$lng->separator.$rec["identifier"]] = $rec["value"];
		}
		return $trans;
	}

	/**
	* Save a set of translation in the database
	*
	* @access   static
	* @param    string      language key
	* @param    array       "module#:#topic" => translation
	*/
	function _saveTranslations($a_lang_key, $a_translations = array())
	{
		global $ilDB, $lng;
		
		if (!is_array($a_translations))
		{
			return;
		}
		$save_array = array();
		
		// save the single translations in lng_data
		foreach ($a_translations as $key => $value)
		{
			$keys = explode($lng->separator, $key);
			if (count($keys) == 2)
			{
				$module = $keys[0];
				$topic = $keys[1];
				$save_array[$module][$topic] = $value;
			
				$q = "REPLACE INTO lng_data(lang_key, module, identifier, value)"
				. " VALUES("
				. $ilDB->quote($a_lang_key). ","
				. $ilDB->quote($module). ","
				. $ilDB->quote($topic). ","
				. $ilDB->quote($value).")";
				$ilDB->query($q);
			}
		}

		// save the serialized module entries in lng_modules
		foreach ($save_array as $module => $entries)
		{
			$q = "SELECT * FROM lng_modules WHERE ".
				" lang_key = ".$ilDB->quote($a_lang_key).
				" AND module = ".$ilDB->quote($module);
			$set = $ilDB->query($q);
			$row = $set->fetchRow(DB_FETCHMODE_ASSOC);
			$arr = unserialize($row["lang_array"]);
			if (is_array($arr))
			{
				$entries = array_merge($arr, $entries);
			}
			$q = "REPLACE INTO lng_modules (lang_key, module, lang_array) VALUES ".
				 "(".$ilDB->quote($a_lang_key).", " .
				 " ".$ilDB->quote($module).", " .
				 " ".$ilDB->quote(serialize($entries)).") ";
			$ilDB->query($q);
		}
	}
} // END class.ilObjLanguageExt
?>
