<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Personal Desktop-Presentation for the Study Programme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 * @author : Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @ilCtrl_IsCalledBy ilPDStudyProgrammeSimpleListGUI: ilColumnGUI
 */
class ilPDStudyProgrammeSimpleListGUI extends ilBlockGUI
{
    const BLOCK_TYPE = "prgsimplelist";

    protected ilSetting $setting;
    /**
     * @var ilComponentLogger|ilLogger
     */
    protected $logger;
    protected ilStudyProgrammeAssignmentDBRepository $sp_user_assignment_db;

    /**
     * @var ilStudyProgrammeAssignment[]
     */
    protected array $users_assignments;
    protected ?string $visible_on_pd_mode;
    protected bool $show_info_message;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->setting = $DIC['ilSetting'];
        $this->logger = ilLoggerFactory::getLogger('prg');
        $this->sp_user_assignment_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];

        // No need to load data, as we won't display this.
        if (!$this->shouldShowThisList()) {
            return;
        }

        $this->getUsersAssignments();
        //check which kind of option is selected in settings
        $this->getVisibleOnPDMode();
        //check to display info message if option "read" is selected
        $this->getToShowInfoMessage();
        
        // As this won't be visible we don't have to initialize this.
        if (!$this->userHasReadableStudyProgrammes()) {
            return;
        }

        $this->setTitle($this->lng->txt("objs_prg"));
    }
    
    public function getHTML() : string
    {
        // TODO: This should be determined from somewhere up in the hierarchy, as
        // this will lead to problems, when e.g. a command changes. But i don't see
        // how atm...
        if (!$this->shouldShowThisList()) {
            return "";
        }
        
        if (!$this->userHasReadableStudyProgrammes()) {
            return "";
        }
        return parent::getHTML();
    }
    
    public function getDataSectionContent() : string
    {
        $content = "";
        foreach ($this->users_assignments as $assignment) {
            if (!$this->isReadable($assignment)) {
                continue;
            }

            try {
                $list_item = $this->new_ilStudyProgrammeAssignmentListGUI($assignment);
                $list_item->setShowInfoMessage($this->show_info_message);
                $list_item->setVisibleOnPDMode($this->visible_on_pd_mode);
                $content .= $list_item->getHTML();
            } catch (ilStudyProgrammeNoProgressForAssignmentException $e) {
                $this->logger->alert("$e");
            } catch (ilStudyProgrammeTreeException $e) {
                $this->logger->alert("$e");
            }
        }
        return $content;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::BLOCK_TYPE;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }
    
    public function fillDataSection() : void
    {
        assert($this->userHasReadableStudyProgrammes()); // We should not get here.
        $this->tpl->setVariable("BLOCK_ROW", $this->getDataSectionContent());
    }
    
    protected function userHasVisibleStudyProgrammes() : bool
    {
        if (count($this->users_assignments) == 0) {
            return false;
        }
        foreach ($this->users_assignments as $assignment) {
            if ($this->isVisible($assignment)) {
                return true;
            }
        }
        return false;
    }

    protected function userHasReadableStudyProgrammes() : bool
    {
        if (count($this->users_assignments) == 0) {
            return false;
        }
        foreach ($this->users_assignments as $assignment) {
            if ($this->isReadable($assignment)) {
                return true;
            }
        }
        return false;
    }
    
    protected function getVisibleOnPDMode() : void
    {
        $this->visible_on_pd_mode = $this->setting->get(ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD);
    }

    protected function hasPermission(ilStudyProgrammeAssignment $assignment, string $permission) : bool
    {
        $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
        return $this->access->checkAccess($permission, "", $prg->getRefId(), "prg", $prg->getId());
    }

    protected function getToShowInfoMessage() : void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), (int) $_GET['view']);
        $this->show_info_message = $viewSettings->isStudyProgrammeViewActive();
    }

    protected function isVisible(ilStudyProgrammeAssignment $assignment) : bool
    {
        return $this->hasPermission($assignment, "visible");
    }

    protected function isReadable(ilStudyProgrammeAssignment $assignment) : bool
    {
        if ($this->visible_on_pd_mode == ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
            return true;
        }

        return $this->hasPermission($assignment, "read");
    }
    
    protected function shouldShowThisList() : bool
    {
        $jump_to_selected_list = $_GET["cmd"] == "jumpToSelectedItems";
        $is_ilDashboardGUI = $this->ctrl->getCmdClass() == "ildashboardgui";
        $is_cmd_show = $this->ctrl->getCmd() == "show";

        return ($jump_to_selected_list || ($is_ilDashboardGUI && $is_cmd_show)) && !$_GET["expand"];
    }
    
    protected function getUsersAssignments() : void
    {
        $this->users_assignments = $this->sp_user_assignment_db->getInstancesOfUser($this->user->getId());
    }
    
    protected function new_ilStudyProgrammeAssignmentListGUI(
        ilStudyProgrammeAssignment $assignment
    ) : ilStudyProgrammeProgressListGUI {
        $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
        $progress = $prg->getProgressForAssignment($assignment->getId());
        $progress_gui = new ilStudyProgrammeProgressListGUI($progress);
        $progress_gui->setOnlyRelevant(true);
        return $progress_gui;
    }
}
