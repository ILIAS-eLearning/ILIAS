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
	* @access publicc
	*/
	var $lng;
	
	/**
	* languages directory
	* @var string
	* @access private
	*/
	var $LANGUAGESDIR;

	/**
	* master lang file
	* @var string
	* @access private
	*/
	var $MASTERLANGFILE;
	
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
	* Constructor
	* read the single-language file and put this in an array text.
	* the text array is two-dimensional. First dimension is the language. 
	* Second dimension is the languagetopic. Content is the translation.
	* @access	public 
	* @param	string		languagecode (two characters), e.g. "de", "en", "in"
	* @return	boolean 	false if reading failed
	*/
	function Language($a_lng)
	{
		global $ilias;
		
		$this->ilias =& $ilias;
		
		// if no ilias.ini.php was found set default values (->for setup-routine)
		if ($ilias)
		{
			$this->MASTERLANGFILE = $this->ilias->ini->readVariable("language","masterfile");
			$this->LANGUAGESDIR = $this->ilias->ini->readVariable("server","lang_path");		
		}
		else
		{
			$this->MASTERLANGFILE = "languages.txt";
			$this->LANGUAGESDIR = "./lang";
		}

		$txt = @file($this->LANGUAGESDIR."/".$a_lng.".lang");
		
		$this->text = array();
		
		if (is_array($txt))
		{
			foreach ($txt as $row)
			{
				if ($row[0] != "#")
				{
					$a = explode("#:#",trim($row));
					$this->text[$a_lng][trim($a[0])] = trim($a[1]);
				}
			}
			
			// set language
			$this->lng = $a_lng;
			return true;
		}
		else
		{
			return false;
		}
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

		$translation = $this->text[$this->lng][$a_topic];

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

	/**
	* get all languages in the system
	* 
	* returns a list (an array) with all languages installed on the system. 
	* the functions looks for *.lang-files in the languagedirectory
	* @access	public
	* @return	array	langs
	*/
	function getInstalledLanguages()
	{
		//initialization
		$langs = array();

		//search for all languages files
		if ($dir = @opendir($this->LANGUAGESDIR))
		{
			while ($file = readdir($dir))
			{
				if (strpos($file,".lang") > 0)
				{
					$id = substr($file,0,2);
					//read the first line from each lang-file, first line is the name of language
					$fp = fopen ($this->LANGUAGESDIR."/".$file, "r");
					$name = fgets($fp,1000);
					fclose($fp);
					$langs[] = array( "id" => trim($id),
									  "name" => trim($name),
									  "status" => "installed",
									  "lastchange" => date("Y-m-d H:i:s",filectime($this->LANGUAGESDIR."/".$file))
						);
				}
			}
			
			closedir($dir);
		}

		return $langs;
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

	/**
	* generate all language files from masterfile
	* 
	* input is a string, the masterfile. the masterfile is used for 
	* generating the single-language-files.
	* 
	* @access	public
	*/
	function generateLanguageFiles()
	{
		global $log;

		if (!$l_file = @file($this->LANGUAGESDIR."/".$this->MASTERLANGFILE))
		{
			$this->ilias->raiseError("Input-file '".$this->LANGUAGESDIR."/".$this->MASTERLANGFILE."' not found!",$this->ilias->error_obj->WARNING);
		}
	
		for ($i=1; $i<count($l_file); $i++)
		{
			switch ($i)
			{
				case 1:
					$rows = explode("\t",$l_file[$i]);
					
					for ($j=1; $j<count($rows); $j++)
					{
						$file[] = @fopen("./lang/".trim($rows[$j]).".lang", "w");

						if (!$file[$j-1])
						{
							$this->ilias->raiseError("Could not open output-file '".trim($rows[$j]).".lang'",$this->ilias->error_obj->WARNING);
						}
					} //for
					break;
					
				case 2:
					$langs = array();
					
					$rows = explode("\t",$l_file[$i]);

					for ($j=1; $j<count($rows); $j++)
					{
						$langs[] = $rows[$j];

						if (fputs($file[$j-1], trim($rows[$j])."\n") == false)
						{
							$this->ilias->raiseError("Could not write to file",$this->ilias->error_obj->WARNING);
						}
					}
					break;
					
				default:
					$rows = explode("\t",$l_file[$i]);

					for ($j=1; $j<count($rows); $j++)
					{
						$translation = trim($rows[$j]);
						
						//check content of translation, if no translation present
						// the topic itself is returned
						if ($translation=="")
						{
							$translation = $rows[0];	
							$log->write("Language: '".$rows[0]."' not defined in Language '".$langs[$j]."'");
						}
							
						fputs($file[$j-1], trim($rows[0])."#:#". $translation ."\n");
					}
			} //switch
		}
		
		for ($i=0; $i<count($file); $i++)
		{
			fclose($file[$i]);
		}
		
		return true;
		
	}
	
	/**
	* get all languages from the master textfile
	* 
	* if you want to install new languages you want to know which languages you can install
	* this function gives back an array with all languages the masterfile contains
	* @access	public 
 	* @return	array
	*/
	function getAvailableLanguages()
	{
		//initialization
		$langs = array();

		//try to open the masterlangfile
		if (!$l_file = @file($this->LANGUAGESDIR."/".$this->MASTERLANGFILE))
		{
			$this->ilias->raiseError("Input-file '".$this->LANGUAGESDIR."/".$this->MASTERLANGFILE."' not found!",$this->ilias->error_obj->WARNING);
		}

		$ids = explode("\t",$l_file[1]);
		$names = explode("\t",$l_file[2]);

		for ($i = 1; $i<count($ids); $i++)
		{
			unset($status);

			//get status of language, is language installed on the system?
			if (file_exists($this->LANGUAGESDIR."/".trim($ids[$i]).".lang"))
			{
				$status = "installed";
			}
			else
			{
				$status = "not_installed";
			}
			
			unset($lastchange);
			
			if ($status == "installed")
			{
				$lastchange = date("Y-m-d H:i:s",filectime($this->LANGUAGESDIR."/".trim($ids[$i]).".lang"));
			}

			//build arrayentry
			$langs[] = array(
							"id"		=> $ids[$i],
							"name"		=> $names[$i],
							"status"	=> $status,
							"lastchange"=> $lastchange
							);
		}
		
		return $langs;
	}

	/**
	* install a language
	* 
	* @access	public
	* @param	string
 	* @return	boolean
	*/
	function installLanguage($a_id)
	{
		global $log;

		$a_id = trim($a_id);
		
		$l_file = @file($this->LANGUAGESDIR."/".$this->MASTERLANGFILE);

		if ($l_file == false)
		{
			$this->ilias->raiseError("Input-file '".$this->LANGUAGESDIR."/".$this->MASTERLANGFILE."' not found!",$this->ilias->error_obj->MESSAGE);
		}
	
		$ids = explode("\t",$l_file[1]);

		//search for the given id
		unset($index);
		
		for ($i=1; $i<count($ids); $i++)
		{
			if ($id == trim($ids[$i]))
			{
				$index = $i;
				break;
			}
		}
		
		if ($index == 0)
		{
			//language not found
			return false;
		}
		
		//open file and write
		if (!$fp = @fopen("./lang/".$a_id.".lang", "w"))
		{
			$this->ilias->raiseError("Could not open output-file '".$a_id.".lang'",$this->ilias->error_obj->WARNING);
		}
		
		//write name to file
		$row = explode("\t",$l_file[2]);

		if (fputs($fp, trim($row[$index])."\n") == false)
		{
			$this->ilias->raiseError("Could not write to file!",$this->ilias->error_obj->WARNING);
		}

		//write topics
		for ($i = 3; $i<count($l_file); $i++)
		{
			$row = explode("\t",$l_file[$i]);

			$translation = trim($row[$index]);
			//check content of translation, if no translation present
			// the topic itself is returned
			
			if ($translation == "")
			{
				$translation = $row[0];	
				$log->write("Language: '".$row[0]."' not defined in Language '".$names[$index]."'");
			}

			fputs($fp, trim($row[0])."#:#". $translation ."\n");

		}

		fclose($fp);
	}
	
	/**
	* deinstall a language
	* 
	* remove the language from the system
	* @access	public
	* @param	string 
 	* @return	boolean		true if language was successfully removed
	*/
	function deinstallLanguage($a_id)
	{
		$a_id = trim($a_id);
		
		//TODO: check if anybody uses this language
		//delete file and return success or failure
		if (file_exists($this->LANGUAGESDIR."/".$a_id.".lang"))
		{
			if ($a_id == $this->systemLang || $a_id == $this->userLang)
			{
				return false;
			}
			
			return unlink($this->LANGUAGESDIR."/".$a_id.".lang");
		}
		else
		{
			return false;
		}
	}
	
	/**
	* set the system language
	* @access	public
	* @param	string
	*/
	function setSystemLanguage($a_id)
	{
		$this->systemLang = $a_id;
	}

	/**
	* set the user language
	* @access	public
	* @param	string
	*/
	function setUserLanguage($a_id)
	{
		$this->userLang = $a_id;
	}
} // END class.Language
?>