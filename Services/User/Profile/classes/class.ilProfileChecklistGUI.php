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

use ILIAS\UI\Component\Listing\Workflow\Step;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilProfileChecklistGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected ilProfileChecklistStatus $status;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->status = new ilProfileChecklistStatus();
        $this->lng = $DIC->language();
    }

    public function render(int $active_step): string
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $active_step_nr = 0;
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
     */
    protected function getUIChecklistStatus(int $check_list_status): int
    {
        switch ($check_list_status) {
            case ilProfileChecklistStatus::STATUS_NOT_STARTED: return Step::NOT_STARTED;
            case ilProfileChecklistStatus::STATUS_IN_PROGRESS: return Step::IN_PROGRESS;
            case ilProfileChecklistStatus::STATUS_SUCCESSFUL: return Step::SUCCESSFULLY;
        }
        return 0;
    }
}
