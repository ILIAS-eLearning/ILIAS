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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Test\Table\ResultsByQuestionTable;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ResultsByQuestionGUI
{
    public const CMD_SINGLE_RESULTS = 'singleResults';

    public function __construct(
        private readonly Factory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly int $parent_obj_id,
        private readonly int $request_ref_id,
        private readonly Renderer $ui_renderer,
        private readonly GlobalHttpState $http_state,
        private readonly ilCtrl $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilObjTest $object,
        private readonly ilTabsGUI $tabs,
        private readonly bool $statistics_access,
    ) {
    }

    public function executeCommand(): bool
    {
        switch (strtolower((string) $this->ctrl->getNextClass($this))) {
            case strtolower(__CLASS__):
            case '':
                $cmd = $this->ctrl->getCmd() . 'Cmd';
                return $this->$cmd();
            default:
                $this->ctrl->setReturn($this, self::CMD_SINGLE_RESULTS);

                if ($this->ctrl->getNextClass($this) === strtolower(ilFormPropertyDispatchGUI::class)) {
                    $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                    $form_prop_dispatch->setItem(new ilFormPropertyGUI());
                    return (bool) $this->ctrl->forwardCommand($form_prop_dispatch);
                }

                return false;
        }
    }

    public function singleResultsCmd(): bool
    {
        $this->tpl->setContent($this->getTable());
        return true;
    }

    protected function fillRow(array $a_set): void
    {
        if (isset($a_set['number_of_answers']) && $a_set['number_of_answers'] > 0) {
            $this->tpl->setVariable('PRINT_ANSWERS', $a_set['output']);
        }

        $this->tpl->setVariable('QUESTION_ID', $a_set['qid']);
        $this->tpl->setVariable('QUESTION_TITLE', $a_set['question_title']);
        $this->tpl->setVariable('NUMBER_OF_ANSWERS', $a_set['number_of_answers']);
        $this->tpl->setVariable('FILE_UPLOADS', $a_set['file_uploads']);
    }

    private function getTable(): string
    {
        $table = new ResultsByQuestionTable(
            $this->ui_factory,
            $this->lng,
            $this->parent_obj_id,
            $this->request_ref_id,
            $this->object,
            $this->ctrl,
            $this->tpl,
            $this->tabs,
            $this->statistics_access
        );

        return $this->ui_renderer->render($table->getComponent()->withRequest($this->http_state->request()));
    }
}
