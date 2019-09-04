<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilBuddySystemLinkButtonType
 * @author Guido Vollbach <gvollbach@databay.de>
 */
interface ilBuddySystemLinkButtonType
{
    /**
     * @return string
     */
    public function getHTML() : string;

    /**
     * @return int
     */
    public function getUsrId() : int;

    /**
     * @return ilBuddyList
     */
    public function getBuddyList() : ilBuddyList;
}