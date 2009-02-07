<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once "./classes/class.ilObject.php";

/**
 * Class ilObjLanguage
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @extends ilObject
 */
class ilObjLanguage extends ilObject
{
	/**
	 * separator of module, comment separator, identifier & values
	 * in language files
	 *
	 * @var		string
	 * @access	private
	 */
	var $separator;
	var $comment_separator;
	var $lang_default;
	var $lang_user;
	var $lang_path;

	var $key;
	var $status;


	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function ilObjLanguage($a_id = 0, $a_call_by_reference = false)
	{
		global $lng;

		$this->type = "lng";
		$this->ilObject($a_id,$a_call_by_reference);

		$this->type = "lng";
		$this->key = $this->title;
		$this->status = $this->desc;
		$this->lang_default = $lng->lang_default;
		$this->lang_user = $lng->lang_user;
		$this->lang_path = $lng->lang_path;
		$this->cust_lang_path = $lng->cust_lang_path;
		$this->separator = $lng->separator;
		$this->comment_separator = $lng->comment_separator;
	}

	/**
	 * get language key
	 *
	 * @return	string		language key
	 */
	function getKey()
	{
		return $this->key;
	}

	/**
	 * get language status
	 *
	 * @return	string		language status
	 */
	function getStatus()
	{
		return $this->status;
	}

	/**
	 * check if language is system language
	 */
	function isSystemLanguage()
	{
		if ($this->key == $this->lang_default)
			return true;
		else
			return false;
	}

	/**
	 * check if language is system language
	 */
	function isUserLanguage()
	{
		if ($this->key == $this->lang_user)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Check language object status, and return true if language is installed.
	 * 
	 * @return  boolean     true if installed
	 */
	function isInstalled()
	{
		if (substr($this->getStatus(), 0, 9) == "installed")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check language object status, and return true if a local language file 
	 * is installed.
	 * 
	 * @return  boolean     true if local language is installed
	 */
	function isLocal()
	{
		if (substr($this->getStatus(), 10) == "local")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * install current language
	 *
	 * @return	string	installed language key
	 * @param   string  $scope  empty (global) or "local"
	 */
	function install($scope = '')
	{
		if (!empty($scope))
		{
			if ($scope == 'global')
			{
				$scope = ''; 
			}
			else
			{
				$scopeExtension = '.' . $scope;
			}
		}

		if (($this->isInstalled() == false) || 
				($this->isInstalled() == true && $this->isLocal() == false && !empty($scope)))
		{
			if ($this->check($scope))
			{
				// lang-file is ok. Flush data in db and...
				if (empty($scope))
				{
					$this->flush('keep_local');
				}

				// ...re-insert data from lang-file
				$this->insert($scope);

				// update information in db-table about available/installed languages
				if (empty($scope))
				{
					$newDesc = 'installed';
				}
				else if ($scope == 'local')
				{
					$newDesc = 'installed_local';
				}
				$this->setDescription($newDesc);
				$this->update();
				$this->optimizeData();
				return $this->getKey();
			}
		}
		return "";
	}


	/**
	 * uninstall current language
	 *
	 * @return	string	uninstalled language key
	 */
	function uninstall()
	{
		if ((substr($this->status, 0, 9) == "installed") && ($this->key != $this->lang_default) && ($this->key != $this->lang_user))
		{
			$this->flush('all');
			$this->setTitle($this->key);
			$this->setDescription("not_installed");
			$this->update();
			$this->resetUserLanguage($this->key);

			return $this->key;
		}
		return "";
	}


	/**
	 * remove language data from database
	 * @param   string     "all" or "keep_local"
	 */
	function flush($a_mode = 'all')
	{
		global $ilDB;
		
		$query = "DELETE FROM lng_data WHERE lang_key=".
			$ilDB->quote($this->key);
			
		if ($a_mode == 'keep_local')
		{
			$query .= " AND local_change='0000-00-00 00:00:00'";
		}
		$ilDB->query($query);

		if ($a_mode == 'all')
		{
			$st = $ilDB->prepareManip("DELETE FROM lng_modules WHERE lang_key = ?",
				array("text"));
			$ilDB->execute($st, array($this->key));
		}
	}


	/**
	* get locally changed language entries
	* @param    string  	minimum change date "yyyy-mm-dd hh:mm:ss"
	* @param    string  	maximum change date "yyyy-mm-dd hh:mm:ss"
	* @return   array       [module][identifier] => value
	*/
	function getLocalChanges($a_min_date = "", $a_max_date = "")
	{
		global $ilDB;
		
		$query = "SELECT * FROM lng_data WHERE"
				." lang_key =".$ilDB->quote($this->key);
		if ($a_min_date <> "")
		{
			$query .= " and local_change >= ".$ilDB->quote($a_min_date);
		}
		if ($a_max_date <> "")
		{
			$query .= " and local_change <= ".$ilDB->quote($a_max_date);
		}
		$result = $ilDB->query($query);

		$changes = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$changes[$row["module"]][$row["identifier"]] = $row["value"];
		}
		return $changes;
	}


	/**
	 * insert language data from file into database
	 * 
	 * @param   string  $scope  empty (global) or "local"
	 */
	function insert($scope = '')
	{
		global $ilDB;
		
		if (!empty($scope))
		{
			if ($scope == 'global')
			{
				$scope = ''; 
			}
			else
			{
				$scopeExtension = '.' . $scope;
			}
		}
		
		$path = $this->lang_path;
		if ($scope == "local")
		{
			$path = $this->cust_lang_path;
		}

		$tmpPath = getcwd();
		chdir($path);

		$lang_file = "ilias_" . $this->key . ".lang" . $scopeExtension;

		if ($lang_file)
		{
			// initialize the array for updating lng_modules below
			$lang_array = array();
			$lang_array["common"] = array();

			// remove header first
			if ($content = $this->cut_header(file($lang_file)))
			{
				// get the local changes from the database
				if (empty($scope))
				{
					$local_changes = $this->getLocalChanges();
				}
				else if ($scope == 'local')
				{
					$change_date = date("Y-m-d H:i:s",time());
					$min_date = date("Y-m-d H:i:s", filemtime($lang_file));
					$local_changes = $this->getLocalChanges($min_date);
				}
				
				foreach ($content as $key => $val)
				{
					$separated = explode($this->separator,trim($val));
					
					//get position of the comment_separator
					$pos = strpos($separated[2], $this->comment_separator);
				
					if ($pos !== false)
					{ 
						//cut comment of
						$separated[2] = substr($separated[2] , 0 , $pos);
					}

					// check if the value has a local change
					$local_value = $local_changes[$separated[0]][$separated[1]];

					if (empty($scope))
					{
						if ($local_value != "" and $local_value != $separated[2])
						{
							// keep the locally changed value
							$lang_array[$separated[0]][$separated[1]] = $local_value;
						}
						else
						{
							if ($double_checker[$separated[0]][$separated[1]][$this->key])
							{
								$this->ilias->raiseError("Duplicate Language Entry: ".
									$separated[0]."-".$separated[1]."-".$this->key,
									$this->ilias->error_obj->MESSAGE);
							}
							
							$double_checker[$separated[0]][$separated[1]][$this->key] = true;
							
							// insert a new value if no local value exists
							// reset local_change if the values are equal
							$query = "REPLACE INTO lng_data " .
									"(module, identifier, lang_key, value, local_change) " .
									"VALUES " .
									"(".$ilDB->quote($separated[0]).",".
									$ilDB->quote($separated[1]).",".
									$ilDB->quote($this->key).",".
									$ilDB->quote($separated[2]).",".
									$ilDB->quote("0000-00-00 00:00:00").")";
							$ilDB->query($query);
							$lang_array[$separated[0]][$separated[1]] = $separated[2];
						}
					}
					else if ($scope == 'local')
					{
						if ($local_value != "")
						{
							// keep a locally changed value that is newer than the local file
							$lang_array[$separated[0]][$separated[1]] = $local_value;
						}
						else
						{
							// UPDATE because the global values have already been INSERTed
							$query = "UPDATE lng_data SET ".
									 "value = ".$ilDB->quote($separated[2]).", " .
									 "local_change = ".$ilDB->quote($change_date)." " .
									 "WHERE module = ".$ilDB->quote($separated[0])." " .
									 "AND identifier = ".$ilDB->quote($separated[1])." " .
									 "AND lang_key = ".$ilDB->quote($this->key);
							$ilDB->query($query);
							$lang_array[$separated[0]][$separated[1]] = $separated[2];
						}
					}
				}

				if (empty($scope))
				{
					$query = "UPDATE object_data SET " .
							"description = 'installed', " .
							"last_update = now() " .
							"WHERE title = ".$ilDB->quote($this->key)." " .
							"AND type = 'lng'";
                    $ilDB->query($query);
				}
				else if ($scope == 'local')
				{
					$query = "UPDATE object_data SET " .
							"description = 'installed_local', " .
							"last_update = now() " .
							"WHERE title = ".$ilDB->quote($this->key)." " .
							"AND type = 'lng'";
                    $ilDB->query($query);
				}
			}
			
			foreach($lang_array as $module => $lang_arr)
			{
				if ($scope == "local")
				{
					$st = $ilDB->prepare("SELECT * FROM lng_modules " .
						"WHERE lang_key = ? AND module = ?",
						array("text", "text"));
					$set = $ilDB->execute($st, array($this->key, $module));
					$row = $ilDB->fetchAssoc($set);
					$arr2 = unserialize($row["lang_array"]);
					if (is_array($arr2))
					{
						$lang_arr = array_merge($arr2, $lang_arr);
					}
				}
				ilObjLanguage::replaceLangModule($this->key, $module, $lang_arr);
			}
		}

		chdir($tmpPath);
	}

	/**
	* Replace language module array
	*/
	static final function replaceLangModule($a_key, $a_module, $a_array)
	{
		global $ilDB;
		
		$st = $ilDB->prepareManip("DELETE FROM lng_modules WHERE lang_key = ? AND module = ?",
			array("text", "text"));
		$ilDB->execute($st, array($a_key, $a_module));
		$st = $ilDB->prepareManip("INSERT INTO lng_modules (lang_key, module, lang_array) VALUES ".
			"(?,?,?)", array("text", "text", "clob"));
		$ilDB->execute($st, array($a_key, $a_module, serialize($a_array)));
	}

	/**
	 * search ILIAS for users which have selected '$lang_key' as their prefered language and
	 * reset them to default language (english). A message is sent to all affected users
	 *
	 * @param	string		$lang_key	international language key (2 digits)
	 */
	function resetUserLanguage($lang_key)
	{
		global $ilDB;
		
		$query = "UPDATE usr_pref SET " .
				"value = ".$ilDB->quote($this->lang_default)." " .
				"WHERE keyword = 'language' " .
				"AND value = ".$ilDB->quote($lang_key);
		$this->ilias->db->query($query);
	}

	/**
	 * remove lang-file haeder information from '$content'
	 * This function seeks for a special keyword where the language information starts.
	 * if found it returns the plain language information, otherwise returns false
	 *
	 * @param	string	$content	expecting an ILIAS lang-file
	 * @return	string	$content	content without header info OR false if no valid header was found
	 */
	function cut_header($content)
	{
		foreach ($content as $key => $val)
		{
			if (trim($val) == "<!-- language file start -->")
			{
				return array_slice($content,$key +1);
			}
	 	}

	 	return false;
	}

	/**
	 * optimizes the db-table langdata
	 *
	 * @return	boolean	true on success
	 */
	function optimizeData()
	{
		// optimize
		$query = "OPTIMIZE TABLE lng_data";
		$this->ilias->db->query($query);

		return true;
	}

	/**
	 * Validate the logical structure of a lang file.
	 * This function checks if a lang file exists, the file has a 
	 * header, and each lang-entry consists of exactly three elements
	 * (module, identifier, value).
	 *
	 * @return	string	system message
	 * @param   string  $scope  empty (global) or "local"
	 */
	function check($scope = '')
	{
		if (!empty($scope))
		{
			if ($scope == 'global')
			{
				$scope = ''; 
			}
			else
			{
				$scopeExtension = '.' . $scope;
			}
		}

		$path = $this->lang_path;
		if ($scope == "local")
		{
			$path = $this->cust_lang_path;
		}
		
		$tmpPath = getcwd();
		
		// dir check
		if (!is_dir($path))
		{
			$this->ilias->raiseError("Directory not found: ".$path, $this->ilias->error_obj->MESSAGE);
		}

		chdir($path);

		// compute lang-file name format
		$lang_file = "ilias_" . $this->key . ".lang" . $scopeExtension;

		// file check
		if (!is_file($lang_file))
		{
			$this->ilias->raiseError("File not found: ".$lang_file,$this->ilias->error_obj->MESSAGE);
		}

		// header check
		if (!$content = $this->cut_header(file($lang_file)))
		{
			$this->ilias->raiseError("Wrong Header in ".$lang_file,$this->ilias->error_obj->MESSAGE);
		}

		// check (counting) elements of each lang-entry
		$line = 0;
		foreach ($content as $key => $val)
		{
			$separated = explode($this->separator, trim($val));
			$num = count($separated);
			++$n;
			if ($num != 3)
			{
				$line = $n + 36;
				$this->ilias->raiseError("Wrong parameter count in ".$lang_file." in line $line (Value: $val)! Please check your language file!",$this->ilias->error_obj->MESSAGE);
			}
		}

		chdir($tmpPath);

		// no error occured
		return true;
	}
} // END class.LanguageObject
?>
