<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Mail/classes/class.ilMail.php";
require_once 'Services/Mail/classes/class.ilMailFormCall.php';

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
	/**
	 * @var string
	 */
	const VIEWMODE_SESSION_KEY = 'mail_viewmode';
	
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
		
		if(isset($_POST['mobj_id']) && (int)$_POST['mobj_id'])
		{
			$_GET['mobj_id'] = $_POST['mobj_id'];
		}
		
		$this->ctrl->saveParameter($this, "mobj_id");
		$this->lng->loadLanguageModule("mail");

		$this->umail = new ilMail($ilUser->getId());

		// CHECK HACK
		if (!$rbacsystem->checkAccess('internal_mail', $this->umail->getMailObjectReferenceId()))
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
		
		// check whether password of user have to be changed
		// due to first login or password of user is expired
		if( $ilUser->isPasswordChangeDemanded() || $ilUser->isPasswordExpired() )
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		if ($_GET["type"] == "search_res")
		{
			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "searchResults");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["type"] == "attach")
		{
            ilMailFormCall::storeReferer($_GET);

			$this->ctrl->setParameterByClass("ilmailformgui", "cmd", "mailAttachment");
			$this->ctrl->redirectByClass("ilmailformgui");
		}

		if ($_GET["type"] == "new")
		{
			$_SESSION['rcp_to'] = $_GET['rcp_to'];			
			$_SESSION['rcp_cc'] = $_GET['rcp_cc'];
			$_SESSION['rcp_bcc'] = $_GET['rcp_bcc'];

            ilMailFormCall::storeReferer($_GET);
			
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
		
		if ($_GET["type"] == "message_sent")
		{
			ilUtil::sendInfo($this->lng->txt('mail_message_send'), true);
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

            ilMailFormCall::storeReferer($_GET);

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
			ilSession::set(self::VIEWMODE_SESSION_KEY, $_GET["viewmode"]);
			$this->ctrl->setCmd("setViewMode");
		}
		
		$this->forwardClass = $this->ctrl->getNextClass($this);
		if ($this->ctrl->getCmd() != "showMenu" && $this->ctrl->getCmd() != "refresh")
		{
			$this->showHeader();
		}

		include_once "Services/jQuery/classes/class.iljQueryUtil.php";
		iljQueryUtil::initjQuery();

		switch($this->forwardClass)
		{			
			case 'ilmailformgui':
				include_once 'Services/Mail/classes/class.ilMailFormGUI.php';

				$this->ctrl->forwardCommand(new ilMailFormGUI());
				break;

			case 'ilmailaddressbookgui':
				include_once 'Services/Contact/classes/class.ilMailAddressbookGUI.php';

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
		if('tree' == ilSession::get(self::VIEWMODE_SESSION_KEY))
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
//echo "-".$_GET["target"]."-";
			$this->ctrl->redirectByClass($_GET["target"]);
		}
	}
	
	public function refresh()
	{
		$this->showMenu();
	}
	
	private function showHeader()
	{
		global $ilMainMenu, $ilTabs, $ilHelp;
		
		$ilHelp->setScreenIdComponent("mail");

		$ilMainMenu->setActive("mail");

//		$this->tpl->getStandardTemplate();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mail_b.png"));
		
		// display infopanel if something happened
		ilUtil::infoPanel();
		
		$ilTabs->addTarget('fold', $this->ctrl->getLinkTargetByClass('ilmailfoldergui'));		
		$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'new');
		$ilTabs->addTarget('compose', $this->ctrl->getLinkTargetByClass('ilmailformgui'));
		$this->ctrl->clearParametersByClass('ilmailformgui');
		$ilTabs->addTarget('mail_addressbook', $this->ctrl->getLinkTargetByClass('ilmailaddressbookgui'));
		$ilTabs->addTarget('options', $this->ctrl->getLinkTargetByClass('ilmailoptionsgui'));
		
		switch($this->forwardClass)
		{				
			case 'ilmailformgui':
				$ilTabs->setTabActive('compose');
				break;
				
			case 'ilmailaddressbookgui':
				$ilTabs->setTabActive('mail_addressbook');
				break;
				
			case 'ilmailoptionsgui':
				$ilTabs->setTabActive('options');
				break;
				
			case 'ilmailfoldergui':
			default:
				$ilTabs->setTabActive('fold');
				break;
			
		}
		if(isset($_GET['message_sent'])) $ilTabs->setTabActive('fold');
		
		if('tree' != ilSession::get(self::VIEWMODE_SESSION_KEY))
		{
			$this->ctrl->setParameter($this, 'viewmode', 'tree');
			$this->tpl->setTreeFlatIcon($this->ctrl->getLinkTarget($this), 'tree');
		}
		else
		{
			$this->ctrl->setParameter($this, 'viewmode', 'flat');
			$this->tpl->setTreeFlatIcon($this->ctrl->getLinkTarget($this), 'flat');
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
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
		
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
