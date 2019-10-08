<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component\Symbol\Icon as I;

class Factory implements I\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($name, $aria_label, $size='small', $is_disabled = false)
    {
        return new Standard($name, $aria_label, $size, $is_disabled);
    }

    /**
     * @inheritdoc
     */
    public function custom($icon_path, $aria_label, $size='small', $is_disabled = false)
    {
        return new Custom($icon_path, $aria_label, $size, $is_disabled);
    }
}
