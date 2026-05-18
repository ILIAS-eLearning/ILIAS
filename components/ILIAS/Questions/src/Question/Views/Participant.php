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

use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Questions\Question\Question;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;

class Participant implements Viewable
{
    public function __construct(
        private readonly Language $lng,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly RequiredCapabilities $required_capabilities,
        private readonly Question $question,
        private readonly ?Attempt $attempt_data,
        private readonly bool $interactive,
        private readonly bool $show_marks,
        private readonly bool $show_best_response,
        private readonly bool $show_feedback
    ) {
    }

    public function getAttemptId(): ?Uuid
    {
        return $this->attempt_data?->getId();
    }

    #[\Override]
    public function getUI(): array
    {
        $question_page = new \QstsQuestionPageGUI(
            $this->question,
            $this->question->getParentObjId(),
            $this->required_capabilities,
            $this->interactive,
            $this->show_best_response,
            $this->show_feedback
        )->withAttemptData($this->attempt_data);
        $question_page->setPresentationTitle($this->question->getTitle());


        if ($this->show_marks) {
            $content[] = $this->ui_factory->listing()->characteristicValue()->text(
                [
                    $this->lng->txt('awarded_points') => $this->refinery->kindlyTo()->string()->transform(
                        $this->attempt_data?->getResponseForQuestion(
                            $this->question->getId()
                        )?->getAwardedPoints() ?? 0
                    )
                ]
            );
            $content[] = $this->ui_factory->divider()->horizontal();
        }

        $content[] = $this->ui_factory->legacy()->content(
            $question_page->presentation()
        );

        return $content;
    }
}
