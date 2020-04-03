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

    /**
     * @var string
     */
    protected $aria_role;

    /**
     * @var string[]
     */
    protected static $allowed_aria_roles = array(
        self::MENUITEM,
        self::MENUITEM_SEARCH
    );

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

    /**
     * @inheritdoc
     */
    public function withAriaRole(string $aria_role)
    {
        $this->checkArgIsElement(
            "role",
            $aria_role,
            self::$allowed_aria_roles,
            implode('/', self::$allowed_aria_roles)
        );
        $this->checkStringArg("role", $aria_role);
        $clone = clone $this;
        $clone->aria_role = $aria_role;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAriaRole()
    {
        return $this->aria_role;
    }
}
