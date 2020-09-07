<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;

/**
 * Bulky Button
 */
class Bulky extends Button implements C\Button\Bulky
{

    /**
     * @var 	ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph
     */
    protected $icon_or_glyph;

    /**
     * @var 	bool
     */
    protected $engaged = false;

    public function __construct($icon_or_glyph, $label, $action)
    {
        $allowed_classes = [C\Icon\Icon::class, C\Glyph\Glyph::class];
        $graphical_param = array($icon_or_glyph);
        $this->checkArgListElements("icon_or_glyph", $graphical_param, $allowed_classes);
        $this->checkStringArg("label", $label);
        $this->checkStringArg("action", $action);
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

    /**
     * @inheritdoc
     */
    public function withEngagedState($state)
    {
        $this->checkBoolArg("state", $state);
        $clone = clone $this;
        $clone->engaged = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isEngaged()
    {
        return $this->engaged;
    }
}
