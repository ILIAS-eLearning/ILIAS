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
	\ilSession;
	
//use LTI\ilSearchSettings;
//use LTI\ilMainMenuSearchGUI;
include_once("Services/Mail/classes/class.ilMailGlobalServices.php");
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
		$this->ilias = $ilias;
		$this->target = $a_target;
		$this->start_template = $a_use_start_template;
		
		$this->mail = false;
		
		$this->setMode(self::MODE_TOPBAR_REDUCED); // ?	
		
		$this->log($this->mode);
		
		$lti_cmd = $_GET['lti_cmd'];
		switch ($lti_cmd) 
		{
			case "exit" :
				$this->exitLti();
				break;
		}
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
		
		$this->tpl->addBlockFile("USERLOGGEDIN","userisloggedin","tpl.user_logged_in.html","Services/LTI");
		$this->tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
		$user_img_src = $ilias->account->getPersonalPicturePath("small", true);
		$user_img_alt = $ilias->account->getFullname();
		$this->tpl->setVariable("USER_IMG", ilUtil::img($user_img_src, $user_img_alt));
		$this->tpl->setVariable("TXT_LTI_EXIT",$lng->txt("lti_exit_session"));
		$this->tpl->setVariable("LINK_LTI_EXIT", "./ilias.php?lti_cmd=exit");
		
		if(!$this->topbar_back_url)
		{
			include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
			$header_top_title = ilObjSystemFolder::_getHeaderTitle();
			if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title"))
			{
				$this->tpl->setCurrentBlock("header_top_title");
				// php7-workaround alex: added phpversion() to help during development of php7 compatibility
				$this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("header_back_bl");
			$this->tpl->setVariable("URL_HEADER_BACK", $this->topbar_back_url);
			$this->tpl->setVariable("TXT_HEADER_BACK", $this->topbar_back_caption
				? $this->topbar_back_caption
				: $lng->txt("back"));
			$this->tpl->parseCurrentBlock();			
		}

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		// $this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
		$this->tpl->setVariable("HEADER_URL", $this->getHeaderURL());
		$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
		
		
		//include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");

		$this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
		
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	 * exit LTI session and if defined redirecting to returnUrl
	 * ToDo: Standard Template with delos ...
	 */
	private function exitLti() 
	{
		global $lng;
		$this->dic->logger()->root()->write("exitLti");
		if ($this->getSessionValue('lti_launch_presentation_return_url') === '') {
			$tplExit = new ilTemplate("tpl.lti_exit.html", true, true, "Services/LTI");
			$tplExit->setVariable('TXT_EXITED_TITLE',$lng->txt('exited_title'));
			$tplExit->setVariable('TXT_EXITED',$lng->txt('exited'));
			$html = $tplExit->get();
			$this->logout();
			print $html;
			exit;
		}
		else {
			$this->logout();
			header('Location: ' . $_SESSION['lti_launch_presentation_return_url']);
			exit; 
		}	
	}
	
	/**
	 * logout ILIAS and destroys Session and ilClientId cookie
	 */
	private function logout() 
	{
		//$DIC->logger()->root()->write("logout");
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);		
		$this->dic['ilAuthSession']->logout();
		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		ilUtil::setCookie("ilClientId","");
	}
	
	/**
	 * get session value != ''
	 * 
	 * @param $sess_key string 
	 * @return string
	 */ 
	private function getSessionValue($sess_key) 
	{
		if (isset($_SESSION[$sess_key]) && $_SESSION[$sess_key] != '') {
			return $_SESSION[$sess_key];
		}
		else {
			return '';
		}
	}
	
	private function log($txt) 
	{
		$this->dic->logger()->root()->write($txt);
	}
	
}
