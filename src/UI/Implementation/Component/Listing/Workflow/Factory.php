<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Factory implements \ILIAS\UI\Component\Listing\Workflow\Factory
{

    /**
     * @inheritdoc
     */
    public function step($label, $description = '', $action = null)
    {
        return new Step($label, $description, $action);
    }

    /**
     * @inheritdoc
     */
    public function linear($title, array $steps)
    {
        return new Linear($title, $steps);
    }
}
