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
* Handles display of the main menu
*
* @author Alex Killing
* @version $Id$
* @package ilias-core
*/
class ilMainMenuGUI
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;
	var $tpl;
	var $target;
	var $start_template;


	/**
	* @param	string		$a_target				target frame
	* @param	boolean		$a_use_start_template	true means: target scripts should
	*												be called through start template
	*/
	function ilMainMenuGUI($a_target = "_top", $a_use_start_template = false)
	{
		global $ilias;
		
		
		$this->tpl = new ilTemplate("tpl.main_buttons.html", true, true);
		$this->ilias =& $ilias;
		$this->target = $a_target;
		$this->start_template = $a_use_start_template;

	}
	
	/**
	* @param	string	$a_active	"desktop"|"repository"|"search"|"mail"|"administration"
	*/
	function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	* set output template
	*/
	function setTemplate(&$tpl)
	{
		echo "ilMainMenu->setTemplate is deprecated. Use getHTML instead.";
		return;
		$this->tpl =& $tpl;
	}

	/**
	* get output template
	*/
	function getTemplate()
	{
		echo "ilMainMenu->getTemplate is deprecated. Use getHTML instead.";
		return;
		return $this->tpl;
	}

	/**
	* add menu template as block
	*/
	function addMenuBlock($a_var = "CONTENT", $a_block = "navigation")
	{
		echo "ilMainMenu->addMenuBlick is deprecated. Use getHTML instead.";
		return;
		$this->tpl->addBlockFile($a_var, $a_block, "tpl.main_buttons.html");
	}

	/**
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias;

		// administration button

		#if ($rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			$this->tpl->setCurrentBlock("userisadmin");
			$this->tpl->setVariable("IMG_ADMIN", ilUtil::getImagePath("navbar/admin.gif", false));
			$this->tpl->setVariable("IMG_SPACE_ADMIN", ilUtil::getImagePath("spacer.gif", false));
			$this->tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
			$this->tpl->setVariable("SCRIPT_ADMIN", $this->getScriptTarget("ilias.php?baseClass=ilAdministrationGUI"));
			$this->tpl->setVariable("TARGET_ADMIN", $this->target);
			if ($this->active == "administration")
			{
				$this->tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$this->tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$this->tpl->parseCurrentBlock();
		}

		include_once 'Services/Search/classes/class.ilSearchSettings.php';
		if($rbacsystem->checkAccess('search',ilSearchSettings::_getSearchSettingRefId()))
		{
			$this->tpl->setCurrentBlock("searchbutton");
			$this->tpl->setVariable("SCRIPT_SEARCH",$this->getScriptTarget('search.php'));
			#$this->tpl->setVariable("SCRIPT_SEARCH",$this->getScriptTarget('search_new.php'));
			$this->tpl->setVariable("TARGET_SEARCH",$this->target);
			$this->tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
			if ($this->active == "search")
			{
				$this->tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$this->tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$this->tpl->parseCurrentBlock();
		}

		// help button
		//$this->tpl->setCurrentBlock("userhelp");
		//$this->tpl->setVariable("TXT_HELP", $lng->txt("help"));
		//$this->tpl->setVariable("SCRIPT_HELP", "ilias.php?baseClass=ilHelpGUI");
		//$this->tpl->setVariable("TARGET_HELP", "ilias_help");
		//$this->tpl->parseCurrentBlock();


		// mail & desktop button
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			$this->tpl->setCurrentBlock("desktopbutton");
			$this->tpl->setVariable("IMG_DESK", ilUtil::getImagePath("navbar/desk.gif", false));
			$this->tpl->setVariable("IMG_SPACE_DESK", ilUtil::getImagePath("spacer.gif", false));
			$this->tpl->setVariable("TXT_PERSONAL_DESKTOP", $lng->txt("personal_desktop"));
			$this->tpl->setVariable("SCRIPT_DESK", $this->getScriptTarget("ilias.php?baseClass=ilPersonalDesktopGUI"));
			$this->tpl->setVariable("TARGET_DESK", $this->target);
			if ($this->active == "desktop")
			{
				$this->tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$this->tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$this->tpl->parseCurrentBlock();

			include_once "./classes/class.ilMail.php";
			
			$mail =& new ilMail($_SESSION["AccountId"]);

			if($rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId()))
			{
				$this->tpl->setCurrentBlock("mailbutton");
				$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("navbar/mail.gif", false));
				$this->tpl->setVariable("IMG_SPACE_MAIL", ilUtil::getImagePath("spacer.gif", false));
				$this->tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
				$this->tpl->setVariable("SCRIPT_MAIL", $this->getScriptTarget("mail_frameset.php"));
				$this->tpl->setVariable("TARGET_MAIL", $this->target);
				if ($this->active == "mail")
				{
					$this->tpl->setVariable("MM_CLASS", "MMActive");
				}
				else
				{
					$this->tpl->setVariable("MM_CLASS", "MMInactive");
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		$link_dir = (defined("ILIAS_MODULE"))
			? "../"
			: "";

		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			include_once 'Services/Registration/classes/class.ilRegistrationSettingsGUI.php';
			if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED)
			{
				$this->tpl->setCurrentBlock("registration_link");
				$this->tpl->setVariable("TXT_REGISTER",$lng->txt("register"));
				$this->tpl->setVariable("LINK_REGISTER", $link_dir."register.php?lang=".$ilias->account->getCurrentLanguage());
				$this->tpl->parseCurrentBlock();
			}

			$languages = $lng->getInstalledLanguages();
			
			foreach ($languages as $lang_key)
			{
				$this->tpl->setCurrentBlock("languages");
				$this->tpl->setVariable("LANG_KEY", $lang_key);
				$this->tpl->setVariable("LANG_NAME",
					ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("TXT_OK", $lng->txt("ok"));
			$this->tpl->setVariable("LANG_FORM_ACTION", "repository.php?ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));

			$this->tpl->setCurrentBlock("userisanonymous");
			$this->tpl->setVariable("TXT_NOT_LOGGED_IN",$lng->txt("not_logged_in"));
			$this->tpl->setVariable("TXT_LOGIN",$lng->txt("log_in"));
			$this->tpl->setVariable("LINK_LOGIN",
				$link_dir."login.php?cmd=force_login&lang=".$ilias->account->getCurrentLanguage());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("userisloggedin");
			$this->tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
			$this->tpl->setVariable("TXT_LOGOUT2",$lng->txt("logout"));
			$this->tpl->setVariable("LINK_LOGOUT2", $link_dir."logout.php?lang=".$ilias->account->getCurrentLanguage());
			$this->tpl->setVariable("USERNAME",$ilias->account->getFullname());
			$this->tpl->parseCurrentBlock();
		}


		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		//$this->tpl->setVariable("JS_BUTTONS", ilUtil::getJSPath("buttons.js"));

		// set tooltip texts
		$this->tpl->setVariable("SCRIPT_CATALOG", "repository.php?cmd=frameset&getlast=true");
		$this->tpl->setVariable("TXT_CATALOG", $lng->txt("repository"));
		if ($this->active == "repository" || $this->active == "")
		{
			$this->tpl->setVariable("MM_CLASS", "MMActive");
		}
		else
		{
			$this->tpl->setVariable("MM_CLASS", "MMInactive");
		}
		$this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));

		// temporary disable dateplaner
		//$this->tpl->setVariable("TXT_DP",  $lng->txt("dateplaner"));

		// set target frame
		$this->tpl->setVariable("TARGET", $this->target);

		$this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.png"));
		$this->tpl->setVariable("HEADER_BG_IMAGE", ilUtil::getImagePath("HeaderBackground.gif"));
		include_once("classes/class.ilObjSystemFolder.php");
		$this->tpl->setVariable("TXT_HEADER_TITLE", ilObjSystemFolder::_getHeaderTitle());

		// set link to return to desktop, not depending on a specific position in the hierarchy
		$this->tpl->setVariable("SCRIPT_START", $this->getScriptTarget("start.php"));

		$this->tpl->parseCurrentBlock();
	}

	/**
	* generates complete script target (private)
	*/
	function getScriptTarget($a_script)
	{
		global $ilias;

		$script = "./".$a_script;

		if ($this->start_template == true)
		{
			//if(is_file("./templates/".$ilias->account->skin."/tpl.start.html"))
			//{
				$script = "./start.php?script=".rawurlencode($script);
			//}
		}
		if (defined("ILIAS_MODULE"))
		{
			$script = ".".$script;
		}
		return $script;
	}
	// STATIC
	function _checkAdministrationPermission()
	{
		global $rbacsystem;

		if($rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
		{
			return true;
		}
		return false;
		// Allow all local admins to use the administration
		return count(ilUtil::_getObjectsByOperations('cat','cat_administrate_users')) ? true : false;
	}
	
	function getHTML()
	{
		$this->setTemplateVars();
		return $this->tpl->get();
	}
}
?>
