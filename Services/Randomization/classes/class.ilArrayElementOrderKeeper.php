<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Randomization/classes/class.ilArrayElementShuffler.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Services/Randomization
 */
class ilArrayElementOrderKeeper extends ilArrayElementShuffler
{
    /**
     * @param array $array
     * @return array
     */
    public function shuffle($array)
    {
        return $array;
    }
}
