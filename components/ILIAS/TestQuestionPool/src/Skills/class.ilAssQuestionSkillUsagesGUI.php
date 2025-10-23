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

namespace ILIAS\TestQuestionPool\Skills;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilAssQuestionSkillUsagesGUI
{
    public const string CMD_SHOW = 'show';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly GlobalHttpState $http_state,
        private readonly \ilLanguage $lng,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly \ilDBInterface $db,
        private readonly int $parent_obj_id
    ) {
    }

    public function executeCommand(): bool
    {
        return $this->showCmd();
    }

    public function showCmd(): bool
    {
        $this->tpl->setContent($this->getTable());
        return true;
    }

    private function getTable(): string
    {
        $table = new SkillUsagesTable(
            $this->ui_factory,
            $this->lng,
            $this->parent_obj_id,
            new \ilAssQuestionSkillAssignmentList($this->db)
        );

        return $this->ui_renderer->render($table->getComponent()->withRequest($this->http_state->request()));
    }
}
