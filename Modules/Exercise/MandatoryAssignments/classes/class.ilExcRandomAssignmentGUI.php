<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * UI for random assignment
 *
 * (ui)
 *
 * @author killing@leifos.de
 */
class ilExcRandomAssignmentGUI
{
    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $main_tpl;

    /**
     * @var ilExcRandomAssignmentManager
     */
    protected $random_manager;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     */
    public function __construct(\ILIAS\DI\UIServices $ui, ilToolbarGUI $toolbar, ilLanguage $lng, ilCtrl $ctrl, ilExcRandomAssignmentManager $random_manager)
    {
        $this->main_tpl = $ui->mainTemplate();
        $this->ui = $ui;
        $this->random_manager = $random_manager;
        $this->toolbar = $toolbar;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("startExercise");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("startExercise"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Render start page
     */
    public function renderStartPage()
    {
        $toolbar = $this->toolbar;
        $lng = $this->lng;

        $but = $this->ui->factory()->button()->primary(
            $lng->txt("exc_start_exercise"),
            $this->ctrl->getLinkTarget($this, "startExercise")
        );
        $toolbar->addComponent($but);
        $info_gui = new ilInfoScreenGUI($this);

        $info_gui->addSection($lng->txt("exc_random_assignment"));
        $info_gui->addProperty(
            " ",
            $lng->txt("exc_random_assignment_info")
        );
        $info_gui->addProperty(
            $lng->txt("exc_rand_overall_ass"),
            $this->random_manager->getTotalNumberOfAssignments()
        );
        $info_gui->addProperty(
            $lng->txt("exc_rand_nr_mandatory"),
            $this->random_manager->getNumberOfMandatoryAssignments()
        );
        $this->main_tpl->setContent($info_gui->getHTML());
    }
    
    /**
     * Start exercise
     */
    protected function startExercise()
    {
        $this->random_manager->startExercise();
        $this->ctrl->redirectByClass("ilObjExerciseGUI", "showOverview");
    }
}
