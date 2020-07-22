<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/interface.ilExAssignmentTypeGUIInterface.php");
include_once("./Modules/Exercise/AssignmentTypes/GUI/traits/trait.ilExAssignmentTypeGUIBase.php");

/**
 * Team wiki type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilExAssTypeWikiTeamGUI: ilExSubmissionGUI
 */
class ilExAssTypeWikiTeamGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    const MODE_OVERVIEW = "overview";

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, array("createWiki"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form)
    {
        $lng = $this->lng;

        // template
        $rd_template = new ilRadioGroupInputGUI($lng->txt("exc_template"), "template");
        $rd_template->setRequired(true);
        $radio_no_template = new ilRadioOption($lng->txt("exc_without_wiki_template"), 0, $lng->txt("exc_without_wiki_template_info"));
        $radio_with_template = new ilRadioOption($lng->txt("exc_with_wiki_template"), 1, $lng->txt("exc_with_wiki_template_info"));

        include_once "Services/Form/classes/class.ilRepositorySelector2InputGUI.php";
        $repo = new ilRepositorySelector2InputGUI($lng->txt("wiki_exc_template"), "template_ref_id");
        $repo->setRequired(true);
        $repo->getExplorerGUI()->setSelectableTypes(array("wiki"));
        $repo->getExplorerGUI()->setTypeWhiteList(array("root", "wiki", "cat", "crs", "grp", "fold"));
        $radio_with_template->addSubItem($repo);

        $rd_template->addOption($radio_no_template);
        $rd_template->addOption($radio_with_template);
        $form->addItem($rd_template);

        // container
        include_once "Services/Form/classes/class.ilRepositorySelector2InputGUI.php";
        $cont = new ilRepositorySelector2InputGUI($lng->txt("exc_wiki_container"), "container_ref_id");
        $cont->setRequired(true);
        $cont->setInfo($lng->txt("exc_wiki_container_info"));
        $cont->getExplorerGUI()->setSelectableTypes(array("cat", "crs", "grp", "fold"));
        $cont->getExplorerGUI()->setTypeWhiteList(array("root", "cat", "crs", "grp", "fold"));
        $form->addItem($cont);
    }

    /**
     * @inheritdoc
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form)
    {
        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssWikiTeamAR.php");
        $ar = new ilExAssWikiTeamAR();
        $ar->setId($ass->getId());
        $ar->setTemplateRefId(0);
        if ($form->getInput("template_ref_id") && $form->getInput("template")) {
            $ar->setTemplateRefId($form->getInput("template_ref_id"));
        }
        $ar->setContainerRefId($form->getInput("container_ref_id"));
        $ar->save();
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass)
    {
        $values = [];

        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssWikiTeamAR.php");
        $ar = new ilExAssWikiTeamAR($ass->getId());

        if ($ar->getTemplateRefId() > 0) {
            $values["template_ref_id"] = $ar->getTemplateRefId();
            $values["template"] = 1;
        }
        $values["container_ref_id"] = $ar->getContainerRefId();

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
        $this->ctrl->getHTML($this, array("mode" => self::MODE_OVERVIEW, "info" => $a_info, "submission" => $a_submission));
    }

    /**
     * Get HTML
     *
     * @param array $par parameter
     */
    public function getHTML($par)
    {
        switch ($par["mode"]) {
            case self::MODE_OVERVIEW:
                $this->renderOverviewContent($par["info"], $par["submission"]);
                break;
        }
    }

    /**
     * Render overview content
     *
     * @param ilInfoScreenGUI $a_info
     * @param ilExSubmission $a_submission
     */
    protected function renderOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        include_once "Modules/Wiki/classes/class.ilObjWiki.php";

        $files_str = "";
        $valid_wiki = false;

        $team_members = $a_submission->getTeam()->getMembers();
        $team_available = (sizeof($team_members));

        $selected_wiki = $a_submission->getSelectedObject();
        if ($selected_wiki) {
            $wiki_ref_id = (int) $selected_wiki["filetitle"];

            // #11746
            if (ilObject::_exists($wiki_ref_id, true, "wiki") && $this->tree->isInTree($wiki_ref_id)) {
                $wiki = new ilObjWiki($wiki_ref_id);
                if ($wiki->getTitle()) {
                    // #10116 / #12791
                    $ctrl->setParameterByClass("ilobjwikigui", "ref_id", $wiki_ref_id);
                    $wiki_link = ilLink::_getLink($wiki_ref_id);
                    $files_str = '<a href="' . $wiki_link .
                        '">' . $wiki->getTitle() . '</a>';
                    $valid_wiki = true;
                }
            }
            // remove invalid resource if no upload yet (see download below)
            elseif (substr($selected_wiki["filename"], -1) == "/") {
                // #16887
                $a_submission->deleteResourceObject($selected_wiki["returned_id"]);
            }
        }
        if ($a_submission->canSubmit()) {
            if (!$valid_wiki && $team_available) {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_create_wiki");
                $button->setUrl($ctrl->getLinkTarget($this, "createWiki"));

                $files_str .= "" . $button->render();
            }
        }
        if ($files_str) {
            $a_info->addProperty($lng->txt("exc_ass_team_wiki"), $files_str);
        }
        if ($a_submission->hasSubmitted()) {
            $ctrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_wiki["returned_id"]);
            $dl_link = $ctrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "download");
            $ctrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", "");

            $button = ilLinkButton::getInstance();
            $button->setCaption("download");
            $button->setUrl($dl_link);

            $a_info->addProperty($lng->txt("exc_files_returned"), $button->render());
        }
    }


    /**
     * Create wiki for assignment
     */
    protected function createWiki()
    {
        $access = $this->access;
        $lng = $this->lng;

        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssWikiTeamAR.php");
        $ar = new ilExAssWikiTeamAR($this->submission->getAssignment()->getId());
        $template_ref_id = $ar->getTemplateRefId();
        $container_ref_id = $ar->getContainerRefId();

        // @todo: move checks to central place
        // check if team exists
        $team_members = $this->submission->getTeam()->getMembers();
        $team_available = (sizeof($team_members));
        if (!$team_available) {
            $lng->loadLanguageModule("exc");
            ilUtil::sendInfo($lng->txt("exc_team_needed_first"), true);
            $this->ctrl->returnToParent($this);
        }

        // check if submission is possible
        if (!$this->submission->canSubmit()) {
            $lng->loadLanguageModule("exc");
            ilUtil::sendInfo($lng->txt("exercise_time_over"), true);
            $this->ctrl->returnToParent($this);
        }

        // check create permission of exercise owner
        if (!$access->checkAccessOfUser($this->exercise->getOwner(), "create", "", $container_ref_id, "wiki")) {
            $lng->loadLanguageModule("exc");
            ilUtil::sendInfo($lng->txt("exc_owner_has_no_permission_to_create_wiki"), true);
            $this->ctrl->returnToParent($this);
        }

        if ($template_ref_id > 0 && ilObject::_exists($template_ref_id, true, "wiki")) {
            $template_wiki = new ilObjWiki($template_ref_id);
            $wiki = $template_wiki->cloneObject($container_ref_id);
            $wiki->setTitle($this->exercise->getTitle() . " - " . $this->submission->getAssignment()->getTitle());
        } else {
            include_once "Modules/Wiki/classes/class.ilObjWiki.php";
            $wiki = new ilObjWiki();
            $wiki->setTitle($this->exercise->getTitle() . " - " . $this->submission->getAssignment()->getTitle());
            $wiki->create();
            $wiki->setStartPage($this->submission->getAssignment()->getTitle());

            $wiki->createReference();
            $wiki->putInTree($container_ref_id);
            $wiki->setPermissions($container_ref_id);
        }

        $wiki->setOwner($this->exercise->getOwner());
        $wiki->setOnline(true);
        $wiki->update();
        $wiki->updateOwner();

        $this->submission->deleteAllFiles();
        //$this->handleRemovedUpload();

        $this->submission->addResourceObject($wiki->getRefId());

        $lng->loadLanguageModule("wiki");
        ilUtil::sendSuccess($lng->txt("wiki_exc_wiki_created"), true);
        $this->ctrl->returnToParent($this);
    }
}
