<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Listing\Workflow\Step;

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilProfileChecklistGUI
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilProfileChecklistStatus
     */
    protected $status;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->status = new ilProfileChecklistStatus();
        $this->lng = $DIC->language();
    }

    /**
     * Render
     *
     * @param
     * @return
     */
    public function render($active_step)
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $workflow_factory = $ui->factory()->listing()->workflow();
        $status = $this->status;

        //setup steps
        $steps = [];
        $cnt = 0;
        foreach ($this->status->getSteps() as $step => $txt) {
            if ($step == $active_step) {
                $active_step_nr = $cnt;
            }
            $cnt++;
            $s = $workflow_factory->step($txt, $status->getStatusDetails($step))
                ->withStatus($this->getUIChecklistStatus($status->getStatus($step)));
            $steps[] = $s;
        }

        //setup linear workflow
        $wf = $workflow_factory->linear($lng->txt("user_privacy_checklist"), $steps)
            ->withActive($active_step_nr);

        //render
        return $ui->renderer()->render($wf);
    }

    /**
     * Get ui checklist status. Maps the checklist status to the UI element status.
     * @param int $check_list_status
     * @return int
     */
    protected function getUIChecklistStatus(int $check_list_status)
    {
        switch ($check_list_status) {
            case ilProfileChecklistStatus::STATUS_NOT_STARTED: return Step::NOT_STARTED; break;
            case ilProfileChecklistStatus::STATUS_IN_PROGRESS: return Step::IN_PROGRESS; break;
            case ilProfileChecklistStatus::STATUS_SUCCESSFUL: return Step::SUCCESSFULLY; break;
        }
    }
}
