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

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Random\Seed\GivenSeed;
use ILIAS\Refinery\Random\Seed\RandomSeed;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;

class Select extends Type
{
    private const bool DEFAULT_SHUFFLE_ANSWER_OPTIONS = false;

    public function __construct(
        Refinery $refinery,
        Language $lng,
        private readonly UIFactory $ui_factory
    ) {
        parent::__construct($refinery, $lng);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'select';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap,
        ?Attempt $attempt_data
    ): string {
        $gaptemplate = new \ilTemplate(
            'tpl.il_as_qpl_cloze_question_gap_select.html',
            true,
            true,
            'components/ILIAS/TestQuestionPool'
        );

        foreach ($gap->getAnswerOptions()->buildArrayForSelectInput(
            $this->buildShuffler(
                $gap,
                $attempt_data
            )
        ) as $key => $answer_option) {
            $gaptemplate->setCurrentBlock('select_gap_option');
            $gaptemplate->setVariable(
                'SELECT_GAP_VALUE',
                $key
            );
            $gaptemplate->setVariable(
                'SELECT_GAP_TEXT',
                \ilLegacyFormElementsUtil::prepareFormOutput($answer_option)
            );
            $gaptemplate->parseCurrentBlock();
        }

        $gaptemplate->setVariable(
            'PLEASE_SELECT',
            $this->lng->txt('please_select')
        );

        $gaptemplate->setVariable(
            'GAP_COUNTER',
            $gap->getAnswerInputId()->toString()
        );

        return $gaptemplate->get();
    }

    #[\Override]
    public function getEditAnswerOptionsInputs(
        FileUpload $file_upload,
        Environment $environment,
        Gap $gap
    ): array {
        $ff = $this->ui_factory->input()->field();
        return [
            'answer_options' => $ff->tag(
                $this->lng->txt('answer_options'),
                []
            )->withRequired(true)
            ->withValue($gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'shuffle_answer_options' => $ff->checkbox(
                $this->lng->txt('shuffle_answers')
            )->withValue($gap?->getShuffleAnswerOptions() ?? self::DEFAULT_SHUFFLE_ANSWER_OPTIONS)
        ];
    }

    #[\Override]
    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return null;
    }

    #[\Override]
    public function getEditPointsInputs(
        AnswerOptions $answer_options
    ): array {
        return $answer_options->getEditPointsInputs(
            $this->ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue()
        );
    }

    #[\Override]
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

    #[\Override]
    public function getBuildGapTransformation(
        Gap $gap
    ): Transformation {
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withAnswerOptions(
                $gap->getAnswerOptions()->withAnswerOptionsFromTags(
                    $vs['answer_options']
                )
            )->withShuffleAnswerOptions($vs['shuffle_answer_options'])
        );
    }

    private function buildShuffler(
        Gap $gap,
        Attempt $attempt_data
    ): Transformation {
        if (!$gap->getShuffleAnswerOptions()) {
            return $this->refinery->random()->dontShuffle();
        }

        return $this->refinery->random()->shuffleArray(
            $attempt_data === null
                ? new RandomSeed()
                : new GivenSeed(
                    $this->refinery->kindlyTo()->int()->transform(
                        $attempt_data->getAdditionalDataFor($gap->getAnswerInputId())
                    )
                )
        );
    }
}
