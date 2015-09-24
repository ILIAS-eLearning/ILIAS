<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Database/classes/class.ilDB.php';
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
	 * @return ilTermsOfServiceTestSuite
	 */
	public static function suite()
	{
		if(!defined("ANONYMOUS_USER_ID"))
		{
			define("ANONYMOUS_USER_ID", 13);
		}

		$suite = new self();

		require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemUnlinkedRelationTest.php';
		$suite->addTestSuite('ilBuddySystemUnlinkedRelationTest');

		require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemRequestedRelationTest.php';
		$suite->addTestSuite('ilBuddySystemRequestedRelationTest');

		require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemRequestIgnoredRelationTest.php';
		$suite->addTestSuite('ilBuddySystemRequestIgnoredRelationTest');

		require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemLinkedRelationTest.php';
		$suite->addTestSuite('ilBuddySystemLinkedRelationTest');

		require_once 'Services/Contact/BuddySystem/test/ilBuddyListTest.php';
		$suite->addTestSuite('ilBuddyListTest');

		require_once 'Services/Contact/BuddySystem/test/ilBuddySystemRelationTest.php';
		$suite->addTestSuite('ilBuddySystemRelationTest');

		require_once 'Services/Contact/BuddySystem/test/ilBuddySystemRelationCollectionTest.php';
		$suite->addTestSuite('ilBuddySystemRelationCollectionTest');

		return $suite;
	}
}