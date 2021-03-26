<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Randomization/classes/class.ilArrayElementShuffler.php';

/**
 * @author Björn Heyser <bheyser@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 * @package Services/Randomization
 */
class ilDeterministicArrayElementProvider extends ilBaseRandomElementProvider implements ilRandomArrayElementProvider
{
    /**
     * @return int
     */
    protected function getInitialSeed() : int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function shuffle(array $array) : array
    {
        return $array;
    }
}
