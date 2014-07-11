<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilCoupon.php';
require_once 'Services/Billing/classes/class.ilCoupons.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilCouponTest extends PHPUnit_Extensions_Database_TestCase
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
	 * @var ilCoupon
	 */
	private $coupon;


	public function setUp()
	{
		$this->coupon                 = new ilCoupon();
		$GLOBALS['ilLog']             = $this->getMockBuilder('ilLog')->disableOriginalConstructor()->setMethods(array('write'))->getMock();
		$GLOBALS['ilAppEventHandler'] = $this->getMockBuilder('ilAppEventHandler')->disableOriginalConstructor()->setMethods(array('raise'))->getMock();
		$GLOBALS['ilSetting']         = $this->getMockBuilder('ilSetting')->disableOriginalConstructor()->setMethods(array('get'))->getMock();

		parent::setUp();
	}


	protected function tearDown()
	{
		unset($this->con);
		unset($this->adapter);
		unset($this->database);
		unset($this->pdo);
		unset($this->db);
		unset($this->ilCoupon);
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

	public function testCanCreateNewCoupon()
	{
		$this->assertInstanceOf('ilCoupon', $this->coupon);
	}

	public function testGetCode()
	{
		$this->assertEquals(null, $this->coupon->getCode());
	}


	public function testGetAndSetValue()
	{
		$reflection_class = new ReflectionClass("ilCoupon");
		$method           = $reflection_class->getMethod("setValue");
		$method->setAccessible(true);
		$ilCoupon = new ilCoupon(null);
		$method->invoke($ilCoupon, 1000);
		$method1 = $reflection_class->getMethod("getValue");
		$value   = $method1->invoke($ilCoupon);
		$this->assertEquals(1000, $value);
	}

	public function testGetAndSetUserid()
	{
		$reflection_class = new ReflectionClass("ilCoupon");
		$method           = $reflection_class->getMethod("setUserId");
		$method->setAccessible(true);
		$ilCoupon = new ilCoupon(null);
		$method->invoke($ilCoupon, "1");
		$method1 = $reflection_class->getMethod("getUserId");
		$value   = $method1->invoke($ilCoupon);
		$this->assertEquals("1", $value);
	}

	public function testGetAndSetLastChange()
	{
		$reflection_class = new ReflectionClass("ilCoupon");
		$method           = $reflection_class->getMethod("setLastChange");
		$method->setAccessible(true);
		$ilCoupon = new ilCoupon(null);
		$method->invoke($ilCoupon, "1420066800");
		$method1 = $reflection_class->getMethod("getLastChange");
		$value   = $method1->invoke($ilCoupon);
		$this->assertEquals("1420066800", $value);
	}

	public function testGenerateRandomStringWithLength8()
	{
		$reflection_class = new ReflectionClass("ilCoupon");
		$method           = $reflection_class->getMethod("generateRandomString");
		$method->setAccessible(true);
		$ilCoupon     = new ilCoupon(null);
		$randomString = $method->invoke($ilCoupon, 8);
		$haystack     = "0123456789ahjkmnpz";

		for($ranStrIndex = 0; $ranStrIndex < strlen($randomString); $ranStrIndex++)
		{
			$this->assertContains($randomString{$ranStrIndex}, $haystack);
		}
	}

	public function testTheHistoryOfExistingCoupon()
	{
		$reflection_class = new ReflectionClass("ilCoupon");
		$ilCoupon         = new ilCoupon(null);

		$method = $reflection_class->getMethod("setCode");
		$method->setAccessible(true);

		$method->invoke($ilCoupon, "12354321");

		$method1 = $reflection_class->getMethod("getHistory");
		$method1->setAccessible(true);
		$history = $method1->invoke($ilCoupon);


		$this->assertEquals("90.00", $history[0]["value"]);
		$this->assertEquals("1400104800", $history[0]["timestamp"]);
		$this->assertEquals("1", $history[0]["user_id"]);

		$this->assertEquals("100.23", $history[1]["value"]);
		$this->assertEquals("1400191200", $history[1]["timestamp"]);
		$this->assertEquals("1", $history[1]["user_id"]);
	}

	public function testGenerateNewCodeWithPrefixAndGetThisCode()
	{
		$this->coupon->generateNewCode("A");
		$code = $this->coupon->getCode();

		$this->assertEquals("A", $code{0});

		$haystack = "0123456789ahjkmnpz";

		for($ranStrIndex = 1; $ranStrIndex < strlen($code); $ranStrIndex++)
		{
			$this->assertContains($code{$ranStrIndex}, $haystack);
		}
	}

	public function testGetInstanceFromExistingCode()
	{
		$instance = $this->coupon->getInstance("12354321");

		$this->assertEquals("100.23", $instance->getValue());
		$this->assertEquals("1", $instance->getActive());
		$this->assertEquals("12354321", $instance->getCode());
		$this->assertEquals("1397599200", $instance->getCreationTimestamp());
		$this->assertEquals("1429135200", $instance->getExpirationTimestamp());
		$this->assertEquals("1400191200", $instance->getLastChange());
		$this->assertEquals("1", $instance->getUserId());
	}

	public function testGetInstanceFromNotExistingCode()
	{
		try
		{
			$this->coupon->getInstance("99999");
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No coupon with code: 99999 found');
		}
	}

	public function testCheckIfCouponIsExpiredWithNotExpiredCoupon()
	{
		$this->coupon = $this->coupon->getInstance("24645687");
		$this->assertEquals(false, $this->coupon->isExpired());
	}

	public function testCheckIfCouponIsExpiredWithExpiredCoupon()
	{
		$this->coupon = $this->coupon->getInstance("111");
		$this->assertEquals(true, $this->coupon->isExpired());
	}

	public function testInsertNewCouponIntoDB()
	{
		$rowCount         = $this->getConnection()->getRowCount('coupon');
		$reflection_class = new ReflectionClass("ilCoupon");
		$ilCoupon         = new ilCoupon(null);

		$method = $reflection_class->getMethod("generatenewcode");
		$method->setAccessible(true);

		$method1 = $reflection_class->getMethod("insertCoupon");
		$method1->setAccessible(true);
		$method1->invoke($ilCoupon, "100");

		$this->assertEquals($rowCount + 1, $this->getConnection()->getRowCount('coupon'));
	}

	public function testCreateNewCouponAndAddAValue()
	{
		$rowCount     = $this->getConnection()->getRowCount('coupon');
		$this->coupon = $this->coupon->getInstance("24645687");
		$oldval       = $this->coupon->getValue();
		$this->coupon->addValue(100.223434);
		$this->assertEquals($rowCount + 1, $this->getConnection()->getRowCount('coupon'));
		$this->coupon = $this->coupon->getInstance("24645687");
		$this->assertEquals(round($oldval + 100.223434, 2), $this->coupon->getValue());
	}

	public function testAddValueWithNegativeValue()
	{
		$this->coupon = $this->coupon->getInstance("24645687");
		try
		{
			$this->coupon->addValue(-1000);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'The added coupon value must not be negative');
		}
	}

	public function testSubstractValueWithNegativeValue()
	{
		$this->coupon = $this->coupon->getInstance("24645687");
		try
		{
			$this->coupon->subtractValue(-1000);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'The coupon value to be subtracted must not be negative');
		}
	}


	public function testCreateNewCouponAndSubstractAValue()
	{
		$rowCount     = $this->getConnection()->getRowCount('coupon');
		$this->coupon = $this->coupon->getInstance("24645687");
		$oldval       = $this->coupon->getValue();
		$this->coupon->subtractValue(100.223434);
		$this->assertEquals($rowCount + 1, $this->getConnection()->getRowCount('coupon'));
		$this->coupon = $this->coupon->getInstance("24645687");
		$this->assertEquals(round($oldval - 100.223434, 2), $this->coupon->getValue());
	}

	public function testCreateNewCouponAndSubstractAValueThatEntryBecomeBelowZero()
	{
		$this->coupon = $this->coupon->getInstance("24645687");

		try
		{
			$this->coupon->subtractValue(1000.223434);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'The coupon value must not be negative after subtraction');
		}
	}

	public function testCreateNewCouponAndAddAValueThatIsBelowZero()
	{
		$this->coupon = $this->coupon->getInstance("24645687");

		try
		{
			$this->coupon->addValue(-1000.223434);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'The added coupon value must not be negative');
		}
	}

	public function testCreateNewCouponAndAddAValueOnAAlreadyExpiredCoupon()
	{
		$this->coupon = $this->coupon->getInstance("111");

		try
		{
			$this->coupon->addValue(-1000.223434);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot add value because the coupon is already expired');
		}
	}

	public function testCreateNewCouponAndSubstractAValueOnAAlreadyExpiredCoupon()
	{
		$this->coupon = $this->coupon->getInstance("111");

		try
		{
			$this->coupon->subtractValue("-1000");
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot subtract value because the coupon is already expired');
		}
	}

	public function testAddCouponWithCommaInsteadDot()
	{
		$this->coupon = $this->coupon->getInstance("24645687");
		$this->coupon->addValue("100.000,12");

	}
}
