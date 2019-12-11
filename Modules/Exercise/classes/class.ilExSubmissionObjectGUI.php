<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object-based submissions (ends up as static file)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilExSubmissionObjectGUI:
 * @ingroup ModulesExercise
 */
class ilExSubmissionObjectGUI extends ilExSubmissionBaseGUI
{
    /**
     * Execute command
     *
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
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
    
    public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
        switch ($a_submission->getAssignment()->getType()) {
            case ilExAssignment::TYPE_BLOG:
                return self::getOverviewContentBlog($a_info, $a_submission);
            
            case ilExAssignment::TYPE_PORTFOLIO:
                return self::getOverviewContentPortfolio($a_info, $a_submission);
        }
    }
    
    protected static function getOverviewContentBlog(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
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
                $blog_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjbloggui"), "");
                $ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", "");
                $files_str = '<a href="' . $blog_link . '">' .
                    $node["title"] . '</a>';
                $valid_blog = true;
            }
            // remove invalid resource if no upload yet (see download below)
            elseif (substr($selected_blog["filename"], -1) == "/") {
                // #16887
                $a_submission->deleteResourceObject($selected_blog["returned_id"]);
            }
        }
        if ($a_submission->canSubmit()) {
            if (!$valid_blog) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_create_blog");
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createBlog"));
                $buttons_str.= $button->render();
            }
            // #10462
            $blogs = sizeof($wsp_tree->getObjectsFromType("blog"));
            if ((!$valid_blog && $blogs)
                || ($valid_blog && $blogs > 1)) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_select_blog" . ($valid_blog ? "_change" : ""));
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectBlog"));
                $buttons_str.= " " . $button->render();
            }
        }

        // todo: move this to ks somehow
        if ($buttons_str != "") {
            $files_str.="<p>" . $buttons_str . "</p>";
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

    protected static function getOverviewContentPortfolio(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
                        
        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";

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

                    $ref_id = $_REQUEST['ref_id'];
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "ref_id", $ref_id);

                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", (int) $_GET["ref_id"]);

                    $prtf_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "view");
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
                $a_submission->deleteResourceObject($selected_prtf["returned_id"]);
            }
        }
        if ($a_submission->canSubmit()) {
            if (!$valid_prtf) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_create_portfolio");
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createPortfolioFromAssignment"));

                $buttons_str .= "" . $button->render();
            }
            // #10462
            //selectPortfolio ( remove it? )
            $prtfs = sizeof(ilObjPortfolio::getPortfoliosOfUser($a_submission->getUserId()));
            if ((!$valid_prtf && $prtfs)
                || ($valid_prtf && $prtfs > 1)) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_select_portfolio" . ($valid_prtf ? "_change" : ""));
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectPortfolio"));
                $buttons_str.= " " . $button->render();
            }
            if ($valid_prtf) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_select_portfolio" . ($valid_prtf ? "_unlink" : ""));
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "askUnlinkPortfolio"));
                $buttons_str.= " " . $button->render();
            }
        }
        // todo: move this to ks somehow
        if ($buttons_str != "") {
            $files_str.="<p>" . $buttons_str . "</p>";
        }
        if ($files_str) {
            $a_info->addProperty($lng->txt("exc_portfolio_returned"), $files_str);
        }
        if ($a_submission->hasSubmitted()) {
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_prtf["returned_id"]);
            $dl_link =$ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "download");
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", "");

            $button = ilLinkButton::getInstance();
            $button->setCaption("download");
            $button->setUrl($dl_link);

            $a_info->addProperty($lng->txt("exc_files_returned"), $button->render());
        }
    }
    
    protected function renderResourceSelection($a_title, $a_info, $a_cmd, $a_explorer_cmd, array $a_items = null)
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
                
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
                

        ilUtil::sendInfo($this->lng->txt($a_info));
        
        $title = $this->lng->txt($a_title) . ": " . $this->assignment->getTitle();
        
        include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
        $panel = ilPanelGUI::getInstance();
        $panel->setBody($html);
        $panel->setHeading($title);
                    
        $this->tpl->setContent($panel->getHTML());
    }
    
    
    //
    // BLOG
    //
    
    protected function createBlogObject()
    {
        $this->handleTabs();
                
        return $this->renderResourceSelection(
            "exc_create_blog",
            "exc_create_blog_select_info",
            "saveBlog",
            "createBlog"
        );
    }
    
    protected function selectBlogObject()
    {
        $this->handleTabs();
        
        return $this->renderResourceSelection(
            "exc_select_blog",
            "exc_select_blog_info",
            "setSelectedBlog",
            "selectBlog"
        );
    }
    
    protected function saveBlogObject()
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }

        if (!$_GET["sel_wsp_obj"]) {
            ilUtil::sendFailure($this->lng->txt("select_one"));
            return $this->createBlogObject();
        }
        
        $parent_node = $_GET["sel_wsp_obj"];
        
        include_once "Modules/Blog/classes/class.ilObjBlog.php";
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
        
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
        
        ilUtil::sendSuccess($this->lng->txt("exc_blog_created"), true);
        $this->returnToParentObject();
    }
    
    protected function setSelectedBlogObject()
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        if ($_GET["sel_wsp_obj"]) {
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
            $tree = new ilWorkspaceTree($this->submission->getUserId());
            $node = $tree->getNodeData($_GET["sel_wsp_obj"]);
            if ($node && $node["type"] == "blog") {
                $this->submission->deleteAllFiles();
                $this->handleRemovedUpload();
                
                $this->submission->addResourceObject($node["wsp_id"]);
                
                ilUtil::sendSuccess($this->lng->txt("exc_blog_selected"), true);
                $this->ctrl->setParameter($this, "blog_id", $node["wsp_id"]);
                $this->ctrl->redirect($this, "askDirectSubmission");
            }
        }
        
        //		ilUtil::sendFailure($this->lng->txt("select_one_blog"));
        return $this->selectBlogObject();
    }

    /**
     * @param string $a_cmd
     * @return string
     */
    protected function renderWorkspaceExplorer($a_cmd)
    {
        include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorerGUI.php");
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
    
    protected function selectPortfolioObject()
    {
        $this->handleTabs();
        
        $items = array();
        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
        $portfolios = ilObjPortfolio::getPortfoliosOfUser($this->submission->getUserId());
        if ($portfolios) {
            foreach ($portfolios as $portfolio) {
                $items[$portfolio["id"]]= $portfolio["title"];
            }
        }
        
        return $this->renderResourceSelection(
            "exc_select_portfolio",
            "exc_select_portfolio_info",
            "setSelectedPortfolio",
            null,
            $items
        );
    }
    
    protected function initPortfolioTemplateForm(array $a_templates)
    {
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
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

    protected function createPortfolioFromAssignmentObject()
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";

        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();

        //template id is stored in the DB with the ref_id.
        $template_id = $this->assignment->getPortfolioTemplateId();
        //get the object id to compare with a list of template objects.
        $template_object_id = ilObject::_lookupObjectId($template_id);

        // select a template, if available
        if (count($templates) > 0 && $template_object_id == 0) {
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
        $ctrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", (int) $_GET["ref_id"]);
        $ctrl->redirectByClass(array("ilPersonalDesktopGUI", "ilPortfolioRepositoryGUI", "ilObjPortfolioGUI"), "createPortfolioFromAssignment");
    }

    protected function createPortfolioTemplateObject(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
        if (!sizeof($templates)) {
            $this->returnToParentObject();
        }
        
        if (!$a_form) {
            $a_form = $this->initPortfolioTemplateForm($templates);
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function setSelectedPortfolioTemplateObject()
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
        if (!sizeof($templates)) {
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
                $this->ctrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", (int) $_GET["ref_id"]);
                $this->ctrl->redirectByClass(array("ilPersonalDesktopGUI", "ilPortfolioRepositoryGUI", "ilObjPortfolioGUI"), "createPortfolioFromTemplate");
            } else {
                // do not use template
                return $this->createPortfolioObject();
            }
        }
        
        $form->setValuesByPost();
        $this->createPortfolioTemplateObject($form);
    }
    
    protected function createPortfolioObject()
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
        $portfolio = new ilObjPortfolio();
        $portfolio->setTitle($this->exercise->getTitle() . " - " . $this->assignment->getTitle());
        $portfolio->create();
    
        $this->submission->deleteAllFiles();
        $this->handleRemovedUpload();
            
        $this->submission->addResourceObject($portfolio->getId());
        
        ilUtil::sendSuccess($this->lng->txt("exc_portfolio_created"), true);
        $this->returnToParentObject();
    }
    
    protected function setSelectedPortfolioObject()
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        if ($_POST["item"]) {
            $this->submission->deleteAllFiles();
            $this->handleRemovedUpload();
            
            $this->submission->addResourceObject($_POST["item"]);
                        
            ilUtil::sendSuccess($this->lng->txt("exc_portfolio_selected"), true);
            $this->ctrl->setParameter($this, "prtf_id", $_POST["item"]);
            $this->ctrl->redirect($this, "askDirectSubmission");
        }
        
        ilUtil::sendFailure($this->lng->txt("select_one"));
        return $this->selectPortfolioObject();
    }

    protected function askUnlinkPortfolioObject()
    {
        $tpl = $this->tpl;

        include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this, "unlinkPortfolio"));
        $conf->setHeaderText($this->lng->txt("exc_sure_unlink_portfolio", "sure_unlink_portfolio"));
        $conf->setConfirm($this->lng->txt("confirm"), "unlinkPortfolio");
        $conf->setCancel($this->lng->txt("cancel"), "returnToParent");

        $submission = $this->submission->getSelectedObject();
        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
        $port = new ilObjPortfolio($submission["filetitle"], false);

        $conf->addItem("id[]", "", $port->getTitle(), ilUtil::getImagePath("icon_prtf.svg"));

        $tpl->setContent($conf->getHTML());
    }

    protected function unlinkPortfolioObject()
    {
        global $DIC;

        $user = $DIC->user();

        $portfolio = $this->submission->getSelectedObject();
        $port_id = $portfolio["returned_id"];
        
        $ilsub = new ilExSubmission($this->assignment, $user->getId());
        $ilsub->deleteResourceObject($port_id);

        ilUtil::sendSuccess($this->lng->txt("exc_portfolio_unlinked_from_assignment"), true);

        $this->ctrl->redirect($this, "returnToParent");
    }
    
    //
    // SUBMIT BLOG/PORTFOLIO
    //
    
    protected function askDirectSubmissionObject()
    {
        $tpl = $this->tpl;
        
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $conf = new ilConfirmationGUI();
        
        if ($_REQUEST["blog_id"]) {
            $this->ctrl->setParameter($this, "blog_id", $_REQUEST["blog_id"]);
            $txt = $this->lng->txt("exc_direct_submit_blog");
        } else {
            $this->ctrl->setParameter($this, "prtf_id", $_REQUEST["prtf_id"]);
            $txt = $this->lng->txt("exc_direct_submit_portfolio");
        }
        $conf->setFormAction($this->ctrl->getFormAction($this, "directSubmit"));
        
        $conf->setHeaderText($txt);
        $conf->setConfirm($this->lng->txt("exc_direct_submit"), "directSubmit");
        $conf->setCancel($this->lng->txt("exc_direct_no_submit"), "returnToParent");
        
        $tpl->setContent($conf->getHTML());
    }
    
    protected function directSubmitObject()
    {
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
            $this->returnToParentObject();
        }
        
        $success = false;
        
        // submit current version of blog
        if ($_REQUEST["blog_id"]) {
            $success = $this->submitBlog($_REQUEST["blog_id"]);
            $this->ctrl->setParameter($this, "blog_id", "");
        }
        // submit current version of portfolio
        elseif ($_REQUEST["prtf_id"]) {
            $success = 	$this->submitPortfolio($_REQUEST["prtf_id"]);
            $this->ctrl->setParameter($this, "prtf_id", "");
        }
                
        if ($success) {
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        } else {
            ilUtil::sendFailure($this->lng->txt("msg_failed"), true);
        }
        $this->ctrl->redirect($this, "returnToParent");
    }
    
    /**
     * Submit blog for assignment
     *
     * @param int $a_blog_id
     * @return bool
     */
    public function submitBlog($a_blog_id)
    {
        if (!$this->submission->canSubmit()) {
            return;
        }
        
        $blog_id = $a_blog_id;

        include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
        $blog_gui = new ilObjBlogGUI($blog_id, ilObjBlogGUI::WORKSPACE_NODE_ID);
        if ($blog_gui->object) {
            $file = $blog_gui->buildExportFile();
            $size = filesize($file);
            if ($size) {
                $this->submission->deleteAllFiles();

                $meta = array(
                    "name" => $blog_id,
                    "tmp_name" => $file,
                    "size" => $size
                    );
                $this->submission->uploadFile($meta, true);

                $this->handleNewUpload();
                return true;
            }
        }
        return false;
    }
    
    /**
     * Submit portfolio for assignment
     *
     * @param int $a_portfolio_id
     * @return bool
     */
    public function submitPortfolio($a_portfolio_id)
    {
        if (!$this->submission->canSubmit()) {
            return;
        }
        
        $prtf_id = $a_portfolio_id;

        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
        $prtf = new ilObjPortfolio($prtf_id, false);
        if ($prtf->getTitle()) {
            include_once "Modules/Portfolio/classes/class.ilPortfolioHTMLExport.php";
            $export = new ilPortfolioHTMLExport(null, $prtf);
            $file = $export->buildExportFile();
            $size = filesize($file);
            if ($size) {
                $this->submission->deleteAllFiles();

                $meta = array(
                    "name" => $prtf_id,
                    "tmp_name" => $file,
                    "size" => $size
                    );
                $this->submission->uploadFile($meta, true);

                $this->handleNewUpload();
                return true;
            }
        }
        return false;
    }
    
    public static function initGUIForSubmit($a_ass_id, $a_user_id = null)
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        
        include_once "Modules/Exercise/classes/class.ilObjExercise.php";
        include_once "Modules/Exercise/classes/class.ilExAssignment.php";
        include_once "Modules/Exercise/classes/class.ilExSubmission.php";
                        
        $ass = new ilExAssignment($a_ass_id);
        $sub = new ilExSubmission($ass, $a_user_id);
        $exc_id = $ass->getExerciseId();
        
        // #11173 - ref_id is needed for notifications
        $exc_ref_id = array_shift(ilObject::_getAllReferences($exc_id));
        $exc = new ilObjExercise($exc_ref_id, true);
                
        return new self($exc, $sub);
    }
}
