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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval\Info\Iterator;

use ILIAS\Tracking\View\DataRetrieval\Info\CombinedInterface as CombinedInfoInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\CombinedInterface;

class Combined implements CombinedInterface
{
    protected int $index;
    /**
     * @var CombinedInfoInterface[] $elements
     */
    protected array $elements;

    public function __construct(
        CombinedInfoInterface ...$elements
    ) {
        $this->index = 0;
        $this->elements = $elements;
    }

    public function current(): CombinedInfoInterface
    {
        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function next(): void
    {
        $this->index++;
    }
}
