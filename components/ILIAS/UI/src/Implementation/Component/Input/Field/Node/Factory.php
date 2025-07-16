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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field\Node;

use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Component\Symbol;
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory implements Field\Node\Factory
{
    public function branch(
        string|int $id,
        string $name,
        ?Symbol\Icon\Icon $icon = null,
        Field\Node\Node ...$children
    ): Branch {
        return new Branch($id, $name, $icon, $children);
    }

    public function async(URI $render_url, string|int $id, string $name, ?Symbol\Icon\Icon $icon = null): Async
    {
        return new Async($render_url, $id, $name, $icon);
    }

    public function leaf(string|int $id, string $name, ?Symbol\Icon\Icon $icon = null): Leaf
    {
        return new Leaf($id, $name, $icon);
    }
}
