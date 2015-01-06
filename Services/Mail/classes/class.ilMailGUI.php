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
		$_GET['mobj_id'] = (int)$_GET['mobj_id'];
		
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
		
		$this->showHeader();
		
		if('tree' == ilSession::get(self::VIEWMODE_SESSION_KEY) &&
			$this->ctrl->getCmd() != "showExplorer")
		{
			$this->showExplorer();
		}

		include_once "Services/jQuery/classes/class.iljQueryUtil.php";
		iljQueryUtil::initjQuery();

		// always load ui framework
		include_once("./Services/UICore/classes/class.ilUIFramework.php");
		ilUIFramework::init();

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

		if($_GET['type'] == 'redirect_to_read')
		{
			$this->ctrl->setParameterByClass('ilMailFolderGUI', 'mail_id', (int)$_GET['mail_id']);
			$this->ctrl->redirectByClass('ilMailFolderGUI', 'showMail');
		}
		else if ($_GET["type"] == "add_subfolder")
		{
			$this->ctrl->redirectByClass($_GET["target"], "addSubFolder");
		}
		else if ($_GET["type"] == "enter_folderdata")
		{
			$this->ctrl->redirectByClass($_GET["target"], "enterFolderData");
		}
		else if ($_GET["type"] == "confirmdelete_folderdata")
		{
			$this->ctrl->redirectByClass($_GET["target"], "confirmDeleteFolder");
		}
		else
		{
			$this->ctrl->redirectByClass($_GET["target"]);
		}
	}
	
	private function showHeader()
	{
		global $ilMainMenu, $ilTabs, $ilHelp;
		
		$ilHelp->setScreenIdComponent("mail");

		$ilMainMenu->setActive("mail");

//		$this->tpl->getStandardTemplate();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mail.svg"));
		
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
			$tree_state = 'tree';
		}
		else
		{
			$tree_state = 'flat';
		}

		if($this->isMailDetailCommand($this->ctrl->getCmd()))
		{
			$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
			$this->ctrl->setParameter($this, 'type', 'redirect_to_read');
		}

		$this->ctrl->setParameter($this, 'viewmode', $tree_state);
		$this->tpl->setTreeFlatIcon($this->ctrl->getLinkTarget($this), $tree_state);

		$this->ctrl->clearParameters($this);
		$this->tpl->setCurrentBlock("tree_icons");
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @param string $cmd
	 * @return bool
	 */
	private function isMailDetailCommand($cmd)
	{
		return in_array(strtolower($cmd), array('showmail')) && isset($_GET['mail_id']) && (int)$_GET['mail_id'];
	}

	private function showExplorer()
	{
		global $ilUser;
		
		require_once "Services/Mail/classes/class.ilMailExplorer.php";
		$exp = new ilMailExplorer($this, "showExplorer", $ilUser->getId());		
		if (!$exp->handleCommand())
		{			
			$this->tpl->setLeftNavContent($exp->getHTML());
		}
	}
}

?>
