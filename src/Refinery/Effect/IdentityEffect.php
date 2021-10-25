<?php declare(strict_types=1);

namespace ILIAS\Refinery\Effect;

class IdentityEffect implements Effect
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }
}
