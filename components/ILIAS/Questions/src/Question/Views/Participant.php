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

namespace ILIAS\Questions\Question\Views;

use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Questions\Question\Question;
use ILIAS\Data\UUID\Uuid;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Legacy\Content as LegacyContent;

class Participant implements Viewable
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly array $required_capabilities,
        private readonly Question $question,
        private readonly ?Attempt $attempt_data,
        private readonly bool $interactive,
        private readonly bool $show_marks,
        private readonly bool $show_correct_solution
    ) {
    }

    public function getAttemptId(): ?Uuid
    {
        return $this->attempt_data?->getIdentifier();
    }

    #[\Override]
    public function getUI(): LegacyContent
    {
        $tpl = new \ilTemplate(
            'tpl.qsts_question_presentation.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $question_page = new \QstsQuestionPageGUI(
            $this->question,
            $this->question->getParentObjId()
        )->withAttemptData($this->attempt_data);
        $question_page->setPresentationTitle($this->question->getTitle());

        $tpl->setVariable(
            'QUESTION_OUTPUT',
            $question_page->presentation()
        );
        return $this->ui_factory->legacy()->content($tpl->get());
    }
}
