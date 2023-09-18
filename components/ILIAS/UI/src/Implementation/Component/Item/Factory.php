<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Factory implements Item\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($title): Item\Standard
    {
        return new Standard($title);
    }

    /**
     * @inheritdoc
     */
    public function shy(string $title): Item\Shy
    {
        return new Shy($title);
    }

    /**
     * @inheritdoc
     */
    public function group(string $title, array $items): Item\Group
    {
        return new Group($title, $items);
    }

    /**
     * @inheritdoc
     */
    public function notification($title, Icon $icon): Item\Notification
    {
        return new Notification($title, $icon);
    }
}
