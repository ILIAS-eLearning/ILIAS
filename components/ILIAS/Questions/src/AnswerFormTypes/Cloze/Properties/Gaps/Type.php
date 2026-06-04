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
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Data\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;

abstract class Type
{
    public function __construct(
        protected readonly Refinery $refinery
    ) {
    }

    abstract public function getIdentifier(): string;
    abstract public function getEditAnswerOptionsInputs(Data $data): array;
    abstract public function getEditAnswerOptionsSectionConstraint(): ?Constraint;
    abstract public function getEditPointsInputs(AnswerOptions $answer_options): array;
    abstract public function getEditPointsSectionConstraint(): ?Constraint;
    abstract public function getBuildGapTransformation(Gap $gap): Transformation;
    abstract public function getAnswerInput(): \ilFormPropertyGUI;

    public function getAddPointsTransformation(Gap $gap): Transformation
    {
        $data = $gap->getData();
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withData(
                $data->withAnswerOptions(
                    $data->getAnswerOptions()
                        ->withAnswerOptionsWithAddedPointsFromForm($this->refinery, $vs)
                )
            )
        );
    }
}
