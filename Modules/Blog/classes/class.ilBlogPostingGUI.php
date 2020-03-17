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
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var int
     */
    protected $node_id;
    protected $access_handler; // [object]

    /**
     * @var bool
     */
    protected $enable_public_notes;

    /**
     * @var bool
     */
    protected $may_contribute;

    /**
     * @var bool
     */
    protected $fetchall;

    /**
     * @var int
     */
    protected $blpg;

    /**
     * @var string
     */
    protected $term;

    /**
     * Constructor
     *
     * @param int $a_node
     * @param object $a_access_handler
     * @param int $a_id
     * @param int $a_old_nr
     * @param bool $a_enable_notes
     * @param bool $a_may_contribute
     */
    public function __construct($a_node_id, $a_access_handler = null, $a_id = 0, $a_old_nr = 0, $a_enable_public_notes = true, $a_may_contribute = true, $a_style_sheet_id = 0)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();

        $lng->loadLanguageModule("blog");

        $this->node_id = $a_node_id;
        $this->access_handler = $a_access_handler;
        $this->enable_public_notes = (bool) $a_enable_public_notes;

        parent::__construct("blp", $a_id, $a_old_nr);

        // needed for notification
        $this->getBlogPosting()->setBlogNodeId($this->node_id, $this->isInWorkspace());
        $this->getBlogPosting()->getPageConfig()->setEditLockSupport(!$this->isInWorkspace());
        
        // #11151
        $this->may_contribute = (bool) $a_may_contribute;
        $this->setEnableEditing($a_may_contribute);
        
        // content style
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->parseCurrentBlock();
        
        // #17814
        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($a_style_sheet_id)
        );
        $tpl->parseCurrentBlock();
                    
        // needed for editor
        $this->setStyleId($a_style_sheet_id);

        $this->blpg = (int) $_GET["blpg"];
        $this->fetchall = (bool) $_GET["fetchall"];
        $this->term = ilUtil::stripSlashes($_GET["term"]);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilLocator = $this->locator;
        $tpl = $this->tpl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        $posting = $this->getBlogPosting();
        $ilCtrl->setParameter($this, "blpg", $posting->getId());
        
        switch ($next_class) {
            case "ilnotegui":
                // $this->getTabs();
                // $ilTabs->setTabActive("pg");
                return $this->previewFullscreen();

            default:
                if ($posting) {
                    if ($ilCtrl->getCmd() == "deactivatePageToList") {
                        ilUtil::sendSuccess($this->lng->txt("blog_draft_info"), true);
                    } elseif ($ilCtrl->getCmd() == "activatePageToList") {
                        ilUtil::sendSuccess($this->lng->txt("blog_new_posting_info"), true);
                    }
                    $this->setPresentationTitle($posting->getTitle());
                    
                    $tpl->setTitle(ilObject::_lookupTitle($this->getBlogPosting()->getBlogId()) . ": " . // #15017
                        $posting->getTitle());
                    $tpl->setTitleIcon(
                        ilUtil::getImagePath("icon_blog.svg"),
                        $this->lng->txt("obj_blog")
                    ); // #12879
                    
                    $ilLocator->addItem(
                        $posting->getTitle(),
                        $ilCtrl->getLinkTarget($this, "preview")
                    );
                }
                return parent::executeCommand();
        }
    }

    /**
     * Set blog posting
     *
     * @param ilBlogPosting $a_posting
     */
    public function setBlogPosting(ilBlogPosting $a_posting)
    {
        $this->setPageObject($a_posting);
    }

    /**
     * Get blog posting
     *
     * @returnilBlogPosting
     */
    public function getBlogPosting()
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
        if ($a_cmd == "contribute") {
            return $this->may_contribute;
        }
        return $this->access_handler->checkAccess($a_cmd, "", $this->node_id);
    }

    /**
     * Preview blog posting
     */
    public function preview($a_mode = null)
    {
        global $DIC;

        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilSetting = $this->settings;

        $toolbar = $DIC->toolbar();

        $this->getBlogPosting()->increaseViewCnt();
        
        $wtpl = new ilTemplate(
            "tpl.blog_page_view_main_column.html",
            true,
            true,
            "Modules/Blog"
        );
        
        // page commands
        if (!$a_mode) {
            if (!$this->getEnableEditing()) {
                $this->ctrl->redirect($this, "previewFullscreen");
            }
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
        } else {
            $callback = array($this, "observeNoteAction");
                                    
            // notes
            
            $may_delete_comments = ($this->checkAccess("contribute") &&
                $ilSetting->get("comments_del_tutor", 1));

            $wtpl->setVariable("TOOLBAR", $toolbar->getHTML());

            $wtpl->setVariable("NOTES", $this->getNotesHTML(
                $this->getBlogPosting(),
                false,
                $this->enable_public_notes,
                $may_delete_comments,
                $callback
            ));
        }

        // permanent link
        if ($a_mode != "embedded") {
            $append = ($this->blpg > 0)
                ? "_" . $this->blpg
                : "";
            if ($this->isInWorkspace()) {
                $append .= "_wsp";
            }
            $tpl->setPermanentLink("blog", $this->node_id, $append);
        }
        
        $wtpl->setVariable("PAGE", parent::preview());

        $tpl->setLoginTargetPar("blog_" . $this->node_id . $append);

        $ilCtrl->setParameter($this, "blpg", $this->getBlogPosting()->getId());

        return $wtpl->get();
    }
    
    /**
     * Needed for portfolio/blog handling
     *
     * @return string
     */
    public function previewEmbedded()
    {
        return $this->preview("embedded");
    }
    
    /**
     * Needed for portfolio/blog handling
     *
     * @return string
     */
    public function previewFullscreen()
    {
        $this->add_date = true;
        return $this->preview("fullscreen");
    }

    /**
     * Embedded posting in portfolio
     *
     * @return string
     */
    public function showPage($a_title = "")
    {
        $this->setTemplateOutput(false);

        if (!$this->getAbstractOnly()) {
            if ($a_title != "") {
                $this->setPresentationTitle($a_title);
            } else {
                $this->setPresentationTitle($this->getBlogPosting()->getTitle());
            }
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
        $class = '';
        if (is_object($this->access_handler)) {
            $class = get_class($this->access_handler);
        }

        return stristr($class, "workspace");
    }

    /**
     * Finalizing output processing
     *
     * @param string $a_output
     * @return string
     */
    public function postOutputProcessing($a_output)
    {
        // #8626/#9370
        if (($this->getOutputMode() == "preview" || $this->getOutputMode() == "offline")
            && !$this->getAbstractOnly() && $this->add_date) {
            $author = "";
            if (!$this->isInWorkspace()) {
                $authors = array();
                $author_id = $this->getBlogPosting()->getAuthor();
                if ($author_id) {
                    include_once "Services/User/classes/class.ilUserUtil.php";
                    $authors[] = ilUserUtil::getNamePresentation($author_id);
                }
                                
                foreach (ilBlogPosting::getPageContributors("blp", $this->getBlogPosting()->getId()) as $editor) {
                    if ($editor["user_id"] != $author_id) {
                        $authors[] = ilUserUtil::getNamePresentation($editor["user_id"]);
                    }
                }
                
                if ($authors) {
                    $author = implode(", ", $authors) . " - ";
                }
            }
            
            // prepend creation date
            $rel = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);
            $prefix = "<div class=\"il_BlockInfo\" style=\"text-align:right\">" .
                $author . ilDatePresentation::formatDate($this->getBlogPosting()->getCreated()) .
                "</div>";
            ilDatePresentation::setUseRelativeDates($rel);
            
            $a_output = $prefix . $a_output;
        }
        
        return $a_output;
    }

    /**
     * Get tabs
     *
     * @param string $a_activate
     */
    public function getTabs($a_activate = "")
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilobjbloggui", "blpg", $this->getBlogPosting()->getId());

        parent::getTabs($a_activate);
    }

    /**
     * Delete blog posting confirmation screen
     */
    public function deleteBlogPostingConfirmationScreen()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $confirmation_gui = new ilConfirmationGUI();
            $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
            $confirmation_gui->setHeaderText($lng->txt("blog_posting_deletion_confirmation"));
            $confirmation_gui->setCancel($lng->txt("cancel"), "cancelBlogPostingDeletion");
            $confirmation_gui->setConfirm($lng->txt("delete"), "confirmBlogPostingDeletion");
            
            $dtpl = new ilTemplate(
                "tpl.blog_posting_deletion_confirmation.html",
                true,
                true,
                "Modules/Blog"
            );
                
            $dtpl->setVariable("PAGE_TITLE", $this->getBlogPosting()->getTitle());
            
            // notes/comments
            include_once("./Services/Notes/classes/class.ilNote.php");
            $cnt_note_users = ilNote::getUserCount(
                $this->getBlogPosting()->getParentId(),
                $this->getBlogPosting()->getId(),
                "wpg"
            );
            $dtpl->setVariable(
                "TXT_NUMBER_USERS_NOTES_OR_COMMENTS",
                $lng->txt("blog_number_users_notes_or_comments")
            );
            $dtpl->setVariable("TXT_NR_NOTES_COMMENTS", $cnt_note_users);
            
            $confirmation_gui->addItem("", "", $dtpl->get());
            
            $tpl->setContent($confirmation_gui->getHTML());
        }
    }

    /**
     * Cancel blog posting deletion
     */
    public function cancelBlogPostingDeletion()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "preview");
    }
    
    /**
    * Delete the blog posting
    */
    public function confirmBlogPostingDeletion()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
            // delete all md keywords
            $md_section = $this->getBlogPosting()->getMDSection();
            foreach ($md_section->getKeywordIds() as $id) {
                $md_key = $md_section->getKeyword($id);
                $md_key->delete();
            }
            
            $this->getBlogPosting()->delete();
            ilUtil::sendSuccess($lng->txt("blog_posting_deleted"), true);
        }
        
        $ilCtrl->setParameterByClass("ilobjbloggui", "blpg", ""); // #14363
        $ilCtrl->redirectByClass("ilobjbloggui", "render");
    }
    
    public function editTitle($a_form = null)
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("ilobjblogGUI"));

        $ilTabs->activateTab("edit");
        
        if (!$a_form) {
            $a_form = $this->initTitleForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    public function updateTitle()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $form = $this->initTitleForm();
        if ($form->checkInput()) {
            if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
                $page = $this->getPageObject();
                $page->setTitle($form->getInput("title"));
                $page->update();

                $page->handleNews(true);

                ilUtil::sendSuccess($lng->txt("settings_saved"), true);
                //$ilCtrl->redirect($this, "preview");
                $this->ctrl->redirectByClass("ilObjBlogGUI", "");
            }
        }
        
        $form->setValuesByPost();
        $this->editTitle($form);
    }
    
    public function initTitleForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt('blog_rename_posting'));
        
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        $title->setValue($this->getPageObject()->getTitle());
    
        $form->addCommandButton('updateTitle', $lng->txt('save'));
        $form->addCommandButton('cancelEdit', $lng->txt('cancel'));

        return $form;
    }
    
    public function editDate($a_form = null)
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("ilobjblogGUI"));

        $ilTabs->activateTab("edit");
        
        if (!$a_form) {
            $a_form = $this->initDateForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    public function updateDate()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $form = $this->initDateForm();
        if ($form->checkInput()) {
            if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
                $dt = $form->getItemByPostVar("date");
                $dt = $dt->getDate();

                $page = $this->getPageObject();
                $page->setCreated($dt);
                $page->update();

                ilUtil::sendSuccess($lng->txt("settings_saved"), true);
                //$ilCtrl->redirect($this, "preview");
                $this->ctrl->redirectByClass("ilObjBlogGUI", "");
            }
        }
        
        $form->setValuesByPost();
        $this->editTitle($form);
    }
    
    public function initDateForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
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
        $form->addCommandButton('cancelEdit', $lng->txt('cancel'));

        return $form;
    }

    /**
     * Cancel editing
     *
     * @param
     * @return
     */
    protected function cancelEdit()
    {
        $this->ctrl->redirectByClass("ilObjBlogGUI", "");
    }

    
    public function observeNoteAction($a_blog_id, $a_posting_id, $a_type, $a_action, $a_note_id)
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
        $lng = $this->lng;
        
        return array("deactivatePage" => $lng->txt("blog_toggle_draft"),
                "activatePage" => $lng->txt("blog_toggle_final"));
    }
    
    public function deactivatePageToList()
    {
        $this->deactivatePage(true);
    }
    
    public function deactivatePage($a_to_list = false)
    {
        if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
            $this->getBlogPosting()->unpublish();
        }

        if (!$a_to_list) {
            $this->ctrl->redirect($this, "edit");
        } else {
            $this->ctrl->setParameterByClass("ilobjbloggui", "blpg", "");
            $this->ctrl->redirectByClass("ilobjbloggui", "");
        }
    }
    
    public function activatePageToList()
    {
        $this->activatePage(true);
    }
    
    public function activatePage($a_to_list = false)
    {
        // send notifications
        include_once "Modules/Blog/classes/class.ilObjBlog.php";
        ilObjBlog::sendNotification("new", $this->isInWorkspace(), $this->node_id, $this->getBlogPosting()->getId());

        if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
            $this->getBlogPosting()->setActive(true);
            $this->getBlogPosting()->update(true, false, false);
        }
        if (!$a_to_list) {
            $this->ctrl->redirect($this, "edit");
        } else {
            $this->ctrl->setParameterByClass("ilobjbloggui", "blpg", "");
            $this->ctrl->redirectByClass("ilobjbloggui", "");
        }
    }

    /**
     * Diplay the form
     * @param ilPropertyFormGUI|null $a_form
     */
    public function editKeywords(ilPropertyFormGUI $a_form = null)
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();

        $ilTabs = $this->tabs;
        $tpl = $this->tpl;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("ilobjblogGUI"));

        if (!$this->checkAccess("contribute")) {
            return;
        }
        
        $ilTabs->activateTab("pg");
        
        if (!$a_form) {
            $a_form = $this->initKeywordsForm();
        }

        $tpl->setContent($renderer->render($a_form));
    }
    
    protected function initKeywordsForm()
    {
        global $DIC;

        $ui_factory = $DIC->ui()->factory();
        //$ilUser = $this->user;

        $md_section = $this->getBlogPosting()->getMDSection();

        $keywords = array();
        foreach ($ids = $md_section->getKeywordIds() as $id) {
            $md_key = $md_section->getKeyword($id);
            if (trim($md_key->getKeyword()) != "") {
                //$keywords[$md_key->getKeywordLanguageCode()][]
                //	= $md_key->getKeyword();
                $keywords[] = $md_key->getKeyword();
            }
        }
                                        
        // language is not "used" anywhere
        /*$ulang = $ilUser->getLanguage();
        if($keywords[$ulang])
        {
            asort($keywords[$ulang]);
        }*/
        
        // other keywords in blog
        $other = array();
        foreach (array_keys(ilBlogPosting::getAllPostings($this->getBlogPosting()->getBlogId())) as $posting_id) {
            if ($posting_id != $this->getBlogPosting()->getId()) {
                $other = array_merge($other, ilBlogPosting::getKeywords($this->getBlogPosting()->getBlogId(), $posting_id));
            }
        }
        // #17414
        $other = array_unique($other);
        sort($other, SORT_LOCALE_STRING);
        if (is_array($keywords[$ulang])) {
            $other = array_diff($other, $keywords[$ulang]);
        }

        $input_tag = $ui_factory->input()->field()->tag($this->lng->txt("blog_keywords"), $other, $this->lng->txt("blog_keyword_enter"));
        $input_tag = $input_tag->withValue($keywords);

        $DIC->ctrl()->setParameter(
            $this,
            'tags',
            'tags_processing'
        );

        $section = $ui_factory->input()->field()->section([$input_tag], $this->lng->txt("blog_edit_keywords"), "");

        $form_action = $DIC->ctrl()->getFormAction($this, "saveKeywordsForm");
        $form = $ui_factory->input()->container()->form()->standard($form_action, ["tags" => $section]);

        return $form;
    }
    
    protected function getParentObjId()
    {
        if ($this->node_id) {
            if ($this->isInWorkspace()) {
                return $this->access_handler->getTree()->lookupObjectId($this->node_id);
            } else {
                return ilObject::_lookupObjId($this->node_id);
            }
        }
    }
    
    public function saveKeywordsForm()
    {
        global $DIC;

        $request = $DIC->http()->request();
        $form = $this->initKeywordsForm();

        if ($request->getMethod() == "POST"
            && $request->getQueryParams()['tags'] == 'tags_processing') {
            $form = $form->withRequest($request);
            $result = $form->getData();

            //TODO identify the input instead of use 0
            $keywords = $result["tags"][0];

            if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
                if (is_array($keywords)) {
                    $this->getBlogPosting()->updateKeywords($keywords);
                }
            }

            $this->ctrl->redirectByClass("ilObjBlogGUI", "");
        }
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
    public static function getSnippet($a_id, $a_truncate = false, $a_truncate_length = 500, $a_truncate_sign = "...", $a_include_picture = false, $a_picture_width = 144, $a_picture_height = 144, $a_export_directory = null)
    {
        $bpgui = new self(0, null, $a_id);
        
        // scan the full page for media objects
        if ($a_include_picture) {
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
        
        if ($a_truncate) {
            $page = ilPageObject::truncateHTML($page, $a_truncate_length, $a_truncate_sign);
        }
        
        if ($img) {
            $page = '<div>' . $img . $page . '</div><div style="clear:both;"></div>';
        }
    
        return $page;
    }
    
    protected function getFirstMediaObjectAsTag($a_width = 144, $a_height = 144, $a_export_directory = null)
    {
        $this->obj->buildDom();
        $mob_ids = $this->obj->collectMediaObjects();
        if ($mob_ids) {
            require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            foreach ($mob_ids as $mob_id) {
                $mob_obj = new ilObjMediaObject($mob_id);
                $mob_item = $mob_obj->getMediaItem("Standard");
                if (stristr($mob_item->getFormat(), "image")) {
                    $mob_size = $mob_item->getOriginalSize();
                    if ($mob_size["width"] >= $a_width ||
                        $mob_size["height"] >= $a_height) {
                        if (!$a_export_directory) {
                            $mob_dir = ilObjMediaObject::_getDirectory($mob_obj->getId());
                        } else {
                            // see ilCOPageHTMLExport::exportHTMLMOB()
                            $mob_dir = "./mobs/mm_" . $mob_obj->getId();
                        }
                        $mob_res = self::parseImage(
                            $mob_size["width"],
                            $mob_size["height"],
                            $a_width,
                            $a_height
                        );


                        $location = $mob_item->getLocationType() == "Reference"
                            ? $mob_item->getLocation()
                            : $mob_dir . "/" . $mob_item->getLocation();
                        
                        return '<img' .
                            ' src="' . $location . '"' .
                            ' width="' . $mob_res[0] . '"' .
                            ' height="' . $mob_res[1] . '"' .
                            ' class="ilBlogListItemSnippetPreviewImage ilFloatLeft noMirror"' .
                            ' />';
                    }
                }
            }
        }
    }
    
    protected static function parseImage($src_width, $src_height, $tgt_width, $tgt_height)
    {
        $ratio_width = $ratio_height = 1;
        if ($src_width > $tgt_width) {
            $ratio_width = $tgt_width / $src_width;
        }
        if ($src_height > $tgt_height) {
            $ratio_height = $tgt_height / $src_height;
        }
        $shrink_ratio = min($ratio_width, $ratio_height);
                        
        return array(
            (int) round($src_width * $shrink_ratio),
            (int) round($src_height * $shrink_ratio)
        );
    }

    /**
     * Get disabled text
     *
     * @param
     * @return
     */
    public function getDisabledText()
    {
        return $this->lng->txt("blog_draft_text");
    }
}
