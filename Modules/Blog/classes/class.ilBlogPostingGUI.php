<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Blog/classes/class.ilBlogPosting.php");

/**
 * Class ilBlogPosting GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilBlogPostingGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilBlogPostingGUI: ilRatingGUI, ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
 *
 * @ingroup ModulesBlog
 */
class ilBlogPostingGUI extends ilPageObjectGUI
{
	protected $node_id; // [int]
	protected $access_handler; // [object]
	protected $enable_public_notes; // [bool]

	/**
	 * Constructor
	 *
	 * @param int $a_node
	 * @param object $a_access_handler
	 * @param int $a_id
	 * @param int $a_old_nr
	 * @param bool $a_enable_notes
	 * @return ilBlogPostingGUI
	 */
	function __construct($a_node_id, $a_access_handler = null, $a_id = 0, $a_old_nr = 0, $a_enable_public_notes = true)
	{
		global $tpl, $lng;

		$lng->loadLanguageModule("blog");

		$this->node_id = $a_node_id;
		$this->access_handler = $a_access_handler;
		$this->enable_public_notes = (bool)$a_enable_public_notes;

		parent::__construct("blp", $a_id, $a_old_nr);

		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledWikiLinks(false);
		$this->setEnabledPCTabs(true);
	}

	/**
	 * Init internal data object
	 *
	 * @param string $a_parent_type
	 * @param int $a_id
	 * @param int $a_old_nr
	 */
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$this->setPageObject(new ilBlogPosting($a_id, $a_old_nr));
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilCtrl, $ilTabs;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		$posting = $this->getBlogPosting();

		switch($next_class)
		{
			case "ilnotegui":
				$this->getTabs();
				$ilTabs->setTabActive("pg");
				return $this->preview();
				break;

			/*
			case "ilratinggui":
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->getBlogPosting()->getParentId(), "blog",
					$this->getBlogPosting()->getId(), "blp");
				$this->ctrl->forwardCommand($rating_gui);
				$ilCtrl->redirect($this, "preview");
				break;
			*/
				
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("blp",
					$this->getPageObject()->getId(),
					$this->getPageObject()->old_nr);
				if($posting)
				{
					$this->setPresentationTitle($posting->getTitle());
				}
				return $ilCtrl->forwardCommand($page_gui);
				
			default:
				if($posting)
				{
					$this->setPresentationTitle($posting->getTitle());
				}
				return parent::executeCommand();
		}
	}

	/**
	 * Set blog posting
	 *
	 * @param ilBlogPosting $a_posting
	 */
	function setBlogPosting(ilBlogPosting $a_posting)
	{
		$this->setPageObject($a_posting);
	}

	/**
	 * Get blog posting
	 *
	 * @returnilBlogPosting
	 */
	function getBlogPosting()
	{
		return $this->getPageObject();
	}

	/**
	 * Centralized access management
	 *
	 * @param string $a_cmd
	 * @return bool
	 */
	protected function checkAccess($a_cmd)
	{
		return $this->access_handler->checkAccess($a_cmd, "", $this->node_id);
	}

	/**
	 * Preview blog posting
	 */
	function preview()
	{
		global $ilCtrl, $lng, $tpl, $ilUser, $ilToolbar;
		
		$this->getBlogPosting()->increaseViewCnt();
		
		$wtpl = new ilTemplate("tpl.blog_page_view_main_column.html",
			true, true, "Modules/Blog");
		
		// page commands
		 
		// delete
		$page_commands = false;
		if ($this->checkAccess("write"))
		{
			$wtpl->setCurrentBlock("page_command");
			$wtpl->setVariable("HREF_PAGE_CMD",
				$ilCtrl->getLinkTarget($this, "deleteBlogPostingConfirmationScreen"));
			$wtpl->setVariable("TXT_PAGE_CMD", $lng->txt("delete"));
			$wtpl->parseCurrentBlock();
		}		
		if ($page_commands)
		{
			$wtpl->setCurrentBlock("page_commands");
			$wtpl->parseCurrentBlock();
		}

		// notes
		$wtpl->setVariable("NOTES", $this->getNotesHTML($this->getBlogPosting(),
			true, $this->enable_public_notes, $this->checkAccess("write")));

		// permanent link
		$append = ($_GET["page"] != "")
			? "_".$_GET["page"]
			: "";
		include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
		$perma_link = new ilPermanentLinkGUI("blog", $this->node_id, $append);
		$wtpl->setVariable("PERMA_LINK", $perma_link->getHTML());
		
		$wtpl->setVariable("PAGE", parent::preview());

		$tpl->setLoginTargetPar("blog_".$this->node_id.$append);

		$ilCtrl->setParameter($this, "page", $this->getBlogPosting()->getId());

		return $wtpl->get();
	}

	/**
	 * Show current page
	 *
	 * @return string
	 */
	function showPage()
	{
		$this->setTemplateOutput(false);

		$this->setPresentationTitle($this->getBlogPosting()->getTitle());
		$this->getBlogPosting()->increaseViewCnt();
		
		return parent::showPage();
	}

	/**
	 * Finalizing output processing
	 *
	 * @param string $a_output
	 * @return string
	 */
	function postOutputProcessing($a_output)
	{
		// :TODO: anything?
		return $a_output;
	}

	/**
	 * Get tabs
	 * 
	 * @param string $a_activate
	 */
	function getTabs($a_activate = "")
	{
		global $ilTabs, $ilCtrl;

		// $ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", $this->getBlogPosting()->getParentId());
		$ilCtrl->setParameterByClass("ilobjbloggui", "page", $this->getBlogPosting()->getId());

		parent::getTabs($a_activate);
	}

	/**
	 * Delete blog posting confirmation screen
	 */
	function deleteBlogPostingConfirmationScreen()
	{
		global $tpl, $ilCtrl, $lng;

		if ($this->checkAccess("write"))
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$confirmation_gui = new ilConfirmationGUI();
			$confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
			$confirmation_gui->setHeaderText($lng->txt("blog_posting_deletion_confirmation"));
			$confirmation_gui->setCancel($lng->txt("cancel"), "cancelBlogPostingDeletion");
			$confirmation_gui->setConfirm($lng->txt("delete"), "confirmBlogPostingDeletion");
			
			$dtpl = new ilTemplate("tpl.blog_posting_deletion_confirmation.html", true,
				true, "Modules/Blog");
				
			$dtpl->setVariable("PAGE_TITLE", $this->getBlogPosting()->getTitle());
			
			// notes/comments
			include_once("./Services/Notes/classes/class.ilNote.php");
			$cnt_note_users = ilNote::getUserCount($this->getBlogPosting()->getParentId(),
				$this->getBlogPosting()->getId(), "wpg");
			$dtpl->setVariable("TXT_NUMBER_USERS_NOTES_OR_COMMENTS",
				$lng->txt("blog_number_users_notes_or_comments"));
			$dtpl->setVariable("TXT_NR_NOTES_COMMENTS", $cnt_note_users);
			
			$confirmation_gui->addItem("", "", $dtpl->get());
			
			$tpl->setContent($confirmation_gui->getHTML());
		}
	}

	/**
	 * Cancel blog posting deletion
	 */
	function cancelBlogPostingDeletion()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "preview");
	}
	
	/**
	* Delete the blog posting
	*/
	function confirmBlogPostingDeletion()
	{
		global $ilCtrl, $lng;

		if ($this->checkAccess("write"))
		{
			$this->getBlogPosting()->delete();
			ilUtil::sendSuccess($lng->txt("blog_posting_deleted"), true);
		}
		
		$ilCtrl->redirectByClass("ilobjbloggui", "render");
	}
}

?>