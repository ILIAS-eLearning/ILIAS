<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailAddressList
{
    /**
     * @return \ilMailAddress[]
     */
    public function value() : array;
}
