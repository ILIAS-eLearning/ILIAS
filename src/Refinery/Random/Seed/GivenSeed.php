<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Random\Seed;

class GivenSeed implements Seed
{
    private int $seed;

    public function __construct(int $seed)
    {
        $this->seed = $seed;
    }

    public function seedRandomGenerator() : void
    {
        mt_srand($this->seed);
    }
}
