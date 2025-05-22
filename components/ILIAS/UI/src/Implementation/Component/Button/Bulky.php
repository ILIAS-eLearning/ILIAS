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

    protected ?string $aria_role = null;

    /**
     * @var string[]
     */
    protected static array $allowed_aria_roles = array(self::MENUITEM);

    public function __construct(string $label, string $action)
    {
        $this->label = $label;
        $this->action = $action;
    }

    /**
     * Get a button like this, but with an additional ARIA role.
     *
     * ATTENTION: This is intentionally missing on the interface, because thit
     * is not functionality we want to publicly announce. This was added to fix
     * a11y problems with the Main Bar. Aria-roles is a detail that can be decided
     * on internally, so no need for consumers to bother with this...
     */
    public function withAriaRole(string $aria_role): C\Button\Bulky
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
    public function getAriaRole(): ?string
    {
        return $this->aria_role;
    }
}
