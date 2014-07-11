<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilBill.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBillItemTest extends PHPUnit_Extensions_Database_TestCase
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
	 * @var ilBill
	 */
	private $bill;

	/**
	 * @var ilBillItem
	 */
	private $billitem;

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
		unset($this->adapter);
		unset($this->database);
		unset($this->pdo);
		unset($this->ilBill);
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
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet|PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../persistence/seeds/billItems.xml');
	}

	public function testInstanceBillCanBeCreated()
	{
		$this->bill = new ilBill();
		$this->bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$this->assertInstanceOf('ilBill', $this->bill);
		return $this->bill;
	}

	public function testInstanceBillItemCanBeCreated()
	{
		$this->billitem = new ilBillItem();
		$this->assertInstanceOf('ilBillItem', $this->billitem);
		return $this->billitem;
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testSetAndGetBill(ilBill $bill)
	{
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$item = new ilBillItem();

		$item->setBill($bill);
		$this->assertEquals($bill, $item->getBill());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testSetAndGetBillNumber(ilBill $bill)
	{
		$item = new ilBillItem();
		$item->setBill($bill);
		$this->assertEquals($bill->getBillNumber(), $item->getBillNumber());
	}

	public function testSetAndGetVat()
	{
		$item = new ilBillItem();
		$item->setVAT(12.12121212);
		$this->assertEquals(12.12, $item->getVAT());

		$another_item = new ilBillItem();
		$this->assertEquals(0.00, $another_item->getVAT());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testSetAndGetVATWhenVatNotSet(ilBill $bill)
	{
		$item = new ilBillItem();
		$bill = $bill->getInstanceById(2);
		$bill->addItem($item);
		$another_item = new ilBillItem();
		$this->assertEquals(0.00, $another_item->getVAT());
	}

	public function testSetAndGetVATWhenVatNotSetAndBillNotSet()
	{
		$item = new ilBillItem();
		$this->assertEquals(0.00, $item->getVAT());
	}

	public function testSetAndGetVATWhenVatSetAndBillNotSetAndVatUnderZero()
	{
		$item = new ilBillItem();
		$item->setVAT(-12.12);
		$this->assertEquals(0.00, $item->getVAT());
	}

	public function testGetBillFromItemWhenBillWasNotSet()
	{
		$item = new ilBillItem();
		try
		{
			$item->getBill();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No bill was set for the item');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testGetBillItemsFromBillnumber(ilBill $bill)
	{
		$instance = $bill->getInstanceByBillNumber("000003");
		$items    = $instance->getItems();
		$this->assertEquals("ItemDescription2", $items[0]->getDescription());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testUpdateBillItemWhenAlreadyFinalized(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);

		$item = new ilBillItem();
		$item->setBill($instance);
		$item->create();
		$item->finalize();

		try
		{
			$item->update();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();

			$this->assertEquals($emess, 'Cannot update the bill item because it is already finalized');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testUpdateBillItem(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);

		$item = new ilBillItem();
		$item->setBill($instance);
		$item->create();
		$item->update();
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testCanInsertNewBillItemIntoDb(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);

		$num_items = $this->getConnection()->getRowCount('billitem');
		$bill_item = new ilBillItem();
		$bill_item->setBill($instance);
		$bill_item->setTitle("InsertIntoDB");
		$bill_item->setVAT("77.77");

		$bill_item->setPreTaxAmount("1000");
		$bill_item->setContextId("1");
		$bill_item->setCurrency("Euro");
		$bill_item->setDescription("DESC");

		$bill_item->create();

		$instance->addItem($bill_item);
		$items = $instance->getItems();

		foreach($items as $item)
		{
			$this->assertEquals("InsertIntoDB", $item->getTitle());
			$this->assertEquals("DESC", $item->getDescription());
			$this->assertEquals("Euro", $item->getCurrency());
			$this->assertEquals("1", $item->getContextId());
			$this->assertEquals("77.77", $item->getVAT());
			$this->assertEquals("1000", $item->getPreTaxAmount());
		}
		$this->assertEquals($num_items + 1, $this->getConnection()->getRowCount('billitem'));
	}

	public function testCreateBillItemWhenNoBillSet()
	{
		$item = new ilBillItem();
		try
		{
			$item->create();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{

			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No bill was set for the item');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testAddItemsOnAlreadyFinalizedBill(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		$instance->finalize();
		$billItem = new ilBillItem();
		$billItem->setBill($instance);


		try
		{
			$billItem->create();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot add a bill item because the bill is already finalized');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testCanInsertNewBillItemIntoDbWithVATFromBill(ilBill $bill)
	{
		$instance  = $bill->getInstanceById(2);
		$num_items = $this->getConnection()->getRowCount('billitem');
		$bill_item = new ilBillItem();
		$bill_item->setBill($instance);
		$bill_item->setTitle("TestWithBillVat");
		$bill_item->create();

		$instance->addItem($bill_item);

		$this->assertEquals("14.76", $bill_item->getVAT());

		$this->assertEquals($num_items + 1, $this->getConnection()->getRowCount('billitem'));

		$items = $instance->getItems();

		foreach($items as $item)
		{
			$this->assertEquals("14.76", $item->getVAT());
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testCanInsertNewBillItemIntoDbWithNotValidVAT(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);

		$bill_item = new ilBillItem();
		$bill_item->setBill($instance);
		$bill_item->setVAT("-77.77");
		$bill_item->create();
		$instance->addItem($bill_item);
		$items = $instance->getItems();

		foreach($items as $item)
		{
			$this->assertEquals("0", $item->getVAT());
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testCanInsertNewBillItemIntoDBWithStringAsVAT(ilBill $bill)
	{
		$instance  = $bill->getInstanceById(2);
		$bill_item = new ilBillItem();
		$bill_item->setBill($instance);
		$bill_item->setVAT("-!§DSAF");
		$bill_item->create();
		$instance->addItem($bill_item);
		$items = $instance->getItems();

		foreach($items as $item)
		{
			$this->assertEquals("0", $item->getVAT());
		}
	}

	public function testCreateNegativeBillItem()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setCurrency("Euro");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT(33.33);
		$item->setPreTaxAmount(-1000);
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("-1000", $item->getPreTaxAmount());
		$this->assertEquals("33.33", $item->getVAT());
	}

	/**
	 *
	 */
	public function testFinalizeBillWithoutCreate()
	{
		$item = new ilBillItem();
		try
		{
			$item->finalize();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot finalize a bill item without an id');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testCreateBillItemThenCreateAgain(ilBill $bill)
	{
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->create();
		try
		{
			$item->create();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot create a bill item with an already existing id');
		}
	}

	public function testDeleteBillWithoutCreate()
	{
		$item = new ilBillItem();
		try
		{
			$item->delete();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot delete a bill item without an id');
		}
	}

	/**
	 *
	 */
	public function testTryToCreateAAlreadyFinalizedBillItem()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setCurrency("Euro");
		$bill->create();

		$item = new ilBillItem();
		$item->setBill($bill);
		$item->create();
		$item->finalize();

		try
		{
			$item->create();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot create a bill item with an already existing id');
		}
	}

	/**
	 *
	 */
	public function testTryToUpdateANotCreatedBillItem()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setCurrency("Euro");
		$bill->create();

		$item = new ilBillItem();
		$item->setBill($bill);

		try
		{
			$item->update();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot update a bill item without an id');
		}
	}

	/**
	 *
	 */
	public function testTryToDeleteNotCreatedBillItem()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setCurrency("Euro");
		$bill->create();

		$item = new ilBillItem();
		$item->setBill($bill);

		try
		{
			$item->update();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot update a bill item without an id');
		}
	}

	public function testCreateBillItemWithVATasString()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setCurrency("Euro");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT("wrongVAT");
		$item->setPreTaxAmount(-1000);
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("-1000", $item->getPreTaxAmount());
		$this->assertEquals("0", $item->getVAT());
	}

	public function testCreateBillItemWithWrongInterpuctationInVAT()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setVAT(7.77);
		$bill->setCurrency("Euro");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT("12233,33");
		$item->setPreTaxAmount(-1000);
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("-1000", $item->getPreTaxAmount());
		$this->assertEquals("12233.33", $item->getVAT());
	}

	public function testCreateBillItemWithWrongInterpuctationInPreTax()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setVAT(7.77);
		$bill->setCurrency("Euro");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT("13,33");
		$item->setPreTaxAmount("12,12");
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("12.12", $item->getPreTaxAmount());
		$this->assertEquals("13.33", $item->getVAT());
	}

	public function testCreateBillItemWithWrongStringInPreTax()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setCurrency("Euro");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT("12233,33");
		$item->setPreTaxAmount("wefwe");
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("0.0", $item->getPreTaxAmount());
		$this->assertEquals("12233.33", $item->getVAT());
	}

	public function testPreTaxAmountWithNewItemAndNewBillWithVATFromBillItem()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setVAT(7.77);
		$bill->setCurrency("Euro");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT(33.33);
		$item->setPreTaxAmount(1000);
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("1000", $item->getPreTaxAmount());
		$this->assertEquals("33.33", $item->getVAT());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testSetCurrenyWhenItemsAlreadyExists(ilBill $bill)
	{
		$instance = $bill->getInstanceById(2);
		$instance->setDate(new ilDate(time(), IL_CAL_UNIX));
		$item = new ilBillItem();
		$item->setBill($instance);
		$item->setVAT("-77.77");

		$item->create();
		$instance->addItem($item);

		try
		{
			$instance->setCurrency("Dollar");
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Currency cannot be set.There exists already items for this bill.');
		}
	}

	public function testAddItemWhenCurrencyOfBillAndBillItemIsNotEqual()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setVAT(7.77);
		$bill->setCurrency("Dollar");
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT(33.33);
		$item->setPreTaxAmount(1000);
		$item->create();
		$item->finalize();
		try
		{
			$bill->addItem($item);
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Curreny of the bill not equal to the currency of the Item');
		}
	}

	public function testAddItemWhenCurrencyOfTheBillIsNotSet()
	{
		$bill = new ilBill();
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->setVAT(7.77);
		$bill->create();
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT(33.33);
		$item->setPreTaxAmount(1000);
		$item->create();
		$item->finalize();
		try
		{
			$bill->addItem($item);
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Curreny of the bill not set. Set the currency of the bill first');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testAmountFromExistingBillsWithExisitingItems(ilBill $bill)
	{
		$bill = $bill->getInstanceById(1);
		$this->assertEquals("3950.16", $bill->getAmount());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testTaxAmountFromExistingBillWithExistingItems(ilBill $bill)
	{
		$bill = $bill->getInstanceById(1);
		$this->assertEquals("617.16", $bill->getTaxAmount());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testPreTaxAmountFromExistingBillsWithExisitingItems(ilBill $bill)
	{
		$bill = $bill->getInstanceById(1);
		$this->assertEquals("3333", $bill->getPreTaxAmount());
	}

	public function testFloatParserHelper()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->create();
		$bill->setCurrency("Euro");
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT("334.233,33");
		$item->create();
		$item->finalize();
		$bill->addItem($item);
		$bill->finalize();
		$this->assertEquals("334233.33", $item->getVAT());
	}

	public function testFinalizeFinalizedItem()
	{
		$bill = new ilBill();
		$bill->setVAT(7.77);
		$bill->setDate(new ilDate(time(), IL_CAL_UNIX));
		$bill->create();
		$bill->setCurrency("Euro");
		$item = new ilBillItem();
		$item->setBill($bill);
		$item->setTitle("item1ForBill");
		$item->setContextId(1);
		$item->setCurrency("Euro");
		$item->setVAT("334.233,33");
		$item->create();
		$item->finalize();
		try
		{
			$item->finalize();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot finalize the bill item because it is already finalized');
		}
	}

	public function testCreateItemWithoutExistingBill()
	{
		$item = new ilBillItem();
		try
		{
			$item->create();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'No bill was set for the item');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testDeleteAnExistingItem(ilBill $bill)
	{
		$instance  = $bill->getInstanceById(3);
		$num_items = $this->getConnection()->getRowCount('billitem');

		$items = $instance->getItems();
		$items[0]->delete();

		$this->assertEquals($num_items - 1, $this->getConnection()->getRowCount('billitem'));
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testDeleteAnExistingItemWhenItsAlreadyFinalized(ilBill $bill)
	{
		$instance = $bill->getInstanceById(3);

		$item = new ilBillItem();
		$item->setBill($instance);
		$items = $instance->getItems();
		try
		{
			$items[1]->delete();
			$this->fail("An expected exception has not been thrown.");
		}
		catch(Exception $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot delete the bill item because it is already finalized');
		}
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testFinalizeABillAndAllBelongingItems(ilBill $bill)
	{
		$instance = $bill->getInstanceById(3);
		$instance->finalize();
		$items = $instance->getItems();
		$this->assertEquals(1, $items[0]->isFinalized());
	}

	/**
	 * @depends testInstanceBillCanBeCreated
	 */
	public function testDeleteBillWithFinalizedItems(ilBill $bill)
	{
		$instance = $bill->getInstanceById(3);
		$instance->delete();
	}

}