<?php declare(strict_types=1);

namespace ILIAS\Refinery\Random\Effect;

use ILIAS\Refinery\Effect\IdentityEffect;
use ILIAS\Refinery\Random\Seed\Seed;

class ShuffleEffect extends IdentityEffect
{
    private Seed $seed;

    public function __construct(array $value, Seed $seed)
    {
        parent::__construct($value);
        $this->seed = $seed;
    }

    /**
     * @return array
     */
    public function value()
    {
        $this->seed->seedRandomGenerator();
        $value = parent::value();
        \shuffle($value);

        return $value;
    }
}
