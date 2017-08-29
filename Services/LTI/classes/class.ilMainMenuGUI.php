<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace LTI;
use LTI\ilMainMenuGUI;

use 	\ilSearchSettings,
	\ilMainMenuSearchGUI,
	\ilUIHookProcessor,
	\ilRegistrationSettings,
	\ilSetting,
	\iljQueryUtil,
	\ilPlayerUtil,
	\ilNotificationOSDHandler,
	\ilGlyphGUI,
	\ilObjSystemFolder,
	\ilUtil,
	\ilSession,
	\ilLTIViewGUI;
	
//use LTI\ilSearchSettings;
//use LTI\ilMainMenuSearchGUI;
include_once("Services/Mail/classes/class.ilMailGlobalServices.php");
include_once("Services/LTI/classes/class.ilLTIViewGUI.php");
require_once("Services/MainMenu/classes/class.ilMainMenuGUI.php");


/**
* Handles display of the main menu for LTI
*
* @author Stefan Schneider
* @version $Id$
*/
class ilMainMenuGUI extends \ilMainMenuGUI
{
	/**
	* @param	string		$a_target				target frame
	* @param	boolean		$a_use_start_template	true means: target scripts should
	*												be called through start template
	*/
	
	function __construct($a_target = "_top", $a_use_start_template = false)
	{
		global $ilias, $rbacsystem, $ilUser, $ilLog, $DIC;
		$this->dic = $DIC;
		$this->log("LTI\\ilMainMenuGUI");
		
		// don't call parent constructor
		// parent::__construct($a_target, $a_use_start_template);	
		
		$this->tpl = new ilTemplate("tpl.main_menu.html", true, true,
			"Services/LTI");
		//$this->topbar_back_url = "http://www.google.de";
		$this->ilias = $ilias;
		$this->target = $a_target;
		$this->start_template = $a_use_start_template;
		
		$this->mail = false;
		
		$this->setMode(self::MODE_FULL);	
		
		//parent::__construct($a_target, $a_use_start_template);
		
		// member view : ToDo?
		/*
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		$set = ilMemberViewSettings::getInstance();		
		if($set->isActive())
		{
			$this->initMemberView();
		}		
		*/	
	}
	
	
	public function getSpacerClass()
	{
		return "ilFixedTopSpacerBarOnly";
	}
	
	/**
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting, $ilPluginAdmin;
		
		
		// append internal and external LTI css just before </body> end-tag
		$view = ilLTIViewGUI::getInstance();
		if ($this->dic['tpl']->blockExists('view_append_inline_css'))
		{
			$css_html = "";
			$css = $view->appendInlineCss();
			foreach ($css as $cssfile) 
			{
				$css_html .= "<style type=\"text/css\">\n";
				$css_html .= file_get_contents($cssfile);
				$css_html .= "</style>\n";
			}
			$this->dic['tpl']->setCurrentBlock("view_append_inline_css");
			$this->dic['tpl']->setVariable("APPEND_STYLES", $css_html);
			$this->dic['tpl']->parseCurrentBlock();
		}
		$view->render($this->tpl,'top_bar_header');
		$view->render($this->tpl,'view_nav');
		$view->render($this->tpl,'user_logged_in');
		//$view->checkMessages();
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		$this->tpl->parseCurrentBlock(); 
	}
	
	private function log($txt) 
	{
		$this->dic->logger()->root()->write($txt);
	}
	
}
