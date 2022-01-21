<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Factory implements Item\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($title) : Item\Standard
    {
        return new Standard($title);
    }

    /**
     * @inheritdoc
     */
    public function shy(string $title) : Item\Shy
    {
        return new Shy($title);
    }

    /**
     * @inheritdoc
     */
    public function group(string $title, array $items) : Item\Group
    {
        return new Group($title, $items);
    }

    /**
     * @inheritdoc
     */
    public function notification($title, Icon $icon) : Item\Notification
    {
        return new Notification($title, $icon);
    }
}
