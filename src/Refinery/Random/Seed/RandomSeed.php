<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Random\Seed;

class RandomSeed extends GivenSeed
{
    public function __construct()
    {
        parent::__construct($this->createSeed());
    }

    public function createSeed() : int
    {
        $array = explode(' ', microtime());
        $seed = $array[1] + ($array[0] * 100000);

        return $seed;
    }
}
