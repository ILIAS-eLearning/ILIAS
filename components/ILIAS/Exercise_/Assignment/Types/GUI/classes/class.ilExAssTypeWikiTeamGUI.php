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

/**
 * Team wiki type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilExAssTypeWikiTeamGUI: ilExSubmissionGUI
 */
class ilExAssTypeWikiTeamGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    public const MODE_OVERVIEW = "overview";
    protected \ILIAS\Exercise\InternalGUIService $gui;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTree $tree;
    protected ilAccessHandler $access;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->gui = $DIC->exercise()
            ->internal()
            ->gui();
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if ($cmd === "createWiki") {
                    $this->$cmd();
                }
        }
    }

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form): void
    {
        $lng = $this->lng;

        // template
        $rd_template = new ilRadioGroupInputGUI($lng->txt("exc_template"), "template");
        $rd_template->setRequired(true);
        $radio_no_template = new ilRadioOption($lng->txt("exc_without_wiki_template"), 0, $lng->txt("exc_without_wiki_template_info"));
        $radio_with_template = new ilRadioOption($lng->txt("exc_with_wiki_template"), 1, $lng->txt("exc_with_wiki_template_info"));

        $repo = new ilRepositorySelector2InputGUI($lng->txt("wiki_exc_template"), "template_ref_id");
        $repo->setRequired(true);
        $repo->getExplorerGUI()->setSelectableTypes(array("wiki"));
        $repo->getExplorerGUI()->setTypeWhiteList(array("root", "wiki", "cat", "crs", "grp", "fold"));
        $radio_with_template->addSubItem($repo);

        $rd_template->addOption($radio_no_template);
        $rd_template->addOption($radio_with_template);
        $form->addItem($rd_template);

        // container
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
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form): void
    {
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
    public function getFormValuesArray(ilExAssignment $ass): array
    {
        $values = [];

        $ar = new ilExAssWikiTeamAR($ass->getId());

        if ($ar->getTemplateRefId() > 0) {
            $values["template_ref_id"] = $ar->getTemplateRefId();
            $values["template"] = 1;
        }
        $values["container_ref_id"] = $ar->getContainerRefId();

        return $values;
    }

    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission): void
    {
        $this->ctrl->getHTML($this, array("mode" => self::MODE_OVERVIEW, "info" => $a_info, "submission" => $a_submission));
    }

    /**
     * Get HTML
     *
     * @param array $par parameter
     */
    public function getHTML(array $par): string
    {
        switch ($par["mode"]) {
            case self::MODE_OVERVIEW:
                $this->renderOverviewContent($par["info"], $par["submission"]);
                break;
        }
        return "";
    }

    /**
     * Render overview content
     */
    protected function renderOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $files_str = "";
        $valid_wiki = false;

        $team_members = $a_submission->getTeam()->getMembers();
        $team_available = (count($team_members));

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
                $a_submission->deleteResourceObject();
            }
        }
        if ($a_submission->canSubmit()) {
            if (!$valid_wiki && $team_available) {
                $files_str .= $this->gui->button(
                    $lng->txt("exc_create_wiki"),
                    $ctrl->getLinkTarget($this, "createWiki")
                )->render();
            }
        }
        if ($files_str) {
            $a_info->addProperty($lng->txt("exc_ass_team_wiki"), $files_str);
        }
        if ($a_submission->hasSubmitted()) {
            $ctrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_wiki["returned_id"]);
            $dl_link = $ctrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "download");
            $ctrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", "");

            $a_info->addProperty($lng->txt("exc_files_returned"), $this->gui->button(
                $lng->txt("download"),
                $dl_link
            )->render());
        }
    }


    /**
     * Create wiki for assignment
     */
    protected function createWiki(): void
    {
        $access = $this->access;
        $lng = $this->lng;

        $ar = new ilExAssWikiTeamAR($this->submission->getAssignment()->getId());
        $template_ref_id = $ar->getTemplateRefId();
        $container_ref_id = $ar->getContainerRefId();

        // @todo: move checks to central place
        // check if team exists
        $team_members = $this->submission->getTeam()->getMembers();
        $team_available = (sizeof($team_members));
        if (!$team_available) {
            $lng->loadLanguageModule("exc");
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("exc_team_needed_first"), true);
            $this->ctrl->returnToParent($this);
        }

        // check if submission is possible
        if (!$this->submission->canSubmit()) {
            $lng->loadLanguageModule("exc");
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("exercise_time_over"), true);
            $this->ctrl->returnToParent($this);
        }

        // check create permission of exercise owner
        if (!$access->checkAccessOfUser($this->exercise->getOwner(), "create", "", $container_ref_id, "wiki")) {
            $lng->loadLanguageModule("exc");
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("exc_owner_has_no_permission_to_create_wiki"), true);
            $this->ctrl->returnToParent($this);
        }

        if ($template_ref_id > 0 && ilObject::_exists($template_ref_id, true, "wiki")) {
            $template_wiki = new ilObjWiki($template_ref_id);
            $wiki = $template_wiki->cloneObject($container_ref_id);
            $wiki->setTitle($this->exercise->getTitle() . " - " . $this->submission->getAssignment()->getTitle());
        } else {
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
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("wiki_exc_wiki_created"), true);
        $this->ctrl->returnToParent($this);
    }
}
