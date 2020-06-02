<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;

/**
 * Factory for View Controls
 */
class Factory implements VCInterface\Factory
{
    public function fieldSelection(
        array $options,
        string $label = VCInterface\FieldSelection::DEFAULT_DROPDOWN_LABEL,
        string $button_label = VCInterface\FieldSelection::DEFAULT_BUTTON_LABEL
    ) : VCInterface\FieldSelection {
        throw new \ILIAS\UI\NotImplementedException('');
    }
}
