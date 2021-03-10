<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component\Symbol\Icon as I;

class Factory implements I\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(
        string $name,
        string $label,
        string $size = 'small',
        bool $is_disabled = false
    ) : I\Standard {
        return new Standard($name, $label, $size, $is_disabled);
    }

    /**
     * @inheritdoc
     */
    public function custom(
        string $icon_path,
        string $label,
        string $size = 'small',
        bool $is_disabled = false
    ) : I\Custom {
        return new Custom($icon_path, $label, $size, $is_disabled);
    }
}
