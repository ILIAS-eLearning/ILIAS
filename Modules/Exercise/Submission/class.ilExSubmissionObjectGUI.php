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

use ILIAS\Portfolio\Export\PortfolioHtmlExport;

/**
 * Object-based submissions (ends up as static file)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilExSubmissionObjectGUI:
 */
class ilExSubmissionObjectGUI extends ilExSubmissionBaseGUI
{
    protected $selected_wsp_obj_id;

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function __construct(
        ilObjExercise $a_exercise,
        ilExSubmission $a_submission
    ) {
        parent::__construct($a_exercise, $a_submission);
        $this->selected_wsp_obj_id = $this->request->getSelectedWspObjId();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->submission->canView()) {
            $this->returnToParentObject();
        }

        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($class) {
            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }

    public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ): void {
        switch ($a_submission->getAssignment()->getType()) {
            case ilExAssignment::TYPE_BLOG:
                self::getOverviewContentBlog($a_info, $a_submission);
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                self::getOverviewContentPortfolio($a_info, $a_submission);
                break;
        }
    }

    protected static function getOverviewContentBlog(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ): void {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $wsp_tree = new ilWorkspaceTree($a_submission->getUserId());

        // #12939
        if (!$wsp_tree->getRootId()) {
            $wsp_tree->createTreeForUser($a_submission->getUserId());
        }

        $files_str = "";
        $buttons_str = "";
        $valid_blog = false;
        $selected_blog = $a_submission->getSelectedObject();
        if ($selected_blog) {
            $blog_id = (int) $selected_blog["filetitle"];
            $node = $wsp_tree->getNodeData($blog_id);
            if ($node["title"]) {
                // #10116
                $ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", $blog_id);
                $blog_link = $ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilpersonalworkspacegui", "ilobjbloggui"), "");
                $ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", "");
                $files_str = '<a href="' . $blog_link . '">' .
                    $node["title"] . '</a>';
                $valid_blog = true;
            }
            // remove invalid resource if no upload yet (see download below)
            elseif (substr($selected_blog["filename"], -1) == "/") {
                // #16887
                $a_submission->deleteResourceObject();
            }
        }
        if ($a_submission->canSubmit()) {
            if (!$valid_blog) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_create_blog");
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createBlog"));
                $buttons_str .= $button->render();
            }
            // #10462
            $blogs = count($wsp_tree->getObjectsFromType("blog"));
            if ((!$valid_blog && $blogs)
                || ($valid_blog && $blogs > 1)) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_select_blog" . ($valid_blog ? "_change" : ""));
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectBlog"));
                $buttons_str .= " " . $button->render();
            }
        }

        // todo: move this to ks somehow
        if ($buttons_str != "") {
            $files_str .= "<p>" . $buttons_str . "</p>";
        }


        if ($files_str) {
            $a_info->addProperty($lng->txt("exc_blog_returned"), $files_str);
        }
        if ($a_submission->hasSubmitted()) {
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_blog["returned_id"]);
            $dl_link = $ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "download");
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", "");

            $button = ilLinkButton::getInstance();
            $button->setCaption("download");
            $button->setUrl($dl_link);

            $a_info->addProperty($lng->txt("exc_files_returned"), $button->render());
        }
    }

    protected static function getOverviewContentPortfolio(ilInfoScreenGUI $a_info, ilExSubmission $a_submission): void
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $back_ref_id = $DIC->http()->wrapper()->query()->retrieve(
            "ref_id",
            $DIC->refinery()->kindlyTo()->int()
        ) ?? 0;

        $request = $DIC->exercise()->internal()->gui()->request();


        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $files_str = "";
        $buttons_str = "";
        $valid_prtf = false;
        $selected_prtf = $a_submission->getSelectedObject();
        if ($selected_prtf) {
            $portfolio_id = (int) $selected_prtf["filetitle"];

            // #11746
            if (ilObject::_exists($portfolio_id, false, "prtf")) {
                $portfolio = new ilObjPortfolio($portfolio_id, false);
                if ($portfolio->getTitle()) {
                    // #10116 / #12791
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $portfolio_id);

                    $ref_id = $request->getRefId();
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "ref_id", $ref_id);
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", $back_ref_id);

                    $prtf_link = $ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "view");
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "ref_id", "");


                    $files_str = '<a href="' . $prtf_link .
                        '">' . $portfolio->getTitle() . '</a>';
                    $valid_prtf = true;
                }
            }
            // remove invalid resource if no upload yet (see download below)
            elseif (substr($selected_prtf["filename"], -1) == "/") {
                // #16887
                $a_submission->deleteResourceObject();
            }
        }
        if ($a_submission->canSubmit()) {
            if (!$valid_prtf) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_create_portfolio");
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createPortfolioFromAssignment"));

                $buttons_str .= $button->render();
            }
            // #10462
            //selectPortfolio ( remove it? )
            $prtfs = count(ilObjPortfolio::getPortfoliosOfUser($a_submission->getUserId()));
            if ((!$valid_prtf && $prtfs)
                || ($valid_prtf && $prtfs > 1)) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_select_portfolio" . ($valid_prtf ? "_change" : ""));
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectPortfolio"));
                $buttons_str .= " " . $button->render();
            }
            if ($valid_prtf) {
                $button = ilLinkButton::getInstance();
                $button->setCaption('exc_select_portfolio_unlink');
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "askUnlinkPortfolio"));
                $buttons_str .= " " . $button->render();
            }
        }
        // todo: move this to ks somehow
        if ($buttons_str != "") {
            $files_str .= "<p>" . $buttons_str . "</p>";
        }
        if ($files_str) {
            $a_info->addProperty($lng->txt("exc_portfolio_returned"), $files_str);
        }
        if ($a_submission->hasSubmitted()) {
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_prtf["returned_id"]);
            $dl_link = $ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "download");
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", "");

            $button = ilLinkButton::getInstance();
            $button->setCaption("download");
            $button->setUrl($dl_link);

            $a_info->addProperty($lng->txt("exc_files_returned"), $button->render());
        }
    }

    protected function renderResourceSelection(
        string $a_title,
        string $a_info,
        string $a_cmd,
        string $a_explorer_cmd,
        array $a_items = null
    ): void {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $html = "";
        $tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");

        if (is_array($a_items)) {
            $tpl->setCurrentBlock("item");
            foreach ($a_items as $item_id => $item_title) {
                $tpl->setVariable("ITEM_ID", $item_id);
                $tpl->setVariable("ITEM_TITLE", $item_title);
                $tpl->parseCurrentBlock();
            }
            $tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
            $tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
            $tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
            $tpl->setVariable("CMD_SUBMIT", $a_cmd);
            $tpl->setVariable("CMD_CANCEL", "returnToParent");
            $html = $tpl->get();
        } elseif ($a_explorer_cmd) {
            $html = $this->renderWorkspaceExplorer($a_explorer_cmd);
        }


        $this->tpl->setOnScreenMessage('info', $this->lng->txt($a_info));

        $title = $this->lng->txt($a_title) . ": " . $this->assignment->getTitle();

        $panel = ilPanelGUI::getInstance();
        $panel->setBody($html);
        $panel->setHeading($title);

        $this->tpl->setContent($panel->getHTML());
    }


    //
    // BLOG
    //

    protected function createBlogObject(): void
    {
        $this->handleTabs();

        $this->renderResourceSelection(
            "exc_create_blog",
            "exc_create_blog_select_info",
            "saveBlog",
            "createBlog"
        );
    }

    protected function selectBlogObject(): void
    {
        $this->handleTabs();

        $this->renderResourceSelection(
            "exc_select_blog",
            "exc_select_blog_info",
            "setSelectedBlog",
            "selectBlog"
        );
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    protected function saveBlogObject(): void
    {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        if ($this->selected_wsp_obj_id == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"));
            $this->createBlogObject();
            return;
        }

        $parent_node = $this->selected_wsp_obj_id;

        $blog = new ilObjBlog();
        $blog->setTitle($this->exercise->getTitle() . " - " . $this->assignment->getTitle());
        $blog->create();

        $tree = new ilWorkspaceTree($this->submission->getUserId()); // #15993

        $node_id = $tree->insertObject($parent_node, $blog->getId());

        $access_handler = new ilWorkspaceAccessHandler($tree);
        $access_handler->setPermissions($parent_node, $node_id);

        $this->submission->deleteAllFiles();
        $this->handleRemovedUpload();

        $this->submission->addResourceObject($node_id);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_blog_created"), true);
        $this->returnToParentObject();
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    protected function setSelectedBlogObject(): void
    {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        if ($this->selected_wsp_obj_id > 0) {
            $tree = new ilWorkspaceTree($this->submission->getUserId());
            $node = $tree->getNodeData($this->selected_wsp_obj_id);
            if ($node && $node["type"] == "blog") {
                $this->submission->deleteAllFiles();
                $this->handleRemovedUpload();

                $this->submission->addResourceObject($node["wsp_id"]);

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_blog_selected"), true);
                $this->ctrl->setParameter($this, "blog_id", $node["wsp_id"]);
                $this->ctrl->redirect($this, "askDirectSubmission");
            }
        }

        $this->selectBlogObject();
    }

    protected function renderWorkspaceExplorer(
        string $a_cmd
    ): string {
        $exp2 = null;
        switch ($a_cmd) {
            case "selectBlog":
                $exp2 = new ilWorkspaceExplorerGUI($this->submission->getUserId(), $this, $a_cmd, $this, "setSelectedBlog");
                $exp2->setTypeWhiteList(array("blog", "wsrt", "wfld"));
                $exp2->setSelectableTypes(array("blog"));
                break;

            case "createBlog":
                $exp2 = new ilWorkspaceExplorerGUI($this->submission->getUserId(), $this, $a_cmd, $this, "saveBlog");
                $exp2->setTypeWhiteList(array("wsrt", "wfld"));
                $exp2->setSelectableTypes(array("wsrt", "wfld"));
                break;
        }
        if (!$exp2->handleCommand()) {
            return $exp2->getHTML();
        }
        exit;
    }


    //
    // PORTFOLIO
    //

    protected function selectPortfolioObject(): void
    {
        $this->handleTabs();

        $items = array();
        $portfolios = ilObjPortfolio::getPortfoliosOfUser($this->submission->getUserId());
        foreach ($portfolios as $portfolio) {
            $items[$portfolio["id"]] = $portfolio["title"];
        }

        $this->renderResourceSelection(
            "exc_select_portfolio",
            "exc_select_portfolio_info",
            "setSelectedPortfolio",
            "",
            $items
        );
    }

    protected function initPortfolioTemplateForm(
        array $a_templates
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("exc_create_portfolio") . ": " . $this->assignment->getTitle());
        $form->setFormAction($this->ctrl->getFormAction($this, "setSelectedPortfolioTemplate"));

        $prtt = new ilRadioGroupInputGUI($this->lng->txt("obj_prtt"), "prtt");
        $prtt->setRequired(true);
        $prtt->addOption(new ilRadioOption($this->lng->txt("exc_create_portfolio_no_template"), -1));
        foreach ($a_templates as $id => $title) {
            $prtt->addOption(new ilRadioOption('"' . $title . '"', $id));
        }
        $prtt->setValue(-1);
        $form->addItem($prtt);

        $form->addCommandButton("setSelectedPortfolioTemplate", $this->lng->txt("save"));
        $form->addCommandButton("returnToParent", $this->lng->txt("cancel"));

        return $form;
    }

    protected function createPortfolioFromAssignmentObject(): void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();

        //template id is stored in the DB with the ref_id.
        $template_id = $this->assignment->getPortfolioTemplateId();
        //get the object id to compare with a list of template objects.
        $template_object_id = ilObject::_lookupObjectId($template_id);

        // select a template, if available
        if ($templates !== [] && $template_object_id == 0) {
            $this->createPortfolioTemplateObject();
            return;
        }

        $title = $this->exercise->getTitle() . " - " . $this->assignment->getTitle();
        $ctrl->setParameterByClass("ilObjPortfolioGUI", "exc_id", $this->exercise->getRefId());
        $ctrl->setParameterByClass("ilObjPortfolioGUI", "ass_id", $this->assignment->getId());
        $ctrl->setParameterByClass("ilObjPortfolioGUI", "pt", $title);

        if ($template_object_id > 0) {
            $ctrl->setParameterByClass("ilObjPortfolioGUI", "prtt", $template_object_id);
        }
        $ctrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", $this->requested_ref_id);
        $ctrl->redirectByClass(array("ildashboardgui", "ilPortfolioRepositoryGUI", "ilObjPortfolioGUI"), "createPortfolioFromAssignment");
    }

    protected function createPortfolioTemplateObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
        if ($templates === []) {
            $this->returnToParentObject();
        }

        if ($a_form === null) {
            $a_form = $this->initPortfolioTemplateForm($templates);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    protected function setSelectedPortfolioTemplateObject(): void
    {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
        if ($templates === []) {
            $this->ctrl->redirect($this, "returnToParent");
        }

        $form = $this->initPortfolioTemplateForm($templates);
        if ($form->checkInput()) {
            $prtt = $form->getInput("prtt");
            if ($prtt > 0 && array_key_exists($prtt, $templates)) {
                $title = $this->exercise->getTitle() . " - " . $this->assignment->getTitle();
                $this->ctrl->setParameterByClass("ilObjPortfolioGUI", "exc_id", $this->exercise->getRefId());
                $this->ctrl->setParameterByClass("ilObjPortfolioGUI", "ass_id", $this->assignment->getId());
                $this->ctrl->setParameterByClass("ilObjPortfolioGUI", "pt", $title);
                $this->ctrl->setParameterByClass("ilObjPortfolioGUI", "prtt", $prtt);
                $this->ctrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", $this->requested_ref_id);
                $this->ctrl->redirectByClass(array("ildashboardgui", "ilPortfolioRepositoryGUI", "ilObjPortfolioGUI"), "createPortfolioFromTemplate");
            } else {
                // do not use template
                $this->createPortfolioObject();
                return;
            }
        }

        $form->setValuesByPost();
        $this->createPortfolioTemplateObject($form);
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    protected function createPortfolioObject(): void
    {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $portfolio = new ilObjPortfolio();
        $portfolio->setTitle($this->exercise->getTitle() . " - " . $this->assignment->getTitle());
        $portfolio->create();

        $this->submission->deleteAllFiles();
        $this->handleRemovedUpload();

        $this->submission->addResourceObject($portfolio->getId());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_portfolio_created"), true);
        $this->returnToParentObject();
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    protected function setSelectedPortfolioObject(): void
    {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $prtf_id = $this->request->getResourceObjectId();
        if ($prtf_id > 0) {
            $this->submission->deleteAllFiles();
            $this->handleRemovedUpload();

            $this->submission->addResourceObject($prtf_id);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_portfolio_selected"), true);
            $this->ctrl->setParameter($this, "prtf_id", $prtf_id);
            $this->ctrl->redirect($this, "askDirectSubmission");
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"));
        $this->selectPortfolioObject();
    }

    protected function askUnlinkPortfolioObject(): void
    {
        $tpl = $this->tpl;

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this, "unlinkPortfolio"));
        $conf->setHeaderText($this->lng->txt("exc_sure_unlink_portfolio", "sure_unlink_portfolio"));
        $conf->setConfirm($this->lng->txt("confirm"), "unlinkPortfolio");
        $conf->setCancel($this->lng->txt("cancel"), "returnToParent");

        $submission = $this->submission->getSelectedObject();
        $port = new ilObjPortfolio((int) $submission["filetitle"], false);

        $conf->addItem("id[]", "", $port->getTitle(), ilUtil::getImagePath("icon_prtf.svg"));

        $tpl->setContent($conf->getHTML());
    }

    protected function unlinkPortfolioObject(): void
    {
        global $DIC;

        $user = $DIC->user();

        //$portfolio = $this->submission->getSelectedObject();
        //$port_id = $portfolio["returned_id"];

        //$ilsub = new ilExSubmission($this->assignment, $user->getId());
        $this->submission->deleteResourceObject();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_portfolio_unlinked_from_assignment"), true);

        $this->ctrl->redirect($this, "returnToParent");
    }

    //
    // SUBMIT BLOG/PORTFOLIO
    //

    protected function askDirectSubmissionObject(): void
    {
        $tpl = $this->tpl;

        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $conf = new ilConfirmationGUI();

        if ($this->request->getBlogId() > 0) {
            $this->ctrl->setParameter($this, "blog_id", $this->request->getBlogId());
            $txt = $this->lng->txt("exc_direct_submit_blog");
        } else {
            $this->ctrl->setParameter($this, "prtf_id", $this->request->getPortfolioId());
            $txt = $this->lng->txt("exc_direct_submit_portfolio");
        }
        $conf->setFormAction($this->ctrl->getFormAction($this, "directSubmit"));

        $conf->setHeaderText($txt);
        $conf->setConfirm($this->lng->txt("exc_direct_submit"), "directSubmit");
        $conf->setCancel($this->lng->txt("exc_direct_no_submit"), "returnToParent");

        $tpl->setContent($conf->getHTML());
    }

    /**
     * @throws ilException
     * @throws ilFileUtilsException
     */
    protected function directSubmitObject(): void
    {
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        $success = false;

        // submit current version of blog
        if ($this->request->getBlogId() > 0) {
            $success = $this->submitBlog($this->request->getBlogId());
            $this->ctrl->setParameter($this, "blog_id", "");
        }
        // submit current version of portfolio
        elseif ($this->request->getPortfolioId() > 0) {
            $success = $this->submitPortfolio($this->request->getPortfolioId());
            $this->ctrl->setParameter($this, "prtf_id", "");
        }

        if ($success) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_failed"), true);
        }
        $this->ctrl->redirect($this, "returnToParent");
    }

    /**
     * Submit blog for assignment
     * @throws ilFileUtilsException
     * @throws ilException
     */
    public function submitBlog(
        int $a_blog_id
    ): bool {
        if (!$this->submission->canSubmit()) {
            return false;
        }

        $blog_id = $a_blog_id;

        $blog_gui = new ilObjBlogGUI($blog_id, ilObject2GUI::WORKSPACE_NODE_ID);
        if ($blog_gui->getObject()) {
            $file = $blog_gui->buildExportFile();
            $size = filesize($file);
            if ($size) {
                $this->submission->deleteAllFiles();

                $meta = array(
                    "name" => $blog_id . ".zip",
                    "tmp_name" => $file,
                    "size" => $size
                    );
                $this->submission->uploadFile($meta, true);

                // print version
                $file = $file = $blog_gui->buildExportFile(false, true);
                $size = filesize($file);
                if ($size) {
                    $meta = array(
                        "name" => $blog_id . "print.zip",
                        "tmp_name" => $file,
                        "size" => $size
                    );
                    $this->submission->uploadFile($meta, true);
                }

                $this->handleNewUpload();
                return true;
            }
        }
        return false;
    }

    /**
     * Submit portfolio for assignment
     * @throws ilFileUtilsException
     */
    public function submitPortfolio(
        int $a_portfolio_id
    ): bool {
        if (!$this->submission->canSubmit()) {
            return false;
        }

        $prtf_id = $a_portfolio_id;

        $prtf = new ilObjPortfolio($prtf_id, false);
        if ($prtf->getTitle()) {
            $port_gui = new ilObjPortfolioGUI($prtf_id);
            $port_export = new PortfolioHtmlExport($port_gui);
            $file = $port_export->exportHtml();

            $size = filesize($file);
            if ($size) {
                $this->submission->deleteAllFiles();

                $meta = array(
                    "name" => $prtf_id . ".zip",
                    "tmp_name" => $file,
                    "size" => $size
                    );
                $this->submission->uploadFile($meta, true);

                // print version
                $port_export->setPrintVersion(true);
                $file = $port_export->exportHtml();
                $size = filesize($file);
                if ($size) {
                    $meta = array(
                        "name" => $prtf_id . "print.zip",
                        "tmp_name" => $file,
                        "size" => $size
                    );
                    $this->submission->uploadFile($meta, true);
                }

                $this->handleNewUpload();
                return true;
            }
        }
        return false;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public static function initGUIForSubmit(
        int $a_ass_id,
        int $a_user_id = null
    ): ilExSubmissionObjectGUI {
        global $DIC;

        $ilUser = $DIC->user();

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }

        $ass = new ilExAssignment($a_ass_id);
        $sub = new ilExSubmission($ass, $a_user_id);
        $exc_id = $ass->getExerciseId();

        // #11173 - ref_id is needed for notifications
        $ref_ids = ilObject::_getAllReferences($exc_id);
        $exc_ref_id = current($ref_ids);
        $exc = new ilObjExercise($exc_ref_id, true);

        return new self($exc, $sub);
    }
}
