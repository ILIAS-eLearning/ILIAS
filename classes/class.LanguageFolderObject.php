<?php
/**
* Class LanguageFolderObject
* contains all function to manage language support for ILIAS3
* install, uninstall, checkfiles ....
* 
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class LanguageFolderObject extends Object
{
	/**
	* indicator for the system language
	* this language must not be deleted
	* @var		string
	* @access	private
	*/
	var $lang_default;

	/**
	* language that is in use
	* by current user
	* this language must not be deleted
	* 
	* @var		string
	* @access	private
	*/
	var $lang_user;

	/**
	* path to language files
	* relative path is taken from ini file
	* and added to absolute path of ilias
	* 
	* @var		string
	* @access	private
	*/
	var $lang_path;

	/**
	* separator value between module,identivier & value 
	* @var		string
	* @access	private
	*/
	var $separator;
	
	/**
	* contians all informations about languages
	* 
	* @var		array
	* @access	public
	*/
	var $languages;
	
	/**
	* Constructor
	* @access	public
	*/
	function LanguageFolderObject()
	{
		$this->Object();

		// init language support
		global $lng;

		$this->lang_path = $lng->lang_path;
		$this->lang_default = $lng->lang_default;
		$this->lang_user = $lng->lang_user;
		$this->separator = $lng->separator;
		
		$this->getLanguages();
	}
	
	/**
	* gather all information about available languages
	* 
	* This function builds an array with the following structure:
	* $languages[lang_key][long][installed][update][info]
	* 
	* lang_key:		string		international language key (2 digits, i.e. de,en,dk...)
	* long:			string		full language name in the chosen user language
	* installed:	boolean		is the language installed (true) or not (false)?
	* update:		int			contains the timestamp of last db-modification
	* info:			string		optional information. valid is: 'notfound','new'
	*
	* @param	void
	*
	* @return	array	$languages	status information about available languages
	*/
	function getLanguages ()
	{
		global $lng;
		
		// set path to directory where lang-files reside
		$d = dir($this->lang_path);
		$tmpPath = getcwd();
		chdir ($this->lang_path);
	
		// get available lang-files
		while ($entry = $d->read())
		{
			if (is_file($entry) && (ereg ("(^ilias_.{2}\.lang)", $entry)))
			{
				$lang_key = substr($entry,6,2);
				$languages[$lang_key] = ""; // long names will be set in class Out
			}
		}
		
		// ensure that arrays are initiated when no lang file was found
		if (!array($languages))
		{
			$language = array();
			$tmp_array = array();
		}
		
		$tmp_array = array_keys($languages);
		$lang_keys[] = array();
		
		// now get languages from database
		if ($lang_db = getObjectList("lng"))
		{
			foreach ($lang_db as $lang)
			{
				// set values
				$lang_key = $lang["title"];
				$languages[$lang_key] = $lang;
				$lang_keys[] = $lang_key;
				
				// determine default language and language of current user
				if ($lang_key == $this->lang_user)
				{
					$languages[$lang_key]["status"] = "in_use";
				}
				
				if ($lang_key == $this->lang_default)
				{
					$languages[$lang_key]["status"] = "system_language";
				}
	
				// check if files are missing
				if ((count($tmp_array) > 0) && (!in_array($lang_key,$tmp_array)))
				{
					$languages[$lang_key]["info"] = "file_not_found";
				}
			}
		}		

		//compute new languages
		foreach ($languages as $lang_key => $lang_data)
		{
			if (!in_array($lang_key,$lang_keys))
			{
				$languages[$lang_key]["info"] = "new_language";
			}
		}
		
		// Insert languages with files new found into table language
		$languages = $this->addNewLanguages($languages);

		// Remove from array & db languages which are not installed and no lang-files
		$languages = $this->removeLanguages($languages);
		
		// setting language's full names
		foreach ($languages as $lang_key => $lang_data)
		{
			$languages[$lang_key]["name"] = $lng->txt("lang_".$lang_key);
		}
		
		// sort array
		require_once("../include/inc.sort.php");
		uasort($languages,"sortLanguagesbyName");

		chdir($tmpPath);

		$this->languages = $languages;
		return $this->languages;
	}

	/**
	* add new languages
	*
	* This functions checks in $languages for languages with the attribute 'new'
	* and insert these languages in db-table 'languages'
	* 
	* @param	array	$languages		expect $languages
	* 
	* @return	boolean					true: language array is not empty, otherwise false
	*/
	function addNewLanguages($a_languages)
	{
		if (count($a_languages) > 0)
		{
			foreach ($a_languages as $lang_key => $lang_data)
			{
				if ($lang_data["info"] == "new_language")
				{
					$obj_data["title"] = $lang_key;
					$obj_data["desc"] = "not_installed";
					
					$lng_id = createNewObject("lng", $obj_data);
					
					$a_languages[$lang_key] = getObject($lng_id);
					$a_languages[$lang_key]["info"] = "new_language";
				}
			}
		}

		return $a_languages;
	}
	
	/**
	* remove languages which are not installed AND has no lang-file
	*
	* This function removes only the entry in db-table 'languages' and
	* in the array $languages. Does not uninstall a language (see: function flushLanguage())
	*
	* @param	array	$languages
	*
	* @return	array	$languages	updated status information about available languages
	*/
	function removeLanguages($a_languages)
	{
		foreach ($a_languages as $lang_key => $lang_data)
		{
			if ($lang_data["desc"] == "not_installed" && $lang_data["info"] == "file_not_found")
			{
				// update languages array
				unset($a_languages[$lang_key]);
	
				// update object_data table
				$query = "DELETE FROM object_data ".
						 "WHERE type = 'lng' ".
						 "AND title = '".$lang_key."'";
				$this->ilias->db->query($query);
			}
		}
		
		return $a_languages;
	}

	/**
	* output menu with list of available and installed languages
	*
	* @param	void
	*
	* @return	array	data to view passed to Out class
	*/		
	function viewObject()
	{
		global $lng, $tpl;

		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "language", "status", "", "last_change");
		
		$languages = $this->languages;
		
		foreach ($languages as $lang_key => $lang_data)
		{
			$status = "";

			// set status info (in use oder systemlanguage)
			if ($lang_data["status"])
			{
				$status = "<span class=\"small\"> (".$lng->txt($lang_data["status"]).")</span>";
			}

			// set remark color
			switch ($lang_data["info"])
			{
				case "file_not_found":
					$remark = "<span class=\"smallred\"> ".$lng->txt($lang_data["info"])."</span>";
					break;
				case "new_language":
					$remark = "<span class=\"smallgreen\"> ".$lng->txt($lang_data["info"])."</span>";
					break;
				default:
					$remark = "";
					break;
			}
			
			//visible data part
			$this->objectList["data"][] = array(
					"type" => "<img src=\"".$tpl->tplPath."/images/icon_lng_b.gif\" border=\"0\">",
					"name" => $lang_data["name"].$status,
					"status" => $lng->txt($lang_data["desc"]),
					"remark" => $remark,
					"last_change" => $lang_data["last_update"]
					);

			//control information
			$this->objectList["ctrl"][] = array(
				"type" => "lng",
				"obj_id" => $lang_data["obj_id"],
				"parent" => $this->id,
				"parent_parent" => $this->parent,
			);

		} //for
		return $this->objectList;
		
	} //function
	

	function getSubObjects()	
	{
		return false;
	} //function

	/**
	* install a language
	*
	* This function copy all language entries from a lang-file to database
	*
	* @param	void
	*
	* @return	void
	*/	
	function installObject()
	{
		global $lng;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}
		
		foreach ($_POST["id"] as $obj_id)
		{
			$lang = getObject($obj_id);
			$lang_key = $lang["title"];
			$lang_status = $lang["desc"];
			
			if ($lang_status != "installed")
			{
				if ($this->checkLanguage($lang_key))
				{
					// lang-file is ok. Flush data in db and...
					$this->flushLanguage($lang_key);
			
					// ...re-insert data from lang-file
					$this->insertLanguage($lang_key);
	
					// update information in db-table about available/installed languages
					$lang["desc"] = "installed";
					updateObject($obj_id,"lng",$lang);
			
					$this->optimizeLangdata();
					
					$lang_installed[] = $lang_key;
				}
			}
		}
		
		if (isset($lang_installed))
		{
			if (count($lang_installed) == 1)
			{
				return $lng->txt("lang_".$lang_installed[0])." have been installed.";	
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $lng->txt("lang_".$lang_key); 
				}

				return implode(", ",$langnames)." have been installed.";			
			}
		}
		
		return "Funny! Chosen language(s) are already installed.";

	}

	/**
	* uninstall a language
	*
	* This function removes all language data from database and updates the language information
	* in db-table 'languages'.
	*
	* @param	string	$lang_key	international language key (2 digits)
	*
	* @return	
	*/	
	function uninstallObject()
	{
		global $lng;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$lang = getObject($obj_id);
			$lang_key = $lang["title"];
			$lang_status = $lang["desc"];
			
			if ($lang_status == "installed")
			{
				$this->flushLanguage($lang_key);
				
				$lang["desc"] = "not_installed";
				updateObject($obj_id,"lng",$lang);

				$this->resetUserLanguage($lang_key);
				
				$lang_uninstalled[] = $lang_key;
			}
		}
		
		if (isset($lang_uninstalled))
		{
			if (count($lang_uninstalled) == 1)
			{
				return $lng->txt("lang_".$lang_uninstalled[0])." have been uninstalled.";	
			}
			else
			{
				foreach ($lang_uninstalled as $lang_key)
				{
					$langnames[] = $lng->txt("lang_".$lang_key); 
				}

				return implode(", ",$langnames)." have been uninstalled.";			
			}
		}
		
		return "Funny! Chosen language(s) are already uninstalled.";
	}

	/**
	* refresh all installed languages
	*
	* This function flushes all installed languages and re-reads them from their lang-files
	* 
	* @param	void
	*
	* @return	string	$info_text	status message about final event within the function
	*/
	// refreshes all installed languages
	function refreshObject()
	{
		$languages = getObjectList("lng");
		
		foreach ($languages as $lang)
		{
			$obj_id = $lang["obj_id"];
			$lang_key = $lang["title"];
			$lang_status = $lang["desc"];
			
			if ($lang_status == "installed")
			{
				if ($this->checkLanguage($lang_key))
				{
					$this->flushLanguage($lang_key);
					$this->insertLanguage($lang_key);
				
					updateObject($obj_id,"lng",$lang);

					$this->optimizeLangdata();
				}
			}
		}

		return "All installed languages have been updated!";
	}

	/**
	* set default language (the system language)
	*
	* @param	string		lang_key
	*
	* @return	void
	*/
	function setsyslangObject ()
	{
		global $lng;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError("Please choose only one language.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}
		
		$obj_id = $_POST["id"][0];
		
		$new_lang = getObject($obj_id);
		$new_lang_key = $new_lang["title"];
		$new_lang_status = $new_lang["desc"];
		
		if ($new_lang_key == $this->lang_default)
		{
			$this->ilias->raiseError($lng->txt("lang_".$new_lang_key)." is already the system language!<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		foreach ($this->languages as $lang_key => $lang_data)
		{
			if ($new_lang_key == $lang_key && $new_lang_status != "installed")
			{
				$this->ilias->raiseError($lng->txt("lang_".$new_lang_key)." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
			}		
		}
		
		$this->ilias->setSetting("language",$new_lang_key);
		// update ini-file
		$this->ilias->ini->setVariable("language","default",$new_lang_key);
		$this->ilias->ini->write();
		
		return "Systemlanguage changed to ".$lng->txt("lang_".$new_lang_key).".";
	}
	
	/**
	* set the user language
	* @access	public
	* @param	string
	*/
	function setuserlangObject ()
	{
		global $lng;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError("Please choose only one language. Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];
		
		$new_lang = getObject($obj_id);
		$new_lang_key = $new_lang["title"];
		$new_lang_status = $new_lang["desc"];
		
		if ($new_lang_key == $this->lang_user)
		{
			$this->ilias->raiseError($lng->txt("lang_".$new_lang_key)." is already your user language!<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		foreach ($this->languages as $lang_key => $lang_data)
		{
			if ($new_lang_key == $lang_key && $new_lang_status != "installed")
			{
				$this->ilias->raiseError($lng->txt("lang_".$new_lang_key)." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
			}		
		}
		
		$this->setUserLanguage($new_lang_key);
		
		return "Userlanguage changed to ".$lng->txt("lang_".$new_lang_key).".";
	}

	/**
	* set the user language
	* @access	public
	* @param	string
	*/
	function setUserLanguage($a_lang_key)
	{
		$query = "UPDATE usr_pref SET value = '".$a_lang_key."' ".
				 "WHERE usr_id = '".$_SESSION["AccountId"]."' ".
				 "AND keyword = 'language'";
		$this->ilias->db->query($query);
		
		$this->lang_user = $a_lang_key;
	}

	/**
	* validate the logical structure of a lang-file
	*
	* This function checks if a lang-file of a given lang_key exists,
	* the file has a header and each lang-entry consist of exact three elements
	* (module,identifier,value)
	*
	* @param	string		$lang_key	international language key (2 digits)
	*
	* @return	string		$info_text	message about results of check OR "1" if all checks successfully passed
	*/
	function checkLanguage ($a_lang_key)
	{
		$tmpPath = getcwd();
		chdir ($this->lang_path);
	
		// compute lang-file name format
		$lang_file = "ilias_".$a_lang_key.".lang";
	
		// file check
		if (!is_file($lang_file))
		{
			$this->ilias->raiseError("File not found: ".$lang_file,$this->ilias->error_obj->MESSAGE);		
		}
	
		// header check
		if (!$content = $this->cut_header(file($lang_file))) {
			$this->ilias->raiseError("Wrong Header in ".$lang_file,$this->ilias->error_obj->MESSAGE);
		}
	
		// check (counting) elements of each lang-entry
		foreach ($content as $key => $val)
		{
			$separated = explode ($this->separator,trim($val));
			$num = count($separated);
	
			if ($num != 3) {
				$this->ilias->raiseError("Wrong parameter count in ".$lang_file."! Please check your language file!",$this->ilias->error_obj->MESSAGE);
			}
		}

		chdir($tmpPath);

		// no error occured
		return true;
	}

	/**
	* validate the logical structure of a lang-file
	*
	* This function is similar to function checkLanguage() (see below) but checks for all
	* lang-files and outputs more helpful information.
	*
	* @param	void
	*
	* @return	void
	*/
	function checklangObject ()
	{
		global $lng;
		
		// set path to directory where lang-files reside
		$d = dir($this->lang_path);
		$tmpPath = getcwd();
		chdir ($this->lang_path);
	
		// for giving a message when no lang-file was found
		$found = false;
		
		// get available lang-files
		while ($entry = $d->read())
		{
			if (is_file($entry) && (ereg ("(^ilias_.{2}\.lang)", $entry)))
			{
				// textmeldung, wenn langfile gefunden wurde
				$output .= "<br>langfile found: ".$entry;
				$content = file ($entry);
				
				$found = true;
				$error = false;
	
				if ($content = $this->cut_header($content)) {
					foreach ($content as $key => $val) {
						$separated = explode ($this->separator,trim($val));
						$num = count($separated);

						if ($num != 3) {
	
							$error = true;
						
							switch ($num) {
								case 1:
									if (empty($separated[0])) {
										$output .= "<br>no params! Please check your langfiles";
									}
									else {
										$output .= "<br>only 1 param! Please check your langfiles";
									}
								break;
	
								case 2:
									$output .= "<br>only 2 params! Please check your langfiles";
								break;
	
								default:
									$output .= "<br>more than 3 params! Please check your langfiles";
								break;
							}
						}
					}
	
					if ($error) {
						$output .= "<br>File not valid! reason: wrong param count!";
					}
					else {
						$output .= "<br>file is valid";
					}
				}
				else {
					$output .= "<br>File not valid! reason: wrong header!";
				}
			}
		}
		
		$d->close();
		
		if (!$found) {
			$output .= "<br>no langfiles found!";
		}
		
		chdir($tmpPath);
		return $output;
	}

	/**
	* remove lang-file haeder information from '$content'
	*
	* This function seeks for a special keyword where the language information starts.
	* if found it returns the plain language information, otherwise returns false
	*
	* @param	string	$content	expect an ILIAS lang-file
	*
	* @return	string	$content	content without header info OR false if no valid header was found
	*/
	function cut_header ($content) {
		foreach ($content as $key => $val) {
			if (trim($val) == "<!-- language file start -->") {
				return array_slice($content,$key +1);
			}
	 	}
	 	
	 	return false;
	}

	/**
	* remove one or all languagee from database 
	*
	* sub-function: to uninstall a language use function uninstallLanguage()
	* if $lang_key ist not given all installed languages are removed from database
	* 
	* @param	string	$lang_key	(optional) international language key (2 digits)
	*
	* @return	void
	*/
	function flushLanguage ($lang_key="") {
	
		$clause = "";
	
		if (!empty($lang_key)) {
			$clause = " WHERE lang_key='".$lang_key."'";
		}
		
		$query = "DELETE FROM lng_data".$clause;
		$this->ilias->db->query($query);
	}

	//TODO: remove redundant checks here!
	/**
	* insert language data form file in database
	*
	* @param	string	$lang_key	international language key (2 digits)
	*
	* @return	void
	*/
	function insertLanguage ($lang_key)
	{
		$tmpPath = getcwd();
		chdir($this->lang_path);

		$lang_file = "ilias_".$lang_key.".lang";
		
		if ($lang_file)
		{
			// remove header first
			if ($content = $this->cut_header(file($lang_file))) {
				foreach ($content as $key => $val) {
					$separated = explode ($this->separator,trim($val));
					$num = count($separated);
	
					$query = "INSERT INTO lng_data ".
						 	 "(module,identifier,lang_key,value) ".
						 	 "VALUES ".
						 	 "('".$separated[0]."','".$separated[1]."','".$lang_key."','".addslashes($separated[2])."')";
					$res = $this->ilias->db->query($query);
				}
					$query = "UPDATE object_data SET ".
						 	 "last_update = now() ".
							 "WHERE title = '".$lang_key."' ".
							 "AND type = 'lng'";
					$res = $this->ilias->db->query($query);
			}
		}

		chdir($tmpPath);
	}

	/**
	* optimizes the db-table langdata
	*
	* @param	void
	*
	* @return	void
	*/
	function optimizeLangdata () {

		// optimize
		$query = "OPTIMIZE TABLE lng_data";
		$this->ilias->db->query($query);
		
		return true;
	}

	/**
	* search ILIAS for users which have selected '$lang_key' as their prefered language and
	* reset them to default language (english). A message is sent to all affected users
	*
	* @param	string		$lang_key	international language key (2 digits)
	*
	* @return	boolean					true: user(s) were affected
	*/
	function resetUserLanguage($lang_key)
	{
		$query = "UPDATE usr_pref SET ".
				 "value = '".$this->lang_default."' ".
				 "WHERE keyword = 'language' ".
				 "AND value = '".$lang_key."'";
		$this->ilias->db->query($query);
	}
} // END class.LanguageFolderObject
?>