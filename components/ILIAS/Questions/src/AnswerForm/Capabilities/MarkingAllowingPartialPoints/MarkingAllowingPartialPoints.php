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

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Marking;
use ILIAS\Questions\AnswerForm\Capabilities\TypeSpecification;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;

abstract class MarkingAllowingPartialPoints implements Marking, TypeSpecification
{
    abstract public function getEditFormInputsBuilder(
        Environment $environment
    ): InputsBuilderSession;

    abstract public function getCarryPropertiesFromCarryTransformation(
        Environment $environment
    ): CustomTransformation;

    #[\Override]
    final public static function getCapabilityIdentifier(): string
    {
        return Capability::getIdentifier();
    }
}
