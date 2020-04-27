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

    // allowed ARIA roles
    const MENUITEM = 'menuitem';

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
        self::MENUITEM
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
     * Get a button like this, but with an additional ARIA role.
     *
     * @param string $aria_role
     * @return Button
     */
    public function withAriaRole(string $aria_role) : Button
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
     *
     * @return string|null
     */
    public function getAriaRole() : ?string
    {
        return $this->aria_role;
    }
}
