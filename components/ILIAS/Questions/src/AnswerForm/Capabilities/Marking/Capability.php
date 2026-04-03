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

namespace ILIAS\Questions\AnswerForm\Capabilities\Marking;

use ILIAS\Questions\AnswerForm\Capabilities\AdditionalFormStepAction;
use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Questions\Response\Response;

class Capability implements CapabilityInterface
{
    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return $answer_form_properties
            ->getTypeGenericProperties()
            ->getDefinition()
            ->hasCapability(
                Marking::class
            )
        && $answer_form_properties
            ->getTypeGenericProperties()
            ->getAvailablePoints() !== null;
    }

    #[\Override]
    public function providesAnswerFormEditAdditionalTab(): bool
    {
        return false;
    }

    #[\Override]
    public function getAnswerFormEditAdditionalTab(): null
    {
        return null;
    }

    #[\Override]
    public function providesAnswerFormEditAdditionalStep(): bool
    {
        return true;
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
                    ->getCapability(Marking::class)
                    ->getEditFormInputsBuilder($v)
        );
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void {
    }
}
