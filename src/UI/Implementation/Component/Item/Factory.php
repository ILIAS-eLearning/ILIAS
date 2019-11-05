<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item as I;

class Factory implements I\Factory
{

    /**
     * @inheritdoc
     */
    public function standard($title)
    {
        return new Standard($title);
    }

    /**
     * @inheritdoc
     */
    public function group($title, $items)
    {
        return new Group($title, $items);
    }
    /**
     * @inheritdoc
     */
    public function notification($title, \ILIAS\UI\Component\Symbol\Icon\Icon $icon)
    {
        return new Notification($title,$icon);
    }
}