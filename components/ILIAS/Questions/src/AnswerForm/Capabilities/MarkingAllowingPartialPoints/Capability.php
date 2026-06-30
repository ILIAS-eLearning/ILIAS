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

namespace ILIAS\Questions\AnswerForm\Capabilities\MarkingAllowingPartialPoints;

use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalFormStepAction;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalStepProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Marking;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\MarkingProvider;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Data\UUID\Factory as UuidFactory;

class Capability implements CapabilityInterface, AdditionalStepProvider, MarkingProvider
{
    #[\Override]
    public static function getIdentifier(): string
    {
        return 'marking_allowing_partial_points';
    }

    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return $answer_form_properties
            ->getTypeGenericProperties()
            ->getDefinition()
            ->hasCapability(
                self::getIdentifier()
            )
        && $answer_form_properties
            ->getTypeGenericProperties()
            ->getAvailablePoints() !== null;
    }

    #[\Override]
    public function getAnswerFormEditAdditionalStep(): AdditionalFormStepAction
    {
        return new AdditionalFormStepAction(
            $this,
            'edit_available_points',
            fn(Environment $v): InputsBuilderSession
                => $v->getAnswerFormProperties()
                    ->getDefinition()
                    ->getCapability(self::getIdentifier())
                    ->getEditFormInputsBuilder($v)
        );
    }

    #[\Override]
    public function getMarking(
        Properties $answer_form_properties
    ): Marking {
        return $answer_form_properties
            ->getDefinition()
            ->getCapability(self::getIdentifier());
    }

    #[\Override]
    public function onAnswerFormClone(
        UuidFactory $uuid_factory,
        Properties $old_answer_form_properties,
        Properties $new_answer_form_properties
    ): void {
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void {
    }

    #[\Override]
    public function onAnswerFormDelete(
        Properties $answer_form_properties
    ): void {
    }
}
