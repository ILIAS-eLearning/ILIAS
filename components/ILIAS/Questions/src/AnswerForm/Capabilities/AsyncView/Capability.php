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

namespace ILIAS\Questions\AnswerForm\Capabilities\AsyncView;

use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Capabilities\ParticipantViewProvider;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Views\Participant;

class Capability implements CapabilityInterface, ParticipantViewProvider
{
    #[\Override]
    public static function getIdentifier(): string
    {
        return 'async_view';
    }

    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return $answer_form_properties
            ->getDefinition()
            ->hasCapability(
                self::getIdentifier()
            );
    }

    #[\Override]
    public function getParticipantView(
        Properties $answer_form_properties
    ): Participant {
        return $answer_form_properties
            ->getDefinition()
            ->getCapability(self::getIdentifier())
            ->getParticipantView();
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
