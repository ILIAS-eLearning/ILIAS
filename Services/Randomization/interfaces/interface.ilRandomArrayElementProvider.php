<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilRandomArrayElementProvider
 * @package Services/Randomization
 * @author Michael Jansen <mjansen@databay.de>
 * @author Marvin Beym <mBeym@databay.de>
 */
interface ilRandomArrayElementProvider
{
    public function getSeed() : int;

    public function setSeed(int $seed) : void;

    public function buildSeedFromString(string $string) : int;

    public function shuffle(array $array) : array;
}
