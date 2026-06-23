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
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\Upload;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerInput as AnswerInputResponse;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\FileUpload\MimeType;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UICore\GlobalTemplate;

class LongMenu extends Type
{
    private const int DEFAULT_MIN_AUTOCOMPLETE = 3;
    private const array ACCEPTED_MIME_TYPES = [MimeType::TEXT__PLAIN];

    public function __construct(
        Language $lng,
        Refinery $refinery,
        private readonly GlobalTemplate $global_tpl
    ) {
        parent::__construct($lng, $refinery);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'long_menu';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data
    ): string {
        $gap_name = $gap->getAnswerInputId()->toString();

        $gaptemplate = new \ilTemplate(
            'tpl.cloze_gap_longmenu.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $gaptemplate->setVariable(
            'GAP_NAME',
            $gap_name
        );

        $response = $response_data?->getResponseForInput($gap->getAnswerInputId());
        if ($response !== null) {
            $gaptemplate->setVariable(
                'VALUE',
                htmlentities(
                    $response instanceof Uuid
                        ? $gap->getAnswerOptions()
                            ->getAnswerOptionById($response)
                            ->getTextValue()
                        : $response
                )
            );
        }

        $this->global_tpl->addOnLoadCode('il.questions.cloze.initLongmenuGap('
            . "document.querySelector('input[name=\"{$gap_name}\"]'), "
            . "{$gap->getMinAutocomplete()}, "
            . json_encode(
                array_values(
                    $gap->getAnswerOptions()->buildArrayForSelectInput(
                        $this->refinery->random()->dontShuffle()
                    )
                )
            ) . ')');
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
            )->withValue($gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'upload_answer_options' => $ff->file(
                new Upload(
                    $file_upload,
                    $environment
                ),
                $environment->getLanguage()->txt('upload_answer_options'),
                $environment->getLanguage()->txt('upload_answer_options_info')
            )->withAcceptedMimeTypes(self::ACCEPTED_MIME_TYPES),
            'min_autocomplete' => $ff->numeric(
                $environment->getLanguage()->txt('min_auto_complete')
            )->withRequired(true)
            ->withValue($gap->getMinAutocomplete() ?? self::DEFAULT_MIN_AUTOCOMPLETE),
            'options_awarding_points' => $ff->tag(
                $environment->getLanguage()->txt('answer_options'),
                $gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()
            )
            ->withRequired(true)
            ->withValue(
                array_values(
                    array_map(
                        fn(AnswerOption $v): string => $v->getTextValue(),
                        $gap->getAnswerOptions()->getAnswerOptionsAwardingPoints()
                    )
                )
            )
        ];
    }

    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            function (array $vs): bool {
                $values = [
                    ...$vs['answer_options'],
                    ...$this->retrieveAnswerOptionsArrayFromUpload(
                        $vs['upload_answer_options']
                    )
                ];

                return $values !== [] && array_filter(
                    $vs['options_awarding_points'],
                    fn(string $v): bool => !in_array($v, $values)
                ) === [];
            },
            $this->lng->txt('error')
        );
    }

    public function getEditPointsInputs(
        UIFactory $ui_factory,
        AnswerOptions $answer_options
    ): array {
        return $answer_options->getEditPointsInputs(
            $ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue(),
            $answer_options->getAnswerOptionsAwardingPoints()
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
            fn(array $vs): Gap => $gap
                ->withMinAutocomplete($vs['min_autocomplete'])
                ->withAnswerOptions(
                    $gap->getAnswerOptions()->withAnswerOptionsFromTags(
                        [
                            ...$vs['answer_options'],
                            ...$this->retrieveAnswerOptionsArrayFromUpload(
                                $vs['upload_answer_options']
                            )
                        ]
                    )->withAnswerOptionsAwardingPoints($vs['options_awarding_points'])
                )
        );
    }

    #[\Override]
    public function retrieveResponseFromPost(
        RequestWrapper $post_wrapper,
        UuidFactory $uuid_factory,
        Gap $gap
    ): AnswerInputResponse {
        $response_value = $this->retrieveResponseValueFromPost(
            $post_wrapper,
            $uuid_factory,
            $gap
        );

        $response_is_uuid = $response_value instanceof Uuid;

        return new AnswerInputResponse(
            $gap,
            $response_is_uuid
                ? $response_value
                : null,
            $response_is_uuid
                ? ''
                : $response_value
        );
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

    private function retrieveResponseValueFromPost(
        RequestWrapper $post_wrapper,
        UuidFactory $uuid_factory,
        Gap $gap
    ): Uuid|string {
        return $post_wrapper->retrieve(
            $gap->getAnswerInputId()->toString(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    function (?string $v) use ($gap): Uuid|string {
                        if ($v === null) {
                            return '';
                        }

                        $answer_option = $gap->getAnswerOptions()
                            ->getAnswerOptionByTextValue($v);

                        if ($answer_option === null) {
                            return $v;
                        }

                        return $answer_option?->getAnswerOptionId();
                    }
                ),
                $this->refinery->always('')
            ])
        );
    }
}
