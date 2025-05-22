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

use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\ObjectDataInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ObjectDataInterface as ObjectDataInfoInterface;

class ObjectData implements ObjectDataInterface
{
    protected int $index;
    /**
     * @var ObjectDataInfoInterface[] $elements
     */
    protected array $elements;

    public function __construct(
        ObjectDataInfoInterface ...$objectDataInfo
    ) {
        $this->index = 0;
        $this->elements = $objectDataInfo;
    }

    public function current(): ObjectDataInfoInterface
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
