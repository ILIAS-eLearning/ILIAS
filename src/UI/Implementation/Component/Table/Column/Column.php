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
use ILIAS\UI\Component\Component;

abstract class Column implements C\Column
{
    use ComponentHelper;

    protected const SEPERATOR = ', ';

    protected bool $sortable = true;
    protected bool $optional = false;
    protected bool $initially_visible = true;
    protected bool $highlighted = false;
    protected int $index;
    protected string $title;
    protected \ilLanguage $lng;

    public function __construct(
        \ilLanguage $lng,
        string $title
    ) {
        $this->title = $title;
        $this->lng = $lng;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        $class = explode('\\', static::class);
        return array_pop($class);
    }

    public function withIsSortable(
        bool $flag
    ): self {
        $clone = clone $this;
        $clone->sortable = $flag;
        return $clone;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function withOrderingLabels(
        string $asc_label = null,
        string $desc_label = null
    ): self {
        $clone = clone $this;
        $clone->asc_label = $asc_label;
        $clone->desc_label = $desc_label;
        return $clone;
    }

    /**
     * @return string[]
     */
    public function getOrderingLabels(): array
    {
        return [
            $this->asc_label ?? $this->getTitle() . self::SEPERATOR . $this->lng->txt('order_option_generic_ascending'),
            $this->desc_label ?? $this->getTitle() . self::SEPERATOR . $this->lng->txt('order_option_generic_descending')
        ];
    }

    public function withIsOptional(bool $is_optional, bool $is_initially_visible = true): self
    {
        $clone = clone $this;
        $clone->optional = $is_optional;
        $clone->initially_visible = $is_initially_visible;
        return $clone;
    }

    public function isOptional(): bool
    {
        return $this->optional;
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

    public function withHighlight(bool $flag): self
    {
        $clone = clone $this;
        $clone->highlighted = $flag;
        return $clone;
    }

    public function isHighlighted(): bool
    {
        return $this->highlighted;
    }

    /**
     * @return string|Component
     */
    public function format($value)
    {
        return (string) $value;
    }
}
