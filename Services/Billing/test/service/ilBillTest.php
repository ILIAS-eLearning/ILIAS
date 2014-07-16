<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBillTest extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * @var ilLog
	 */
	private $logger;

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
		$this->logger                 = $GLOBALS['ilLog'];

		parent::setUp();
	}

	/**
	 *
	 */
	protected function tearDown()
	{
		unset($this->db);
		unset($this->con);
		unset($this->ilBill);
		unset($this->pdo);

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

	/**
	 * Returns the test dataset.
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet('Services/Billing/test/persistence/seeds/bill1.xml');
	}

	/**
	 * @return ilBill
	 */
	public function testInstanceCanBeCreated()
	{
		$bill = new ilBill();
		$this->assertInstanceOf('ilBill', $bill);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));

		return $bill;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetLastEntryOfTheFinalizedBills(ilBill $bill)
	{
		$billnumbercount = $bill->fetchNumberOfFinalizedBillsByYear(date("Y"));
		$this->assertEquals(1, $billnumbercount);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testFillUpBillNumberToFiveDigits(ilBill $bill)
	{
		$billnumbercount = $bill->fetchNumberOfFinalizedBillsByYear(date("Y"));
		$billnumbercount = $bill->fillUpBillNumberToRequiredNumberOfDigits($billnumbercount);
		$this->assertEquals("00001", $billnumbercount);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetRecipientName(ilBill $bill)
	{
		$bill->setRecipientName("Name");
		$this->assertEquals("Name", $bill->getRecipientName());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetRecipientStreet(ilBill $bill)
	{
		$bill->setRecipientStreet("Street");
		$this->assertEquals("Street", $bill->getRecipientStreet());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetRecipientHouseNumber(ilBill $bill)
	{
		$bill->setRecipientHousenumber("HouseNumber");
		$this->assertEquals("HouseNumber", $bill->getRecipientHouseNumber());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetRecipientZipCode(ilBill $bill)
	{
		$bill->setRecipientZipcode("ZipCode");
		$this->assertEquals("ZipCode", $bill->getRecipientZipCode());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetRecipientCity(ilBill $bill)
	{
		$bill->setRecipientCity("City");
		$this->assertEquals("City", $bill->getRecipientCity());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetRecipientCountry(ilBill $bill)
	{
		$bill->setRecipientCountry("Country");
		$this->assertEquals("Country", $bill->getRecipientCountry());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetFinal(ilBill $bill)
	{
		$bill->setFinal(1);
		$this->assertEquals(1, $bill->getFinal());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetIlDate(ilBill $bill)
	{
		$ildate = new ilDate();
		$bill->setDate($ildate);
		$this->assertEquals($ildate, $bill->getDate());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetTitle(ilBill $bill)
	{
		$bill->setTitle("Title");
		$this->assertEquals("Title", $bill->getTitle());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetDescription(ilBill $bill)
	{
		$bill->setDescription("Description");
		$this->assertEquals("Description", $bill->getDescription());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetVAT(ilBill $bill)
	{
		$bill->setVAT(1223);
		$this->assertEquals(1223, $bill->getVAT());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetLogger(ilBill $bill)
	{
		$bill->setLogger($this->logger);
		$this->assertEquals($this->logger, $bill->getLogger());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetCostCenter(ilBill $bill)
	{
		$bill->setCostcenter("costcenter");
		$this->assertEquals("costcenter", $bill->getCostcenter());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetCurrency(ilBill $bill)
	{
		$bill->setCurrency("currency");
		$this->assertEquals("currency", $bill->getCurrency());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetUserId(ilBill $bill)
	{
		$bill->setUserId(1);
		$this->assertEquals(1, $bill->getUserid());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetContextId(ilBill $bill)
	{
		$bill->setContextId(1);
		$this->assertEquals(1, $bill->getContextId());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSetAndGetBillNumberPrefix(ilBill $bill)
	{
		$reflection_class = new ReflectionClass("ilBill");
		$method           = $reflection_class->getMethod("setBillNumberPrefix");
		$method->setAccessible(true);
		$method->invoke($bill, "a");

		$this->assertEquals("a", $bill->getBill_number_prefix());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetInstanceByIdWithKnownId(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		$this->assertEquals(2, $instance->getId());
		$this->assertEquals("00005", $instance->getNumber());
		$this->assertEquals("Max", $instance->getRecipientName());
		$this->assertEquals("Street", $instance->getRecipientStreet());
		$this->assertEquals("Number", $instance->getRecipientHousenumber());
		$this->assertEquals("ZIP", $instance->getRecipientZipcode());
		$this->assertEquals("City", $instance->getRecipientCity());
		$this->assertEquals("Country", $instance->getRecipientCountry());
		$this->assertEquals("title", $instance->getTitle());
		$this->assertEquals("desc", $instance->getDescription());
		$this->assertEquals(0, $instance->getVAT());
		$this->assertEquals(1000, $instance->getcostCenter());
		$this->assertEquals("curr", $instance->getCurrency());
		$this->assertEquals("2014", $instance->getBillyear());
		$this->assertEquals(0, $instance->getUserId());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetInstanceByBillNumberWithKnownBillNumber(ilBill $bill)
	{
		$instance = $bill->getInstanceByBillNumber("00005");

		$this->assertEquals(2, $instance->getId());
		$this->assertEquals("00005", $instance->getNumber());
		$this->assertEquals("Max", $instance->getRecipientName());
		$this->assertEquals("Street", $instance->getRecipientStreet());
		$this->assertEquals("Number", $instance->getRecipientHousenumber());
		$this->assertEquals("ZIP", $instance->getRecipientZipcode());
		$this->assertEquals("City", $instance->getRecipientCity());
		$this->assertEquals("Country", $instance->getRecipientCountry());
		$this->assertEquals("title", $instance->getTitle());
		$this->assertEquals("desc", $instance->getDescription());
		$this->assertEquals(0, $instance->getVAT());
		$this->assertEquals(1000, $instance->getcostCenter());
		$this->assertEquals("curr", $instance->getCurrency());
		$this->assertEquals("2014", $instance->getBillyear());
		$this->assertEquals(0, $instance->getUserId());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testExpectTrueWhenBillNumberAlreadyInDatabase(ilBill $bill)
	{
		$reflection_class = new ReflectionClass("ilBill");
		$method           = $reflection_class->getMethod("checkIfBillNumberAlreadyInDatabase");
		$method->setAccessible(true);
		$value = $method->invoke($bill, "00005");
		$this->assertEquals(true, $value);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testExpectFalseWhenBillNumberNotAlreadyInDatabase(ilBill $bill)
	{
		$reflection_class = new ReflectionClass("ilBill");
		$method           = $reflection_class->getMethod("checkIfBillNumberAlreadyInDatabase");
		$method->setAccessible(true);
		$value = $method->invoke($bill, "111111");
		$this->assertEquals(false, $value);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetInstanceByBillNumberWithUnknownBillNumber(ilBill $bill)
	{
		try
		{
			$instance = $bill->getInstanceByBillNumber(777);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No Bill with Billnumber:777 found');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetInstanceByIdWithEmptyResultUnknownId(ilBill $bill)
	{
		try
		{
			$instance = $bill->getInstanceById(777);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No Bill with ID:777 found');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetIdWhenEntryInDbIsExistent(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);

		$this->assertEquals($instance->getId(), 2);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testGetIdWhenEntryInDbIsNotExistent(ilBill $bill)
	{
		try
		{
			$instance = $bill->getInstanceById(777);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No Bill with ID:777 found');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testCreateNewBillInTableBillWithFilledProps(ilBill $bill)
	{
		$bill->setBillyear(date("Y"));
		$bill->setContextId("7");
		$bill->setCostCenter("Munich");
		$bill->setCurrency("€");
		$bill->setDescription("Description 4000 Chars");
		$bill->setFinal(0);
		$bill->setRecipientCity("City");
		$bill->setRecipientCountry("Germany");
		$bill->setRecipientHousenumber("23");
		$bill->setRecipientName("Max Fri");
		$bill->setRecipientStreet("Street Av.");
		$bill->setRecipientZipcode("12345");
		$bill->setTitle("Title");
		$bill->setUserId("8");
		$bill->setVAT(14.35);
		$this->assertEquals(true, $bill->create());
		$this->assertEquals(3, $this->getConnection()->getRowCount('bill'));
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testCreateNewBillFromInstance(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testUpdateInstanciatedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		$instance->setTitle("TITLEUPDATE");
		$check = $instance->update();
		$this->assertEquals("TITLEUPDATE", $instance->getTitle());
		$this->assertEquals(true, $check);
	}

	public function testUpdateBillSuccess()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setTitle("Title");
		$bill->create();


		$this->assertEquals("Title", $bill->getTitle());
		$bill->setTitle("TitleAfterUpdate");

		$check = $bill->update();
		$this->assertEquals("TitleAfterUpdate", $bill->getTitle());
		$this->assertEquals(true, $check);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testFinalizeABillOnAAlreadyFinalizedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(1);

		$this->assertEquals(1, $instance->isFinalized());
		$this->assertEquals(null, $instance->finalize());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testFinalizeABillOnANotFinalizedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);

		$this->assertEquals(0, $instance->isFinalized());
		$this->assertEquals(true, $instance->finalize());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testTryToUpdateAAlreadyFinalizedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(1);

		try
		{
			$instance->update();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot update the bill because its already finalized');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testTryToDeleteAAlreadyFinalizedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(1);

		try
		{
			$instance->delete();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Bill was already finalized');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testDeleteAExistingNotFinalizedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		$this->assertEquals(true, $instance->delete());
		$this->assertEquals(1, $this->getConnection()->getRowCount('bill'));
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testDeleteAExistingNotFinalizedAndThenCallIsFinalizeToExpectExceptionBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		$this->assertEquals(true, $instance->delete());
		try
		{
			$instance->isFinalized();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No Bill with ID:2 found');
		}
	}

	/**
	 *
	 */
	public function testCreateNewBillNumber()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->create();
		$bill->finalize();
		$this->assertEquals($bill->getBillNumber(), date("Ymd") . "-00001");
	}

	public function testDeleteBillWithoutIdSet()
	{
		$bill = new ilBill();
		try
		{
			$bill->delete();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot delete a bill without id');
		}
	}

	public function testUpdateBillWithoutIdSet()
	{
		$bill = new ilBill();
		try
		{
			$bill->update();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot update a bill without id');
		}
	}

	public function testFinalizeBillWithoutIdSet()
	{
		$bill = new ilBill();
		try
		{
			$bill->finalize();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot finalize a bill without id');
		}

	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testCreateBillWithIdSet(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		try
		{
			$instance->create();
			$this->fail("An expected exception has not been thrown.");

		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot create bill with already defined id');
		}
	}
}