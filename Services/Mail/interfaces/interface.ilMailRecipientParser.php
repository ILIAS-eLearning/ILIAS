<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailRecipientParser
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailRecipientParser
{
    /**
     * @return ilMailAddress[]
     */
    public function parse() : array;
}
