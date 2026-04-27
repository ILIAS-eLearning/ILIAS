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

use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Types as FeedbackTypes;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

abstract class Type
{
    public function __construct(
        protected readonly Refinery $refinery,
        protected readonly Language $lng
    ) {
    }

    abstract public function getIdentifier(): string;

    abstract public function getParticipantViewLegacyInput(
        Gap $gap,
        ?Attempt $attempt
    ): string;

    abstract public function getEditAnswerOptionsInputs(
        FileUpload $file_upload,
        Environment $environment,
        Gap $gap
    ): array;

    abstract public function getEditAnswerOptionsSectionConstraint(): ?Constraint;

    abstract public function getEditPointsInputs(
        AnswerOptions $answer_options
    ): array;

    abstract public function getEditPointsSectionConstraint(): ?Constraint;

    abstract public function getBuildGapTransformation(
        Gap $gap
    ): Transformation;

    public function getCombinationsSelectValues(
        Gap $gap
    ): array {
        return $gap->getAnswerOptions()->buildArrayForSelectInput(
            $this->refinery->random()->dontShuffle()
        );
    }

    public function getFeedbackSelectValues(
        Gap $gap,
        bool $is_marking_required
    ): array {
        $basic_select_values = $this->getCombinationsSelectValues($gap);
        if (!$is_marking_required) {
            return $basic_select_values;
        }
        return array_merge(
            [
                FeedbackTypes::MaxPoints => FeedbackTypes::MaxPoints
                    ->getTranslatedOptionName($this->lng),
                FeedbackTypes::NotMaxPoints => FeedbackTypes::NotMaxPoints
                    ->getTranslatedOptionName($this->lng),
                FeedbackTypes::NothingSelected => FeedbackTypes::NothingSelected
                    ->getTranslatedOptionName($this->lng)
            ],
            $basic_select_values
        );
    }

    public function isValidFeedbackCondition(
        UuidFactory $uuid_factory,
        Gap $gap,
        string $condition
    ): bool {
        if (FeedbackTypes::tryFrom($condition) !== null
            || Range::tryFrom($condition) !== null) {
            return true;
        }

        try {
            return $gap->getAnswerOptions()->getAnswerOptionById(
                $uuid_factory->fromString($condition)
            ) !== null;
        } catch (InvalidUuidStringException $e) {
            return false;
        }
    }

    public function getLabelForValue(
        UuidFactory $uuid_factory,
        Gap $gap,
        string $value
    ): string {
        return $gap->getAnswerOptions()->getAnswerOptionById(
            $uuid_factory->fromString($value)
        )->getTextValue();
    }

    public function getAddPointsTransformation(
        Gap $gap
    ): Transformation {
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withAnswerOptions(
                $gap->getAnswerOptions()
                    ->withAnswerOptionsWithAddedPointsFromForm($this->refinery, $vs)
            )
        );
    }
}
