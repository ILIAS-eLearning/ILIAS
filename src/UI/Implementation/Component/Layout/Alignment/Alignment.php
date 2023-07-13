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

namespace ILIAS\UI\Implementation\Component\Layout\Alignment;

use ILIAS\UI\Component\Layout\Alignment as I;
use ILIAS\UI\Implementation\Component\ComponentHelper;

abstract class Alignment implements I\Alignment
{
    use ComponentHelper;

    /**
     * @var I\Block[]
     */
    protected $blocks = [];

    /**
     * @param  I\Block[] $blocks
     */
    public function __construct(I\Block ...$blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @return I\Block[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
