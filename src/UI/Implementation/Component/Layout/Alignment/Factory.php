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

namespace ILIAS\UI\Implementation\Component\Layout\Alignment;

use ILIAS\UI\Component\Layout\Alignment as I;

class Factory implements I\Factory
{
    /**
     * @inheritdoc
     */
    public function preferHorizontal(array ...$blocksets): I\PreferHorizontal
    {
        return new PreferHorizontal(...$blocksets);
    }

    /**
     * @inheritdoc
     */
    public function forceHorizontal(array ...$blocksets): I\ForceHorizontal
    {
        return new ForceHorizontal(...$blocksets);
    }
}
