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
 * 
 * @todo Das Datefeld wird bei Aenderungen einer Sprache (update, install, deinstall) nicht richtig gesetzt!!!
 *  Die Formatfunktionen gehoeren nicht in class.Language. Die sind auch woanders einsetzbar!!!
 *  Daher->besser in class.Format
 */
class ilLanguage
{
	/**
	 * ilias object
	 * 
	 * @var object Ilias
	 * @access private
	 */
	var $ilias;
	
	/**
	 * text elements
	 * 
	 * @var array
	 * @access private
	 */
	var $text;
	
	/**
	 * indicator for the system language
	 * this language must not be deleted
	 * 
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
	 * language key in use by current user
	 * 
	 * @var		string	languagecode (two characters), e.g. "de", "en", "in"
	 * @access	private
	 */
	var $lang_key;

	/**
	 * language full name in that language current in use
	 * 
	 * @var		string
	 * @access	private
	 */
	var $lang_name;

	/**
	 * separator value between module,identivier & value 
	 * 
	 * @var		string
	 * @access	private
	 */
	var $separator = "#:#";
	
	/**
	 * separator value between the content and the comment of the lang entry
	 * 
	 * @var		string
	 * @access	private
	 */
	var $comment_separator = "###";

	/**
	 * array of loaded languages
	 * 
	 * @var		array
	 * @access	private
	 */
	var $loaded_modules;

	/**
	 * Constructor
	 * read the single-language file and put this in an array text.
	 * the text array is two-dimensional. First dimension is the language.
	 * Second dimension is the languagetopic. Content is the translation.
	 * 
	 * @access	public
	 * @param	string		languagecode (two characters), e.g. "de", "en", "in"
	 * @return	boolean 	false if reading failed
	 */
	function ilLanguage($a_lang_key)
	{
		global $ilias,$log,$ilIliasIniFile,$ilUser;


		$this->ilias =& $ilias;

		if (!isset($log))
		{
			if (is_object($ilias))
			{
				$this->log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,$ilias->getClientId(),ILIAS_LOG_ENABLED);
			}
		}
		else
		{
			$this->log =& $log;
		}

		$this->lang_key = $a_lang_key;
		
		$this->text = array();
		$this->loaded_modules = array();
		//$this->lang_path = ILIAS_ABSOLUTE_PATH.substr($this->ilias->ini->readVariable("language","path"),1);

		// if no directory was found fall back to default lang dir
		//if (!is_dir($this->lang_path))
		//{
			$this->lang_path = ILIAS_ABSOLUTE_PATH."/lang";
		//}
		$this->cust_lang_path = ILIAS_ABSOLUTE_PATH."/Customizing/global/lang";

		$this->lang_default = $ilIliasIniFile->readVariable("language","default");
		$this->lang_user = $ilUser->prefs["language"];
		
		$langs = $this->getInstalledLanguages();
		
		if (!in_array($this->lang_key,$langs))
		{
			$this->lang_key = $this->lang_default;
		}

		$this->loadLanguageModule("common");

		return true;
	}

	function getLangKey()
	{
		return $this->lang_key;
	}
	
	function getDefaultLanguage()
	{
		return $this->lang_default;
	}
	
	/**
	 * gets the text for a given topic in a given language
	 * if the topic is not in the list, the topic itself with "-" will be returned
	 * 
	 * @access	public 
	 * @param	string	topic
	 * @param string $a_language The language of the output string
	 * @return	string	text clear-text
	 */
	function txtlng($a_module, $a_topic, $a_language)
	{
		if (strcmp($a_language, $this->lang_key) == 0)
		{
			return $this->txt($a_topic);
		}
		else
		{
			$query = sprintf("SELECT value FROM lng_data WHERE lang_key = %s AND module = %s AND identifier = %s",
				$this->ilias->db->quote($a_language . ""),
				$this->ilias->db->quote($a_module . ""),
				$this->ilias->db->quote($a_topic . "")
			);
			$r = $this->ilias->db->query($query);
	
			if  ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				return $row->value;
			}
			else
			{
				return "-".$a_topic."-";
			}
		}
	}

	/**
	 * gets the text for a given topic
	 * if the topic is not in the list, the topic itself with "-" will be returned
	 * 
	 * @access	public 
	 * @param	string	topic
	 * @return	string	text clear-text
	 */
	function txt($a_topic)
	{
		if (empty($a_topic))
		{
			return "";
		}
		$translation = $this->text[$a_topic];

		if ($translation == "")
		{
			if (ILIAS_LOG_ENABLED && is_object($this->log))
			{
				$this->log->writeLanguageLog($a_topic,$this->lang_key);
			}

			return "-".$a_topic."-";
		}
		else
		{
			return $translation;
		}
	}
	
	function loadLanguageModule ($a_module)
	{
		global $ilDB;
		
		if (in_array($a_module, $this->loaded_modules))
		{
			return;
		}

		$this->loaded_modules[] = $a_module;

		$lang_key = $this->lang_key;

		if (empty($this->lang_key))
		{
			$lang_key = $this->lang_user;
		}

/*
		$query = "SELECT identifier,value FROM lng_data " .
				"WHERE lang_key = '" . $lang_key."' " .
				"AND module = '$a_module'";
		$r = $this->ilias->db->query($query);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->text[$row->identifier] = $row->value;
		}
*/

		$query = "SELECT * FROM lng_modules " .
				"WHERE lang_key = ".$ilDB->quote($lang_key)." " .
				"AND module = ".$ilDB->quote($a_module);
		$r = $ilDB->query($query);
		$row = $r->fetchRow(DB_FETCHMODE_ASSOC);
		
		$new_text = unserialize($row["lang_array"]);
		if (is_array($new_text))
		{
			$this->text = array_merge($this->text, $new_text);
		}
	}
	
	
	function getInstalledLanguages()
	{
		$langlist = ilObject::_getObjectsByType("lng");
		
		foreach ($langlist as $lang)
		{
			if (substr($lang["desc"], 0, 9) == "installed")
			{
				$languages[] = $lang["title"];
			}
		
		}

		return $languages ? $languages : array();
	}
	
	function _lookupEntry($a_lang_key, $a_mod, $a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM lng_data WHERE".
			" module = ".$ilDB->quote($a_mod).
			" AND lang_key =".$ilDB->quote($a_lang_key).
			" AND identifier =".$ilDB->quote($a_id);
			
		$set = $ilDB->query($q);
		
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		
		if ($rec["value"] != "")
		{
			return $rec["value"];
		}
		
		return "-".$a_id."-";
	}
} // END class.Language
?>
