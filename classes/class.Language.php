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
* @package application
*/
class Language
{
	/**
	 * languages directory
	 * @var string
	 * @access private
	 */
	var $LANGUAGESDIR = "./lang";

	/**
	* master lang file
	* @var string
	* @access private
	*/
	var $MASTERLANGFILE = "languages.txt";
	
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
	 * constructor
	 * 
	 * read the single-language file and put this in an array text.
	 * the text array is two-dimensional. First dimension is the language. 
	 * Second dimension is the languagetopic. Content is the translation.
	 * 
	 * @param string lng languagecode (two characters), e.g. "de", "en", "in"
	 * @return boolean false if reading failed
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
	function Language($lng)
	{
		global $log;
    
		$this->setMasterFile($this->MASTERLANGFILE);
	    $txt = @file($this->LANGUAGESDIR."/".$lng.".lang");
        $this->log = $log;
		$this->text = array();
        if (is_array($txt)==true)
        {
			foreach($txt as $row)
			{
				if ($row[0]!="#")
				{
					$a = explode("#:#",trim($row));
					$this->text[$lng][trim($a[0])] = trim($a[1]);
				}
			}
			$this->lng = $lng;
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
	 * 
	 * @param string topic
	 * @return string text clear-text
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
	function txt($topic)
	{
	    $translation = $this->text[$this->lng][$topic];

	    if ($translation == "")
		{
			$this->log->writeLanguageLog($topic);
			return "-".$topic."-";
		}
	    else
			return $translation;
	}

	/**
	 * get all languages in the system
	 * 
	 * returns a list (an array) with all languages installed on the system. 
	 * the functions looks for *.lang-files in the languagedirectory
	 *
	 * @return array langs
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
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
					$langs[] = array( "id" => $id,
									  "name" => $name,
									  "status" => "installed",
									  "lastchange" => date("Y-m-d H:i:s",filectime($this->LANGUAGESDIR."/".$file))
						);
				} //if
			}  //while
			closedir($dir);
		} //if
		return $langs;
	} //function

	/** 
	 * formatting function for dates
	 *
	 * in different languages, dates are formatted different. 
	 * formatDate reads a value "lang_dateformat" from the languagefile.
	 * if it is not present it sets it to a defaultvalue given in the class
	 * the format must have DD, MM, and YYYY strings
	 * formatDate replaces the strings with the current values given in str
	 *
	 * @param string date date, given in sql-format YYYY-MM-DD
	 * @param string format type (normal is as given in lang_dateformat)
	 * @return string formatted date
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
    function fmtDate($str, $fmt="normal")
	{
		//read the format
	    $date = $this->txt("lang_dateformat");

		//no format defined set to defaultformat
	    if ($date == "-lang_dateformat-")
		{
			$date = $this->DEFAULTDATEFORMAT;
		}

		//get values from given sql-date
		$d = substr($str,8,2);
		$m = substr($str,5,2);
		$y = substr($str,0,4);
		
		//do substitutions
		$date = ereg_replace("DD", $d, $date);
		$date = ereg_replace("MM", $m, $date);
		$date = ereg_replace("YYYY", $y, $date);

		//return
		return $date;
	}

	/** 
	 * formatting function for datetime
	 *
	 * @see fmtDate()
	 * @param string datetime given in sql-format YYYY-MM-DD HH:MM:SS
	 * @param string format type (normal is as given in lang_dateformat)
	 * @return string formatted date
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
    function fmtDateTime($str, $fmt="normal")
	{
		//formate date-part
	    $datetime = $this->fmtDate($str, $fmt);

		//format timeformat
		$datetime .= " ".substr($str,11,2).":".substr($str,14,2);
		
		//return
		return $datetime;
	}	
	
	/**
	 * format a float
	 * 
	 * this functions takes php's number_format function and 
	 * formats the given value with appropriate thousand and decimal
	 * separator.
	 * 
	 * @param float the float to format
	 * @param integer count of decimals
	 * @param integer display thousands separator
	 * @return string formatted number
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
	function fmtFloat($float, $decimals=0, $th=1)
	{
		//thousandskomma?
		if ($th==1)
		{
			$th = $this->txt("sep_thousand");
			if ($th == "-sep_thousand-")
				$th = $this->DEFAULTSEPTHOUSAND;
		}
		else
			$th="";

		//decimalpoint?
		$dec = $this->txt("sep_decimal");
		if ($dec == "-sep_decimal-")
			$dec = $this->DEFAULTSEPDECIMAL;

		return number_format($float, $decimals, $dec, $th);
	}

	/**
	 * user agreement
	 * 
	 * returns the user agreement, but who needs it?
	 * 
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 0.1
 	 */
	function getUserAgreement()
	{
		return "here is da user agreement";
	}

	/**
	* set MasterFile	
	* @param string
	* @acess public
	*/
	function setMasterFile($langfile)
	{
		if ($langfile != "")
		{
			$this->MASTERLANGFILE = $this->LANGUAGESDIR."/".$langfile;
		}
	}

	/**
	 * generate all language files from masterfile
	 * 
	 * input is a string, the masterfile. the masterfile is used for 
	 * generating the single-language-files.
	 * 
	 * @param string textfile with all language topics
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
	function generateLanguageFiles()
	{
		$l_file = @file($this->MASTERLANGFILE);

		if ($l_file == false)
		{
			$this->error = "Input-file '".$this->MASTERLANGFILE."' not found!";
			return false;
		} //if
	
		for ($i=1; $i<count($l_file); $i++)
		{

			switch ($i)
			{
				case 1:
					$rows = explode("\t",$l_file[$i]);
					
					for ($j=1; $j<count($rows); $j++)
					{
						$file[] = @fopen("./lang/".trim($rows[$j]).".lang", "w");

						if ($file[$j-1] == false)
						{
							$this->error = "Could not open output-file '".trim($rows[$j]).".lang'";
							return false;
						}
					} //for
					break;
					
				case 2:
					$langs = array();
					
					$rows = explode("\t",$l_file[$i]);
					for ($j=1; $j<count($rows); $j++)
					{
						$langs[] = $rows[$j];
						if (fputs($file[$j-1], trim($rows[$j])."\n")==false)
						{
							$this->error = "Could not write to file";
							return false;
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
							$this->log->write("Language: '".$rows[0]."' not defined in Language '".$langs[$j]."'");
						}
							
						fputs($file[$j-1], trim($rows[0])."#:#". $translation ."\n");
					}

			} //switch
		} //for
		
		for ($i=0; $i<count($file); $i++)
		{
			fclose($file[$i]);
		} //for
		
		return true;
		
	} //function
	
	/**
	* get all languages from the master textfile
	* 
	* if you want to install new languages you want to know which languages you can install
	* this function gives back an array with all languages the masterfile contains
	* 
 	* @return array
	* @access public
	* @version 1.0
	* @author Peter Gabriel <pgabriel@databay.de>
	*/
	function getAvailableLanguages()
	{
		//initialization
		$langs = array();

		//try to open the masterlangfile
		$l_file = @file($this->MASTERLANGFILE);

		if ($l_file == false)
		{
			$this->error = "Input-file '".$this->MASTERLANGFILE."' not found!";
			return false;
		} //if

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
				"id" => $ids[$i],
				"name" => $names[$i],
				"status" => $status,
				"lastchange" => $lastchange
			);
		} //for
		
		return $langs;
	}

	/**
	* install a language
	* 
	* @param string
 	* @return boolean
	* @access public
	* @version 1.0
	* @author Peter Gabriel <pgabriel@databay.de>
	*/
	function installLanguage($id)
	{
		$id = trim($id);
		
		$l_file = @file($this->MASTERLANGFILE);

		if ($l_file == false)
		{
			$this->error = "Input-file '".$this->MASTERLANGFILE."' not found!";
			return false;
		} //if
	
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
		$fp = @fopen("./lang/".$id.".lang", "w");
		if ($fp == false)
		{
			$this->error = "Could not open output-file '".$id.".lang'";
			return false;
		}
		
		//write name to file
		$row = explode("\t",$l_file[2]);

		if (fputs($fp, trim($row[$index])."\n")==false)
		{
			$this->error = "Could not write to file";
			return false;
		}

		//write topics
		for ($i = 3; $i<count($l_file); $i++)
		{
			$row = explode("\t",$l_file[$i]);

			$translation = trim($row[$index]);
			//check content of translation, if no translation present
			// the topic itself is returned
			if ($translation=="")
			{
				$translation = $row[0];	
				$this->log->write("Language: '".$row[0]."' not defined in Language '".$names[$index]."'");
			}
			fputs($fp, trim($row[0])."#:#". $translation ."\n");

		} //for

		fclose($fp);
	} //function
	
	/**
	* deinstall a language
	* 
	* this function removes the language from the system
	* 
 	* @return boolean
	* @access public
	* @version 1.0
	* @author Peter Gabriel <pgabriel@databay.de>
	*/
	function deinstallLanguage($id)
	{
		$id = trim($id);
		//TODO: check if anybody uses this language
		//delete file and return success or failure		
		if (file_exists($this->LANGUAGESDIR."/".$id.".lang"))
		{
			if ($id == $this->systemLang || $id == $this->userLang) {
			    return false;
			}
		    return unlink($this->LANGUAGESDIR."/".$id.".lang");
		}
		else
		{
			return false;
		}
		
	}
	
	function setSystemLanguage($id)
	{
		$this->systemLang = $id;
	}

	function setUserLanguage($id)
	{
		$this->userLang = $id;
	}
	
} //class
?>