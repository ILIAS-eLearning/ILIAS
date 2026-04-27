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

use ILIAS\Questions\AnswerForm\Capabilities\Async\Async as AsyncInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\UI\Component\Component;

class Async implements AsyncInterface, Viewable
{
    private ?Properties $answer_form_properties = null;

    #[\Override]
    public function getViewable(
        Properties $answer_form_properties
    ): Viewable {
        $clone = clone $this;
        $clone->answer_form_properties = $answer_form_properties;
        return $clone;
    }

    #[\Override]
    public function getUI(): array|Component
    {
        if ($this->answer_form_properties === null) {
            throw new UnexpectedValueException(
                'This is an uninitalized Async and cannot be viewed.'
            );
        }
        return $this->answer_form_properties
            ->getDefinition()
            ->getCapability(
                Async::class
            );
    }
}
