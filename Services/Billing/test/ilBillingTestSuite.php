<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

//require_once 'Services/Database/classes/class.ilDB.php';
require_once 'Services/Language/classes/class.ilLanguage.php';
require_once 'Services/Logging/classes/class.ilLog.php';
require_once 'Services/EventHandling/classes/class.ilAppEventHandler.php';
require_once 'Services/Exceptions/classes/class.ilException.php';
require_once 'Services/Billing/test/persistence/class.ilPDOToilDBAdapter.php';
require_once 'Services/Billing/classes/class.ilBill.php';
require_once 'Services/Billing/classes/class.ilBillItem.php';
require_once 'Services/Billing/classes/class.ilCoupons.php';
require_once 'Services/Billing/classes/class.ilCoupon.php';
require_once 'Services/Billing/classes/class.ilPDFBill.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBillingTestSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return ilBillingTestSuite
	 */
	public static function suite()
	{
		// Set timezone to prevent notices
		date_default_timezone_set('Europe/Berlin');

		$suite = new self();

		require_once dirname(__FILE__) . '/service/ilBillTest.php';
		$suite->addTestSuite('ilBillTest');

		require_once dirname(__FILE__) . '/service/ilBillItemTest.php';
		$suite->addTestSuite('ilBillItemTest');

		require_once dirname(__FILE__) . '/service/ilCouponTest.php';
		$suite->addTestSuite('ilCouponTest');

		require_once dirname(__FILE__) . '/service/ilCouponsTest.php';
		$suite->addTestSuite('ilCouponsTest');

		require_once dirname(__FILE__) . '/service/ilPDFBillTest.php';
		$suite->addTestSuite('ilPDFBillTest');

		return $suite;
	}
}