<?php
/**
* language handling
*
* this class offers the language handling for an application.
* it works initially on one file: languages.txt
* from this file the class can generate many single language files.
* the constructor is called with a small language abbreviation
* e.g. $lng = new Language("en");
* the constructor reads the single-languagefile en.lang and puts this into an array.
* with 
* e.g. $lng->txt("user_updated");
* you can translate a lang-topic into the actual language
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
* 
* @todo Das Datefeld wird bei Änderungen einer Sprache (update, install, deinstall) nicht richtig gesetzt!!!
*  Die Formatfunktionen gehören nicht in class.Language. Die sind auch woanders einsetzbar!!!
*  Daher->besser in class.Format
*/
class Language
{
	/**
	* ilias object
	* @var object Ilias
	* @access private
	*/
	var $ilias;
	
	/**
	* text elements
	* @var array
	* @access private
	*/
	var $text;
	
	/**
	* languagecode (two characters), e.g. "de", "en", "in"
	* @var string
	* @access public
	*/
	var $langkey;
	
	/**
	* languages directory
	* @var string
	* @access private
	*/
	var $LANGUAGESDIR;
	
	var $lang_path;

	/**
	* default date format
	* @var string
	* @access private
	*/
	var $DEFAULTDATEFORMAT = "DD.MM.YYYY";

	/**
	* default date format
	* @var string
	* @access private
	*/
	var $DEFAULTSEPTHOUSAND = ".";

	/**
	* default date format
	* @var string
	* @access private
	*/
	var $DEFAULTSEPDECIMAL = ",";

	/**
	* indicator for the system language
	* 
	* this language must not be deleted
	* @var		string
	* @access	private
	*/
	var $systemLang;
	
	/**
	* indicator for the user language
	* 
	* this language must not be deleted
	* 
	* @var		string
	* @access	private
	*/
	var $userLang;
	
	/**
	* indicator for the user language
	* 
	* this language must not be deleted
	* 
	* @var		string
	* @access	private
	*/
	var $separator;

	/**
	* Constructor
	* read the single-language file and put this in an array text.
	* the text array is two-dimensional. First dimension is the language. 
	* Second dimension is the languagetopic. Content is the translation.
	* @access	public 
	* @param	string		languagecode (two characters), e.g. "de", "en", "in"
	* @return	boolean 	false if reading failed
	*/
	function Language($a_lang_key)
	{
		global $ilias;
		
		$this->ilias = $ilias;
		
		$this->separator = "#:#";
		
		$this->getInstalledLanguages();

		$this->userLang = $a_lang_key;

		$this->setSystemLanguage($this->ilias->ini->readVariable("language","default"));

		// if no ilias.ini.php was found set default values (->for setup-routine)
		if ($ilias)
		{
			$this->LANGUAGESDIR = $this->ilias->ini->readVariable("server","lang_path");
		}
		else
		{
			$this->LANGUAGESDIR = "./lang";
		}
		
		$this->lang_path = getcwd()."/".substr($this->LANGUAGESDIR,1);
		
		$this->loadLanguage();
	}

	/**
	* output menu with list of available and installed languages
	*
	* @param	string	$info_text	(optional) display $info_text on screen
	*
	* @return	void
	*/
	function overviewLanguages ()
	{
		global $PHP_SELF;
	

		// compute languages array
		$languages = $this->getLanguages();
		
		//vd($languages);
		//exit;
	
		foreach ($languages as $lang_key => $lang_data) {
	
			if ($lang_data["installed"]) {
				$command = "uninstall";
				$lang_installed = $lang["admLang_installed"];
			}
			else {
				$command = "install";
				$lang_installed = $lang["admLang_uninstalled"];
			}
	
			switch ($lang_data["info"]) {
				case "notfound":
					$lang_info = "<font color=\"red\">datei nicht gefunden</font>";
				break;
				
				case "new":
					$lang_info = "<b><font color=\"green\">".$lang["admLang_new"]."</font></b>";
				break;
				
				default:
					$lang_info = "";
				break;
			}
		}
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
		// set path to directory where lang-files reside
		$d = dir($this->lang_path);
		chdir ($this->lang_path);
	
		// get available lang-files
		while ($entry = $d->read())
		{
			if (is_file($entry) && (ereg ("(^ilias_.{2}\.lang)", $entry)))
			{
				$lang_key = substr($entry,6,2);
				$languages[$lang_key]["long"] = $this->text["lang_".$lang_key];
			}
		}
	
		//this is for determining missing lang-files (see below)
		// dirty: if no lang-file was found surpress PHP-warning
		@$tmp_array = array_keys($languages);
		
		// get db-entries
		$query = "SELECT * FROM languages";
		$res = $this->ilias->db->query($query);
	
		$lang_keys[] = array();
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$lang_key = $row->lang_key;
			$lang_update = $row->last_update;
	
			$lang_keys[] = $lang_key;
			
			//get installed languages
			if ($row->installed == "y")
			{
				$languages[$lang_key]["installed"] = true;
			}
			else
			{
				$languages[$lang_key]["installed"] = false;
			}
	
			// set last update time
			$languages[$lang_key]["update"] = $lang_update;
	
			// check if files are missing
			// dirty: if no lang-file was found surpress PHP-warning
			if (@!in_array($lang_key,$tmp_array))
			{
				$languages[$lang_key]["info"] = "notfound";
	
				// Initialize long language name again because this lang-file is missing
				$languages[$lang_key]["long"] = $this->text["lang_".$lang_key];
			}
		}
	
		//compute new languages
		foreach ($languages as $lang_key => $lang_data)
		{
			if (!in_array($lang_key,$lang_keys))
			{
				$languages[$lang_key]["info"] = "new";
			}
		}
	
		// Insert languages with files new found into table language
		$this->addNewLanguages($languages);
	
		// Remove from array & db languages which are not installed and no lang-files
		$languages = $this->removeLanguages($languages);
	
		// sort language list by long name (depends on chosen user language)
		uasort($languages,"cmp");
		reset($languages);
		
		return $languages;
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
	function addNewLanguages($languages) {
		
		if (count($languages) > 0)
		{
			foreach ($languages as $lang_key => $lang_data) {
				if ($lang_data["info"] == "new") {
					$query = "INSERT INTO languages (lang_key,installed) VALUES ('$lang_key','n')";
					$this->ilias->db->query($query);
				}
			}
			
			// new lang-files found and db-table 'languages' has been updated
			return true;
		}
		
		// no new lang-files found
		return false;
	}
	
	
	/**
	* install a language
	*
	* This function copy all language entries from a lang-file to database
	*
	* @param	string	$lang_key	international language key (2 digits)
	*
	* @return	string	$info_text	status message about final event within the function
	*/
	function installLanguage ($lang_key) {
	
		// validate lang-file first
		$result = $this->checkLanguage($lang_key);
	
		if ($result == "1") {
			// lang-file is ok. Flush data in db and...
			$this->flushLanguage($lang_key);
			
			// ...re-insert data from lang-file
			$this->insertLanguage($lang_key);
	
			// update information in db-table about available/installed languages
			$query = "UPDATE languages SET installed='y' WHERE lang_key='".$lang_key."'";
			$this->ilias->db->query($query);
			
			$this->optimizeLangdata();
			
			// return info message
			return $lang["admLang_msg_languageInstalled"]." ".$lang["header_lang_".$lang_key];
		}
		
		// language installed; return 'true'
		return $result;
	}
	
	
	/**
	* uninstall a language
	*
	* This function removes all language data from database and updates the language information
	* in db-table 'languages'.
	*
	* @param	string	$lang_key	international language key (2 digits)
	*
	* @return	string	$info_text	status message about final event within the function
	*/
	// remove a language form db
	function uninstallLanguage ($lang_key) {
		
		$this->flushLanguage($lang_key);
	
		$query = "UPDATE languages SET installed='n' WHERE lang_key='".$lang_key."'";
		$this->ilias->db->query($query);
		
		//resetUserLanguage($lang_key);
		
		return $this->text["lang_".$lang_key]." wurde deinstalliert";
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
	// re-reads lang-files and import them into db
	function refreshLanguages ()
	{		
		foreach ($this->LANGUAGES as $lang) {
	
			// validate languages
			$result = $this->checkLanguage($lang["id"]);
	
			// if validation was successful
			if ($result == "1") {
			
				$this->flushLanguage($lang["id"]);
	
				$this->insertLanguage($lang["id"]);
			}
			else
			{
				// return info message
				return $lang["admLang_msg_errorFile"]." ".$lang["header_lang_".$lang_key].". ".$lang["admLang_msg_checkFile"];
			}
		}
		
		// return info message
		return $lang["admLang_msg_languagesUpdated"];
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
	
		//echo "Flushing langdata...<br>";
	
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
	function insertLanguage ($lang_key) {
		
		chdir($this->lang_path);
		$lang_file = "ilias_".$lang_key.".lang";
		
		if ($lang_file) {
	
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
					$query = "UPDATE languages SET ".
						 	 "last_update = now() ".
							 "WHERE lang_key='".$lang_key."'";
					$res = $this->ilias->db->query($query);
			}
		}
	}
	
	
	/**
	* search ILIAS for users which have selected '$lang_key' as their prefered language and
	* reset them to default language (english). A message is sent to all affected users
	*
	* @param	string		$lang_key	international language key (2 digits)
	*
	* @return	boolean					true: user(s) were affected
	*/
	function resetUserLanguage ($lang_key) {
		
		// find affected users
		$query = "SELECT id,benutzername FROM benutzer WHERE lang='$lang_key'";
		$this->ilias->db->query($query);
		
		if ($this->ilias->db->num_rows() > 0) {
			while ($this->ilias->db->next_record()) {
				$user_arr[] = array(
									"id"   => $this->ilias->db->f("id"),
									"user" => $this->ilias->db->f("benutzername")
									);
			}
			
			// send an information message within ILIAS mail system
			$subject = "Your language has been removed!";
			$content = "The Systemadministrator uninstalled your prefered language. ".
					   "Don't ask me why. I'm an autogenerated message. ".
					   "Your language setting has been reset to the default language (english).";
			
			foreach ($user_arr as $user_data) {
				$query = "UPDATE benutzer SET lang='en' WHERE id=".$user_data["id"];
				$this->ilias->db->query($query);
	
				//msg_send_msg($user_data["user"],"","",$subject,$content,5);
			}
			
			// one or more user's language setting has been updated
			return true;
		}
		
		// no user were affected
		return false;
	}
	
	
	/**
	* remove languages which are not installed AND has no lang-file
	*
	* This function removes only the entry in db-table 'languages' and
	* in the array $languages. Does not uninstall a language (see: function flushLanguage())
	*
	* @param	string	$lang_key	international language key (2 digits)
	*
	* @return	array	$languages	updated status information about available languages
	*/
	function removeLanguages ($languages) {
	
		foreach ($languages as $lang_key => $lang_data) {
			if (!$lang_data["installed"] and $lang_data["info"] == "notfound") {
	
				// update languages array
				unset($languages[$lang_key]);
	
				// update db-table languages
				$query = "DELETE FROM languages WHERE lang_key='$lang_key'";
				$this->ilias->db->query($query);
			}
		}
		
		return $languages;
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
	function checkLanguageFiles () {
		global $PHP_SELF;
	
		// set path to directory where lang-files reside
		$d = dir($this->lang_path);
		$tmpPath = getcwd();
		chdir ($this->lang_path);
	
		// for giving a message when no lang-file was found
		$found = false;
	
		while ($entry = $d->read())
		{
			if (is_file($entry) && (ereg ("(^ilias_.{2}\.lang)", $entry))) {
	
				// textmeldung, wenn langfile gefunden wurde
				$content = file ($entry);
				
				$found = true;
				$error = false;
	
				if ($content = $this->cut_header($content)) {
					foreach ($content as $key => $val) {
						$separated = explode ($this->separator,trim($val));
						$num = count($separated);

						if ($num != 3) {
	
							$error = true;
	
							//echo "<br><br><b>".$lang["admLang_msg_errorLine"]." ".$key." !</b>";
							//echo "<br>module: ".$separated[0];
							//echo "<br>identifier: ".$separated[1];
							//echo "<br>value: ".$separated[2]."<br>";
							
							switch ($num) {
								case 1:
									if (empty($separated[0])) {
										echo "<br><b>".show_text($lang["admLang_msg_error0"]." ".$lang["admLang_msg_checkFile"],"text")."</b>";
									}
									else {
										echo "<br><b>".show_text($lang["admLang_msg_error1"]." ".$lang["admLang_msg_checkFile"],"text")."</b>";
									}
								break;
	
								case 2:
									$this->ilias->error_obj->sentMessage("testdfd");
									//echo "<br><b>".show_text($lang["admLang_msg_error2"]." ".$lang["admLang_msg_checkFile"],"text")."</b>";
								break;
	
								default:
									echo "<br><b>".show_text($lang["admLang_msg_error3"]." ".$lang["admLang_msg_checkFile"],"text")."</b>";
								break;
							}
						}
					}
	
					if ($error) {
						//echo "<br><br><b>".show_text($lang["admLang_msg_notvalid"],"text")."</b> ".show_text($lang["admLang_msg_reason"]." ".$lang["admLang_msg_errorParams"],"text")."</b>";
					}
					else {
						//echo "<br>".show_text($lang["admLang_msg_valid"],"text");
					}
				}
				else {
					//echo "<br><br><b>".show_text($lang["admLang_msg_notvalid"],"text")."</b> ".show_text($lang["admLang_msg_reason"]." ".$lang["admLang_msg_errorHeader"],"text")."</b>";
				}
			}
		}
		
		$d->close();
		
		if (!$found) {
			//echo "<br><b>".show_text($lang["admLang_msg_errorPath"],"text")."</b>";
		}
		
		
		chdir($tmpPath);
		//$link[] = array($lang["LayoutInc_zurück"],$PHP_SELF,0);
		//echo col_show(link_button($link,"-1","200"),1,1);
		//unset($link);
		
		// optimize DB
		$this->optimizeLangdata();
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
	function checkLanguage ($lang_key) {
				
		chdir($this->lang_path);
	
		// compute lang-file name format
		$lang_file = "ilias_".$lang_key.".lang";
	
		// file check
		if (!is_file($lang_file)) {
			$this->ilias->raiseError("File not found!",$this->ilias->error_obj->MESSAGE);		
		}
	
		// header check
		if (!$content = $this->cut_header(file($lang_file))) {
			$this->ilias->raiseError("Wrong Header!",$this->ilias->error_obj->MESSAGE);
		}
	
		// check (counting) elements of each lang-entry
		foreach ($content as $key => $val) {
			$separated = explode ($this->separator,trim($val));
			$num = count($separated);
	
			if ($num != 3) {
				$this->ilias->raiseError("Wrong parameter count. Please check your language file!",$this->ilias->error_obj->MESSAGE);
			}
		}

		// no error occured
		return true;
	}
	
	
	/**
	* format timestamp to german datetime
	*
	* @param	int		$t			expect a timestamp with 14 digits
	*
	* @return	string	$datetime	Datetime in german format (dd.mm.yyyy)
	*/
	function ftimestamp2datetimeDE ($t)
	{
		return sprintf("%02d.%02d.%04d %02d:%02d:%02d",substr($t, 6, 2),substr($t, 4, 2),substr($t, 0, 4),substr($t, 8, 2),substr($t, 10, 2),substr($t, 12, 2));
	}
	
	
	/**
	* sub-function to sort $languages-array by their long names
	*
	* Long names of languages depends on the chosen language setting by the current user
	* The function is called only in this way: uasort($languages,"cmp");
	*
	* @param	array	$a	expect $languages
	* @param	string	$b	the function name itself ('cmp')
	*
	* @return	array	$languages	sorted array $languages
	*/
	function cmp ($a, $b) {
		return strcmp($a["long"], $b["long"]);
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
	* set default language (the system language)
	*
	* @param	string		lang_key
	*
	* @return	void
	* */
	function setDefaultLanguage ($a_lang_key)
	{
		$lang_key = substr($a_lang_key,-2);
		
		// update db
		$this->ilias->setSetting("language",$lang_key);
		
		// update in-file
		$this->ilias->ini->setVariable("language","default",$lang_key);
		$this->ilias->ini->write();
			
		$_GET["message"] = "Systemlanguage changed.";
	
		return $_GET["message"];
	}
	
	
	/**
	* show default language formular
	*
	* @param		string	$back			return URL (adress)
	*//*
	function setDefaultLanguageForm ($back)
	{
		global $PHP_SELF, $lang, $Session, $__ilias_languages, $virtus_lang, $__virtus_cust,$__virtus_user;
		
		kopf_admin($lang["admLang_header"],1,"");
		buttons("admin_menu.php");
		
			//echo show_pfeil_back("admin_languages.php",$lang["admLang_setDefaultLanguage"]);	
		
	
		//echo "<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">\n";
		//echo $Session->hidden();
		echo "<INPUT TYPE=hidden NAME=\"cmd\" VALUE=\"setsyslang\">\n";
		echo "<INPUT TYPE=hidden NAME=\"back\" VALUE=\"$back\">\n";
			
		echo rand_anfang();
				
			echo tab_start("600", "bg");
	
				echo col_width(array(249,1,350));
	
				echo row_start();
					echo col_show("&nbsp; <B>".$lang["admLang_defaultLanguage"]."</B> &nbsp;",3,1,"table_top_text","table_top_bg","","center","center","admin");
				echo row_end();
	
				echo abstand(3,1);
	
				echo row_start("table_bg");
					echo col_show("&nbsp; <B>".$lang["admLang_chooseDefaultLanguage"].":</B> &nbsp;","","","","","","right","center","admin");
					echo vert_line();
						$text = "&nbsp; <SELECT NAME=\"newsyslang\">";
						reset($__ilias_languages);
						while(list($lang_key, $lang_name) = each($__ilias_languages))
						{
							$text .= "<OPTION ";
							if ($__virtus_cust["lang_default"] == $lang_key) $text .= "SELECTED ";
							$text .= "VALUE=\"$lang_key\">".$lang_name." ";
						}
						$text .= " </SELECT> &nbsp;";
					echo col_show($text);		
				echo row_end();
				
				echo abstand(3,1);
				
				echo row_start("bg");
					echo col_show("",2,1);
					echo col_show("<INPUT TYPE=\"submit\" VALUE=\"".$lang["admLang_submit"]."\">",1,1,"text");
				echo row_end();
						
			echo tab_end();
			
		echo rand_ende();	
		
		echo "</FORM>\n";
	}
*/
	function loadLanguage ()
	{
		$query = "SELECT identifier,value FROM lng_data ".
				 "WHERE lang_key = '".$this->userLang."' ".
				 "AND module = 'common'";
		$res = $this->ilias->db->query($query);
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->text[$row->identifier] = $row->value;
		}
	}
	
	/**
	* gets the text for a given identifier
	*
	* if the idnetifier is not in the list, the identifier itself with "-" will be returned
	* @access	public 
	* @param	string	identifier for language value
	* @return	string	text clear-text
	*/
	function txt($a_identifier)
	{
		global $log;

		$translation = $this->text[$a_identifier];

		if ($translation == "")
		{
			$log->writeLanguageLog($a_identifier);
			return "-".$a_identifier."-";
		}
		else
		{
			return $translation;
		}
	}
	
	function getInstalledLanguages ()
	{
		$query = "SELECT * FROM languages ".
				 "WHERE installed = 'y'";
		$res = $this->ilias->db->query($query);
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->LANGUAGES[]["id"] = $row->lang_key;
		}
		
		return true;
	}
	
	/**
	* set the system language
	* @access	public
	* @param	string
	*/
	function setSystemLanguage($a_lang_key)
	{
		$valid = false;
		
		foreach ($this->LANGUAGES as $lang)
		{
			if ($lang["id"] == $a_lang_key)
			{
				$valid = true;
				$this->systemLang = $a_lang_key;
				break;
			}		
		}
		
		if ($valid)
		{
			$this->ilias->setSetting("language",$a_lang_key);
		}
		
		return $valid;
	}

	/**
	* set the user language
	* @access	public
	* @param	string
	*/
	function setUserLanguage($a_lang_key)
	{
		$lang_key = substr($a_lang_key,-2);
		
		$query = "UPDATE usr_pref SET value = '".$lang_key."' ".
				 "WHERE usr_id = '".$_SESSION["AccountId"]."' ".
				 "AND keyword = 'language'";
		$this->ilias->db->query($query);
		
		$this->userLang = $lang_key;
	}
/** 
	* formatting function for dates
	*
	* in different languages, dates are formatted different. 
	* formatDate reads a value "lang_dateformat" from the languagefile.
	* if it is not present it sets it to a defaultvalue given in the class
	* the format must have DD, MM, and YYYY strings
	* formatDate replaces the strings with the current values given in str
	* @access	public
	* @param	string	date date, given in sql-format YYYY-MM-DD
	* @return	string	formatted date
	*/
	function fmtDate($a_str)
	{
		//read the format
		$date = $this->txt("lang_dateformat");

		//no format defined set to defaultformat
		if ($date == "-lang_dateformat-")
		{
			$date = $this->DEFAULTDATEFORMAT;
		}

		//get values from given sql-date
		$d = substr($a_str,8,2);
		$m = substr($a_str,5,2);
		$y = substr($a_str,0,4);
		
		//do substitutions
		$date = ereg_replace("DD", $d, $date);
		$date = ereg_replace("MM", $m, $date);
		$date = ereg_replace("YYYY", $y, $date);

		return $date;
	}

	/** 
	* formatting function for datetime
	* @access	public
	* @param	string	datetime given in sql-format YYYY-MM-DD HH:MM:SS
	* @param	string	format type (normal is as given in lang_dateformat)
	* @return	string	formatted date
	* @see		fmtDate()
	*/
	function fmtDateTime($a_str, $a_fmt="normal")
	{
		//formate date-part
		$datetime = $this->fmtDate($a_str, $a_fmt);

		//format timeformat
		$datetime .= " ".substr($a_str,11,2).":".substr($a_str,14,2);
		
		return $datetime;
	}	
	
	/**
	* format a float
	* 
	* this functions takes php's number_format function and 
	* formats the given value with appropriate thousand and decimal
	* separator.
	* @access	public
	* @param	float		the float to format
	* @param	integer		count of decimals
	* @param	integer		display thousands separator
	* @return	string		formatted number
	*/
	function fmtFloat($a_float, $a_decimals = 0, $a_th = 1)
	{
		//thousandskomma?
		if ($a_th == 1)
		{
			$a_th = $this->txt("sep_thousand");
			
			if ($a_th == "-sep_thousand-")
			{
				$a_th = $this->DEFAULTSEPTHOUSAND;
			}
		}
		else
		{
			$a_th = "";
		}
		
		//decimalpoint?
		$dec = $this->txt("sep_decimal");
		
		if ($dec == "-sep_decimal-")
		{
			$dec = $this->DEFAULTSEPDECIMAL;
		}

		return number_format($a_float, $a_decimals, $dec, $a_th);
	}

	/**
	* user agreement
	* 
	* returns the user agreement, but who needs it?
	* TODO: the user_agreement should load from a separate file !!!
	* @access	public
 	*/
	function getUserAgreement()
	{
		return " ";
	}

	function getLanguageNames ($lang_key)
	{
		foreach ($this->LANGUAGES as $key => $lang)
		{
			//echo $lang_key;
			$query = "SELECT value FROM lng_data ".
					 "WHERE lang_key = '".$lang_key."' ".
					 "AND identifier = 'lang_".$lang["id"]."'";		
			$res = $this->ilias->db->query($query);
		
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			$this->LANGUAGES[$key]["name"] = $row->value;
		}		
	}	
	
	
} // END class.Language
?>