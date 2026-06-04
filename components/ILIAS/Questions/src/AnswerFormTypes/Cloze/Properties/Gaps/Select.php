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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Data\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Data\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Data\Data;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;

class Select extends Type
{
    private const bool DEFAULT_SHUFFLE_ANSWER_OPTIONS = false;

    public function __construct(
        Refinery $refinery,
        private readonly Language $lng,
        private readonly UIFactory $ui_factory
    ) {
        parent::__construct($refinery);
    }

    public function getIdentifier(): string
    {
        return 'select';
    }

    public function getEditAnswerOptionsInputs(Data $data): array
    {
        $ff = $this->ui_factory->input()->field();
        return [
            'answer_options' => $ff->tag(
                $this->lng->txt('answer_options'),
                []
            )->withRequired(true)
            ->withValue($data->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'shuffle_answer_options' => $ff->checkbox(
                $this->lng->txt('shuffle_answers')
            )->withValue($data?->getShuffleAnswerOptions() ?? self::DEFAULT_SHUFFLE_ANSWER_OPTIONS)
        ];
    }

    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return null;
    }

    public function getEditPointsInputs(AnswerOptions $answer_options): array
    {
        return $answer_options->getEditPointsInputs(
            $this->ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue()
        );
    }

    public function getEditPointsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            function (array $vs): bool {
                foreach ($vs as $v) {
                    if ($v > 0.0) {
                        return true;
                    }
                }
                return false;
            },
            $this->lng->txt('at_least_one_gap_positiv_points')
        );
    }

    public function getBuildGapTransformation(Gap $gap): Transformation
    {
        $data = $gap->getData();
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withData(
                $data->withAnswerOptions(
                    $data->getAnswerOptions()->withAnswerOptionsFromTags($vs['answer_options'])
                )
            )
        );
    }

    public function getAnswerInput(): \ilFormPropertyGUI
    {
        ;
    }
}
