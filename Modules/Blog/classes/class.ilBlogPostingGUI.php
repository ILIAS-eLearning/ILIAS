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
		
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledWikiLinks(false);
		$this->setEnabledPCTabs(true);
		
		$this->setEnabledActivation(true);
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
		
		// needed for notification
		$this->getBlogPosting()->setBlogNodeId($this->node_id, $this->isInWorkspace());
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilCtrl, $ilTabs, $ilLocator, $tpl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		$posting = $this->getBlogPosting();
		$ilCtrl->setParameter($this, "blpg", $posting->getId());
		
		switch($next_class)
		{
			case "ilnotegui":				
				// $this->getTabs();
				// $ilTabs->setTabActive("pg");
				return $this->previewFullscreen();

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
					
					$tpl->setTitle(ilObject::_lookupTitle($this->getBlogPosting()->getBlogId())." - ".
						$posting->getTitle());
					
					$ilLocator->addItem($posting->getTitle(),
						$ilCtrl->getLinkTarget($this, "preview"));							
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
	function preview($a_mode = null)
	{
		global $ilCtrl, $lng, $tpl, $ilUser, $ilToolbar;
		
		$this->getBlogPosting()->increaseViewCnt();
		
		$wtpl = new ilTemplate("tpl.blog_page_view_main_column.html",
			true, true, "Modules/Blog");
		
		// page commands		 
		if(!$a_mode)
		{		
			/*
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
			*/ 
		}
		else
		{
			$callback = array($this, "observeNoteAction");
			
			// notes
			$wtpl->setVariable("NOTES", $this->getNotesHTML($this->getBlogPosting(),
				false, $this->enable_public_notes, $this->checkAccess("write"), $callback));
		}

		// permanent link
		if($a_mode != "embedded")
		{
			$append = ($_GET["blpg"] != "")
				? "_".$_GET["blpg"]
				: "";
			if($this->isInWorkspace())
			{
				$append .= "_wsp";
			}
			include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
			$perma_link = new ilPermanentLinkGUI("blog", $this->node_id, $append);
			$wtpl->setVariable("PERMA_LINK", $perma_link->getHTML());
		}
		
		$wtpl->setVariable("PAGE", parent::preview());

		$tpl->setLoginTargetPar("blog_".$this->node_id.$append);

		$ilCtrl->setParameter($this, "blpg", $this->getBlogPosting()->getId());

		return $wtpl->get();
	}
	
	/**
	 * Needed for portfolio/blog handling
	 * 
	 * @return string
	 */
	function previewEmbedded()
	{		
		return $this->preview("embedded");
	}
	
	/**
	 * Needed for portfolio/blog handling
	 * 
	 * @return string
	 */
	function previewFullscreen()
	{		
		$this->add_date = true;
		return $this->preview("fullscreen");
	}

	/**
	 * Embedded posting in portfolio
	 *
	 * @return string
	 */
	function showPage()
	{
		$this->setTemplateOutput(false);

		if (!$this->getAbstractOnly())
		{
			$this->setPresentationTitle($this->getBlogPosting()->getTitle());
		}
		$this->getBlogPosting()->increaseViewCnt();
		
		return parent::showPage();
	}
	
	/**
	 * Is current page part of personal workspace blog?
	 * 
	 * @return bool 
	 */
	protected function isInWorkspace()
	{
		return stristr(get_class($this->access_handler), "workspace");
	}

	/**
	 * Finalizing output processing
	 *
	 * @param string $a_output
	 * @return string
	 */
	function postOutputProcessing($a_output)
	{
		// #8626/#9370
		if(($this->getOutputMode() == "preview" || $this->getOutputMode() == "offline") 
			&& !$this->getAbstractOnly() && $this->add_date)
		{			
			if(!$this->isInWorkspace())		
			{
				$author = "";
				$author_id = $this->getBlogPosting()->getAuthor();
				if($author_id)
				{
					include_once "Services/User/classes/class.ilUserUtil.php";
					$author = ilUserUtil::getNamePresentation($author_id)." - ";
				}		
			}
			
			// prepend creation date
			$rel = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			$prefix = "<div class=\"il_BlockInfo\" style=\"text-align:right\">".
				$author.ilDatePresentation::formatDate($this->getBlogPosting()->getCreated()).
				"</div>";			
			ilDatePresentation::setUseRelativeDates($rel);
			
			$a_output = $prefix.$a_output;
		}
		
		return $a_output;
	}

	/**
	 * Get tabs
	 * 
	 * @param string $a_activate
	 */
	function getTabs($a_activate = "")
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilobjbloggui", "blpg", $this->getBlogPosting()->getId());

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
			// delete all md keywords
			$md_section = $this->getMDSection();
			foreach($md_section->getKeywordIds() as $id)
			{
				$md_key = $md_section->getKeyword($id);				
				$md_key->delete();				
			}
			
			$this->getBlogPosting()->delete();
			ilUtil::sendSuccess($lng->txt("blog_posting_deleted"), true);
		}
		
		$ilCtrl->redirectByClass("ilobjbloggui", "render");
	}
	
	function editTitle($a_form = null)
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("edit");
		
		if(!$a_form)
		{
			$a_form = $this->initTitleForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function updateTitle()
	{
		global $ilCtrl, $lng;
		
		$form = $this->initTitleForm();
		if($form->checkInput())
		{
			$page = $this->getPageObject();
			$page->setTitle($form->getInput("title"));
			$page->update();			
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "preview");
		}
		
		$form->setValuesByPost();
		$this->editTitle($form);		
	}
	
	function initTitleForm()
	{
		global $lng, $ilCtrl;
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt('blog_rename_posting'));
		
		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		$title->setValue($this->getPageObject()->getTitle());
	
		$form->addCommandButton('updateTitle', $lng->txt('save'));
		$form->addCommandButton('preview', $lng->txt('cancel'));

		return $form;		
	}
	
	function editDate($a_form = null)
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("edit");
		
		if(!$a_form)
		{
			$a_form = $this->initDateForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function updateDate()
	{
		global $ilCtrl, $lng;
		
		$form = $this->initDateForm();
		if($form->checkInput())
		{
			$dt = $form->getInput("date");
			$dt = new ilDateTime($dt["date"]." ".$dt["time"], IL_CAL_DATETIME);
			
			$page = $this->getPageObject();
			$page->setCreated($dt);
			$page->update();			
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "preview");
		}
		
		$form->setValuesByPost();
		$this->editTitle($form);		
	}
	
	function initDateForm()
	{
		global $lng, $ilCtrl;
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt('blog_edit_date'));
		
		$date = new ilDateTimeInputGUI($lng->txt("date"), "date");
		$date->setRequired(true);
		$date->setShowTime(true);
		$date->setInfo($lng->txt('blog_edit_date_info'));
		$form->addItem($date);
		
		$date->setDate($this->getPageObject()->getCreated());
	
		$form->addCommandButton('updateDate', $lng->txt('save'));
		$form->addCommandButton('preview', $lng->txt('cancel'));

		return $form;		
	}
	
	function observeNoteAction($a_blog_id, $a_posting_id, $a_type, $a_action, $a_note_id)
	{
		// #10040 - get note text
		include_once "Services/Notes/classes/class.ilNote.php";
		$note = new ilNote($a_note_id);
		$note = $note->getText();
		
		include_once "Modules/Blog/classes/class.ilObjBlog.php";
		ilObjBlog::sendNotification("comment", $this->isInWorkspace(), $this->node_id, $a_posting_id, $note);		
	}
	
	protected function getActivationCaptions()
	{
		global $lng;
		
		return array("deactivatePage" => $lng->txt("blog_toggle_draft"),
				"activatePage" => $lng->txt("blog_toggle_final"));
	}	
	
	function deactivatePage()
	{
		$this->getBlogPosting()->setApproved(false);
		$this->getBlogPosting()->setActive(false);
		$this->getBlogPosting()->update(true, false, false);
		$this->ctrl->redirect($this, "edit");
	}
	
	function activatePage()
	{
		// send notifications
		include_once "Modules/Blog/classes/class.ilObjBlog.php";
		ilObjBlog::sendNotification("new", $this->isInWorkspace(), $this->node_id, $this->getBlogPosting()->getId());		 
		
		$this->getBlogPosting()->setActive(true);
		$this->getBlogPosting()->update(true, false, false);
		$this->ctrl->redirect($this, "edit");
	}
	
	function editKeywords(ilPropertyFormGUI $a_form = null)
	{
		global $ilTabs, $tpl;
		
		if (!$this->checkAccess("write"))
		{
			return;
		}
		
		$ilTabs->activateTab("pg");
		
		if(!$a_form)
		{
			$a_form = $this->initKeywordsForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function initKeywordsForm()
	{
		global $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();		
		$form->setFormAction($this->ctrl->getFormAction($this, "saveKeywordsForm"));
		$form->setTitle($this->lng->txt("blog_edit_keywords"));
		
		$txt = new ilTextInputGUI($this->lng->txt("blog_keywords"), "keywords");
		// $txt->setRequired(true); #10504
		$txt->setMulti(true);
		$txt->setDataSource($this->ctrl->getLinkTarget($this, "keywordAutocomplete", "", true));
		$txt->setMaxLength(200);
		$txt->setSize(50);
		$txt->setInfo($this->lng->txt("blog_keywords_info"));
		$form->addItem($txt);
				
		$md_section = $this->getMDSection();
		
		$keywords = array();
		foreach($ids = $md_section->getKeywordIds() as $id)
		{
			$md_key = $md_section->getKeyword($id);
			if (trim($md_key->getKeyword()) != "")
			{
				$keywords[$md_key->getKeywordLanguageCode()][]
					= $md_key->getKeyword();
			}
		}
										
		// language is not "used" anywhere
		$ulang = $ilUser->getLanguage();
		if($keywords[$ulang])
		{
			asort($keywords[$ulang]);
			$txt->setValue($keywords[$ulang]);
		}
		
		$form->addCommandButton("saveKeywordsForm", $this->lng->txt("save"));
		$form->addCommandButton("preview", $this->lng->txt("cancel"));

		return $form;				
	}
	
	protected function getParentObjId()
	{
		if($this->node_id)
		{
			if($this->isInWorkspace())
			{
				return $this->access_handler->getTree()->lookupObjectId($this->node_id);
			}
			else
			{
				return ilObject::_lookupObjId($this->node_id);
			}
		}
	}
	
	protected function getMDSection()
	{											
		// general section available?
		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj = new ilMD($this->getParentObjId(), 
			$this->getBlogPosting()->getId(), "blp");
		if(!is_object($md_section = $md_obj->getGeneral()))
		{
			$md_section = $md_obj->addGeneral();
			$md_section->save();
		}						
		
		return $md_section;
	}
	
	function saveKeywordsForm()
	{
		global $ilUser;
		
		$form = $this->initKeywordsForm();
		if($form->checkInput())
		{			
			$keywords = $form->getInput("keywords");
			if(is_array($keywords))
			{
				// language is not "used" anywhere
				$ulang = $ilUser->getLanguage();
				$keywords = array($ulang=>$keywords);
				
				include_once("./Services/MetaData/classes/class.ilMDKeyword.php");				
				ilMDKeyword::updateKeywords($this->getMDSection(), $keywords);				
			}
			
			$this->ctrl->redirect($this, "preview");
		}
		
		$form->setValuesByPost();
		$this->editKeywords($form);
	}
	
	public static function getKeywords($a_obj_id, $a_posting_id)
	{
		include_once("./Services/MetaData/classes/class.ilMDKeyword.php");
		return ilMDKeyword::lookupKeywords($a_obj_id, $a_posting_id);
	}
	
	function keywordAutocomplete()
	{				
		include_once("./Services/MetaData/classes/class.ilMDKeyword.php");
		$res = ilMDKeyword::_getMatchingKeywords(ilUtil::stripSlashes($_GET["term"]),
			"blp", $this->getParentObjId());
		
		$result = array();
		$cnt = 0;
		foreach ($res as $r)
		{
			if ($cnt++ > 19)
			{
				continue;
			}
			$entry = new stdClass();
			$entry->value = $r;
			$entry->label = $r;
			$result[] = $entry;
		}

		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		echo ilJsonUtil::encode($result);
		exit;
	}
	
	/**
	 * Get first text paragraph of page
	 * 
	 * @param int $a_id
	 * @return string 
	 */
	static function getSnippet($a_id)
	{
		$bpgui = new self(0, null, $a_id);
		$bpgui->setRawPageContent(true);
		$bpgui->setAbstractOnly(true);

		// #8627: export won't work - should we set offline mode?
		$bpgui->setFileDownloadLink(".");
		$bpgui->setFullscreenLink(".");
		$bpgui->setSourcecodeDownloadScript(".");

		return $bpgui->showPage();		
	}
}

?>