<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	
	var $js_files = array(0 => "./Services/JavaScript/js/Basic.js");		// list of JS files that should be included
	var $js_files_vp = array("./Services/JavaScript/js/Basic.js" => true);	// version parameter flag
	var $js_files_batch = array("./Services/JavaScript/js/Basic.js" => 1);	// version parameter flag
	var $css_files = array();		// list of css files that should be included
	var $inline_css = array();
	var $admin_panel_commands = array();
	
	private $addFooter; // creates an output of the ILIAS footer

	protected static $il_cache = array();
	protected $message = "";
	
	protected $title_desc = "";	
	protected $title_url = "";
	protected $upper_icon = "";
	protected $tree_flat_link = "";
	protected $mount_webfolder = "";
	protected $stop_floating = "";
	protected $page_form_action = "";
	protected $page_actions = array();
	protected $permanent_link = false;
	protected $content_style_sheet = "";
	
	protected $title_alerts = array();
	protected $header_action;
	protected $lightbox = array();

	protected $translation_linked = false; // fix #9992: remember if a translation link is added

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
		$plugin = false, $a_use_cache = false)
	{
		global $ilias;
//echo "<br>-".$file."-";

		$this->activeBlock = "__global__";
		$this->vars = array();
		$this->addFooter = TRUE;
		
		$this->il_use_cache = $a_use_cache;
		$this->il_cur_key = $file."/".$in_module;

		$fname = $this->getTemplatePath($file, $in_module, $plugin);

		$this->tplName = basename($fname);
		$this->tplPath = dirname($fname);
		// template identifier e.g. "Services/Calendar/tpl.minical.html"
		$this->tplIdentifier = $this->getTemplateIdentifier($file, $in_module);
		
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
		$this->setBodyClass("std");

		return true;
	}

	/**
	 * @param string $file
	 * @param string $vers
	 */
	protected function fillJavascriptFile($file, $vers)
	{
		$this->setCurrentBlock("js_file");
		if($this->js_files_vp[$file])
		{
			$this->setVariable("JS_FILE", ilUtil::appendUrlParameterString($file, $vers));
		}
		else
		{
			$this->setVariable("JS_FILE", $file);
		}
		$this->parseCurrentBlock();
	}

	// overwrite their init function
    function init()
    {
        $this->free();
        $this->buildFunctionlist();
        
        $cache_hit = false;
        if ($this->il_use_cache)
        {
        	// cache hit
        	if (isset(self::$il_cache[$this->il_cur_key]) && is_array(self::$il_cache[$this->il_cur_key]))
        	{
        		$cache_hit = true;
//echo "cache hit";
        		$this->err = self::$il_cache[$this->il_cur_key]["err"];
        		$this->flagBlocktrouble = self::$il_cache[$this->il_cur_key]["flagBlocktrouble"];
        		$this->blocklist = self::$il_cache[$this->il_cur_key]["blocklist"];
        		$this->blockdata = self::$il_cache[$this->il_cur_key]["blockdata"];
        		$this->blockinner = self::$il_cache[$this->il_cur_key]["blockinner"];
        		$this->blockparents = self::$il_cache[$this->il_cur_key]["blockparents"];
        		$this->blockvariables = self::$il_cache[$this->il_cur_key]["blockvariables"];
        	}
        }
        
		if (!$cache_hit)
		{
			$this->findBlocks($this->template);
			$this->template = '';
			$this->buildBlockvariablelist();
	        if ($this->il_use_cache)
	        {
        		self::$il_cache[$this->il_cur_key]["err"] = $this->err;
        		self::$il_cache[$this->il_cur_key]["flagBlocktrouble"] = $this->flagBlocktrouble;
        		self::$il_cache[$this->il_cur_key]["blocklist"] = $this->blocklist;
        		self::$il_cache[$this->il_cur_key]["blockdata"] = $this->blockdata;
        		self::$il_cache[$this->il_cur_key]["blockinner"] = $this->blockinner;
        		self::$il_cache[$this->il_cur_key]["blockparents"] = $this->blockparents;
        		self::$il_cache[$this->il_cur_key]["blockvariables"] = $this->blockvariables;
	        }
		}
		
        // we don't need it any more
        $this->template = '';

    } // end func init
	
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
			$this->fillMessage();
		}

		if ($add_ilias_footer)
		{
			$this->addILIASFooter();
		}

		// set standard parts (tabs and title icon)
		if($add_standard_elements)
		{
			if ($this->blockExists("content") && $a_tabs)
			{
				// determine default screen id
				$this->getTabsHTML();
			}

			// to get also the js files for the main menu
			$this->getMainMenu();
			$this->initHelp();
			
			// these fill blocks in tpl.main.html
			$this->fillCssFiles();
			$this->fillInlineCss();
			$this->fillContentStyle();
			$this->fillBodyClass();

			// these fill just plain placeholder variables in tpl.main.html
			$this->setCurrentBlock("DEFAULT");
			$this->fillNewContentStyle();
			$this->fillContentLanguage();
			$this->fillWindowTitle();

			// these fill blocks in tpl.adm_content.html
			$this->fillHeader();
			$this->fillSideIcons();
			$this->fillScreenReaderFocus();
			$this->fillStopFloating();
			$this->fillLeftContent();
			$this->fillLeftNav();
			$this->fillRightContent();
			$this->fillAdminPanel();
			$this->fillToolbar();
			$this->fillPermanentLink();
			
			$this->setCenterColumnClass();

			// late loading of javascipr files, since operations above may add files
			$this->fillJavaScriptFiles();
			$this->fillOnLoadCode();

			// these fill just plain placeholder variables in tpl.adm_content.html
			if ($this->blockExists("content"))
			{
				$this->setCurrentBlock("content");
				if ($a_tabs)
				{
					$this->fillTabs();
				}
				$this->fillMainContent();
				if ($a_main_menu)
				{
					$this->fillMainMenu();
				}
				$this->fillLightbox();
				$this->parseCurrentBlock();
			}
		}

		if ($handle_referer)
		{
			$this->handleReferer();
		}

		if ($part == "DEFAULT")
		{
			$html = parent::get();
		}
		else
		{
			$html = parent::get($part);
		}

		// include the template output hook
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			
			$resp = $gui_class->getHTML("", "template_get", 
					array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $html));

			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				$html = $gui_class->modifyHTML($html, $resp);
			}
		}

		// fix #9992: save language usages as late as possible
		if ($this->translation_linked)
		{
			ilObjLanguageAccess::_saveUsages();
		}

		return $html;
	}

	/**
	* Set message. Please use ilUtil::sendInfo(), ilUtil::sendSuccess()
	* and ilUtil::sendFailure()
	*/
	function setMessage($a_type, $a_txt, $a_keep = false)
	{
		if (!in_array($a_type, array("info", "success", "failure", "question")) || $a_txt == "")
		{
			return;
		}
		if ($a_type == "question")
		{
			$a_type = "mess_question";
		}
		if (!$a_keep)
		{
			$this->message[$a_type] = $a_txt;
		}
		else
		{
			$_SESSION[$a_type] = $a_txt;
		}
	}
	
	function hasMessage($a_type)
	{		
		return (isset($this->message[$a_type]) && strlen($this->message[$a_type]));
	}
	
	function fillMessage()
	{
		global $lng;

		$ms = array("info", "success", "failure", "question");
		$out = "";
		
		foreach ($ms as $m)
		{
			$txt = "";
			if ($m == "question")
			{
				$m = "mess_question";
			}

			if (isset($_SESSION[$m]) && $_SESSION[$m] != "")
			{
				$txt = $_SESSION[$m];
			}
			else if (isset($this->message[$m]))
			{
				$txt = $this->message[$m];
			}

			if ($m == "mess_question")
			{
				$m = "question";
			}

			if ($txt != "")
			{
				$out.= $this->getMessageHTML($txt, $m);
			}
		
			if ($m == "question")
			{
				$m = "mess_question";
			}

			if (isset($_SESSION[$m]) && $_SESSION[$m])
			{
				unset($_SESSION[$m]);
			}
		}
		
		if ($out != "")
		{
			$this->setVariable("MESSAGE", $out);
		}
	}

	/**
	* Get HTML for a system message
	*/
	public function getMessageHTML($a_txt, $a_type = "info")
	{
		global $lng;
		$mtpl = new ilTemplate("tpl.message.html", true, true, "Services/Utilities");
		$mtpl->setCurrentBlock($a_type."_message");
		$mtpl->setVariable("TEXT", $a_txt);
		$mtpl->setVariable("MESSAGE_HEADING", $lng->txt($a_type."_message"));
		$mtpl->parseCurrentBlock();
		
		return $mtpl->get();
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
	function show($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false)
	{
		global $ilias, $ilTabs;

		// include yahoo dom per default
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initDom();
		
//echo "-".ilUtil::getP3PLocation()."-";
		//header('P3P: policyref="'.ilUtil::getP3PLocation().
		//	'", CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
		header('P3P: CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
		header("Content-type: " . $this->getContentType() . "; charset=UTF-8");

		$this->fillMessage();
		
		// display ILIAS footer
		if ($part !== false)
		{
			$this->addILIASFooter();
		}

		// set standard parts (tabs and title icon)
		$this->fillBodyClass();
		if ($a_fill_tabs)
		{
			if ($this->blockExists("content"))
			{
				// determine default screen id
				$this->getTabsHTML();
			}

			// to get also the js files for the main menu
			if (!$a_skip_main_menu)
			{
				$this->getMainMenu();
				$this->initHelp();
			}

			if($this->blockExists("content") && $this->variableExists('MAINMENU'))
			{
				global $tpl;
				
				include_once 'Services/Authentication/classes/class.ilSessionReminderGUI.php';
				$session_reminder_gui = new ilSessionReminderGUI(ilSessionReminder::createInstanceWithCurrentUserSession());
				$tpl->setVariable('SESSION_REMINDER', $session_reminder_gui->getHtml());
			}

			// these fill blocks in tpl.main.html
			$this->fillCssFiles();
			$this->fillInlineCss();
			//$this->fillJavaScriptFiles();
			$this->fillContentStyle();

			// these fill just plain placeholder variables in tpl.main.html
			$this->setCurrentBlock("DEFAULT");
			$this->fillNewContentStyle();
			$this->fillContentLanguage();
			$this->fillWindowTitle();

			// these fill blocks in tpl.adm_content.html
			$this->fillHeader();
			$this->fillSideIcons();
			$this->fillScreenReaderFocus();
			$this->fillStopFloating();
			$this->fillLeftContent();
			$this->fillLeftNav();
			$this->fillRightContent();
			$this->fillAdminPanel();
			$this->fillToolbar();
			$this->fillPermanentLink();
			
			$this->setCenterColumnClass();

			// late loading of javascipr files, since operations above may add files
			$this->fillJavaScriptFiles();
			$this->fillOnLoadCode();

			// these fill just plain placeholder variables in tpl.adm_content.html
			// these fill just plain placeholder variables in tpl.adm_content.html
			if ($this->blockExists("content"))
			{
				$this->setCurrentBlock("content");
				$this->fillTabs();
				$this->fillMainContent();
				$this->fillMainMenu();
				$this->fillLightbox();
				$this->parseCurrentBlock();
			}
		}
		
		if ($part == "DEFAULT" or is_bool($part))
		{
			$html = parent::get();
		}
		else
		{
			$html = parent::get($part);
		}
		
		// include the template output hook
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			
			$resp = $gui_class->getHTML("", "template_show", 
					array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $html));

			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				$html = $gui_class->modifyHTML($html, $resp);
			}
		}

		// fix #9992: save language usages as late as possible
		if ($this->translation_linked)
		{
			ilObjLanguageAccess::_saveUsages();
		}

		print $html;
		
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
	 	$contentLanguage = 'en';
		$rtl = array('ar','fa','ur','he');//, 'de'); //make a list of rtl languages
		/* rtl-review: add "de" for testing with ltr lang shown in rtl
		 * and set unicode-bidi to bidi-override for mirror effect */
		$textdir = 'ltr';
	 	if(is_object($ilUser))
	 	{
	 		if($ilUser->getLanguage())
	 		{
		 		$contentLanguage = $ilUser->getLanguage();
	 		}
	 		else if(is_object($lng))
	 		{
		 		$contentLanguage = $lng->getDefaultLanguage();
	 		}
	 	}
 		$this->setVariable('META_CONTENT_LANGUAGE', $contentLanguage);
		if (in_array($contentLanguage, $rtl)) { 
			$textdir = 'rtl'; 
		}
		$this->setVariable('LANGUAGE_DIRECTION', $textdir);
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
	
	/**
	 * Get tabs HTML
	 *
	 * @param
	 * @return
	 */
	function getTabsHTML()
	{
		global $ilTabs;
		
		if ($this->blockExists("tabs_outer_start"))
		{
			$this->sthtml = $ilTabs->getSubTabHTML();
			$this->thtml = $ilTabs->getHTML((trim($sthtml) == ""));
		}
	}
	
	
	function fillTabs()
	{
		global $ilias,$ilTabs;

		if ($this->blockExists("tabs_outer_start"))
		{
			$this->touchBlock("tabs_outer_start");
			$this->touchBlock("tabs_outer_end");
			$this->touchBlock("tabs_inner_start");
			$this->touchBlock("tabs_inner_end");

			if ($this->thtml != "")
			{
				$this->setVariable("TABS",$this->thtml);
			}
			$this->setVariable("SUB_TABS", $this->sthtml);
		}
	}
	
	function fillToolbar()
	{
		global $ilToolbar;

		$thtml = $ilToolbar->getHTML();
		if ($thtml != "")
		{
			$this->setCurrentBlock("toolbar_buttons");
			$this->setVariable("BUTTONS", $thtml);
			$this->parseCurrentBlock();
		}
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
	
	function fillJavaScriptFiles($a_force = false)
	{
		global $ilias, $ilTabs, $ilSetting, $ilUser;

		if (is_object($ilSetting))		// maybe this one can be removed
		{
			$vers = "vers=".str_replace(array(".", " "), "-", $ilSetting->get("ilias_version"));
		}
		if ($this->blockExists("js_file"))
		{
			// three batches
			for ($i=0; $i<=3; $i++)
			{
				reset($this->js_files);
				foreach($this->js_files as $file)
				{
					if (is_file($file) || substr($file, 0, 4) == "http" || substr($file, 0, 2) == "//" || $a_force)
					{
						if ($this->js_files_batch[$file] == $i)
						{
							$this->fillJavascriptFile($file, $vers);
						}
					}
					else if(substr($file, 0, 2) == './') // #13962
					{
						$url_parts = parse_url($file);
						if(is_file($url_parts['path']))
						{
							$this->fillJavascriptFile($file, $vers);
						}
					}
				}
			}
		}
	}

	/**
	 * Fill in the css file tags
	 * 
	 * @param boolean $a_force
	 */
	function fillCssFiles($a_force = false)
	{
		if (!$this->blockExists("css_file"))
		{
			return;
		}
		foreach($this->css_files as $css)
		{
			$filename = $css["file"];
			if (strpos($filename, "?") > 0) $filename = substr($filename, 0, strpos($filename, "?"));
			if (is_file($filename) || $a_force)
			{
				$this->setCurrentBlock("css_file");
				$this->setVariable("CSS_FILE", $css["file"]);
				$this->setVariable("CSS_MEDIA", $css["media"]);
				$this->parseCurrentBlock();
			}
		}
	}

	/**
	 * Fill in the inline css
	 *
	 * @param boolean $a_force
	 */
	function fillInlineCss()
	{
		if (!$this->blockExists("css_inline"))
		{
			return;
		}
		foreach($this->inline_css as $css)
		{
			$this->setCurrentBlock("css_file");
			$this->setVariable("CSS_INLINE", $css["css"]);
			//$this->setVariable("CSS_MEDIA", $css["media"]);
			$this->parseCurrentBlock();
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

		if($this->variableExists('MAINMENU'))
		{
			$ilMainMenu->setLoginTargetPar($this->getLoginTargetPar());
			$this->main_menu = $ilMainMenu->getHTML();
			$this->main_menu_spacer = $ilMainMenu->getSpacerClass();
		}
	}
	
	function fillMainMenu()
	{
		global $tpl;
		if($this->variableExists('MAINMENU'))
		{
			$tpl->setVariable("MAINMENU", $this->main_menu);
			$tpl->setVariable("MAINMENU_SPACER", $this->main_menu_spacer);
		}
	}

	/**
	 * Init help
	 *
	 * @param
	 * @return
	 */
	function initHelp()
	{
		include_once("./Services/Help/classes/class.ilHelpGUI.php");
		ilHelpGUI::initHelp($this);
	}
	
	
	/**
	* add ILIAS footer
	*/
	function addILIASFooter()
	{
		global $ilAuth;
		
		if (!$this->getAddFooter()) return;
		global $ilias, $ilClientIniFile, $ilCtrl, $ilDB, $ilSetting, $lng;
		
		$ftpl = new ilTemplate("tpl.footer.html", true, true, "Services/UICore");
		
		$ftpl->setVariable("ILIAS_VERSION", $ilias->getSetting("ilias_version"));
		
		$link_items = array();
		
		// imprint
		include_once "Services/Imprint/classes/class.ilImprint.php";
		if($_REQUEST["baseClass"] != "ilImprintGUI" && ilImprint::isActive())
		{
			include_once "Services/Link/classes/class.ilLink.php";
			$link_items[ilLink::_getStaticLink(0, "impr")] = array($lng->txt("imprint"), true);
		}

		// system support contacts
		include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContactsGUI.php");
		if (($l = ilSystemSupportContactsGUI::getFooterLink()) != "")
		{
			$link_items[$l] = array(ilSystemSupportContactsGUI::getFooterText(), false);
		}
				
		if (DEVMODE)
		{
			$link_items[ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=xhtml")] = array("Validate", true);
			$link_items[ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=accessibility")] = array("Accessibility", true);			
		}

        // output translation link (extended language maintenance)
        if ($ilSetting->get("lang_ext_maintenance") == "1")
        {
            include_once("Services/Language/classes/class.ilObjLanguageAccess.php");
            if (ilObjLanguageAccess::_checkTranslate() and !ilObjLanguageAccess::_isPageTranslation())
            {
				// fix #9992: remember linked translation instead of saving language usages here
				$this->translation_linked = true;
                $link_items[ilObjLanguageAccess::_getTranslationLink()] = array($lng->txt('translation'), true);
            }
        }

        $cnt = 0;
		foreach($link_items as $url => $caption)
		{
			$cnt ++;		
			if($caption[1])
			{
				$ftpl->touchBlock("blank");
			} 
			if($cnt < sizeof($link_items))
			{
				$ftpl->touchBlock("item_separator");
			}
			
			$ftpl->setCurrentBlock("items");
			$ftpl->setVariable("URL_ITEM", $url);
			$ftpl->setVariable("TXT_ITEM", $caption[0]);
			$ftpl->parseCurrentBlock();			
		}

		if (DEVMODE)
		{
			// execution time
			$t1 = explode(" ", $GLOBALS['ilGlobalStartTime']);
			$t2 = explode(" ", microtime());
			$diff = $t2[0] - $t1[0] + $t2[1] - $t1[1];

			$mem_usage = array();
			if(function_exists("memory_get_usage"))
			{
				$mem_usage[] =
					"Memory Usage: ".memory_get_usage()." Bytes";
			}
			if(function_exists("xdebug_peak_memory_usage"))
			{
				$mem_usage[] =
					"XDebug Peak Memory Usage: ".xdebug_peak_memory_usage()." Bytes";
			}
			$mem_usage[] = round($diff, 4)." Seconds";
			
			if (sizeof($mem_usage))
			{
				$ftpl->setVariable("MEMORY_USAGE", "<br>".implode(" | ", $mem_usage));
			}
			
			if (is_object($ilAuth) && isset($_SESSION[$ilAuth->_sessionName]) &&
				isset($_SESSION[$ilAuth->_sessionName]["timestamp"]))
			{
				$ftpl->setVariable("SESS_INFO", "<br />maxlifetime: ".
					ini_get("session.gc_maxlifetime")." (".
					(ini_get("session.gc_maxlifetime")/60)."), id: ".session_id()."<br />".
					"timestamp: ".date("Y-m-d H:i:s", $_SESSION[$ilAuth->_sessionName]["timestamp"]).
					", idle: ".date("Y-m-d H:i:s", $_SESSION[$ilAuth->_sessionName]["idle"]).
					"<br />expire: ".($exp = $ilClientIniFile->readVariable("session","expire")).
					" (".($exp/60)."), session ends at: ".
					date("Y-m-d H:i:s", $_SESSION[$ilAuth->_sessionName]["idle"] + $exp));
			}
			
			if (!empty($_GET["do_dev_validate"]) && $ftpl->blockExists("xhtml_validation"))
			{
				require_once("Services/XHTMLValidator/classes/class.ilValidatorAdapter.php");
				$template2 = clone($this);
//echo "-".ilValidatorAdapter::validate($template2->get(), $_GET["do_dev_validate"])."-";
				$ftpl->setCurrentBlock("xhtml_validation");
				$ftpl->setVariable("VALIDATION",
					ilValidatorAdapter::validate($template2->get("DEFAULT",
					false, false, false, true), $_GET["do_dev_validate"]));
				$ftpl->parseCurrentBlock();
			}
			
			// controller history
			if (is_object($ilCtrl) && $ftpl->blockExists("c_entry") &&
				$ftpl->blockExists("call_history"))
			{
				$hist = $ilCtrl->getCallHistory();
				foreach($hist as $entry)
				{
					$ftpl->setCurrentBlock("c_entry");
					$ftpl->setVariable("C_ENTRY", $entry["class"]);
					if (is_object($ilDB))
					{
						$file = $ilCtrl->lookupClassPath($entry["class"]);
						$add = $entry["mode"]." - ".$entry["cmd"];
						if ($file != "")
						{
							$add.= " - ".$file;
						}
						$ftpl->setVariable("C_FILE", $add);
					}
					$ftpl->parseCurrentBlock();
				}
				$ftpl->setCurrentBlock("call_history");
				$ftpl->parseCurrentBlock();
				
				// debug hack
				$debug = $ilCtrl->getDebug();
				foreach($debug as $d)
				{
					$ftpl->setCurrentBlock("c_entry");
					$ftpl->setVariable("C_ENTRY", $d);
					$ftpl->parseCurrentBlock();
				}
				$ftpl->setCurrentBlock("call_history");
				$ftpl->parseCurrentBlock();
			}
			
			// included files
			if (is_object($ilCtrl) && $ftpl->blockExists("i_entry") &&
				$ftpl->blockExists("included_files"))
			{
				$fs = get_included_files();
				$ifiles = array();
				$total = 0;
				foreach($fs as $f)
				{
					$ifiles[] = array("file" => $f, "size" => filesize($f));
					$total += filesize($f);
				}
				$ifiles = ilUtil::sortArray($ifiles, "size", "desc", true);
				foreach($ifiles as $f)
				{
					$ftpl->setCurrentBlock("i_entry");
					$ftpl->setVariable("I_ENTRY", $f["file"]." (".$f["size"]." Bytes, ".round(100 / $total * $f["size"], 2)."%)");
					$ftpl->parseCurrentBlock();
				}
				$ftpl->setCurrentBlock("i_entry");
				$ftpl->setVariable("I_ENTRY", "Total (".$total." Bytes, 100%)");
				$ftpl->parseCurrentBlock();
				$ftpl->setCurrentBlock("included_files");
				$ftpl->parseCurrentBlock();				
			}

		}

		// BEGIN Usability: Non-Delos Skins can display the elapsed time in the footer
		// The corresponding $ilBench->start invocation is in inc.header.php
		global $ilBench;
		$ilBench->stop("Core", "ElapsedTimeUntilFooter");
		$ftpl->setVariable("ELAPSED_TIME",
			", ".number_format($ilBench->getMeasuredTime("Core", "ElapsedTimeUntilFooter"),1).' seconds');
		// END Usability: Non-Delos Skins can display the elapsed time in the footer
		
		$this->setVariable("FOOTER", $ftpl->get());
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
			$url_parts = @parse_url($_SERVER["REQUEST_URI"]);
			if(!$url_parts)
			{
				$protocol = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://';
				$host = $_SERVER['HTTP_HOST'];
				$path = $_SERVER['REQUEST_URI'];
				$url_parts = @parse_url($protocol.$host.$path);
			}

			if (isset($url_parts["query"]) && preg_match("/cmd=gateway/",$url_parts["query"]) && (isset($_POST["cmd"]["create"])))
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
				$_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
				
			}
			else if (isset($url_parts["query"]) && preg_match("/cmd=post/",$url_parts["query"]) && (isset($_POST["cmd"]["create"])))
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
				if (isset($_GET['ref_id']))
				{
					$_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
				}
				else
				{
					$_SESSION['referer_ref_id'] = 0;
				}
							}
			else
			{
				$_SESSION["referer"] = $_SERVER["REQUEST_URI"];
				if (isset($_GET['ref_id']))
				{
					$_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
				}
				else
				{
					$_SESSION['referer_ref_id'] = 0;
				}
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
		return (isset($this->blockvariables["content"][$a_blockname]) ? true : false) | (isset($this->blockvariables[$a_blockname]) ? true : false);
	}
	
	private function variableExists($a_variablename)
	{
		return (isset($this->blockvariables["content"][$a_variablename]) ? true : false);
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

		$id = $this->getTemplateIdentifier($tplname, $in_module);
		$template = $this->getFile($tplfile);
		
		// include the template input hook
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			
			$resp = $gui_class->getHTML("", "template_add", 
					array("tpl_id" => $id, "tpl_obj" => $this, "html" => $template));

			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				$template = $gui_class->modifyHTML($template, $resp);
			}
		}
		
		return $this->addBlock($var, $block, $template);
	}

	
	/**
     * Reads a template file from the disk.
     *
	 * overwrites IT:loadTemplateFile to include the template input hook
	 *
     * @param    string      name of the template file
     * @param    bool        how to handle unknown variables.
     * @param    bool        how to handle empty blocks.
     * @access   public
     * @return   boolean    false on failure, otherwise true
     * @see      $template, setTemplate(), $removeUnknownVariables,
     *           $removeEmptyBlocks
     */
    function loadTemplatefile( $filename,
                               $removeUnknownVariables = true,
                               $removeEmptyBlocks = true )
    {
    	// copied from IT:loadTemplateFile
        $template = '';
        if (!$this->flagCacheTemplatefile ||
            $this->lastTemplatefile != $filename
        ) {
            $template = $this->getFile($filename);
        }
        $this->lastTemplatefile = $filename;
		// copied.	
        
		// new code to include the template input hook:
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			
			$resp = $gui_class->getHTML("", "template_load", 
					array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $template));

			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				$template = $gui_class->modifyHTML($template, $resp);
			}
		}
		// new.     
        
        // copied from IT:loadTemplateFile
        return $template != '' ?
                $this->setTemplate(
                        $template,$removeUnknownVariables, $removeEmptyBlocks
                    ) : false;
        // copied.
                    
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
		
		$fname = "";
		
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

			// use ilStyleDefinition instead of account to get the current skin
			include_once "Services/Style/classes/class.ilStyleDefinition.php";
			if (ilStyleDefinition::getCurrentSkin() != "default")
			{
				$fname = "./Customizing/global/skin/".
					ilStyleDefinition::getCurrentSkin()."/".$module_path.basename($a_tplname);
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
	
	/**
	 * get a unique template identifier
	 *
	 * The identifier is common for default or customized skins
	 * but distincts templates of different services with the same name.
	 *
	 * This is used by the UI plugin hook for template input/output
	 * 
	 * @param	string				$a_tplname		template name
	 * @param	string				$in_module		Component, e.g. "Modules/Forum"
	 * 			boolean				$in_module		or true, if component should be determined by ilCtrl
	 *
	 * @return	string				template identifier, e.g. "Services/Calendar/tpl.minical.html", "tpl.confirm.html"
	 */
	function getTemplateIdentifier($a_tplname, $a_in_module = false)
	{
		global $ilCtrl;
		
		// if baseClass functionality is used (ilias.php):
		// get template directory from ilCtrl
		if (!empty($_GET["baseClass"]) && $a_in_module === true)
		{
			$a_in_module = $ilCtrl->getModuleDir();
		}

		if (strpos($a_tplname,"/") === false)
		{
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
			else
			{
				$module_path = "";
			}
			
			return $module_path.basename($a_tplname);
		}
		else
		{
			return $a_tplname;
		}
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
		// always load jQuery
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();

		// always load ui framework
		include_once("./Services/UICore/classes/class.ilUIFramework.php");
		ilUIFramework::init();

		// always load Basic js
//		$this->addJavaScript("./Services/JavaScript/js/Basic.js",
//			true, 1);

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
	* sets title url in standard template
	*/
	function setTitleUrl($a_url)
	{
		$this->title_url = $a_url;
	}
	
	/**
	 * Set title color
	 *
	 * @param string $a_val color	
	 */
	function setTitleColor($a_val)
	{
		$this->title_color = $a_val;
	}
	
	/**
	 * Get title color
	 *
	 * @return string color
	 */
	function getTitleColor()
	{
		return $this->title_color;
	}

	/**
	 * Set alert properties
	 * @param array $a_props
	 * @return void
	 */
	public function setAlertProperties(array $a_props)
	{
		$this->title_alerts = $a_props;
	}

	/**
	 * Clear header
	 */
	function clearHeader()
	{
		$this->setTitle("");
		$this->setTitleIcon("");
		$this->setDescription("");
		$this->setAlertProperties(array());
	}

	
	/**
	* Fill header
	*/
	private function fillHeader()
	{
		global $lng, $ilUser, $ilCtrl;
		
		$icon = false;
		if ($this->icon_path != "")
		{
			$icon = true;
			$this->setCurrentBlock("header_image");
			if ($this->icon_desc != "")
			{
				$this->setVariable("IMAGE_DESC", $lng->txt("icon")." ".$this->icon_desc);
				$this->setVariable("IMAGE_ALT", $lng->txt("icon")." ".$this->icon_desc);
			}
			
			$this->setVariable("IMG_HEADER", $this->icon_path);
			$this->parseCurrentBlock();
			$header = true;
		}

		if ($this->title != "")
		{
			$this->title = ilUtil::stripScriptHTML($this->title);			
			$this->setVariable("HEADER", $this->title);
			if ($this->title_url != "")
			{
				$this->setVariable("HEADER_URL", ' href="'.$this->title_url.'"');
			}
			if ($this->getTitleColor() != "")
			{
				$this->setVariable("HEADER_COLOR", " style=\"color: #".$this->getTitleColor()."\"");
			}
			
			if ($icon)
			{
				//$this->setVariable("HICONCL", "ilHeaderHasIcon");
			}
			$header = true;
		}
		
		if ($header)
		{
			$this->setCurrentBlock("header_image");
			$this->parseCurrentBlock();
		}
		
		if ($this->title_desc != "")
		{
			$this->setCurrentBlock("header_desc");
			$this->setVariable("H_DESCRIPTION", $this->title_desc);
			$this->parseCurrentBlock();
		}
		
		$header = $this->getHeaderActionMenu();
		if ($header)
		{
			$this->setCurrentBlock("head_action_inner");
			$this->setVariable("HEAD_ACTION", $header);
			$this->parseCurrentBlock();
			$this->touchBlock("head_action");			
		}

		if(count((array) $this->title_alerts))
		{
			foreach($this->title_alerts as $alert)
			{
				$this->setCurrentBlock('header_alert');
				if(!($alert['propertyNameVisible'] === false))
				{
					$this->setVariable('H_PROP', $alert['property'].':');
				}
				$this->setVariable('H_VALUE', $alert['value']);
				$this->parseCurrentBlock();
			}
		}
		
		// add file upload drop zone in header
		if ($this->enable_fileupload != null)
		{
			$ref_id = $this->enable_fileupload;
			$upload_id = "dropzone_" . $ref_id;
			
			include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
			$upload = new ilFileUploadGUI($upload_id, $ref_id, true);
			
			$this->setVariable("FILEUPLOAD_DROPZONE_ID", " id=\"$upload_id\"");
			
			$this->setCurrentBlock("header_fileupload");
			$this->setVariable("HEADER_FILEUPLOAD_SCRIPT", $upload->getHTML());
			$this->parseCurrentBlock();
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

	function setBodyClass($a_class = "")
	{
		$this->body_class = $a_class;
	}
	
	function fillBodyClass()
	{
		if ($this->body_class != "" && $this->blockExists("body_class"))
		{
			$this->setCurrentBlock("body_class");
			$this->setVariable("BODY_CLASS", $this->body_class);
			$this->parseCurrentBlock();
		}
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
		$this->title_desc = $a_descr;
//		$this->setVariable("H_DESCRIPTION", $a_descr);
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
	 * Set header action menu
	 *
	 * @param string $a_gui $a_header
	 */
	function setHeaderActionMenu($a_header)
	{
		$this->header_action = $a_header;
	}
	
	/**
	 * Get header action menu
	 *
	 * @return int ref id
	 */
	function getHeaderActionMenu()
	{
		return $this->header_action;
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
	public function fillMainContent()
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
	
	private function setCenterColumnClass()
	{
		if (!$this->blockExists("center_col_width"))
		{
			return;
		}
		$center_column_class = "";
		if (trim($this->right_content) != "" && trim($this->left_content) != "") {
			$center_column_class = "two_side_col";
		}
		else if (trim($this->right_content) != "" || trim($this->left_content) != "") {
			$center_column_class = "one_side_col";
		}

		switch ($center_column_class)
		{
			case "one_side_col": $center_column_class = "col-sm-9"; break;
			case "two_side_col": $center_column_class = "col-sm-6"; break;
			default: $center_column_class = "col-sm-12"; break;
		}
		if (trim($this->left_content) != "")
		{
			$center_column_class.= " col-sm-push-3";
		}

		$this->setCurrentBlock("center_col_width");
		$this->setVariable("CENTER_COL", $center_column_class);
		$this->parseCurrentBlock();
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
			$left_col_class = (trim($this->right_content) == "")
				? "col-sm-3 col-sm-pull-9"
				: "col-sm-3 col-sm-pull-6";
			$this->setVariable("LEFT_COL_CLASS", $left_col_class);
			$this->parseCurrentBlock();
		}
	}

	/**
	 * Sets content of left navigation column
	 */
	function setLeftNavContent($a_content)
	{
		$this->left_nav_content = $a_content;
	}
		
	/**
	 * Fill left navigation frame
	 */
	function fillLeftNav()
	{
		if (trim($this->left_nav_content) != "")
		{
			$this->setCurrentBlock("left_nav");
			$this->setVariable("LEFT_NAV_CONTENT", $this->left_nav_content);
			$this->parseCurrentBlock();
			$this->touchBlock("left_nav_space");
		}
	}

	/**
	* Insert locator.
	*/
	function setLocator()
	{
		global $ilLocator, $lng, $ilPluginAdmin, $ilMainMenu;
		
		// blog/portfolio
		if($ilMainMenu->getMode() == ilMainMenuGUI::MODE_TOPBAR_REDUCED ||
			$ilMainMenu->getMode() == ilMainMenuGUI::MODE_TOPBAR_ONLY)
		{						
			$this->setVariable("LOCATOR", "");
			return;
		}

		$html = "";
		if (is_object($ilPluginAdmin))
		{
			include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
			$uip = new ilUIHookProcessor("Services/Locator", "main_locator",
				array("locator_gui" => $ilLocator));
			if (!$uip->replaced())
			{
				$html = $ilLocator->getHTML();
			}
			$html = $uip->getHTML($html);
		}
		else
		{
			$html = $ilLocator->getHTML();
		}
		
		$this->setVariable("LOCATOR", $html);
	}
	
	/**
	* sets tabs in standard template
	*/
	function setTabs($a_tabs_html)
	{
		if ($a_tabs_html != "" && $this->blockExists("tabs_outer_start"))
		{
			$this->touchBlock("tabs_outer_start");
			$this->touchBlock("tabs_outer_end");
			$this->touchBlock("tabs_inner_start");
			$this->touchBlock("tabs_inner_end");
			$this->setVariable("TABS", $a_tabs_html);
		}
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
	 * Set target parameter for login (public sector).
	 * This is used by the main menu
	 */
	public function setLoginTargetPar($a_val)
	{
		$this->login_target_par = $a_val;
	}

	/**
	 * Get target parameter for login
	 */
	public function getLoginTargetPar()
	{
		return $this->login_target_par;
	}

	/**
	* Accessibility focus for screen readers
	*/
	function fillScreenReaderFocus()
	{
		global $ilUser;

		if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization") && $this->blockExists("sr_focus"))
		{
			$this->touchBlock("sr_focus");
		}
	}
	
	/**
	* Fill side icons (upper icon, tree icon, webfolder icon)
	*/
	function fillSideIcons()
	{
		global $lng, $ilSetting;
		
		if ($this->upper_icon == "" && $this->tree_flat_link == ""
			&& $this->mount_webfolder == "")
		{
			return;
		}
		
		// upper icon
		// deprecated
		if ($this->upper_icon != "")
		{
			/*if ($this->upper_icon_frame != "")
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
			$this->parseCurrentBlock();*/
		}
		
		// tree/flat icon
		if ($this->tree_flat_link != "")
		{
			if ($this->left_nav_content != "")
			{
				$this->touchBlock("tree_lns");
			}
			
			$this->setCurrentBlock("tree_mode");
			$this->setVariable("LINK_MODE", $this->tree_flat_link);
			if ($ilSetting->get("tree_frame") == "right")
			{
				if ($this->tree_flat_mode == "tree")
				{
					$this->setVariable("IMG_TREE",ilUtil::getImagePath("icon_sidebar_on.svg"));
					$this->setVariable("RIGHT", "Right");
				}
				else
				{
					$this->setVariable("IMG_TREE",ilUtil::getImagePath("icon_sidebar_on.svg"));
					$this->setVariable("RIGHT", "Right");
				}
			}
			else
			{
				if ($this->tree_flat_mode == "tree")
				{
					$this->setVariable("IMG_TREE",ilUtil::getImagePath("icon_sidebar_on.svg"));
				}
				else
				{
					$this->setVariable("IMG_TREE",ilUtil::getImagePath("icon_sidebar_on.svg"));
				}
			}
			$this->setVariable("ALT_TREE",$lng->txt($this->tree_flat_mode."view"));
			$this->setVariable("TARGET_TREE", ilFrameTargetInfo::_getFrame("MainContent"));
			include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
			$this->setVariable("TREE_ACC_KEY",
				ilAccessKeyGUI::getAttribute(($this->tree_flat_mode == "tree")
					? ilAccessKey::TREE_ON
					: ilAccessKey::TREE_OFF));
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
			$this->setVariable("IMG_MOUNT_WEBFOLDER",ilUtil::getImagePath("ic_mount_webfolder.png"));
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
	function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2)
	{
		// three batches currently
		if ($a_batch < 1 || $a_batch > 3)
		{
			$a_batch = 2;
		}

		// ensure jquery files being loaded first
		if (is_int(strpos($a_js_file, "Services/jQuery")) ||
			is_int(strpos($a_js_file, "/jquery.js")) ||
			is_int(strpos($a_js_file, "/jquery-min.js")))
		{
			$a_batch = 0;
		}

		if (!in_array($a_js_file, $this->js_files))
		{
			$this->js_files[] = $a_js_file;
			$this->js_files_vp[$a_js_file] = $a_add_version_parameter;
			$this->js_files_batch[$a_js_file] = $a_batch;
		}
	}

	/**
	 * Reset javascript files
	 */
	function resetJavascript()
	{
		$this->js_files = array();
		$this->js_files_vp = array();
		$this->js_files_batch = array();
	}
	
	/**
	 * Reset css files
	 *
	 * @param
	 * @return
	 */
	function resetCss()
	{
		$this->css_files = array();
	}
	
	
	/**
	* Add on load code
	*/
	function addOnLoadCode($a_code, $a_batch = 2)
	{
		// three batches currently
		if ($a_batch < 1 || $a_batch > 3)
		{
			$a_batch = 2;
		}
		$this->on_load_code[$a_batch][] = $a_code;
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
	 * Add a css file that should be included in the header.
	 */
	function addInlineCss($a_css, $media = "screen")
	{
		$this->inline_css[] = array("css" => $a_css, "media" => $media);
	}
	
	/**
	 * Add lightbox html
	 */
	function addLightbox($a_html, $a_id)
	{
		$this->lightbox[$a_id] = $a_html;
	}

	/**
	 * Fill lightbox content
	 *
	 * @param
	 * @return
	 */
	function fillLightbox()
	{
		$html = "";

		foreach ($this->lightbox as $lb)
		{
			$html.= $lb;
		}
		$this->setVariable("LIGHTBOX", $html);
	}
	
	/**
	* Show admin view button
	*/
	function setPageActions($a_page_actions_html)
	{
		$this->page_actions = $a_page_actions_html;
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
	* @deprecated use addAdminPanelToolbar
	*/
	function addAdminPanelCommand($a_cmd, $a_txt, $a_arrow = false)
	{
		$this->admin_panel_commands[] =
			array("cmd" => $a_cmd, "txt" => $a_txt);
		if ($a_arrow)
		{
			$this->admin_panel_arrow = true;
		}
		$this->admin_panel_top_only = false;
	}

	/**
	 * Add admin panel commands as toolbar
	 * @param ilToolbarGUI $toolb
	 * @param bool $a_top_only
	 */
	public function addAdminPanelToolbar(ilToolbarGUI $toolb,$a_bottom_panel = true, $a_arrow = false)
	{
		$this->admin_panel_commands_toolbar = $toolb;
		$this->admin_panel_arrow = $a_arrow;
		$this->admin_panel_bottom = $a_bottom_panel;
	}
	
	/**
	* Put admin panel into template:
	* - creation selector
	* - admin view on/off button
	*/
	function fillAdminPanel()
	{
		global $lng, $ilHelp;
		
		$adm_view_cmp = $adm_cmds = $adm_view = false;
		
		$toolb = new ilToolbarGUI();
		
		// admin panel commands
		if ((count($this->admin_panel_commands) > 0))
		{
			foreach($this->admin_panel_commands as $cmd)
			{
				$toolb->addFormButton($cmd["txt"], $cmd["cmd"]);
			}
			$adm_cmds = true;
		}
		elseif($this->admin_panel_commands_toolbar instanceof  ilToolbarGUI)
		{
			$toolb = $this->admin_panel_commands_toolbar;
			$adm_cmds = true;
		}
		// Add arrow if desired
		if($this->admin_panel_arrow)
		{
			$toolb->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), $lng->txt("actions"));
		}

		if ($adm_cmds)
		{
			$this->fillPageFormAction();
			
			$this->setCurrentBlock("adm_view_components");
			$this->setVariable("ADM_PANEL1", $toolb->getHTML());
			$this->parseCurrentBlock();
			$adm_view_cmp = true;
		}
		
		// admin view button
		if ($this->page_actions != "")
		{
			$this->setVariable("PAGE_ACTIONS", $this->page_actions);
			$adm_view = true;
		}

		// creation selector
		// see: ilObjectAddNewItemGUI
		// placeholder "SELECT_OBJTYPE_REPOS" still needed!
		
		if ($adm_cmds and $this->admin_panel_bottom)
		{
			$this->setCurrentBlock("adm_view_components2");
			if ($this->admin_panel_arrow)
			{
				$toolb->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), $lng->txt("actions"));
			}
			$this->setVariable("ADM_PANEL2", $toolb->getHTML());

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

	/**
	* Fill add on load code
	*/
	function fillOnLoadCode()
	{
		for ($i = 1; $i <= 3; $i++)
		{
			if (is_array($this->on_load_code[$i]))
			{
				$this->setCurrentBlock("on_load_code");
				foreach ($this->on_load_code[$i] as $code)
				{
					$this->setCurrentBlock("on_load_code_inner");
					$this->setVariable("OLCODE", $code);
					$this->parseCurrentBlock();
				}
				$this->setCurrentBlock("on_load_code");
				$this->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * Get js onload code for ajax calls
	 * 
	 * @return string
	 */
	function getOnLoadCodeForAsynch()
	{
		$js = "";
		for ($i = 1; $i <= 3; $i++)
		{
			if (is_array($this->on_load_code[$i]))
			{
				foreach ($this->on_load_code[$i] as $code)
				{
					$js .= $code."\n";
				}
			}
		}		
		if($js)
		{
			return '<script type="text/javascript">'."\n".
				$js.
				'</script>'."\n";
		}
	}
	
	function setBackgroundColor($a_bg_color)
	{
		// :TODO: currently inactive, JF should discuss this
		return;
		
		if($a_bg_color != "")
		{
			$this->setVariable("FRAME_BG_COLOR", " style=\"background-color: #".$a_bg_color."\"");
		}
	}

	/**
	 * Set banner
	 * 	
	 * @param string $a_img banner full path (background image)	
	 * @param int $a_width banner width
	 * @param int $a_height banner height
	 * @param bool $a_export
	 */
	function setBanner($a_img, $a_width = 1370, $a_height = 100, $a_export = false)
	{		
		if($a_img)
		{
			if(!$a_export)
			{
				$a_img = ILIAS_HTTP_PATH."/".$a_img;
			}
			
			$this->setCurrentBlock("banner_bl");
			$this->setVariable("BANNER_WIDTH", $a_width); // currently not needed
			$this->setVariable("BANNER_HEIGHT", $a_height);
			$this->setVariable("BANNER_URL", $a_img);			
			$this->parseCurrentBlock();
		}
	}
	
	/**
	 * Add current object (repository/workspace) as target for login url
	 * 
	 * @return string
	 */
	public static function buildLoginTarget()
	{
		global $tree, $ilUser;
				
		$target_str = "";
		
		// repository
		if ($_GET["ref_id"] != "")
		{
			if ($tree->isInTree($_GET["ref_id"]) && $_GET["ref_id"] != $tree->getRootId())
			{
				$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
				$type = ilObject::_lookupType($obj_id);
				$target_str = $type."_".$_GET["ref_id"];
			}
		}
		// personal workspace
		else if ($_GET["wsp_id"] != "")
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";			
			$tree = new ilWorkspaceTree($ilUser->getId());									
			$obj_id = $tree->lookupObjectId((int)$_GET["wsp_id"]);
			if($obj_id)
			{
				$type = ilObject::_lookupType($obj_id);							
				$target_str = $type."_".(int)$_GET["wsp_id"]."_wsp";				
			}
		}
		// portfolio
		else if ($_GET["prt_id"] != "")
		{									
			$target_str = "prtf_".(int)$_GET["prt_id"];							
		}
		
		return $target_str;
	}
	
	/**
	 * Set variable
	 */
/*	function setVariable($a, $b = "")
	{
parent::setVariable($a, $b); return;
if ($a == "HEADER") mk();
		parent::setVariable($a, $b);
	}*/
	
	/**
	 * Reset all header properties: title, icon, description, alerts, action menu
	 */
	function resetHeaderBlock($a_reset_header_action = true)
	{
		$this->setTitle(null);
		$this->setTitleIcon(null);
		$this->setDescription(null);
		$this->setAlertProperties(array());		
		$this->enableDragDropFileUpload(null);
		
		// see setFullscreenHeader()
		if($a_reset_header_action)
		{
			$this->setHeaderActionMenu(null);
		}
	}	
	
	/**
	 * Enables the file upload into this object by dropping a file.
	 */
	function enableDragDropFileUpload($a_ref_id)
	{
		$this->enable_fileupload = $a_ref_id;
	}
}

?>
