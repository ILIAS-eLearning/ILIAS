<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilServicesContactSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return self
     */
    public static function suite()
    {
        $suite = new self();

        require_once 'Services/Contact/BuddySystem/test/ilBuddySystemTestSuite.php';
        $suite->addTestSuite('ilBuddySystemTestSuite');

        return $suite;
    }
}
