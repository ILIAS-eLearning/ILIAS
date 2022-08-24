<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Blog\StandardGUIRequest;

/**
 * Class ilBlogPosting GUI class
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilBlogPostingGUI: ilPageEditorGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilBlogPostingGUI: ilRatingGUI, ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
 */
class ilBlogPostingGUI extends ilPageObjectGUI
{
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\Blog\ReadingTime\ReadingTimeManager $reading_time_manager;
    protected StandardGUIRequest $blog_request;
    protected ilTabsGUI$tabs;
    protected ilLocatorGUI $locator;
    protected ilSetting $settings;
    protected int $node_id;
    protected ?object $access_handler = null;
    protected bool $enable_public_notes = false;
    protected bool $may_contribute = false;
    protected bool $fetchall = false;
    protected bool $blpg = false;
    protected string $term = "";
    public bool $add_date = false;

    public function __construct(
        int $a_node_id,
        object $a_access_handler = null,
        int $a_id = 0,
        int $a_old_nr = 0,
        bool $a_enable_public_notes = true,
        bool $a_may_contribute = true,
        int $a_style_sheet_id = 0
    ) {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $this->blog_request = $DIC->blog()
            ->internal()
            ->gui()
            ->standardRequest();

        $lng->loadLanguageModule("blog");

        $this->node_id = $a_node_id;
        $this->access_handler = $a_access_handler;
        $this->enable_public_notes = $a_enable_public_notes;

        parent::__construct("blp", $a_id, $a_old_nr);

        // needed for notification
        $this->getBlogPosting()->setBlogNodeId($this->node_id, $this->isInWorkspace());
        $this->getBlogPosting()->getPageConfig()->setEditLockSupport(!$this->isInWorkspace());

        // #11151
        $this->may_contribute = $a_may_contribute;
        $this->setEnableEditing($a_may_contribute);

        // content style

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

        $this->blpg = $this->blog_request->getBlogPage();
        $this->fetchall = $this->blog_request->getFetchAll();
        $this->term = $this->blog_request->getTerm();

        $this->reading_time_manager = new \ILIAS\Blog\ReadingTime\ReadingTimeManager();
        $this->notes = $DIC->notes();
    }

    public function executeCommand(): string
    {
        $ilCtrl = $this->ctrl;
        $ilLocator = $this->locator;
        $tpl = $this->tpl;

        $next_class = $ilCtrl->getNextClass($this);

        $posting = $this->getBlogPosting();
        $ilCtrl->setParameter($this, "blpg", $posting->getId());

        switch ($next_class) {
            case "ilnotegui":
                // $this->getTabs();
                // $ilTabs->setTabActive("pg");
                return $this->previewFullscreen();

            default:
                if ($posting) {
                    if ($ilCtrl->getCmd() === "deactivatePageToList") {
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("blog_draft_info"), true);
                    } elseif ($ilCtrl->getCmd() === "activatePageToList") {
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("blog_new_posting_info"), true);
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

    public function setBlogPosting(ilBlogPosting $a_posting): void
    {
        $this->setPageObject($a_posting);
    }

    public function getBlogPosting(): ilBlogPosting
    {
        /** @var ilBlogPosting $bp */
        $bp = $this->getPageObject();
        return $bp;
    }

    protected function checkAccess(string $a_cmd): bool
    {
        if ($a_cmd === "contribute") {
            return $this->may_contribute;
        }
        return $this->access_handler->checkAccess($a_cmd, "", $this->node_id);
    }

    public function preview(
        string $a_mode = null
    ): string {
        global $DIC;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilSetting = $this->settings;

        $toolbar = $DIC->toolbar();
        $append = "";

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
        } else {
            $callback = array($this, "observeNoteAction");

            // notes

            $may_delete_comments = ($this->checkAccess("contribute") &&
                $ilSetting->get("comments_del_tutor", '1'));

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
        if ($a_mode !== "embedded") {
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
     */
    public function previewEmbedded(): string
    {
        return $this->preview("embedded");
    }

    /**
     * Needed for portfolio/blog handling
     */
    public function previewFullscreen(): string
    {
        $this->add_date = true;
        return $this->preview("fullscreen");
    }

    public function showPage(
        string $a_title = ""
    ): string {
        $this->setTemplateOutput(false);

        if (!$this->getAbstractOnly() && !$this->showPageHeading()) {
            if ($a_title !== "") {
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
     */
    protected function isInWorkspace(): bool
    {
        $class = '';
        if (is_object($this->access_handler)) {
            $class = get_class($this->access_handler);
        }

        return stristr($class, "workspace");
    }

    /**
     * Finalizing output processing
     */
    public function postOutputProcessing(
        string $a_output
    ): string {
        // #8626/#9370
        if ($this->showPageHeading()) {
            $a_output = $this->getPageHeading() . $a_output;
        }

        return $a_output;
    }

    protected function showPageHeading(): bool
    {
        if (!$this->getAbstractOnly() && $this->add_date) {
            return true;
        }

        return false;
    }

    /**
     * Get page heading
     * see also https://docu.ilias.de/goto_docu_wiki_wpage_5793_1357.html
     * the presentation heading has a defined layout, title is not from page content
     */
    protected function getPageHeading(): string
    {
        $author = "";
        if (!$this->isInWorkspace()) {
            $authors = array();
            $author_id = $this->getBlogPosting()->getAuthor();
            if ($author_id) {
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
        $rel = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(true);
        $tpl = new ilTemplate("tpl.posting_head.html", true, true, "Modules/Blog");

        // reading time
        $reading_time = $this->reading_time_manager->getReadingTime(
            $this->getBlogPosting()->getParentId(),
            $this->getBlogPosting()->getId()
        );
        if (!is_null($reading_time)) {
            $this->lng->loadLanguageModule("copg");
            $tpl->setCurrentBlock("reading_time");
            $tpl->setVariable(
                "READING_TIME",
                $this->lng->txt("copg_est_reading_time") . ": " .
                sprintf($this->lng->txt("copg_x_minutes"), $reading_time)
            );
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("TITLE", $this->getBlogPosting()->getTitle());
        $tpl->setVariable(
            "DATETIME",
            $author . ilDatePresentation::formatDate($this->getBlogPosting()->getCreated())
        );
        ilDatePresentation::setUseRelativeDates($rel);
        return $tpl->get();
    }

    public function getTabs(string $a_activate = ""): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilobjbloggui", "blpg", $this->getBlogPosting()->getId());

        parent::getTabs($a_activate);
    }

    public function deleteBlogPostingConfirmationScreen(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
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
            $cnt_note_users = $this->notes->domain()->getUserCount(
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

    public function cancelBlogPostingDeletion(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "preview");
    }

    public function confirmBlogPostingDeletion(): void
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
            $this->tpl->setOnScreenMessage('success', $lng->txt("blog_posting_deleted"), true);
        }

        $ilCtrl->setParameterByClass("ilobjbloggui", "blpg", ""); // #14363
        $ilCtrl->redirectByClass("ilobjbloggui", "render");
    }

    public function editTitle(ilPropertyFormGUI $a_form = null): void
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

    public function updateTitle(): void
    {
        $lng = $this->lng;

        $form = $this->initTitleForm();
        if ($form->checkInput()) {
            if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
                $page = $this->getPageObject();
                $page->setTitle($form->getInput("title"));
                $page->update();

                $page->handleNews(true);

                $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
                //$ilCtrl->redirect($this, "preview");
                $this->ctrl->redirectByClass("ilObjBlogGUI", "");
            }
        }

        $form->setValuesByPost();
        $this->editTitle($form);
    }

    public function initTitleForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

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

    public function editDate(ilPropertyFormGUI $a_form = null): void
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

    public function updateDate(): void
    {
        $lng = $this->lng;

        $form = $this->initDateForm();
        if ($form->checkInput()) {
            if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
                $dt = $form->getItemByPostVar("date");
                $dt = $dt->getDate();

                $page = $this->getPageObject();
                $page->setCreated($dt);
                $page->update();

                $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
                //$ilCtrl->redirect($this, "preview");
                $this->ctrl->redirectByClass("ilObjBlogGUI", "");
            }
        }

        $form->setValuesByPost();
        $this->editTitle($form);
    }

    public function initDateForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

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

    protected function cancelEdit(): void
    {
        $this->ctrl->redirectByClass("ilObjBlogGUI", "");
    }

    public function observeNoteAction(
        int $a_blog_id,
        int $a_posting_id,
        string $a_type,
        string $a_action,
        int $a_note_id
    ): void {
        // #10040 - get note text
        $note = $this->notes->domain()->getById($a_note_id);
        $text = $note->getText();
        ilObjBlog::sendNotification("comment", $this->isInWorkspace(), $this->node_id, $a_posting_id, $text);
    }

    public function getActivationCaptions(): array
    {
        $lng = $this->lng;

        return array("deactivatePage" => $lng->txt("blog_toggle_draft"),
                "activatePage" => $lng->txt("blog_toggle_final"));
    }

    public function deactivatePageToList(): void
    {
        $this->deactivatePage(true);
    }

    public function deactivatePage(bool $a_to_list = false): void
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

    public function activatePageToList(): void
    {
        $this->activatePage(true);
    }

    public function activatePage(bool $a_to_list = false): void
    {
        // send notifications
        ilObjBlog::sendNotification("new", $this->isInWorkspace(), $this->node_id, $this->getBlogPosting()->getId());

        if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
            $this->getBlogPosting()->setActive(true);
            $this->getBlogPosting()->update(true, false);
        }
        if (!$a_to_list) {
            $this->ctrl->redirect($this, "edit");
        } else {
            $this->ctrl->setParameterByClass("ilobjbloggui", "blpg", "");
            $this->ctrl->redirectByClass("ilobjbloggui", "");
        }
    }

    /**
     * Diplay the keywords form
     */
    public function editKeywords(): void
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

        $tpl->setContent($renderer->render($this->initKeywordsForm()));
    }

    /**
     * @throws ilCtrlException
     */
    protected function initKeywordsForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        global $DIC;

        $ui_factory = $DIC->ui()->factory();

        $md_section = $this->getBlogPosting()->getMDSection();

        $keywords = array();
        foreach ($ids = $md_section->getKeywordIds() as $id) {
            $md_key = $md_section->getKeyword($id);
            if (trim($md_key->getKeyword()) !== "") {
                $keywords[] = $md_key->getKeyword();
            }
        }

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

        $input_tag = $ui_factory->input()->field()->tag($this->lng->txt("blog_keywords"), $other, $this->lng->txt("blog_keyword_enter"))->withUserCreatedTagsAllowed(true);
        if (count($keywords) > 0) {
            $input_tag = $input_tag->withValue($keywords);
        }

        $DIC->ctrl()->setParameter(
            $this,
            'tags',
            'tags_processing'
        );

        $section = $ui_factory->input()->field()->section([$input_tag], $this->lng->txt("blog_edit_keywords"), "");

        $form_action = $DIC->ctrl()->getFormAction($this, "saveKeywordsForm");
        return $ui_factory->input()->container()->form()->standard($form_action, ["tags" => $section]);
    }

    protected function getParentObjId(): int
    {
        if ($this->node_id) {
            if ($this->isInWorkspace()) {
                return $this->access_handler->getTree()->lookupObjectId($this->node_id);
            }

            return ilObject::_lookupObjId($this->node_id);
        }
        return 0;
    }

    public function saveKeywordsForm(): void
    {
        global $DIC;

        $request = $DIC->http()->request();
        $form = $this->initKeywordsForm();

        if ($request->getMethod() === "POST"
            && $request->getQueryParams()['tags'] == 'tags_processing') {
            $form = $form->withRequest($request);
            $result = $form->getData();
            //TODO identify the input instead of use 0
            $keywords = $result["tags"][0];

            if ($this->checkAccess("write") || $this->checkAccess("contribute")) {
                if (is_array($keywords)) {
                    $this->getBlogPosting()->updateKeywords($keywords);
                } else {
                    $this->getBlogPosting()->updateKeywords([]);
                }
            }

            $this->ctrl->redirectByClass("ilObjBlogGUI", "");
        }
    }

    /**
     * Get first text paragraph of page
     */
    public static function getSnippet(
        int $a_id,
        bool $a_truncate = false,
        int $a_truncate_length = 500,
        string $a_truncate_sign = "...",
        bool $a_include_picture = false,
        int $a_picture_width = 144,
        int $a_picture_height = 144,
        string $a_export_directory = null
    ): string {
        $bpgui = new self(0, null, $a_id);

        // scan the full page for media objects
        $img = "";
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

    protected function getFirstMediaObjectAsTag(
        int $a_width = 144,
        int $a_height = 144,
        string $a_export_directory = null
    ): string {
        $this->obj->buildDom();
        $mob_ids = $this->obj->collectMediaObjects();
        if ($mob_ids) {
            foreach ($mob_ids as $mob_id) {
                $mob_obj = new ilObjMediaObject($mob_id);
                $mob_item = $mob_obj->getMediaItem("Standard");
                if (stripos($mob_item->getFormat(), "image") !== false) {
                    $mob_size = $mob_item->getOriginalSize();
                    if (is_null($mob_size)) {
                        continue;
                    }
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


                        $location = $mob_item->getLocationType() === "Reference"
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
        return "";
    }

    protected static function parseImage(
        int $src_width,
        int $src_height,
        int $tgt_width,
        int $tgt_height
    ): array {
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

    public function getDisabledText(): string
    {
        return $this->lng->txt("blog_draft_text");
    }

    public function getCommentsHTMLExport(): string
    {
        return $this->getNotesHTML(
            $this->getBlogPosting(),
            false,
            $this->enable_public_notes,
            false,
            null,
            true
        );
    }

    protected function showEditToolbar(): void
    {
    }

    public function finishEditing(): void
    {
        $this->ctrl->setParameterByClass("ilobjbloggui", "bmn", "");
        $this->ctrl->redirectByClass("ilobjbloggui", "render");
    }
}
