<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
    public function step(string $label, string $description = '', $action = null) : W\Step
    {
        return new Step($label, $description, $action);
    }

    /**
     * @inheritdoc
     */
    public function linear(string $title, array $steps) : W\Linear
    {
        return new Linear($title, $steps);
    }
}
