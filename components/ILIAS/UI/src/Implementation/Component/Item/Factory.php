<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Factory implements Item\Factory
{
    public function standard($title): Standard
    {
        return new Standard($title);
    }

    public function shy(string $title): Shy
    {
        return new Shy($title);
    }

    public function group(string $title, array $items): Group
    {
        return new Group($title, $items);
    }

    public function notification($title, Icon $icon): Notification
    {
        return new Notification($title, $icon);
    }
}
