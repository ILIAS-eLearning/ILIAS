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

use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\EditOverview;
use ILIAS\Questions\Response\Response;

class Capability implements CapabilityInterface
{
    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return false;
    }

    #[\Override]
    public function getEditAction(): null
    {
        return null;
    }

    #[\Override]
    public function edit(
        Environment $environment
    ): self|Async|EditForm|EditOverview {
        return false;
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void {
        $this->repository->store(
            $answer_form_properties->getAnswerFormId(),
            $answer_form_properties
                ->getTypeGenericProperties()
                ->getDefinition()
                ->getCapability(Feedback::class)
                ->onAnswerFormUpdate(
                    $answer_form_properties
                )
        );
    }
}
