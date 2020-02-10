<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component as C;
use ILIAS\UI\Component\Input\Field\MultiSelect;
use ILIAS\UI\Component\Input\Container\Form;

/**
 * This describes a FieldSelection Control
 */
interface FieldSelection extends C\Component
{
    const DEFAULT_DROPDOWN_LABEL = 'selection';
    const DEFAULT_BUTTON_LABEL = 'refresh';

    public function getDropdownLabel(): string;
    public function getForm(): Form\Standard;
    public function getValue(): array;
}
