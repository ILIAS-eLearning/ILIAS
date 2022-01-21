<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Random\Seed;

interface Seed
{
    public function seedRandomGenerator() : void;
}
