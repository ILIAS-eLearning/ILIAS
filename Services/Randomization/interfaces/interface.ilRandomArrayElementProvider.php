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
    /**
     * @return int
     */
    public function getSeed() : int;

    /**
     * @param int $seed
     */
    public function setSeed(int $seed) : void;

    /**
     * @param string $string
     * @return int
     */
    public function buildSeedFromString(string $string) : int;

    /**
     * @param array $array
     * @return array
     */
    public function shuffle(array $array) : array;
}
