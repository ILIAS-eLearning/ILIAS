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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\Column as C;

abstract class Column implements C\Column
{
    use ComponentHelper;

    protected string $title;
    protected bool $sortable = true;
    protected bool $optional = false;
    protected bool $initially_visible = true;
    protected int $index;


    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        $class = explode('\\', get_class($this));
        $class = array_pop($class);
        return $class;
    }

    public function withIsSortable(bool $flag): self
    {
        $clone = clone $this;
        $clone->sortable = $flag;
        return $clone;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function withIsOptional(bool $flag): self
    {
        $clone = clone $this;
        $clone->optional = $flag;
        return $clone;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function withIsInitiallyVisible(bool $flag): self
    {
        $clone = clone $this;
        $clone->initially_visible = $flag;
        return $clone;
    }

    public function isInitiallyVisible(): bool
    {
        return $this->initially_visible;
    }

    public function withIndex(int $index): self
    {
        $clone = clone $this;
        $clone->index = $index;
        return $clone;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}
