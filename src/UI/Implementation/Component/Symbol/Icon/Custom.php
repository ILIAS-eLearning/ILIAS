<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Custom extends Icon implements C\Symbol\Icon\Custom
{

    /**
     * @var	string
     */
    private $icon_path;


    public function __construct(string $icon_path, string $label, string $size, bool $is_disabled)
    {
        $this->checkArgIsElement(
            "size",
            $size,
            self::$possible_sizes,
            implode('/', self::$possible_sizes)
        );
        $this->name = 'custom';
        $this->icon_path = $icon_path;
        $this->label = $label;
        $this->size = $size;
        $this->is_disabled = $is_disabled;
    }

    /**
     * @inheritdoc
     */
    public function getIconPath()
    {
        return $this->icon_path;
    }
}
