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
	* @var		string
	* @access	private
	*/
	var $lang_user;

	/**
	* path to language files
	* relative path is taken from ini file
	* and added to absolute path of ilias
	* @var		string
	* @access	private
	*/
	var $lang_path;

	/**
	* language key in use by current user
	* @var		string	languagecode (two characters), e.g. "de", "en", "in"
	* @access	private
	*/
	var $lang_key;

	/**
	* language full name in that language current in use
	* @var		string
	* @access	private
	*/
	var $lang_name;

	/**
	* separator value between module,identivier & value 
	* @var		string
	* @access	private
	*/
	var $separator = "#:#";

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
		global $ilias, $PHP_SELF;
		
		$this->ilias =& $ilias;
		
		$this->lang_key = $a_lang_key;
		
		$this->text = array();

		// if no ilias.ini.php was found set default values (->for setup-routine)
		if (basename($PHP_SELF) == "setup.php")
		{
			$this->lang_path = getcwd()."/lang";
			$this->lang_default = "en";

			$txt = file($this->lang_path."/setup_".$a_lang_key.".lang");
		
			$this->lang_name = $txt[0];

			if (is_array($txt))
			{
				foreach ($txt as $row)
				{
					if ($row[0] != "#")
					{
						$a = explode($this->separator,trim($row));
						$this->text[trim($a[0])] = trim($a[1]);
					}
				}
			}
		}
		else
		{
			$this->lang_path = getcwd().substr($this->ilias->ini->readVariable("language","path"),1);
			$this->lang_default = $this->ilias->ini->readVariable("language","default");
			$this->lang_user = $this->ilias->account->prefs["language"];
			
			$this->loadLanguage();
		}

		return true;
	}
	
	/**
	* gets the text for a given topic
	*
	* if the topic is not in the list, the topic itself with "-" will be returned
	* @access	public 
	* @param	string	topic
	* @return	string	text clear-text
	*/
	function txt($a_topic)
	{
		global $log;
		
		if (empty($a_topic))
		{
			return "";
		}

		$translation = $this->text[$a_topic];

		if ($translation == "")
		{
			$log->writeLanguageLog($a_topic);
			return "-".$a_topic."-";
		}
		else
		{
			return $translation;
		}
	}
	
	function loadLanguage ()
	{
		$lang_key = $this->lang_key;
		
		if (empty($this->lang_key))
		{
			$lang_key = $this->lang_user;
		}
		
		$query = "SELECT identifier,value FROM lng_data ".
				 "WHERE lang_key = '".$lang_key."' ".
				 "AND module = 'common'";
		$res = $this->ilias->db->query($query);
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->text[$row->identifier] = $row->value;
		}
	}
	
	function getInstalledLanguages()
	{
		$langlist = getObjectList("lng");
		
		foreach ($langlist as $lang)
		{
			if ($lang["desc"] == "installed")
			{
				$languages[] = $lang["title"];
			}
		
		}
		
		return $languages;
	}
} // END class.Language
?>