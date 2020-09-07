<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
$GLOBALS["DIC"] = new \ILIAS\DI\Container();

require_once 'Services/Database/interfaces/interface.ilDBInterface.php';
require_once 'Services/Database/classes/class.ilDBConstants.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/EventHandling/classes/class.ilAppEventHandler.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelation.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelationCollection.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemLinkedRelationState.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemUnlinkedRelationState.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRequestedRelationState.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemIgnoredRequestRelationState.php';
require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateException.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddySystemTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return self
     */
    public static function suite()
    {
        if (!defined("ANONYMOUS_USER_ID")) {
            define("ANONYMOUS_USER_ID", 13);
        }

        $suite = new self();

        require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemUnlinkedStateRelationTest.php';
        $suite->addTestSuite('ilBuddySystemUnlinkedStateRelationTest');

        require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemRequestedStateRelationTest.php';
        $suite->addTestSuite('ilBuddySystemRequestedStateRelationTest');

        require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemRequestIgnoredStateRelationTest.php';
        $suite->addTestSuite('ilBuddySystemRequestIgnoredStateRelationTest');

        require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemLinkedStateRelationTest.php';
        $suite->addTestSuite('ilBuddySystemLinkedStateRelationTest');

        require_once 'Services/Contact/BuddySystem/test/ilBuddyListTest.php';
        $suite->addTestSuite('ilBuddyListTest');

        require_once 'Services/Contact/BuddySystem/test/ilBuddySystemRelationTest.php';
        $suite->addTestSuite('ilBuddySystemRelationTest');

        require_once 'Services/Contact/BuddySystem/test/ilBuddySystemRelationCollectionTest.php';
        $suite->addTestSuite('ilBuddySystemRelationCollectionTest');

        return $suite;
    }
}
