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

declare(strict_types=1);

use ILIAS\User\Profile\ChecklistStatus;
use ILIAS\User\Profile\Mode as ProfileMode;

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Listing\Workflow\Step;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilProfileChecklistGUI
{
    protected UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    protected ChecklistStatus $status;
    protected Language $lng;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->lng = $DIC['lng'];

        $this->status = new ChecklistStatus(
            $this->lng,
            $DIC['ilSetting'],
            $DIC['ilUser'],
            new ProfileMode($this->lng, $DIC['ilSetting'], $DIC['ilUser'])
        );
    }

    public function render(int $active_step): string
    {
        $active_step_nr = 0;
        $workflow_factory = $this->ui_factory->listing()->workflow();
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
        $wf = $workflow_factory->linear($this->lng->txt("user_privacy_checklist"), $steps)
            ->withActive($active_step_nr);

        //render
        return $this->ui_renderer->render($wf);
    }

    /**
     * Get ui checklist status. Maps the checklist status to the UI element status.
     */
    protected function getUIChecklistStatus(int $check_list_status): int
    {
        switch ($check_list_status) {
            case ChecklistStatus::STATUS_NOT_STARTED: return Step::NOT_STARTED;
            case ChecklistStatus::STATUS_IN_PROGRESS: return Step::IN_PROGRESS;
            case ChecklistStatus::STATUS_SUCCESSFUL: return Step::SUCCESSFULLY;
        }
        return 0;
    }
}
