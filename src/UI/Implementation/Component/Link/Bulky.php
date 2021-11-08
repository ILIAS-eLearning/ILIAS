<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\Data\URI;

class Bulky extends Link implements C\Link\Bulky
{
    use JavaScriptBindable;

    // allowed ARIA roles
    public const MENUITEM = 'menuitem';

    protected string $label;
    protected ?string $aria_role = null;
    protected C\Symbol\Symbol $symbol;

    /**
     * @var string[]
     */
    protected static array $allowed_aria_roles = array(
        self::MENUITEM
    );

    public function __construct(C\Symbol\Symbol $symbol, string $label, URI $target)
    {
        parent::__construct($target->__toString());
        $this->label = $label;
        $this->symbol = $symbol;
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getSymbol() : C\Symbol\Symbol
    {
        return $this->symbol;
    }

    /**
     * Get a button like this, but with an additional ARIA role.
     */
    public function withAriaRole(string $aria_role) : C\Link\Bulky
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
