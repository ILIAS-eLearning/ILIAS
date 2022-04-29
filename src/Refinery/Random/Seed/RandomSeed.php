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
        $seed = ((int) $array[1]) + (((float) $array[0]) * 100000);

        return (int) $seed;
    }
}
