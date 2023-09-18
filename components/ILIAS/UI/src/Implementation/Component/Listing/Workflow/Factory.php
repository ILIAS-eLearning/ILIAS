<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component\Listing\Workflow as W;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Factory implements W\Factory
{
    /**
     * @inheritdoc
     */
    public function step(string $label, string $description = '', $action = null): W\Step
    {
        return new Step($label, $description, $action);
    }

    /**
     * @inheritdoc
     */
    public function linear(string $title, array $steps): W\Linear
    {
        return new Linear($title, $steps);
    }
}
