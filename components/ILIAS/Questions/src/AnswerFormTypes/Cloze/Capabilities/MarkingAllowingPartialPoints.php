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

use ILIAS\Questions\AnswerForm\Capabilities\MarkingAllowingPartialPoints\MarkingAllowingPartialPoints as MarkingAllowingPartialPointsInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\Factory as ResponseFactory;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\UI\Component\Input\Field\Section;

class MarkingAllowingPartialPoints extends MarkingAllowingPartialPointsInterface
{
    public function __construct(
        private readonly PropertiesFactory $properties_factory,
        private readonly ResponseFactory $response_factory
    ) {
    }

    #[\Override]
    public function calculateAwardedPoints(
        Properties $properties,
        Response $response
    ): float {
        return $response->calculateAwardedPoints($properties);
    }

    #[\Override]
    public function getBestResponse(
        Properties $properties
    ): Response {
        return $this->response_factory->getBestResponse($properties);
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
                            $environment->getUIFactory(),
                            $properties_from_carry,
                            $environment->isInCreationContext(),
                            $environment->getTableRowIds()
                        );
                }
            )
        );
    }

    #[\Override]
    public function getCarryPropertiesFromCarryTransformation(
        Environment $environment
    ): CustomTransformation {
        return $environment->getRefinery()->custom()->transformation(
            fn(?string $carry): Properties => $this->properties_factory
                ->fromCarry(
                    $environment->getAnswerFormProperties(),
                    $carry
                )
        );
    }
}
