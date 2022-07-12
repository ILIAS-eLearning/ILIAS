<?php declare(strict_types=1);

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
     *
     * ATTENTION: This is intentionally missing on the interface, because thit
     * is not functionality we want to publicly announce. This was added to fix
     * a11y problems with the Main Bar. Aria-roles is a detail that can be decided
     * on internally, so no need for consumers to bother with this...
     */
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
