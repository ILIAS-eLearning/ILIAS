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

namespace ILIAS\UI\Implementation\Component\Prompt;

use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data\URI;

class Factory implements I\Factory
{
    public function __construct(
        protected SignalGeneratorInterface $signal_generator
    ) {
    }

    public function standard(URI $async_url): Standard
    {
        return new Standard($this->signal_generator, $async_url);
    }

    public function state(): State\Factory
    {
        return new State\Factory();
    }
}
