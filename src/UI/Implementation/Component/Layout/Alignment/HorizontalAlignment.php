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
     * @var I\Block[]
     */
    protected $blocks = [];

    /**
     * @param  I\Block[] $blocksets
     */
    public function __construct(array ...$blocksets)
    {
        foreach ($blocksets as $col => $set) {
            $this->setBlocks($col, $set);
        }
    }

    /**
     * @param I\Block[] $blocks
     */
    protected function setBlocks(int $col, array $blocks): void
    {
        $this->blocks[$col] = $blocks;
    }

    /**
     * @return I\Block[]
     */
    public function getBlocks(int $col): array
    {
        return array_key_exists($col, $this->blocks) ? $this->blocks[$col] : [];
    }
}
