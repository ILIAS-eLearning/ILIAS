<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilCoupon.php';
require_once 'Services/Billing/classes/class.ilCoupons.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilCouponsTest extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * @var PDO
	 * @static
	 */
	protected static $db;

	/**
	 * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	protected $con;

	/**
	 *
	 */
	public function setUp()
	{
		$GLOBALS['ilLog']             = $this->getMockBuilder('ilLog')->disableOriginalConstructor()->setMethods(array('write'))->getMock();
		$GLOBALS['ilAppEventHandler'] = $this->getMockBuilder('ilAppEventHandler')->disableOriginalConstructor()->setMethods(array('raise'))->getMock();
		$GLOBALS['ilSetting']         = $this->getMockBuilder('ilSetting')->disableOriginalConstructor()->setMethods(array('get'))->getMock();

		parent::setUp();
	}

	/**
	 *
	 */
	protected function tearDown()
	{
		unset($this->adapter);
		unset($this->database);
		unset($this->pdo);
		unset($this->db);
		unset($this->con);
		unset($this->ilCoupon);
		unset($this->ilCoupons);
		parent::tearDown();
	}

	/**
	 * Returns the test database connection.
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	protected function getConnection()
	{
		if(null === $this->con)
		{
			if(null == self::$db)
			{
				self::$db = new PDO('sqlite::memory:');
				$adapter  = new ilPDOToilDBAdapter(self::$db);
				$queries  = explode(';', file_get_contents('Services/Billing/test/persistence/sql/create.sql'));
				foreach($queries as $query)
				{
					if(!trim($query))
					{
						continue;
					}
					$adapter->query($query);
				}
				$GLOBALS['ilDB'] = $adapter;
			}
			$this->con = $this->createDefaultDBConnection(self::$db, ':memory:');
		}

		return $this->con;
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../persistence/seeds/coupon.xml');
	}

	public function testCanCreateNewCoupons()
	{
		$this->assertInstanceOf('ilCoupons', ilCoupons::getSingleton());
	}

	public function testCreateCouponsWithNegativeAmount()
	{
		$coupons = ilCoupons::getSingleton();
		try
		{
			$coupons->createCoupons(-5, 100, "1420066800", "a");
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'The passed amount parameter must be a positive integer');
		}
	}

	public function testCreateCouponsWithNegativeValue()
	{
		$coupons = ilCoupons::getSingleton();
		try
		{
			$coupons->createCoupons(5, -100, "1420066800", "a");
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'The passed value parameter must be a positive floating point number');
		}
	}

	public function testCreateCouponsWithWithValidPrefix()
	{
		$coupons    = ilCoupons::getSingleton();
		$rowCount   = $this->getConnection()->getRowCount('coupon');
		$couponlist = $coupons->createCoupons(5, 100, "1420066800", "a");
		$this->assertEquals($rowCount + 5, $this->getConnection()->getRowCount('coupon'));
		for($index = 0; $index < count($couponlist); $index++)
		{
			$this->assertEquals("a", $couponlist[$index]{0});
		}
		$this->assertEquals(5, count($couponlist));
	}

	public function testCreateCouponsWithNotValidPrefix()
	{
		try
		{
			ilCoupons::getSingleton()->createCoupons(2, 100, "1420066800", "b");
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Prefix format not in Range of [0123456789ahjkmnpz]');
		}
	}

	public function testCreateCouponsWithNoPrefix()
	{
		$coupons    = ilCoupons::getSingleton();
		$rowCount   = $this->getConnection()->getRowCount('coupon');
		$couponlist = $coupons->createCoupons(5, 77, "1420066800");
		$this->assertEquals($rowCount + 5, $this->getConnection()->getRowCount('coupon'));
		$this->assertEquals(5, count($couponlist));
	}


	public function testCreateCouponsWithLongDoubleValue()
	{
		$coupons    = ilCoupons::getSingleton();
		$rowCount   = $this->getConnection()->getRowCount('coupon');
		$couponlist = $coupons->createCoupons(5, 77.123456789, time() + 60 * 60 * 24 * 365 * 3);
		$this->assertEquals($rowCount + 5, $this->getConnection()->getRowCount('coupon'));
		$this->assertEquals(5, count($couponlist));

		$stats = $coupons->getStatistics();
		$this->assertEquals(1485.83, $stats["value"]);
	}

	public function testGetStatisticsFromAllNotExpiredCodes()
	{
		$coupons    = ilCoupons::getSingleton();
		$couponlist = $coupons->createCoupons(5, 77, "1420066800");
		$stats      = $coupons->getStatistics();
		$this->assertEquals(1485.23, $stats["value"]);
		$this->assertEquals(7, $stats["amount"]);
	}

	public function testGetCouponOfExistingUser()
	{
		$coupons = ilCoupons::getSingleton();
		$coupons->createCoupons(5, 77, "1420066800");
		$couponsOfUser = $coupons->getCouponsOfUser("0");
		$this->assertEquals(5, count($couponsOfUser));
		for($index = 0; $index < count($couponsOfUser); $index++)
		{
			$this->assertEquals(77.00, $couponsOfUser[$index]["value"]);
			$this->assertEquals("1420066800", $couponsOfUser[$index]["expires"]);
		}
	}

	public function testGetCouponOfNotExistingUser()
	{
		$coupons = ilCoupons::getSingleton();
		$coupons->createCoupons(5, 77, "1420066800");
		$couponsOfUser = $coupons->getCouponsOfUser("77");
		$this->assertEquals(0, count($couponsOfUser));
	}
}