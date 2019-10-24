<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes a multi-select input.
 */
interface MultiSelect extends Input
{

    /**
     * Get options as value=>label.
     */
    public function getOptions() : array;
}
