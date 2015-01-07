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
	protected $may_contribute; // [bool]

	/**
	 * Constructor
	 *
	 * @param int $a_node
	 * @param object $a_access_handler
	 * @param int $a_id
	 * @param int $a_old_nr
	 * @param bool $a_enable_notes
	 * @param bool $a_may_contribute
	 * @return ilBlogPostingGUI
	 */
	function __construct($a_node_id, $a_access_handler = null, $a_id = 0, $a_old_nr = 0, $a_enable_public_notes = true, $a_may_contribute = true)
	{
		global $tpl, $lng;

		$lng->loadLanguageModule("blog");

		$this->node_id = $a_node_id;
		$this->access_handler = $a_access_handler;
		$this->enable_public_notes = (bool)$a_enable_public_notes;

		parent::__construct("blp", $a_id, $a_old_nr);

		// needed for notification
		$this->getBlogPosting()->setBlogNodeId($this->node_id, $this->isInWorkspace());
		
		// #11151
		$this->may_contribute = (bool)$a_may_contribute;
		$this->setEnableEditing($a_may_contribute);
		
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
		die("Deprecated. Blog Posting gui forwarding to ilpageobject");
				return;
				
			default:
				if($posting)
				{
					$this->setPresentationTitle($posting->getTitle());
					
					$tpl->setTitle(ilObject::_lookupTitle($this->getBlogPosting()->getBlogId()).": ". // #15017
						$posting->getTitle());
					$tpl->setTitleIcon(ilUtil::getImagePath("icon_blog.svg"),
						$this->lng->txt("obj_blog")); // #12879
					
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
		if($a_cmd == "contribute")
		{
			return $this->may_contribute;
		}
		return $this->access_handler->checkAccess($a_cmd, "", $this->node_id);
	}

	/**
	 * Preview blog posting
	 */
	function preview($a_mode = null)
	{
		global $ilCtrl, $tpl, $ilSetting;
		
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
			
			$may_delete_comments = ($this->checkAccess("contribute") &&
				$ilSetting->get("comments_del_tutor", 1));
			
			$wtpl->setVariable("NOTES", $this->getNotesHTML($this->getBlogPosting(),
				false, $this->enable_public_notes, $may_delete_comments, $callback));
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
			$tpl->setPermanentLink("blog", $this->node_id, $append);
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

		if ($this->checkAccess("write") || $this->checkAccess("contribute"))
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

		if ($this->checkAccess("write") || $this->checkAccess("contribute"))
		{			
			// delete all md keywords
			$md_section = $this->getBlogPosting()->getMDSection();
			foreach($md_section->getKeywordIds() as $id)
			{
				$md_key = $md_section->getKeyword($id);				
				$md_key->delete();				
			}
			
			$this->getBlogPosting()->delete();
			ilUtil::sendSuccess($lng->txt("blog_posting_deleted"), true);
		}
		
		$ilCtrl->setParameterByClass("ilobjbloggui", "blpg", ""); // #14363
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
	
	function deactivatePageToList()
	{
		$this->deactivatePage(true);
	}
	
	function deactivatePage($a_to_list = false)
	{
		$this->getBlogPosting()->setApproved(false);
		$this->getBlogPosting()->setActive(false);
		$this->getBlogPosting()->update(true, false, false);
		if(!$a_to_list)
		{
			$this->ctrl->redirect($this, "edit");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjbloggui", "");
		}
	}
	
	function activatePageToList()
	{
		$this->activatePage(true);	
	}
	
	function activatePage($a_to_list = false)
	{
		// send notifications
		include_once "Modules/Blog/classes/class.ilObjBlog.php";
		ilObjBlog::sendNotification("new", $this->isInWorkspace(), $this->node_id, $this->getBlogPosting()->getId());		 
		
		$this->getBlogPosting()->setActive(true);
		$this->getBlogPosting()->update(true, false, false);
		if(!$a_to_list)
		{
			$this->ctrl->redirect($this, "edit");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjbloggui", "");
		}
	}
	
	function editKeywords(ilPropertyFormGUI $a_form = null)
	{
		global $ilTabs, $tpl;
		
		if (!$this->checkAccess("contribute"))
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
				
		$md_section = $this->getBlogPosting()->getMDSection();
		
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
	
	function saveKeywordsForm()
	{		
		$form = $this->initKeywordsForm();
		if($form->checkInput())
		{			
			$keywords = $form->getInput("keywords");
			if(is_array($keywords))
			{
				$this->getBlogPosting()->updateKeywords($keywords);
			}
			
			$this->ctrl->redirect($this, "preview");
		}
		
		$form->setValuesByPost();
		$this->editKeywords($form);
	}
	
	function keywordAutocomplete()
	{				
		$force_all = (bool)$_GET["fetchall"];
		
		include_once("./Services/MetaData/classes/class.ilMDKeyword.php");
		$res = ilMDKeyword::_getMatchingKeywords(ilUtil::stripSlashes($_GET["term"]),
			"blp", $this->getParentObjId());
		
		include_once("./Services/Search/classes/class.ilSearchSettings.php");
		$cut = (int)ilSearchSettings::getInstance()->getAutoCompleteLength();		
		
		$has_more = false;		
		$result = array();		
		foreach ($res as $r)
		{
			if(!$force_all &&
				sizeof($result["items"]) >= $cut)
			{
				$has_more = true;
				break;
			}			
			$entry = new stdClass();
			$entry->value = $r;
			$entry->label = $r;
			$result["items"][] = $entry;
		}
		
		$result["hasMoreResults"] = $has_more;

		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		echo ilJsonUtil::encode($result);
		exit;
	}
	
	/**
	 * Get first text paragraph of page
	 * 
	 * @param int $a_id
	 * @param bool $a_truncate
	 * @param int $a_truncate_length
	 * @param bool $a_include_picture
	 * @param int $a_picture_width
	 * @param int $a_picture_height
	 * @param string $a_export_directory
	 * @return string 
	 */
	static function getSnippet($a_id, $a_truncate = false, $a_truncate_length = 500, $a_truncate_sign = "...", $a_include_picture = false, $a_picture_width = 144, $a_picture_height = 144, $a_export_directory = null)
	{					
		$bpgui = new self(0, null, $a_id);
		
		// scan the full page for media objects
		if($a_include_picture)
		{
			$img = $bpgui->getFirstMediaObjectAsTag($a_picture_width, $a_picture_height, $a_export_directory);
		}
		
		$bpgui->setRawPageContent(true);
		$bpgui->setAbstractOnly(true);		

		// #8627: export won't work - should we set offline mode?
		$bpgui->setFileDownloadLink(".");
		$bpgui->setFullscreenLink(".");
		$bpgui->setSourcecodeDownloadScript(".");						
		 
		// render without title
		$page = $bpgui->showPage();		
		
		if($a_truncate)
		{
			$page = ilPageObject::truncateHTML($page, $a_truncate_length, $a_truncate_sign);		
		}
		
		if($img)
		{
			$page = '<div>'.$img.$page.'</div><div style="clear:both;"></div>';			
		}
	
		return $page;				
	}
	
	protected function getFirstMediaObjectAsTag($a_width = 144, $a_height = 144, $a_export_directory = null)
	{
		$this->obj->buildDom();
		$mob_ids = $this->obj->collectMediaObjects();
		if($mob_ids)
		{
			require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			foreach($mob_ids as $mob_id)
			{				
				$mob_obj = new ilObjMediaObject($mob_id);
				$mob_item = $mob_obj->getMediaItem("Standard");
				if(stristr($mob_item->getFormat(), "image"))
				{				
					$mob_size = $mob_item->getOriginalSize();
					if($mob_size["width"] >= $a_width ||
						$mob_size["height"] >= $a_height)
					{
						if(!$a_export_directory)
						{
							$mob_dir = ilObjMediaObject::_getDirectory($mob_obj->getId());
						}
						else
						{
							// see ilCOPageHTMLExport::exportHTMLMOB()
							$mob_dir = "./mobs/mm_".$mob_obj->getId();
						}						
						$mob_res = self::parseImage($mob_size["width"],
							$mob_size["height"], $a_width, $a_height);
						
						return '<img'.
							' src="'.$mob_dir."/".$mob_item->getLocation().'"'.
							' width="'.$mob_res[0].'"'.
							' height="'.$mob_res[1].'"'.
							' class="ilBlogListItemSnippetPreviewImage ilFloatLeft noMirror"'.
							' />';
					}
				}
			}
		}
	}
	
	protected static function parseImage($src_width, $src_height, $tgt_width, $tgt_height)
	{	
		$ratio_width = $ratio_height = 1;
		if($src_width > $tgt_width)
		{
			$ratio_width = $tgt_width / $src_width;
		}	
		if($src_height > $tgt_height)
		{
			$ratio_height = $tgt_height / $src_height;
		}			
		$shrink_ratio = min($ratio_width, $ratio_height);
						
		return array(
			(int)round($src_width*$shrink_ratio), 
			(int)round($src_height*$shrink_ratio)
		);
	}	
}

?>