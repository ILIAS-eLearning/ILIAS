<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Bulky Button
 */
class Bulky extends Button implements C\Button\Bulky
{
    // allowed ARIA roles
    public const MENUITEM = 'menuitem';

    protected Symbol $icon_or_glyph;
    protected ?string $aria_role = null;

    /**
     * @var string[]
     */
    protected static array $allowed_aria_roles = array(self::MENUITEM);

    public function __construct(Symbol $icon_or_glyph, string $label, string $action)
    {
        $this->icon_or_glyph = $icon_or_glyph;
        $this->label = $label;
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function getIconOrGlyph() : Symbol
    {
        return $this->icon_or_glyph;
    }

    /**
     * Get a button like this, but with an additional ARIA role.
     */
    // TODO PHP8: Chech missing on interface!
    public function withAriaRole(string $aria_role) : C\Button\Bulky
    {
        $this->checkArgIsElement(
            "role",
            $aria_role,
            self::$allowed_aria_roles,
            implode('/', self::$allowed_aria_roles)
        );
        $clone = clone $this;
        $clone->aria_role = $aria_role;
        return $clone;
    }

    /**
     * Get the ARIA role on the button.
     */
    public function getAriaRole() : ?string
    {
        return $this->aria_role;
    }
}
