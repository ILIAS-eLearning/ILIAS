<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailAddressType
{
    /**
     * Returns an array of resolved user ids
     * @return int[]
     */
    public function resolve();

    /**
     * @param $a_sender_id integer
     * @return bool
     */
    public function validate($a_sender_id);
}
