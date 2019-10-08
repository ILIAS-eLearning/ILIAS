<?php
declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Bulky Button
 */
class Bulky extends Button implements C\Button\Bulky
{
    use Engageable;

    /**
     * @var Symbol
     */
    protected $icon_or_glyph;

    public function __construct(Symbol $icon_or_glyph, string $label, string $action)
    {
        $this->icon_or_glyph = $icon_or_glyph;
        $this->label = $label;
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function getIconOrGlyph()
    {
        return $this->icon_or_glyph;
    }
}
