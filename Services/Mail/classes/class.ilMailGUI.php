<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once "Services/Mail/classes/class.ilMail.php";

/**
* @author Jens Conze
* @version $Id$
*
* @defgroup ServicesMail Services/Mail
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailGUI: ilMailFolderGUI, ilMailFormGUI, ilMailAddressbookGUI, ilMailOptionsGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilObjUserGUI
*/
class ilMailGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	private $tabs_gui = null;
	
	private $umail = null;
	private $exp = null;
	private $output = null;
	private $mtree = null;
	private $forwardClass = null;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $rbacsystem, $ilErr, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->ctrl->saveParameter($this, "mobj_id");
		$this->lng->loadLanguageModule("mail");

		$this->umail = new ilMail($ilUser->getId());

		// CHECK HACK
		if (!$rbacsystem->checkAccess("mail_visible", $this->umail->getMailObjectReferenceId()))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->WARNING);
		}
	}

	public function executeCommand()
	{
		global $ilUser;
		// Check for incomplete profile
		if($ilUser->getProfileIncomplete())
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}
		
		
		// check whether password of user have to be changed due to first login
		if( $ilUser->isPasswordChangeDemanded() )
		{
			ilUtil::sendInfo( $this->lng->txt('password_change_on_first_login_demand'), true );

			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		// check whether password of user is expired
		if( $ilUser->isPasswordExpired() )
		{
			$msg = $this->lng->txt('password_expired');
			$password_age = $ilUser->getPasswordAge();

			ilUtil::sendInfo( sprintf($msg,$password_age), true );

			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		if ($_GET["type"] == "search_res")
		{
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "searchResults");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["type"] == "attach")
		{
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "mailAttachment");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["type"] == "new")
		{
			$_SESSION['rcp_to'] = $_GET['rcp_to'];
			
			$_SESSION['rcp_cc'] = $_GET['rcp_cc'];
			$_SESSION['rcp_bcc'] = $_GET['rcp_bcc'];
			
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "mailUser");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["type"] == "reply")
		{
			$_SESSION['mail_id'] = $_GET['mail_id'];
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "replyMail");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["type"] == "read")
		{
			$_SESSION['mail_id'] = $_GET['mail_id'];
			$this->ctrl->setParameterByClass("ilmailfoldergui", "cmd", "showMail");
			$this->ctrl->redirectByClass("ilmailfoldergui");
		}

		if ($_GET["type"] == "deliverFile")
		{
			$_SESSION['mail_id'] = $_GET['mail_id'];
			$_SESSION['filename'] = ($_POST["filename"] ? $_POST["filename"] : $_GET["filename"]);
			$this->ctrl->setParameterByClass("ilmailfoldergui", "cmd", "deliverFile");
			$this->ctrl->redirectByClass("ilmailfoldergui");
		}

		if ($_GET["type"] == "role")
		{
			if (is_array($_POST['roles']))
			{
				$_SESSION['mail_roles'] = $_POST['roles'];
			}
			else if ($_GET["role"])
			{
				$_SESSION['mail_roles'] = array($_GET["role"]);
			}
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "mailRole");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["view"] == "my_courses")
		{
			$_SESSION['search_crs'] = $_GET['search_crs'];
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "searchCoursesTo");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if (isset($_GET["viewmode"]))
		{
			$_SESSION["viewmode"] = $_GET["viewmode"];
			$this->ctrl->setCmd("setViewMode");
		}
		
		$this->forwardClass = $this->ctrl->getNextClass($this);

		if ($this->ctrl->getCmd() != "showMenu" && $this->ctrl->getCmd() != "refresh")
		{
			$this->showHeader();
		}

		switch($this->forwardClass)
		{
			case 'ilmailmenugui':
				include_once 'Services/Mail/classes/class.ilMailMenuGUI.php';

				$this->ctrl->forwardCommand(new ilMailMenuGUI());
				break;

			case 'ilmailformgui':
				include_once 'Services/Mail/classes/class.ilMailFormGUI.php';

				$this->ctrl->forwardCommand(new ilMailFormGUI());
				break;

			case 'ilmailaddressbookgui':
				include_once 'Services/Mail/classes/class.ilMailAddressbookGUI.php';

				$this->ctrl->forwardCommand(new ilMailAddressbookGUI());
				break;

			case 'ilmailoptionsgui':
				include_once 'Services/Mail/classes/class.ilMailOptionsGUI.php';

				$this->ctrl->forwardCommand(new ilMailOptionsGUI());
				break;

			case 'ilmailfoldergui':
				include_once 'Services/Mail/classes/class.ilMailFolderGUI.php';

				$this->ctrl->forwardCommand(new ilMailFolderGUI());
				break;

			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "setViewMode";
				}

				$this->$cmd();
				break;

		}
		return true;
	}

	private function setViewMode()
	{
		if ($_GET["target"] == "")
		{
			$_GET["target"] = "ilmailfoldergui";
		}
		if ($_SESSION["viewmode"] == "tree")
		{
			include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
			$fs_gui = new ilFramesetGUI();
			$fs_gui->setFramesetTitle($this->lng->txt("mail"));
			$fs_gui->setMainFrameName("content");
			$fs_gui->setSideFrameName("tree");
		
			$this->ctrl->setParameter($this, "cmd", "showMenu");
			$this->ctrl->setParameter($this, "mexpand", 1);
			$fs_gui->setSideFrameSource($this->ctrl->getLinkTarget($this));
			$this->ctrl->clearParameters($this);
		
			if ($_GET["type"] == "add_subfolder")
			{
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTargetByClass($_GET["target"], "addSubFolder"));
			}
			else if ($_GET["type"] == "enter_folderdata")
			{
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTargetByClass($_GET["target"], "enterFolderData"));
			}
			else if ($_GET["type"] == "confirmdelete_folderdata")
			{
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTargetByClass($_GET["target"], "confirmDeleteFolder"));
			}
			else
			{
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTargetByClass($_GET["target"]));
			}
			$fs_gui->show();
		}
		else
		{
			$this->ctrl->redirectByClass($_GET["target"]);
		}
	}
	
	public function refresh()
	{
		$this->showMenu();
	}
	
	private function showHeader()
	{
		global $ilMainMenu;

		$ilMainMenu->setActive("mail");

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mail_b.gif"));
		
		// display infopanel if something happened
		ilUtil::infoPanel();

		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

		// FOLDER
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE", $this->forwardClass == "ilmailfoldergui"
			? "tabactive"
			: "tabinactive");
		#$this->ctrl->setParameterByClass("ilmailfoldergui", "type", "new");
		$this->tpl->setVariable("TAB_LINK", $this->ctrl->getLinkTargetByClass("ilmailfoldergui"));
		$this->ctrl->clearParametersByClass("ilmailfoldergui");
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("fold"));
		$this->tpl->parseCurrentBlock();
		
		
		// COMPOSE
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVAriable("TAB_TYPE", $this->forwardClass == "ilmailformgui"
			? "tabactive"
			: "tabinactive");
		$this->ctrl->setParameterByClass("ilmailformgui", "type", "new");
		$this->tpl->setVariable("TAB_LINK", $this->ctrl->getLinkTargetByClass("ilmailformgui"));
		$this->ctrl->clearParametersByClass("ilmailformgui");
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("compose"));
		$this->tpl->parseCurrentBlock();
		
		// ADDRESSBOOK
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVAriable("TAB_TYPE", $this->forwardClass == "ilmailaddressbookgui"
			? "tabactive"
			: "tabinactive");
		$this->tpl->setVariable("TAB_LINK", $this->ctrl->getLinkTargetByClass("ilmailaddressbookgui"));
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("mail_addressbook"));
		$this->tpl->parseCurrentBlock();
		
		// OPTIONS
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVAriable("TAB_TYPE", $this->forwardClass == "ilmailoptionsgui"
			? "tabactive"
			: "tabinactive");
		$this->tpl->setVariable("TAB_LINK", $this->ctrl->getLinkTargetByClass("ilmailoptionsgui"));
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("options"));
		$this->tpl->parseCurrentBlock();
		
		// FLATVIEW <-> TREEVIEW
		if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
		{
			$this->ctrl->setParameter($this, "viewmode", "tree");
			$this->tpl->setTreeFlatIcon(
				$this->ctrl->getLinkTarget($this),
				"tree");
			//$this->tpl->setVariable("IMG_TREE", ilUtil::getImagePath("ic_treeview.gif"));
			//$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->ctrl->setParameter($this, "viewmode", "flat");
			$this->tpl->setTreeFlatIcon(
				$this->ctrl->getLinkTarget($this),
				"flat");
			//$this->tpl->parseCurrentBlock();
		}
		$this->ctrl->clearParameters($this);
		$this->tpl->setCurrentBlock("tree_icons");
		$this->tpl->parseCurrentBlock();
	}

	private function showMenu()
	{
		global $ilUser;
		
		require_once "Services/Mail/classes/class.ilMailExplorer.php";

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		$this->exp = new ilMailExplorer($this->ctrl->getLinkTargetByClass("ilmailfoldergui"),$ilUser->getId());
		$this->exp->setTargetGet("mobj_id");

		if ($_GET["mexpand"] == "")
		{
			$this->mtree = new ilTree($ilUser->getId());
			$this->mtree->setTableNames('mail_tree','mail_obj_data');
			$expanded = $this->mtree->readRootId();
		}
		else
			$expanded = $_GET["mexpand"];
			
		$this->exp->setExpand($expanded);
		
		//build html-output
		$this->exp->setOutput(0);
		$this->output = $this->exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("mail_folders"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$this->output);
		$this->ctrl->setParameter($this, "mexpand", $_GET["mexpand"]);
		$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this, 'showMenu'));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->show(false);
	}
}

?>
