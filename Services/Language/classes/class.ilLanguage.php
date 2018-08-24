<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	 * array of used topics
	 * @var array
	 */
	protected static $used_topics = array();

	/**
	 * array of used modules
	 * @var array
	 */
	protected static $used_modules = array();
	/**
	 * @var array
	 */
	protected $cached_modules = array();

	/**
	 * @var string[]
	 */
	protected $map_modules_txt = array();

	/**
	 * @var bool
	 */
	protected $usage_log_enabled = false;

	/**
	 * @var string[]
	 */
	protected static $lng_log = array();

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
	function __construct($a_lang_key)
	{
		global $ilias,$log,$ilIliasIniFile,$ilUser,$ilSetting;

		$this->ilias = $ilias;

		if (!isset($log))
		{
			if (is_object($ilias))
			{
				require_once "./Services/Logging/classes/class.ilLog.php";
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

		$this->usage_log_enabled = self::isUsageLogEnabled();

		//$this->lang_path = ILIAS_ABSOLUTE_PATH.substr($this->ilias->ini->readVariable("language","path"),1);

		// if no directory was found fall back to default lang dir
		//if (!is_dir($this->lang_path))
		//{
			$this->lang_path = ILIAS_ABSOLUTE_PATH."/lang";
		//}
		$this->cust_lang_path = ILIAS_ABSOLUTE_PATH."/Customizing/global/lang";

		$this->lang_default = $ilIliasIniFile->readVariable("language","default");
		if (is_object($ilSetting) && $ilSetting->get("language") != "")
		{
			$this->lang_default = $ilSetting->get("language");
		}
		$this->lang_user = $ilUser->prefs["language"];
		
		$langs = $this->getInstalledLanguages();
		
		if (!in_array($this->lang_key,$langs))
		{
			$this->lang_key = $this->lang_default;
		}

		require_once('./Services/Language/classes/class.ilCachedLanguage.php');
		$this->global_cache = ilCachedLanguage::getInstance($this->lang_key);
		if ($this->global_cache->isActive()) {
			$this->cached_modules = $this->global_cache->getTranslations();
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
		return $this->lang_default ? $this->lang_default : 'en';
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
			return ilLanguage::_lookupEntry($a_language, $a_module, $a_topic);
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
	function txt($a_topic, $a_default_lang_fallback_mod = "")
	{
		if (empty($a_topic))
		{
			return "";
		}

		// remember the used topics
		self::$used_topics[$a_topic] = $a_topic;

		$translation = "";
		if (isset($this->text[$a_topic]))
		{
			$translation = $this->text[$a_topic];
		}

		if ($translation == "" && $a_default_lang_fallback_mod != "")
		{
			// #13467 - try current language first (could be missing module)
			if($this->lang_key != $this->lang_default)
			{
				$translation = ilLanguage::_lookupEntry($this->lang_key,
					$a_default_lang_fallback_mod, $a_topic);
			}			
			// try default language last
			if($translation == "" || $translation == "-".$a_topic."-")
			{
				$translation = ilLanguage::_lookupEntry($this->lang_default,
					$a_default_lang_fallback_mod, $a_topic);
			}			
		}


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
			if($this->usage_log_enabled)
			{
				self::logUsage($this->map_modules_txt[$a_topic], $a_topic);
			}
			return $translation;
		}
	}
	
	/**
	 * Check if language entry exists
	 * @param object $a_topic
	 * @return 
	 */
	public function exists($a_topic)
	{
		return isset($this->text[$a_topic]);
	}
	
	function loadLanguageModule ($a_module)
	{
		global $ilDB;

		if (in_array($a_module, $this->loaded_modules))
		{
			return;
		}

		$this->loaded_modules[] = $a_module;

		// remember the used modules globally
		self::$used_modules[$a_module] = $a_module;

		$lang_key = $this->lang_key;

		if (empty($this->lang_key))
		{
			$lang_key = $this->lang_user;
		}

		if(is_array($this->cached_modules[$a_module])) {
			$this->text = array_merge($this->text, $this->cached_modules[$a_module]);

			if($this->usage_log_enabled)
			{
				foreach (array_keys($this->cached_modules[$a_module]) as $key)
				{
					$this->map_modules_txt[$key] = $a_module;
				}
			}

			return;
		}

/*
		$query = "SELECT identifier,value FROM lng_data " .
				"WHERE lang_key = '" . $lang_key."' " .
				"AND module = '$a_module'";
		$r = $this->ilias->db->query($query);

		while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->text[$row->identifier] = $row->value;
		}
*/

		$q = "SELECT * FROM lng_modules " .
				"WHERE lang_key = ".$ilDB->quote($lang_key, "text")." AND module = ".
				$ilDB->quote($a_module, "text");
		$r = $ilDB->query($q);
		$row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
		
		$new_text = unserialize($row["lang_array"]);
		if (is_array($new_text))
		{
			$this->text = array_merge($this->text, $new_text);

			if($this->usage_log_enabled)
			{
				foreach (array_keys($new_text) as $key)
				{
					$this->map_modules_txt[$key] = $a_module;
				}
			}
		}
	}
	
	
	function getInstalledLanguages()
	{
		return self::_getInstalledLanguages();
	}

	static function _getInstalledLanguages()
	{
		include_once("./Services/Object/classes/class.ilObject.php");
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

	public static function _lookupEntry($a_lang_key, $a_mod, $a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query($q = sprintf("SELECT * FROM lng_data WHERE module = %s ".
			"AND lang_key = %s AND identifier = %s",
			$ilDB->quote((string) $a_mod, "text"), $ilDB->quote((string) $a_lang_key, "text"),
			$ilDB->quote((string) $a_id, "text")));
		$rec = $ilDB->fetchAssoc($set);
		
		if ($rec["value"] != "")
		{
			// remember the used topics
			self::$used_topics[$a_id]   = $a_id;
			self::$used_modules[$a_mod] = $a_mod;

			if(self::isUsageLogEnabled())
			{
				self::logUsage($a_mod, $a_id);
			}

			return $rec["value"];
		}
		
		return "-".$a_id."-";
	}

	/**
	 * Lookup obj_id of language
	 * @global ilDB $ilDB
	 * @param string $a_lang_key
	 * @return int
	 */
	public static function lookupId($a_lang_key)
	{
		global $ilDB;

		$query = 'SELECT obj_id FROM object_data '.' '.
		'WHERE title = '.$ilDB->quote($a_lang_key, 'text').' '.
			'AND type = '.$ilDB->quote('lng','text');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}
		return 0;
	}


	function getUsedTopics()
	{
		asort(self::$used_topics);
		return self::$used_topics;
	}
	
	function getUsedModules()
	{
		asort(self::$used_modules);
		return self::$used_modules;
	}

	function getUserLanguage()
	{
		return $this->lang_user;
	}

	/**
	 * Builds the global language object
	 * @return self
	 */
	public static function getGlobalInstance()
	{
		/**
		 * @var $ilUser    ilObjUser
		 * @var $ilSetting ilSetting
		 */
		global $ilUser, $ilSetting, $lng;

		if(!ilSession::get('lang') && !$_GET['lang'])
		{
			if(
				$ilUser instanceof ilObjUser &&
				(!$ilUser->getId() || $ilUser->isAnonymous())
			)
			{
				require_once 'Services/Language/classes/class.ilLanguageDetection.php';
				$language_detection = new ilLanguageDetection();
				$language           = $language_detection->detect();

				$ilUser->setPref('language', $language);
				$_GET['lang'] = $language;
			}
		}

		if(isset($_POST['change_lang_to']) && $_POST['change_lang_to'] != "")
		{
			$_GET['lang'] = ilUtil::stripSlashes($_POST['change_lang_to']);
		}

		// prefer personal setting when coming from login screen
		// Added check for ilUser->getId > 0 because it is 0 when the language is changed and the terms of service should be displayed
		if(
			$ilUser instanceof ilObjUser &&
			($ilUser->getId() && !$ilUser->isAnonymous())
		)
		{
			ilSession::set('lang', $ilUser->getPref('language'));
		}

		ilSession::set('lang', (isset($_GET['lang']) && $_GET['lang']) ? $_GET['lang'] : ilSession::get('lang'));

		// check whether lang selection is valid
		$langs = self::_getInstalledLanguages();
		if(!in_array(ilSession::get('lang'), $langs))
		{
			if($ilSetting instanceof ilSetting && $ilSetting->get('language') != '')
			{
				ilSession::set('lang', $ilSetting->get('language'));
			}
			else
			{
				ilSession::set('lang', $langs[0]);
			}
		}
		$_GET['lang'] = ilSession::get('lang');

		return new self(ilSession::get('lang'));
	}

	/*
	 * Transfer text to Javascript
	 *
	 * @param string|array $a_lang_key languag key or array of language keys
	 * @param ilTemplate $a_tpl template
	 */
	function toJS($a_lang_key, ilTemplate $a_tpl = null)
	{
		global $tpl;

		if (!is_object($a_tpl))
		{
			$a_tpl = $tpl;
		}

		if (!is_array($a_lang_key))
		{
			$a_lang_key = array($a_lang_key);
		}

		$map = array();
		foreach ($a_lang_key as $lk)
		{
			$map[$lk] = $this->txt($lk);
		}
		$this->toJSMap($map, $a_tpl);
	}

	/**
	 * Transfer text to Javascript
	 *
	 * @param array $a_map array of key value pairs (key is text string, value is content)
	 * @param ilTemplate $a_tpl template
	 */
	function toJSMap($a_map, ilTemplate $a_tpl = null)
	{
		global $tpl;

		if (!is_object($a_tpl))
		{
			$a_tpl = $tpl;
		}

		if (!is_array($a_map))
		{
			return;
		}

		foreach ($a_map as $k => $v)
		{
			if ($v != "")
			{
				include_once("./Services/JSON/classes/class.ilJsonUtil.php");
				$a_tpl->addOnloadCode("il.Language.setLangVar('".$k."', ".ilJsonUtil::encode($v).");");
			}
		}
	}

	/**
	 * saves tupel of language module and identifier
	 *
	 * @param string $a_module
	 * @param string $a_identifier
	 */
	protected static function  logUsage($a_module, $a_identifier)
	{
		if($a_module != "" && $a_identifier != "")
		{
			self::$lng_log[$a_identifier] = $a_module;
		}
	}

	/**
	 * checks if language usage log is enabled
	 * you need MySQL to use this function
	 * this function is automatically enabled if DEVMODE is on
	 * this function is also enabled if language_log is 1
	 *
	 * @return bool
	 */
	protected static function isUsageLogEnabled()
	{
		/** @var ilIniFile $ilClientIniFile */
		global $ilClientIniFile, $ilDB;
		
		if(!(($ilDB instanceof ilDBMySQL) || ($ilDB instanceof ilDBPdoMySQLMyISAM)) || !$ilClientIniFile instanceof ilIniFile)
		{

			return false;
		}

		if(DEVMODE)
		{
			return true;
		}

		if(!$ilClientIniFile->variableExists('system', 'LANGUAGE_LOG'))
		{
			return $ilClientIniFile->readVariable('system', 'LANGUAGE_LOG') == 1;
		}
		return false;
	}

	/**
	 * destructor saves all language usages to db if log is enabled and ilDB exists
	 */
	function __destruct()
	{
		global $ilDB;

		//case $ilDB not existing should not happen but if something went wrong it shouldn't leads to any failures
		if(!$this->usage_log_enabled || !(($ilDB instanceof ilDBMySQL) || ($ilDB instanceof ilDBPdoMySQLMyISAM)))
		{
			return;
		}

		foreach((array)self::$lng_log as $identifier => $module)
		{
			$wave[] = '(' . $ilDB->quote($module, 'text'). ', ' . $ilDB->quote($identifier, 'text') . ')';
			unset(self::$lng_log[$identifier]);

			if(count($wave) == 150 || (count(self::$lng_log) == 0 && count($wave) > 0))
			{
				$query = 'REPLACE INTO lng_log (module, identifier) VALUES ' . implode(', ', $wave);
				$ilDB->manipulate($query);

				$wave = array();
			}
		}
	}
	
} // END class.Language
?>
