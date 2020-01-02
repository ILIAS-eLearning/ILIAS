<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemLinkButtonType
 * @author Guido Vollbach <gvollbach@databay.de>
 */
interface ilBuddySystemLinkButtonType
{
    /**
     * @return string
     */
    public function getHTML();

    /**
     * @return int
     */
    public function getUsrId();

    /**
     * @return ilBuddyList
     */
    public function getBuddyList();
}
