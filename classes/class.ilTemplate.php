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


/**
* special template class to simplify handling of ITX/PEAR
* @author	Stefan Kesseler <skesseler@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class ilTemplate extends ilTemplateX
{
	/**
	* Content-type for template output
	* @var	string
	*/
	var $contenttype;
	/**
	* variablen die immer in jedem block ersetzt werden sollen
	* @var	array
	*/
	var $vars;

	/**
	* Aktueller Block
	* Der wird gemerkt bei der berladenen Funktion setCurrentBlock, damit beim ParseBlock
	* vorher ein replace auf alle Variablen gemacht werden kann, die mit dem BLockname anfangen.
	* @var	string
	*/
	var $activeBlock;
	
	var $js_files = array(0 => "Services/JavaScript/js/Basic.js");		// list of JS files that should be included
	var $js_files_vp = array("Services/JavaScript/js/Basic.js" => true);	// version parameter flag
	var $css_files = array();		// list of css files that should be included
	var $admin_panel_commands = array();
	
	private $addFooter; // creates an output of the ILIAS footer

	/**
	* constructor
	* @param	string	$file 		templatefile (mit oder ohne pfad)
	* @param	boolean	$flag1 		remove unknown variables
	* @param	boolean	$flag2 		remove empty blocks
	* @param	boolean	$in_module	should be set to true, if template file is in module subdirectory
	* @param	array	$vars 		variables to replace
	* @access	public
	*/
	function ilTemplate($file,$flag1,$flag2,$in_module = false, $vars = "DEFAULT",
		$plugin = false)
	{
		global $ilias;

		$this->activeBlock = "__global__";
		$this->vars = array();
		$this->addFooter = TRUE;

		/*
		if (strpos($file,"/") === false)
		{
			//$fname = $ilias->tplPath;
			$base = "./";
			if ($module != "")
			{
				$base.= $module."/";
			}
			$base .= "templates/";
			$fname = $base.$ilias->account->skin."/".basename($file);
			if(!file_exists($fname))
			{
				$fname .= $base."default/".basename($file);
			}
		}
		else
		{
			$fname = $file;
		}*/
		$fname = $this->getTemplatePath($file, $in_module, $plugin);

		$this->tplName = basename($fname);
		$this->tplPath = dirname($fname);
		// set default content-type to text/html
		$this->contenttype = "text/html";
		if (!file_exists($fname))
		{
			$ilias->raiseError("template ".$fname." was not found.", $ilias->error_obj->FATAL);
			return false;
		}

		//$this->IntegratedTemplateExtension(dirname($fname));
		$this->callConstructor();
		//$this->loadTemplatefile(basename($fname), $flag1, $flag2);
		$this->loadTemplatefile($fname, $flag1, $flag2);
		//add tplPath to replacevars
		$this->vars["TPLPATH"] = $this->tplPath;
		
		// set Options
		if (method_exists($this, "setOption"))
		{
			$this->setOption('use_preg', false);
		}

		return true;
	}
	
	/*
	* Sets wheather the ILIAS footer should be shown or not
	*
	* @param boolean $value TRUE to show the ILIAS footer, FALSE to hide it
	*/
	function setAddFooter($value)
	{
		$this->addFooter = $value;
	}
	
	/*
	* Returns wheather the ILIAS footer should be shown or not
	*
	* @return boolean TRUE if the ILIAS footer will be shown, FALSE otherwise
	*/
	function getAddFooter()
	{
		return $this->addFooter;
	}

	
	/**
	* Use this for final get before sending asynchronous output (ajax)
	* per echo to output.
	*/
	function getAsynch()
	{
		header("Content-type: " . $this->getContentType() . "; charset=UTF-8");
		return $this->get();
	}
	
	/**
	* ???
	* @access	public
	* @param	string
	* @return	string
	*/
	function get($part = "DEFAULT", $add_error_mess = false,
		$handle_referer = false, $add_ilias_footer = false,
		$add_standard_elements = false, $a_main_menu = true, $a_tabs = true)
	{
		if ($add_error_mess)
		{
			$this->addErrorMessage();
		}

		if ($add_ilias_footer)
		{
			$this->addILIASFooter();
		}

		// set standard parts (tabs and title icon)
		if($add_standard_elements)
		{
			// to get also the js files for the main menu
			$this->getMainMenu();
			
			// these fill blocks in tpl.main.html
			$this->fillCssFiles();
			$this->fillJavaScriptFiles();
			$this->fillContentStyle();

			// these fill just plain placeholder variables in tpl.main.html
			$this->setCurrentBlock("DEFAULT");
			$this->fillNewContentStyle();
			$this->fillContentLanguage();
			$this->fillWindowTitle();

			// these fill blocks in tpl.adm_content.html
			$this->fillHeaderIcon();
			$this->fillSideIcons();
			$this->fillStopFloating();
			$this->fillPageFormAction();
			$this->fillLeftContent();
			$this->fillRightContent();
			$this->fillAdminPanel();
			$this->fillPermanentLink();

			// these fill just plain placeholder variables in tpl.adm_content.html
			if ($this->blockExists("content"))
			{
				$this->setCurrentBlock("content");
				if ($a_tabs)
				{
					$this->fillTabs();
				}
				$this->fillMainContent();
				$this->fillTitle();
				if ($a_main_menu)
				{
					$this->fillMainMenu();
				}
				$this->parseCurrentBlock();
			}
		}

		if ($handle_referer)
		{
			$this->handleReferer();
		}

		if ($part == "DEFAULT")
		{
			return parent::get();
		}
		else
		{
			return parent::get($part);
		}

	}

	function addErrorMessage()
	{
		// ERROR HANDLER SETS $_GET["message"] IN CASE OF $error_obj->MESSAGE
		if ($_SESSION["message"] || $_SESSION["info"])
		{
			if($this->blockExists("MESSAGE"))
			{
				$this->addBlockFile("MESSAGE", "message", "tpl.message.html");
#			$this->setCurrentBlock("message");

				$this->setVariable("MSG", $_SESSION["message"]);
				$this->setVariable("INFO", $_SESSION["info"]);

				session_unregister("message");
				session_unregister("info");

#			$this->parseCurrentBlock();
			}
		}
	}

	/**
	* Get the content type for the template output
	*
	* @return string Content type
	* @access	public
	*/
	function getContentType()
	{
		return $this->contenttype;
	}
	
	/**
	* Set the content type for the template output
	*
	* Set the content type for the template output
	* Usually this is text/html. For MathML output the
	* content type should be set to text/xml
	*
	* @param string $a_content_type Content type
	* @access	public
	*/
	function setContentType($a_content_type = "text/html")
	{
		$this->contenttype = $a_content_type;
	}
	
	/**
	* @access	public
	* @param	string
	* @param bool fill template variable {TABS} with content of ilTabs
	*/
	function show($part = "DEFAULT", $a_fill_tabs = true)
	{
		global $ilias;
//echo "-".ilUtil::getP3PLocation()."-";
		//header('P3P: policyref="'.ilUtil::getP3PLocation().
		//	'", CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
		header('P3P: CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
		header("Content-type: " . $this->getContentType() . "; charset=UTF-8");

		$this->addErrorMessage();
		
		// display ILIAS footer
		if ($part !== false)
		{
			$this->addILIASFooter();
		}

		// set standard parts (tabs and title icon)
		if ($a_fill_tabs)
		{
			// to get also the js files for the main menu
			$this->getMainMenu();

			// these fill blocks in tpl.main.html
			$this->fillCssFiles();
			$this->fillJavaScriptFiles();
			$this->fillContentStyle();

			// these fill just plain placeholder variables in tpl.main.html
			$this->setCurrentBlock("DEFAULT");
			$this->fillNewContentStyle();
			$this->fillContentLanguage();
			$this->fillWindowTitle();

			// these fill blocks in tpl.adm_content.html
			$this->fillHeaderIcon();
			$this->fillSideIcons();
			$this->fillStopFloating();
			$this->fillPageFormAction();
			$this->fillLeftContent();
			$this->fillRightContent();
			$this->fillAdminPanel();
			$this->fillPermanentLink();
			
			// these fill just plain placeholder variables in tpl.adm_content.html
			// these fill just plain placeholder variables in tpl.adm_content.html
			if ($this->blockExists("content"))
			{
				$this->setCurrentBlock("content");
				$this->fillTabs();
				$this->fillMainContent();
				$this->fillTitle();
				$this->fillMainMenu();
				$this->parseCurrentBlock();
			}
		}
		
		if ($part == "DEFAULT" or is_bool($part))
		{
			parent::show();
		}
		else
		{
			parent::show($part);
		}
		
		$this->handleReferer();
	}
	
	
	/**
	 * Add current user language to meta tags
	 *
	 * @access public
	 * 
	 */
	public function fillContentLanguage()
	{
	 	global $ilUser,$lng;
	 	
	 	if(is_object($ilUser))
	 	{
	 		if($ilUser->getLanguage())
	 		{
		 		$this->setVariable('META_CONTENT_LANGUAGE',$ilUser->getLanguage());
		 		return true;
	 		}
	 		if(is_object($lng))
	 		{
		 		$this->setVariable('META_CONTENT_LANGUAGE',$lng->getDefaultLanguage());
		 		return true;
	 		}
	 	}
 		$this->setVariable('META_CONTENT_LANGUAGE','en');
		return true;	 	
	}

	function fillWindowTitle()
	{
		global $ilSetting;
		
		if ($this->header_page_title != "")
		{
			$a_title = ilUtil::stripScriptHTML($this->header_page_title);	
			$this->setVariable("PAGETITLE", "- ".$a_title);
		}
		
		if ($ilSetting->get('short_inst_name') != "")
		{
			$this->setVariable("WINDOW_TITLE",
				$ilSetting->get('short_inst_name'));
		}
		else
		{
			$this->setVariable("WINDOW_TITLE",
				"ILIAS");
		}
	}
	
	function fillTabs()
	{
		global $ilias,$ilTabs;
		
		$this->setVariable("TABS",$ilTabs->getHTML());
		$this->setVariable("SUB_TABS",$ilTabs->getSubTabHTML());
	}
	
	function fillPageFormAction()
	{
		if ($this->page_form_action != "")
		{
			$this->setCurrentBlock("page_form_start");
			$this->setVariable("PAGE_FORM_ACTION", $this->page_form_action);
			$this->parseCurrentBlock();
			$this->touchBlock("page_form_end");
		}
	}
	
	function fillJavaScriptFiles()
	{
		global $ilias,$ilTabs,$ilSetting;
		
		if (is_object($ilSetting))		// maybe this one can be removed
		{
			$vers = "vers=".str_replace(array(".", " "), "-", $ilSetting->get("ilias_version"));
		}
		if ($this->blockExists("js_file"))
		{
			foreach($this->js_files as $file)
			{
				if (is_file($file) || substr($file, 0, 4) == "http")
				{
					$this->setCurrentBlock("js_file");

					if ($this->js_files_vp[$file])
					{
						$this->setVariable("JS_FILE", ilUtil::appendUrlParameterString($file,$vers));
					}
					else
					{
						$this->setVariable("JS_FILE", $file);
					}
					
					$this->parseCurrentBlock();
				}
			}
		}
	}

	function fillCssFiles()
	{
		foreach($this->css_files as $css)
		{
			$filename = $css["file"];
			if (strpos($filename, "?") > 0) $filename = substr($filename, 0, strpos($filename, "?"));
			if (is_file($filename))
			{
				$this->setCurrentBlock("css_file");
				$this->setVariable("CSS_FILE", $css["file"]);
				$this->setVariable("CSS_MEDIA", $css["media"]);
				$this->parseCurrentBlock();
			}
		}
	}

	/**
	* Set content style (used for page content editor)
	*/
	function setContentStyleSheet($a_style)
	{
		$this->content_style_sheet = $a_style;
	}
	
	/**
	* Fill Content Style
	*/
	function fillContentStyle()
	{
		if ($this->content_style_sheet != "")
		{
			$this->setCurrentBlock("ContentStyle");
			$this->setVariable("LOCATION_CONTENT_STYLESHEET",
				$this->content_style_sheet);
			$this->parseCurrentBlock();
		}
	}
	
	/**
	* Fill Content Style
	*/
	private function fillNewContentStyle()
	{
		$this->setVariable("LOCATION_NEWCONTENT_STYLESHEET_TAG",
			'<link rel="stylesheet" type="text/css" href="'.
			ilUtil::getNewContentStyleSheetLocation()
			.'" />');
	}
	
	function getMainMenu()
	{
		global $ilMainMenu;
		
		$this->main_menu = $ilMainMenu->getHTML();
	}
	
	function fillMainMenu()
	{
		global $tpl, $ilMainMenu, $ilCtrl, $ilSetting; 
		$tpl->setVariable("MAINMENU", $this->main_menu);
		if($this->variableExists('MAINMENU'))
		{
			global $ilAuth, $lng, $tpl, $ilClientIniFile;
			
			if ($ilSetting->get('session_reminder_enabled')== '1')
			{
				$tplSR = new ilTemplate('tpl.SessionReminder.js', true, true);

				$session_expires = $ilAuth->sessionValidThru();
				$current_time = time();
				$time_left = $session_expires - $current_time;
				$ende = date("Y-m-d, H:i:s", $session_expires);
				$start = date("Y-m-d, H:i:s",$current_time);
				$time_left = $time_left * 1000;
				
				$tplSR->setVariable('URL', 'index.php');
				$tplSR->setVariable('TIME_LEFT', $time_left);
				$tplSR->setVariable('REMEMBER_TIME', 30000);
				$tplSR->setVariable('ALERT', $lng->txt('session_reminder_alert'));
				
				$expires = $ilClientIniFile->readVariable("session", "expire");
				$time = ilFormat::_secondsToString($expires, true);
				$tplSR->setVariable('SESSION_REMINDER_EXTENDED', sprintf($lng->txt('session_reminder_extended'), $time));
				$tpl->setVariable('SESSION_REMINDER', $tplSR->get());				
			}
		}
	}
		
	function fillHeaderIcon()
	{
		if ($this->icon_path != "")
		{
			if ($this->icon_desc != "")
			{
				$this->setCurrentBlock("header_image_desc");
				$this->setVariable("IMAGE_DESC", $this->icon_desc);
				$this->parseCurrentBlock();
			}
			$this->setCurrentBlock("header_image");
			$this->setVariable("IMG_HEADER", $this->icon_path);
			$this->parseCurrentBlock();
		}
	}
	
	/**
	* add ILIAS footer
	*/
	function addILIASFooter()
	{
		if (!$this->getAddFooter()) return;
		global $ilias, $ilClientIniFile, $ilCtrl, $ilDB, $ilSetting;

		$this->addBlockFile("FOOTER", "footer", "tpl.footer.html");
		$this->setVariable("ILIAS_VERSION", $ilias->getSetting("ilias_version"));

		// output translation link
		if ($ilSetting->get("lang_ext_maintenance") == "1")
		{
			include_once("Services/Language/classes/class.ilObjLanguageAccess.php");
			if (ilObjLanguageAccess::_checkTranslate())
			{
				include_once("Services/Language/classes/class.ilObjLanguageExtGUI.php");
				$this->setVariable("TRANSLATION_LINK",
					ilObjLanguageExtGUI::_getTranslationLink());
			}
		}

		if (DEVMODE)
		{
			$mem_usage = "";
			if(function_exists("memory_get_usage"))
			{
				$mem_usage.=
					"<br /> Memory Usage: ".memory_get_usage()." Bytes";
			}
			if(function_exists("xdebug_peak_memory_usage"))
			{
				$mem_usage.=
					"<br /> XDebug Peak Memory Usage: ".xdebug_peak_memory_usage()." Bytes";
			}
			if ($mem_usage != "")
			{
				$this->setVariable("MEMORY_USAGE", $mem_usage);
			}

			$this->setVariable("SESS_INFO", "<br />maxlifetime: ".
				ini_get("session.gc_maxlifetime")." (".
				(ini_get("session.gc_maxlifetime")/60)."), id: ".session_id()."<br />".
				"timestamp: ".date("Y-m-d H:i:s", $_SESSION["_authsession"]["timestamp"]).
				", idle: ".date("Y-m-d H:i:s", $_SESSION["_authsession"]["idle"]).
				"<br />expire: ".($exp = $ilClientIniFile->readVariable("session","expire")).
				" (".($exp/60)."), session ends at: ".
				date("Y-m-d H:i:s", $_SESSION["_authsession"]["idle"] + $exp));
			
			if (version_compare(PHP_VERSION,'5','>='))
			{
				$this->setVariable("VALIDATION_LINKS",
					'<br /><a href="'.
					ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=xhtml").
					'">Validate</a> | <a href="'.
					ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=accessibility").
					'">Accessibility</a>');
			}
			if (!empty($_GET["do_dev_validate"]) && $this->blockExists("xhtml_validation"))
			{
				require_once("Services/XHTMLValidator/classes/class.ilValidatorAdapter.php");
				$template2 = ilPHP::cloneObject($this);
//echo "-".ilValidatorAdapter::validate($template2->get(), $_GET["do_dev_validate"])."-";
				$this->setCurrentBlock("xhtml_validation");
				$this->setVariable("VALIDATION",
					ilValidatorAdapter::validate($template2->get("DEFAULT",
					false, false, false, true), $_GET["do_dev_validate"]));
				$this->parseCurrentBlock();
			}
			
			// controller history
			if (is_object($ilCtrl) && $this->blockExists("c_entry") &&
				$this->blockExists("call_history"))
			{
				$hist = $ilCtrl->getCallHistory();
				foreach($hist as $entry)
				{
					$this->setCurrentBlock("c_entry");
					$this->setVariable("C_ENTRY", $entry["class"]);
					if (is_object($ilDB))
					{
						$file = $ilCtrl->lookupClassPath($entry["class"]);
						$add = $entry["mode"]." - ".$entry["cmd"];
						if ($file != "")
						{
							$add.= " - ".$file;
						}
						$this->setVariable("C_FILE", $add);
					}
					$this->parseCurrentBlock();
				}
				$this->setCurrentBlock("call_history");
				$this->parseCurrentBlock();
				
				// debug hack
				$debug = $ilCtrl->getDebug();
				foreach($debug as $d)
				{
					$this->setCurrentBlock("c_entry");
					$this->setVariable("C_ENTRY", $d);
					$this->parseCurrentBlock();
				}
				$this->setCurrentBlock("call_history");
				$this->parseCurrentBlock();
			}
		}

		// BEGIN Usability: Non-Delos Skins can display the elapsed time in the footer
		// The corresponding $ilBench->start invocation is in inc.header.php
		global $ilBench;
		$ilBench->stop("Core", "ElapsedTimeUntilFooter");
		$this->setVariable("ELAPSED_TIME",
			", ".number_format($ilBench->getMeasuredTime("Core", "ElapsedTimeUntilFooter"),1).' seconds');
		// END Usability: Non-Delos Skins can display the elapsed time in the footer
	}


	/**
	* TODO: this is nice, but shouldn't be done here
	* (-> maybe at the end of ilias.php!?, alex)
	*/
	function handleReferer()
	{
		if (((substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "error.php")
			&& (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "adm_menu.php")
			&& (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "chat.php")))
		{
			$_SESSION["post_vars"] = $_POST;

			// referer is modified if query string contains cmd=gateway and $_POST is not empty.
			// this is a workaround to display formular again in case of error and if the referer points to another page
			$url_parts = parse_url($_SERVER["REQUEST_URI"]);

			if (preg_match("/cmd=gateway/",$url_parts["query"]) && (isset($_POST["cmd"]["create"])))
			{
				foreach ($_POST as $key => $val)
				{
					if (is_array($val))
					{
						$val = key($val);
					}

					$str .= "&".$key."=".$val;
				}

				$_SESSION["referer"] = preg_replace("/cmd=gateway/",substr($str,1),$_SERVER["REQUEST_URI"]);
			}
			else if (preg_match("/cmd=post/",$url_parts["query"]) && (isset($_POST["cmd"]["create"])))
			{
				foreach ($_POST as $key => $val)
				{
					if (is_array($val))
					{
						$val = key($val);
					}

					$str .= "&".$key."=".$val;
				}

				$_SESSION["referer"] = preg_replace("/cmd=post/",substr($str,1),$_SERVER["REQUEST_URI"]);
			}
			else
			{
				$_SESSION["referer"] = $_SERVER["REQUEST_URI"];
			}

			unset($_SESSION["error_post_vars"]);
		}
	}

	/**
	* check if block exists in actual template
	* @access	private
	* @param string blockname
	* @return	boolean
	*/
	function blockExists($a_blockname)
	{
		// added second evaluation to the return statement because the first one only works for the content block (Helmut Schottmüller, 2007-09-14)
		return ($this->blockvariables["content"][$a_blockname] ? true : false) | ($this->blockvariables[$a_blockname] ? true : false);
	}
	
	private function variableExists($a_variablename)
	{
		return ($this->blockvariables["content"][$a_variablename] ? true : false);
	}

	/**
	* all template vars defined in $vars will be replaced automatically
	* without setting and parsing them with setVariable & parseCurrentBlock
	* @access	private
	* @return	integer
	*/
	function fillVars()
	{
		$count = 0;
		reset($this->vars);

		while(list($key, $val) = each($this->vars))
		{
			if (is_array($this->blockvariables[$this->activeBlock]))
			{
				if  (array_key_exists($key, $this->blockvariables[$this->activeBlock]))
				{
					$count++;

					$this->setVariable($key, $val);
				}
			}
		}
		
		return $count;
	}
	
	/**
	* Überladene Funktion, die sich hier lokal noch den aktuellen Block merkt.
	* @access	public
	* @param	string
	* @return	???
	*/
	function setCurrentBlock ($part = "DEFAULT")
	{
		$this->activeBlock = $part;

		if ($part == "DEFAULT")
		{
			return parent::setCurrentBlock();
		}
		else
		{
			return parent::setCurrentBlock($part);
		}
	}

	/**
	* overwrites ITX::touchBlock.
	* @access	public
	* @param	string
	* @return	???
	*/
	function touchBlock($block)
	{
		$this->setCurrentBlock($block);
		$count = $this->fillVars();
		$this->parseCurrentBlock();

		if ($count == 0)
		{
			parent::touchBlock($block);
		}
	}

	/**
	* Überladene Funktion, die auf den aktuelle Block vorher noch ein replace ausführt
	* @access	public
	* @param	string
	* @return	string
	*/
	function parseCurrentBlock($part = "DEFAULT")
	{
		// Hier erst noch ein replace aufrufen
		if ($part != "DEFAULT")
		{
			$tmp = $this->activeBlock;
			$this->activeBlock = $part;
		}

		if ($part != "DEFAULT")
		{
			$this->activeBlock = $tmp;
		}

		$this->fillVars();

		$this->activeBlock = "__global__";

		if ($part == "DEFAULT")
		{
			return parent::parseCurrentBlock();
		}
		else
		{
			return parent::parseCurrentBlock($part);
		}
	}

	/**
	* ???
	* TODO: Adjust var names to ilias. This method wasn't used so far
	* and isn't translated yet
	* @access	public
	* @param	string
	* @param	string
	* @param	string
	* @param	string
	*/
	function replaceFromDatabase(&$DB,$block,$conv,$select="default")
	{
		$res = $DB->selectDbAll();

		while ($DB->getDbNextElement($res))
		{
			$this->setCurrentBlock($block);
			$result = array();
			reset($conv);

			while (list ($key,$val) = each ($conv))
			{
				$result[$val]=$DB->element->data[$key];
			}

			if (($select != "default")
				&& ($DB->element->data[$select["id"]]==$select["value"]
				|| (strtolower($select["text"]) == "checked"
				&& strpos( ",,".$select["value"].",," , ",".$DB->element->data[$select["id"]]."," )!=false)))
			{
				$result[$select["field"]] = $select["text"];
			}

			$this->replace($result);
			$this->parseCurrentBlock($block);
		}
	}

	/**
	* Wird angewendet, wenn die Daten in ein Formular replaced werden sollen,
	* Dann wird erst noch ein htmlspecialchars drumherum gemacht.
	* @access	public
	* @param	string
	*/
	function prepareForFormular($vars)
	{
		if (!is_array($vars))
		{
			return;
		}

		reset($vars);

		while (list($i) = each($vars))
		{
			$vars[$i] = stripslashes($vars[$i]);
			$vars[$i] = htmlspecialchars($vars[$i]);
		}

		return($vars);
	}

	/**
	* ???
	* @access	public
	*/
	function replace()
	{
		reset($this->vars);

		while(list($key, $val) = each($this->vars))
		{
			$this->setVariable($key, $val);
		}
	}

	/**
	* ???
	* @access	public
	*/
	function replaceDefault()
	{
		$this->replace($this->vars);
	}

	/**
	* checks for a topic in the template
	* @access	private
 	* @param	string
	* @param	string
	* @return	boolean
	*/
	function checkTopic($a_block, $a_topic)
	{
		return array_key_exists($a_topic, $this->blockvariables[$a_block]);
	}

	/**
	* check if there is a NAVIGATION-topic
	* @access	public
	* @return	boolean
	*/
	function includeNavigation()
	{
		return $this->checkTopic("__global__", "NAVIGATION");
	}

	/**
	* check if there is a TREE-topic
	* @access	public
	* @return	boolean
	*/
	function includeTree()
	{
		return $this->checkTopic("__global__", "TREE");
	}

	/**
	* check if a file exists
	* @access	public
	* @return	boolean
	*/
	function fileExists($filename)
	{
		return file_exists($this->tplPath."/".$filename);
	}


	/**
	* overwrites ITX::addBlockFile
	* @access	public
	* @param	string
	* @param	string
	* @param	string		$tplname		template name
	* @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
	* @return	boolean/string
	*/
	function addBlockFile($var, $block, $tplname, $in_module = false)
	{
		if (DEBUG)
		{
			echo "<br/>Template '".$this->tplPath."/".$tplname."'";
		}

		$tplfile = $this->getTemplatePath($tplname, $in_module);
		if (file_exists($tplfile) == false)
		{
			echo "<br/>Template '".$tplfile."' doesn't exist! aborting...";
			return false;
		}

		return parent::addBlockFile($var, $block, $tplfile);
	}

	/**
	* builds a full template path with template and module name
	*
	* @param	string		$a_tplname		template name
	* @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
	*
	* @return	string		full template path
	*/
	function getTemplatePath($a_tplname, $a_in_module = false, $a_plugin = false)
	{
		global $ilias, $ilCtrl;
		
		// if baseClass functionality is used (ilias.php):
		// get template directory from ilCtrl
		if (!empty($_GET["baseClass"]) && $a_in_module === true)
		{
			$a_in_module = $ilCtrl->getModuleDir();
		}

		if (strpos($a_tplname,"/") === false)
		{
			$module_path = "";
			
			//$fname = $ilias->tplPath;
			if ($a_in_module)
			{
				if ($a_in_module === true)
				{
					$module_path = ILIAS_MODULE."/";
				}
				else
				{
					$module_path = $a_in_module."/";
				}
			}

			if ($ilias->account->skin != "default")
			{
				$fname = "./Customizing/global/skin/".
					$ilias->account->skin."/".$module_path.basename($a_tplname);
			}
			if($fname == "" || !file_exists($fname))
			{
				$fname = "./".$module_path."templates/default/".basename($a_tplname);
			}
		}
		else
		{
			$fname = $a_tplname;
		}
		
		return $fname;
	}
	
	function setHeaderPageTitle($a_title)
	{
		$a_title = ilUtil::stripScriptHTML($a_title);	
		$this->header_page_title = $a_title;
	}
	
	function setStyleSheetLocation($a_stylesheet)
	{
		$this->setVariable("LOCATION_STYLESHEET", $a_stylesheet);
	}

	function setNewContentStyleSheetLocation($a_stylesheet)
	{
		$this->setVariable("LOCATION_NEWCONTENT_STYLESHEET", $a_stylesheet);
	}

	function getStandardTemplate()
	{
		$this->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}
	
	/**
	* sets title in standard template
	*/
	function setTitle($a_title)
	{
		$this->title = ilUtil::stripScriptHTML($a_title);
		$this->header_page_title = $a_title;
	}
	
	/**
	* Fill title
	*/
	private function fillTitle()
	{
		if ($this->title != "")
		{
			$this->title = ilUtil::stripScriptHTML($this->title);			
			$this->setVariable("HEADER", $this->title);
		}
	}
	
	/**
	* set title icon
	*/
	function setTitleIcon($a_icon_path, $a_icon_desc = "")
	{
		$this->icon_desc = $a_icon_desc;
		$this->icon_path = $a_icon_path;
	}
	
	function setPageFormAction($a_action)
	{
		$this->page_form_action = $a_action;
	}
	
	/**
	* sets title in standard template
	*/
	function setDescription($a_descr)
	{
		$this->setVariable("H_DESCRIPTION", $a_descr);
	}
	
	/**
	* set stop floating (if no tabs are used)
	*/
	function stopTitleFloating()
	{
		$this->stop_floating = true;
	}
	
	/**
	* stop floating
	*/
	private function fillStopFloating()
	{
		if ($this->stop_floating)
		{
			$this->touchBlock("stop_floating");
		}
	}
	
	/**
	* sets content for standard template
	*/
	function setContent($a_html)
	{
		if ($a_html != "")
		{
			$this->main_content = $a_html;
		}
	}
	
	/**
	* Fill main content
	*/
	private function fillMainContent()
	{
		if (trim($this->main_content) != "")
		{
			$this->setVariable("ADM_CONTENT", $this->main_content);
		}
	}

	/**
	* sets content of right column
	*/
	function setRightContent($a_html)
	{
		$this->right_content = $a_html;
	}
	
	/**
	* Fill right content
	*/
	private function fillRightContent()
	{
		if (trim($this->right_content) != "")
		{
			$this->setCurrentBlock("right_column");
			$this->setVariable("RIGHT_CONTENT", $this->right_content);
			$this->parseCurrentBlock();
		}
	}
	
	/**
	* sets content of left column
	*/
	function setLeftContent($a_html)
	{
		$this->left_content = $a_html;
	}
		
	/**
	* Fill left content
	*/
	private function fillLeftContent()
	{
		if (trim($this->left_content) != "")
		{
			$this->setCurrentBlock("left_column");
			$this->setVariable("LEFT_CONTENT", $this->left_content);
			$this->parseCurrentBlock();
		}
	}

	/**
	* Insert locator.
	*/
	function setLocator()
	{
		global $ilLocator, $lng;
		
		$this->setVariable("LOCATOR", $ilLocator->getHTML());
	}
	
	/**
	* sets tabs in standard template
	*/
	function setTabs($a_tabs_html)
	{
		$this->setVariable("TABS", $a_tabs_html);
	}

	/**
	* sets subtabs in standard template
	*/
	function setSubTabs($a_tabs_html)
	{
		$this->setVariable("SUB_TABS", $a_tabs_html);
	}
	
	/**
	* sets icon to upper level
	*/
	function setUpperIcon($a_link, $a_frame = "")
	{
		global $lng;
		
		$this->upper_icon = $a_link;
		$this->upper_icon_frame = $a_frame;
	}

	/**
	* Fill side icons (upper icon, tree icon, webfolder icon)
	*/
	function fillSideIcons()
	{
		global $lng;
		
		if ($this->upper_icon == "" && $this->tree_flat_link == ""
			&& $this->mount_webfolder == "")
		{
			return;
		}
		
		// upper icon
		if ($this->upper_icon != "")
		{
			if ($this->upper_icon_frame != "")
			{
				$this->setCurrentBlock("target_top");
				$this->setVariable("TARGET_TOP", $this->upper_icon_frame);
				$this->parseCurrentBlock();
			}
	
			$this->setCurrentBlock("alt_top");
			$this->setVariable("ALT_TOP", $lng->txt("up"));
			$this->parseCurrentBlock();
	
			$this->setCurrentBlock("top");
			$this->setVariable("LINK_TOP", $this->upper_icon);
			$this->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
			$this->parseCurrentBlock();
		}
		
		// tree/flat icon
		if ($this->tree_flat_link != "")
		{
			$this->setCurrentBlock("tree_mode");
			$this->setVariable("LINK_MODE", $this->tree_flat_link);
			$this->setVariable("IMG_TREE",ilUtil::getImagePath("ic_".$this->tree_flat_mode."view.gif"));
			$this->setVariable("ALT_TREE",$lng->txt($this->tree_flat_mode."view"));
			$this->setVariable("TARGET_TREE", ilFrameTargetInfo::_getFrame("MainContent"));
			$this->parseCurrentBlock();
		}
		
		// mount webfolder
		if ($this->mount_webfolder != "")
		{
			require_once('Services/WebDAV/classes/class.ilDAVServer.php');
			$davServer = new ilDAVServer();
			$a_ref_id = $this->mount_webfolder;
			$a_link =  $davServer->getMountURI($a_ref_id);
			$a_folder = $davServer->getFolderURI($a_ref_id);
			
			$this->setCurrentBlock("mount_webfolder");
			$this->setVariable("LINK_MOUNT_WEBFOLDER", $a_link);
			$this->setVariable("FOLDER_MOUNT_WEBFOLDER", $a_folder);
			$this->setVariable("IMG_MOUNT_WEBFOLDER",ilUtil::getImagePath("ic_mount_webfolder.gif"));
			$this->setVariable("ALT_MOUNT_WEBFOLDER",$lng->txt("mount_webfolder"));
			$this->setVariable("TARGET_MOUNT_WEBFOLDER", '_blank');
			$this->parseCurrentBlock();
		}
		
		$this->setCurrentBlock("tree_icons");
		$this->parseCurrentBlock();
	}
	
	// BEGIN WebDAV: Mount webfolder icon.
	/**
	* shows icon for mounting a webfolder
	*/
	function setMountWebfolderIcon($a_ref_id)
	{
		global $lng;
		
		$this->mount_webfolder = $a_ref_id;
	}
	// END WebDAV: Mount webfolder icon.

	/**
	* set tree/flat icon
	* @param	string		link target
	* @param	strong		mode ("tree" | "flat")
	*/
	function setTreeFlatIcon($a_link, $a_mode)
	{
		global $lng;

		$this->tree_flat_link = $a_link;
		$this->tree_flat_mode = $a_mode;
	}

	/**
	* Add a javascript file that should be included in the header.
	*/
	function addJavaScript($a_js_file, $a_add_version_parameter = true)
	{
		if (!in_array($a_js_file, $this->js_files))
		{
			$this->js_files[] = $a_js_file;
			$this->js_files_vp[$a_js_file] = $a_add_version_parameter;
		}
	}

	/**
	* Add a css file that should be included in the header.
	*/
	function addCss($a_css_file, $media = "screen")
	{
		if (!array_key_exists($a_css_file . $media, $this->css_files))
		{
			$this->css_files[$a_css_file . $media] = array("file" => $a_css_file, "media" => $media);
		}
	}
	
	/**
	* Set selection and create button for adding new objects
	*/
	function setCreationSelector($a_form_action, $a_options,
		$a_command, $a_txt)
	{
		$this->setPageFormAction($a_form_action);
		$this->creation_selector =
			array("form_action" => $a_form_action,
				"options" => $a_options,
				"command" => $a_command,
				"txt" => $a_txt);
	}
	
	/**
	* Show admin view button
	*/
	function setAdminViewButton($a_link, $a_txt)
	{
		$this->admin_view_button =
			array("link" => $a_link, "txt" => $a_txt);
	}
	
	/**
	* Show admin view button
	*/
	function setEditPageButton($a_link, $a_txt, $a_frame)
	{
		$this->edit_page_button =
			array("link" => $a_link, "txt" => $a_txt, "frame" => $a_frame);
	}
	
	/**
	* Add a command to the admin panel
	*/
	function addAdminPanelCommand($a_cmd, $a_txt)
	{
		$this->admin_panel_commands[] =
			array("cmd" => $a_cmd, "txt" => $a_txt);
	}
	
	/**
	* Put admin panel into template:
	* - creation selector
	* - admin view on/off button
	*/
	function fillAdminPanel()
	{
		global $lng;
		
		$adm_view_cmp = $adm_cmds = $creation_selector = $adm_view = false;
		
		// admin panel commands
		if ((count($this->admin_panel_commands) > 0))
		{
			foreach($this->admin_panel_commands as $cmd)
			{
				$this->setCurrentBlock("admin_panel_cmd");
				$this->setVariable("PANEL_CMD", $cmd["cmd"]);
				$this->setVariable("TXT_PANEL_CMD", $cmd["txt"]);
				$this->parseCurrentBlock();
			}

			$adm_cmds = true;
		}
		if ($adm_cmds)
		{
			$this->setCurrentBlock("adm_view_components");
			$this->setVariable("ADM_IMG_ARROW", ilUtil::getImagePath("arrow_upright.gif"));
			$this->setVariable("ADM_ALT_ARROW", $lng->txt("actions"));
			$this->parseCurrentBlock();
			$adm_view_cmp = true;
		}
		
		// admin view button
		if (is_array($this->admin_view_button))
		{
			if (is_array($this->edit_page_button))
			{
				$this->setCurrentBlock("edit_cmd");
				$this->setVariable("TXT_EDIT_PAGE", $this->edit_page_button["txt"]);
				$this->setVariable("LINK_EDIT_PAGE", $this->edit_page_button["link"]);
				$this->setVariable("FRAME_EDIT_PAGE", $this->edit_page_button["frame"]);
				$this->parseCurrentBlock();
			}
			if (is_array($this->admin_view_button))
			{
				$this->setCurrentBlock("admin_button");
				$this->setVariable("ADMIN_MODE_LINK",
					$this->admin_view_button["link"]);
				$this->setVariable("TXT_ADMIN_MODE",
					$this->admin_view_button["txt"]);
				$this->parseCurrentBlock();
			}
			$this->setCurrentBlock("admin_view");
			$this->parseCurrentBlock();
			$adm_view = true;
		}
		
		// creation selector
		if (is_array($this->creation_selector))
		{
			$this->setCurrentBlock("add_commands");
			if ($adm_cmds)
			{
				$this->setVariable("ADD_COM_WIDTH", 'width="1"');
			}
			$this->setVariable("SELECT_OBJTYPE_REPOS",
				$this->creation_selector["options"]);
			$this->setVariable("BTN_NAME_REPOS",
				$this->creation_selector["command"]);
			$this->setVariable("TXT_ADD_REPOS",
				$this->creation_selector["txt"]);
			$this->parseCurrentBlock();
			$creation_selector = true;
		}
		if ($adm_view || $creation_selector)
		{
			$this->setCurrentBlock("adm_panel");
			if ($adm_view_cmp)
			{
				$this->setVariable("ADM_TBL_WIDTH", 'width:"100%";');
			}
			$this->parseCurrentBlock();
		}
		
		// lower part of admin panel
		if ((count($this->admin_panel_commands) > 0))
		{
			foreach($this->admin_panel_commands as $cmd)
			{
				$this->setCurrentBlock("admin_panel_cmd2");
				$this->setVariable("PANEL_CMD2", $cmd["cmd"]);
				$this->setVariable("TXT_PANEL_CMD2", $cmd["txt"]);
				$this->parseCurrentBlock();
			}

			$adm_cmds2 = true;
		}
		if ($adm_cmds2)
		{
			$this->setCurrentBlock("adm_view_components2");
			$this->setVariable("ADM_IMG_ARROW2", ilUtil::getImagePath("arrow_downright.gif"));
			$this->setVariable("ADM_ALT_ARROW2", $lng->txt("actions"));
			$this->parseCurrentBlock();
		}

	}
	
	function setPermanentLink($a_type, $a_id, $a_append = "", $a_target = "")
	{
		$this->permanent_link = array(
			"type" => $a_type,
			"id" => $a_id,
			"append" => $a_append,
			"target" => $a_target);
	}
	
	/**
	* Fill in permanent link
	*/
	function fillPermanentLink()
	{
		if (is_array($this->permanent_link))
		{
			include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
			$plinkgui = new ilPermanentLinkGUI(
				$this->permanent_link["type"],
				$this->permanent_link["id"],
				$this->permanent_link["append"],
				$this->permanent_link["target"]);
			$this->setVariable("PRMLINK", $plinkgui->getHTML());
		}
	}
}
?>
