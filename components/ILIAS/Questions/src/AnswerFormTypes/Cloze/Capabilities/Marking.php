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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Capabilities;

use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking as MarkingInterface;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Questions\Response\Response;
use ILIAS\UI\Component\Input\Field\Section;

class Marking implements MarkingInterface
{
    public function __construct(
        private readonly PropertiesFactory $properties_factory
    ) {
    }

    #[\Override]
    public function getEditFormInputsBuilder(
        Environment $environment,
    ): InputsBuilderSession {
        return $environment->getPresentationFactory()->getSessionBasedInputsBuilder(
            $environment->getRefinery()->custom()->transformation(
                function (?string $carry) use ($environment): Section {
                    $properties_from_carry = $this->properties_factory
                        ->fromCarry(
                            $environment->getAnswerFormProperties(),
                            $carry
                        );
                    return $properties_from_carry->getGaps()
                        ->buildPointInputs(
                            $environment->getLanguage(),
                            $environment->getUIFactory()->input()->field(),
                            $properties_from_carry,
                            $environment->isInCreationContext(),
                            $environment->getTableRowIds()
                        );
                }
            )
        );
    }

    #[\Override]
    public function addAchievedPointsToResponse(
        Response $response
    ): Response {

    }

    #[\Override]
    public function getBestResponse(): Response
    {

    }
}
