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

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component\Listing\Workflow as W;

class Factory implements W\Factory
{

    public function step(string $label, string $description = '', $action = null): Step
    {
        return new Step($label, $description, $action);
    }

    public function linear(string $title, array $steps): Linear
    {
        return new Linear($title, $steps);
    }
}
