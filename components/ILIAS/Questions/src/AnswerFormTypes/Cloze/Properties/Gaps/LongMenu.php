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
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\Properties;
use ILIAS\FileUpload\MimeType;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;

class LongMenu extends Type
{
    private const int DEFAULT_MIN_AUTOCOMPLETE = 3;
    private const array ACCEPTED_MIME_TYPES = [MimeType::TEXT__PLAIN];

    public function __construct(
        Refinery $refinery,
        private readonly Language $lng,
        private readonly UIFactory $ui_factory
    ) {
        parent::__construct($refinery);
    }

    public function getIdentifier(): string
    {
        return 'long_menu';
    }

    public function getEditAnswerOptionsInputs(
        Gap $gap
    ): array {
        $ff = $this->ui_factory->input()->field();
        return [
            'answer_options' => $ff->tag(
                $this->lng->txt('answer_options'),
                []
            )->withValue($gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'upload_answer_options' => $ff->file(
                new UploadAnswerOptionsGUI(),
                $this->lng->txt('upload_answer_options'),
                $this->lng->txt('upload_answer_options_info')
            )->withAcceptedMimeTypes(self::ACCEPTED_MIME_TYPES),
            'min_autocomplete' => $ff->numeric(
                $this->lng->txt('min_autocomplete')
            )->withRequired(true)
            ->withValue($gap->getMinAutocomplete() ?? self::DEFAULT_MIN_AUTOCOMPLETE),
            'options_awarding_points' => $ff->tag(
                $this->lng->txt('answer_options'),
                $gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()
            )
            ->withRequired(true)
            ->withValue(
                array_map(
                    fn(AnswerOption $v): string => $v->getTextValue(),
                    $gap->getAnswerOptions()->getAnswerOptionsAwardingPoints()
                )
            )
        ];
    }

    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            function (array $vs): bool {
                $values = array_merge(
                    $vs['answer_options'],
                    $this->retrieveAnswerOptionsArrayFromUpload($vs['upload_answer_options'])
                );

                return $values !== [] && array_filter(
                    $vs['options_awarding_points'],
                    fn(string $v): bool => !in_array($v, $values)
                ) === [];
            },
            $this->lng->txt('error')
        );
    }

    public function getEditPointsInputs(
        AnswerOptions $answer_options
    ): array {
        return $answer_options->getEditPointsInputs(
            $this->ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue(),
            $answer_options->getAnswerOptionsAwardingPoints()
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

    public function getBuildGapTransformation(
        Gap $gap
    ): Transformation {
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap
                ->withMinAutocomplete($vs['min_autocomplete'])
                ->withAnswerOptions(
                    $gap->getAnswerOptions()->withAnswerOptionsFromTags(
                        $gap->getAnswerInputId(),
                        array_merge(
                            $vs['answer_options'],
                            $this->retrieveAnswerOptionsArrayFromUpload($vs['upload_answer_options'])
                        )
                    )->withAnswerOptionsAwardingPoints($vs['options_awarding_points'])
                )
        );
    }

    public function getAnswerInput(): \ilFormPropertyGUI
    {
        ;
    }

    private function retrieveAnswerOptionsArrayFromUpload(
        ?array $upload_value
    ): array {
        if ($upload_value === null
            || ($decoded_value = base64_decode($upload_value[0] ?? '')) === '') {
            return [];
        }

        return array_filter(
            mb_split('\R', $decoded_value)
        );
    }
}
