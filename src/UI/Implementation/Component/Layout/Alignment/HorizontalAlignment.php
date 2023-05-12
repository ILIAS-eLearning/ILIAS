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
use ILIAS\UI\Implementation\Component\ComponentHelper;

abstract class HorizontalAlignment implements I\Alignment
{
    use ComponentHelper;

    /**
     * @var list<I\Block[]>
     */
    protected $blocks = [];

    /**
     * @param  I\Block[] $blocksets
     */
    public function __construct(array ...$blocksets)
    {
        $sets = array_merge(...$blocksets);
        $this->checkArgListElements('blocksets', $sets, [I\Block::class]);
        $this->blocks = $blocksets;
    }

    /**
     * @return list<I\Block[]>
     */
    public function getBlocksets(): array
    {
        return $this->blocks;
    }
}
