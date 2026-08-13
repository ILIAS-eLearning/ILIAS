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

use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerInput as AnswerInputResponse;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Random\Seed\GivenSeed;
use ILIAS\Refinery\Random\Seed\RandomSeed;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;

class Select extends Type
{
    private const bool DEFAULT_SHUFFLE_ANSWER_OPTIONS = false;

    #[\Override]
    public function getIdentifier(): string
    {
        return 'select';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data
    ): string {
        $gaptemplate = new \ilTemplate(
            'tpl.cloze_gap_select.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $selected_answer_option = $response_data?->getResponseForInput(
            $gap->getAnswerInputId()
        )?->toString();

        foreach ($gap->getAnswerOptions()->buildArrayForSelectInput(
            $this->buildShuffler(
                $gap,
                $additional_attempt_data
            )
        ) as $answer_option_id => $answer_option) {
            $gaptemplate->setCurrentBlock('select_gap_option');
            $gaptemplate->setVariable(
                'SELECT_GAP_VALUE',
                $answer_option_id
            );
            $gaptemplate->setVariable(
                'SELECT_GAP_TEXT',
                \ilLegacyFormElementsUtil::prepareFormOutput($answer_option)
            );

            if ($answer_option_id === $selected_answer_option) {
                $gaptemplate->setVariable(
                    'SELECT_GAP_SELECTED',
                    ' selected="selected"'
                );
            }

            $gaptemplate->parseCurrentBlock();
        }

        $gaptemplate->setVariable(
            'PLEASE_SELECT',
            $this->lng->txt('please_select')
        );

        $gaptemplate->setVariable(
            'GAP_NAME',
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
        $ff = $environment->getUIFactory()->input()->field();
        return [
            'answer_options' => $ff->tag(
                $environment->getLanguage()->txt('answer_options'),
                []
            )->withRequired(true)
            ->withValue($gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'shuffle_answer_options' => $ff->checkbox(
                $environment->getLanguage()->txt('shuffle_answers')
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
        UIFactory $ui_factory,
        AnswerOptions $answer_options,
        bool $input_required
    ): array {
        return $answer_options->getEditPointsInputs(
            $ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue()
        );
    }

    #[\Override]
    public function getEditPointsSectionConstraint(
        bool $input_required
    ): ?Constraint {
        if (!$input_required) {
            return null;
        }

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

    #[\Override]
    public function retrieveResponseFromPost(
        RequestWrapper $post_wrapper,
        UuidFactory $uuid_factory,
        Gap $gap
    ): AnswerInputResponse {
        return new AnswerInputResponse(
            $gap,
            $post_wrapper->retrieve(
                $gap->getAnswerInputId()->toString(),
                $this->refinery->byTrying([
                    $this->refinery->custom()->transformation(
                        function (?string $v) use ($uuid_factory, $gap): ?Uuid {
                            if ($v === null) {
                                return null;
                            }

                            try {
                                $answer_option_id = $uuid_factory->fromString($v);
                            } catch (\Exception $e) {
                                return null;
                            }

                            if ($gap->getAnswerOptions()->getAnswerOptionById(
                                $answer_option_id
                            ) === null) {
                                return null;
                            }

                            return $answer_option_id;
                        }
                    ),
                    $this->refinery->always(null)
                ])
            ),
            ''
        );
    }

    private function buildShuffler(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data
    ): Transformation {
        if (!$gap->getShuffleAnswerOptions()) {
            return $this->refinery->random()->dontShuffle();
        }

        return $this->refinery->random()->shuffleArray(
            $this->buildSeed(
                $gap,
                $additional_attempt_data
            )
        );
    }

    private function buildSeed(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data
    ): GivenSeed {
        if ($additional_attempt_data === null) {
            return new RandomSeed();
        }

        $given_seed = $additional_attempt_data->getAdditionalDataFor(
            $gap->getAnswerInputId()
        );

        if (is_numeric($given_seed)) {
            return new GivenSeed(
                $this->refinery->kindlyTo()->int()->transform(
                    $given_seed
                )
            );
        }

        return new RandomSeed();
    }
}
