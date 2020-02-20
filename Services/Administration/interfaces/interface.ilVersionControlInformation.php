<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilVersionControlInformation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilVersionControlInformation
{
    /**
     * @return string
     */
    public function getInformationAsHtml();
}
