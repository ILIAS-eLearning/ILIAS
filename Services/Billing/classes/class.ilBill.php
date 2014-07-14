<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilBillItem.php';
require_once 'Services/Exceptions/classes/class.ilException.php';
require_once 'Services/Calendar/classes/class.ilDate.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBill
{
	const BILL_NUMBER_FORMAT                = '%s-YMD-%s';
	const BILL_NUMBER_PREFIX_PADDING_AMOUNT = 5;

	/**
	 * @var int
	 */
	private $id = 0;

	/**
	 * @var string
	 */
	private $recipientName = "";

	/**
	 * @var string
	 */
	private $number = "";

	/**
	 * @var string
	 */
	private $recipientStreet = "";

	/**
	 * @var string
	 */
	private $recipientHousenumber = "";

	/**
	 * @var string
	 */
	private $recipientZipcode = "";

	/**
	 * @var string
	 */
	private $recipientCity = "";

	/**
	 * @var string
	 */
	private $recipientCountry = "";

	/**
	 * @var string
	 */
	private $recipientEmail = "";

	/**
	 * @var ilDate
	 */
	private $date;

	/**
	 * @var string
	 */
	private $title = "";

	/**
	 * @var string
	 */
	private $description = "";

	/**
	 * @var float
	 */
	private $VAT = 0.00;

	/**
	 * @var string
	 */
	private $costCenter = "";

	/**
	 * @var string
	 */
	private $currency = "";

	/**
	 * @var int
	 */
	private $userId = 0;

	/**
	 * @var int
	 */
	private $contextId = 0;

	/**
	 * @var array
	 */
	private $loadedBillItems = array();

	/**
	 * @var ilDatabaseHandler
	 */
	private $db;

	/**
	 * @var
	 */
	private $billnumber;

	/**
	 * @var
	 */
	private $bill_number_prefix;

	/**
	 * @var
	 */
	private $billyear;

	/**
	 * @var bool
	 */
	private $final = false;

	/**
	 * @var ilLog
	 */
	private $logger;

	/**
	 *
	 */
	public function __construct()
	{
		$this->db     = $GLOBALS['ilDB'];
		$this->logger = $GLOBALS['ilLog'];
	}


	/**
	 * @param integer $id
	 */
	private function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @param string $billyear
	 */
	public function setBillyear($billyear)
	{
		$this->billyear = $billyear;
	}

	/**
	 * @param boolean $final
	 */
	public function setFinal($final)
	{
		$this->final = $final;
	}

	/**
	 * @param integer $contextId
	 */
	public function setContextId($contextId)
	{
		$this->contextId = $contextId;
	}

	/**
	 * @param string $recipientName
	 */
	public function setRecipientName($recipientName)
	{
		$this->recipientName = $recipientName;
	}

	/**
	 * @param string $recipientStreet
	 */
	public function setRecipientStreet($recipientStreet)
	{
		$this->recipientStreet = $recipientStreet;
	}

	/**
	 * @param string $recipientHousenumber
	 */
	public function setRecipientHousenumber($recipientHousenumber)
	{
		$this->recipientHousenumber = $recipientHousenumber;
	}

	/**
	 * @param string $recipientZipcode
	 */
	public function setRecipientZipcode($recipientZipcode)
	{
		$this->recipientZipcode = $recipientZipcode;
	}

	/**
	 * @param string $recipientCity
	 */
	public function setRecipientCity($recipientCity)
	{
		$this->recipientCity = $recipientCity;
	}

	/**
	 * @param string $recipientCountry
	 */
	public function setRecipientCountry($recipientCountry)
	{
		$this->recipientCountry = $recipientCountry;
	}
	
	/**
	 * @param string $recipientEmail
	 */
	public function setRecipientEmail($recipientEmail)
	{
		$this->recipientEmail = $recipientEmail;
	}

	/**
	 * @param ilDate $date
	 */
	public function setDate(ilDate $date)
	{
		$this->date = $date;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @param float $VAT
	 */
	public function setVAT($VAT)
	{
		$ilfloatparser = new ilBillingFloatParser();
		$VAT           = $ilfloatparser->getFloat($VAT);
		$this->VAT     = $VAT;
	}

	/**
	 * @param string $costCenter
	 */
	public function setCostCenter($costCenter)
	{
		$this->costCenter = $costCenter;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		if($this->determineIfBillContainsItems())
		{
			$this->currency = $currency;
		}
		else
		{
			throw new ilException("Currency cannot be set.There exists already items for this bill.");
		}
	}

	/**
	 * @param string $currency
	 */
	private function setCurrencyForInstanciation($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * @param integer $userId
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * @param string $bill_number_prefix
	 */
	private function setBillNumberPrefix($bill_number_prefix)
	{
		$this->bill_number_prefix = $bill_number_prefix;
	}

	/**
	 *
	 */
	private function setBillNumber($billnumber)
	{
		$this->billnumber = $billnumber;
	}

	/**
	 * @param ilLog $logger
	 */
	public function setLogger(ilLog $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @param $number
	 */
	private function setNumber($number)
	{
		$this->number = $number;
	}

	/**
	 * @return ilLog
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * @return string
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @return int
	 */
	public function getPreTaxAmount()
	{
		$preTaxAmount = 0;
		$billItems    = $this->getItems();

		foreach($billItems as $billItem)
		{
			$val = $billItem->getPreTaxAmount();

			$preTaxAmount = $preTaxAmount + round($val, 2);
		}
		return $preTaxAmount;
	}

	/**
	 * @return ilBillItem[]
	 */
	public function getItems()
	{
		return $this->loadedBillItems;
	}

	/**
	 * @return float|int
	 */
	public function getAmount()
	{
		$amount    = 0;
		$billItems = $this->getItems();
		foreach($billItems as $billItem)
		{
			$amount += round($billItem->getAmount(), 2);
		}
		return $amount;
	}

	/**
	 * @return float|int
	 */
	public function getTaxAmount()
	{
		$taxAmount = 0;
		$billItems = $this->getItems();
		foreach($billItems as $billItem)
		{
			$taxAmount += round($billItem->getTaxAmount(), 2);
		}
		return $taxAmount;
	}

	/**
	 * @return mixed
	 */
	public function getBillNumber()
	{
		return $this->billnumber;
	}

	/**
	 * @return mixed
	 */
	public function getBill_number_prefix()
	{
		return $this->bill_number_prefix;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @return string
	 */
	public function getCostCenter()
	{
		return $this->costCenter;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return float
	 */
	public function getVAT()
	{
		return $this->VAT;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{

		return $this->date;
	}

	/**
	 * @return int|null
	 */
	public function getId()
	{
		return $this->id > 0 ? $this->id : null;
	}

	/**
	 * @return string
	 */
	public function getBillyear()
	{
		return $this->billyear;
	}

	/**
	 * @return boolean
	 */
	public function getFinal()
	{
		return $this->final;
	}

	/**
	 * @return int
	 */
	public function getContextId()
	{
		return $this->contextId;
	}

	/**
	 * @return string
	 */
	public function getRecipientName()
	{
		return $this->recipientName;
	}

	/**
	 * @return string
	 */
	public function getRecipientStreet()
	{
		return $this->recipientStreet;
	}

	/**
	 * @return string
	 */
	public function getRecipientHousenumber()
	{
		return $this->recipientHousenumber;
	}

	/**
	 * @return string
	 */
	public function getRecipientZipcode()
	{
		return $this->recipientZipcode;
	}

	/**
	 * @return string
	 */
	public function getRecipientCity()
	{
		return $this->recipientCity;
	}

	/**
	 * @return string
	 */
	public function getRecipientCountry()
	{
		return $this->recipientCountry;
	}
	
	/**
	 * @return string
	 */
	public function getRecipientEmail() {
		return $this->recipientEmail;
	}

	/**
	 * @param string $a_bill_number
	 * @return ilBill
	 * @throws ilException
	 */
	public static function getInstanceByBillNumber($a_bill_number)
	{

		$result = $GLOBALS['ilDB']->query(
								  "SELECT * "
								  . "FROM bill "
								  . "WHERE bill_number= " . $GLOBALS['ilDB']->quote($a_bill_number, 'integer') . " ");


		$row = $GLOBALS['ilDB']->fetchAssoc($result);
		if(!$row)
		{
			throw new ilException("No Bill with Billnumber:" . $a_bill_number . " found");
		}

		$newBill = self::createInstanceFromArray($row);

		$items = $newBill->loadExistingBillItems($newBill->getId());
		foreach($items as $item)
		{
			array_push($newBill->loadedBillItems, $item);
		}

		return $newBill;
	}

	/**
	 * @param integer $a_bill_id
	 * @return ilBill
	 * @throws ilException
	 */
	public static function getInstanceById($a_bill_id)
	{

		$result = $GLOBALS['ilDB']->query(
								  "SELECT * "
								  . "FROM bill "
								  . "WHERE bill_pk= " . $GLOBALS['ilDB']->quote($a_bill_id, 'integer') . " ");

		$row = $GLOBALS['ilDB']->fetchAssoc($result);


		if(!$row)
		{
			throw new ilException("No Bill with ID:" . $a_bill_id . " found");
		}

		$newBill = self::createInstanceFromArray((array)$row);
		$items   = $newBill->loadExistingBillItems($newBill->getId());

		foreach($items as $item)
		{
			array_push($newBill->loadedBillItems, $item);
		}

		return $newBill;
	}

	/**
	 * @param array $instancedata
	 * @param ilDB  $db
	 * @param ilLog $logger
	 * @return ilBill
	 */
	private function createInstanceFromArray($instancedata)
	{
		$instance = new self();
		$instance->setId($instancedata["bill_pk"]);
		$instance->setNumber($instancedata["bill_number"]);
		$instance->setBillNumber($instancedata["bill_number"]);
		$instance->setRecipientName($instancedata["bill_recipient_name"]);
		$instance->setRecipientStreet($instancedata["bill_recipient_street"]);
		$instance->setRecipientHousenumber($instancedata["bill_recipient_hnr"]);
		$instance->setRecipientZipcode($instancedata["bill_recipient_zip"]);
		$instance->setRecipientCity($instancedata["bill_recipient_city"]);
		$instance->setRecipientCountry($instancedata["bill_recipient_cntry"]);
		$instance->setRecipientEmail($instancedata["bill_recipient_email"]);
		$instance->setTitle($instancedata["bill_title"]);
		$instance->setDescription($instancedata["bill_description"]);
		$instance->setVAT($instancedata["bill_vat"]);
		$instance->setDate(new ilDate($instancedata["bill_date"], IL_CAL_UNIX));
		$instance->setcostCenter($instancedata["bill_cost_center"]);
		$instance->setCurrencyForInstanciation($instancedata["bill_currency"]);
		$instance->setBillyear($instancedata["bill_year"]);
		$instance->setUserId($instancedata["bill_usr_id"]);
		$instance->setFinal($instancedata["bill_final"]);
		$instance->setContextId($instancedata["bill_context_id"]);
		return $instance;
	}

	/**
	 * @param integer $a_bill_id
	 * @return ilBillItem[]
	 */
	private function loadExistingBillItems($a_bill_id)
	{

		$loadedbillitems = array();

		$result = $this->db->queryF("SELECT * FROM billitem WHERE bill_fk=%s", array('integer'), array($a_bill_id));

		while($row = $this->db->fetchAssoc($result))
		{

			$tmpbillitem = new ilBillItem();
			$tmpbillitem = $tmpbillitem->seedInstanceWithData($row, $tmpbillitem);

			$tmpbillitem->setBill($this);
			array_push($loadedbillitems, $tmpbillitem);
		}


		return $loadedbillitems;
	}

	/**
	 * @return boolean
	 */
	private function determineIfBillContainsItems()
	{
		$result = $this->db->queryF("SELECT * FROM billitem WHERE bill_fk=%s", array('integer'), array($this->getId()));

		$row = $this->db->fetchAssoc($result);
		if(!$row)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return boolean
	 */
	public function create()
	{
		if($this->id)
		{
			throw new ilException("Cannot create bill with already defined id");
		}

		$id = $this->db->nextId('bill');

		$query = "INSERT INTO bill"
			. " (bill_pk"
			. ",bill_recipient_name"
			. ",bill_recipient_street"
			. ",bill_recipient_hnr"
			. ",bill_recipient_zip"
			. ",bill_recipient_city"
			. ",bill_recipient_cntry"
			. ",bill_recipient_email"
			. ",bill_date"
			. ",bill_title"
			. ",bill_description"
			. ",bill_vat"
			. ",bill_cost_center"
			. ",bill_currency"
			. ",bill_usr_id"
			. ",bill_year"
			. ",bill_final"
			. ",bill_context_id) "
			. "VALUES ( "
			. $this->db->quote($id, 'integer')
			. "," . $this->db->quote($this->recipientName, 'text')
			. "," . $this->db->quote($this->recipientStreet, 'text')
			. "," . $this->db->quote($this->recipientHousenumber, 'text')
			. "," . $this->db->quote($this->recipientZipcode, 'text')
			. "," . $this->db->quote($this->recipientCity, 'text')
			. "," . $this->db->quote($this->recipientCountry, 'text')
			. "," . $this->db->quote($this->recipientEmail, 'text')
			. "," . $this->db->quote($this->getDate()->get(IL_CAL_UNIX), 'integer')
			. "," . $this->db->quote($this->title, 'text')
			. "," . $this->db->quote($this->description, 'text')
			. "," . $this->db->quote($this->VAT, 'float')
			. "," . $this->db->quote($this->costCenter, 'text')
			. "," . $this->db->quote($this->currency, 'text')
			. "," . $this->db->quote($this->userId, 'integer')
			. "," . $this->db->quote($this->billyear, 'integer')
			. "," . $this->db->quote(0, 'integer')
			. "," . $this->db->quote($this->contextId, 'integer')
			. ")";

		$this->db->manipulate($query);
		$this->setId($id);
		return true;
	}

	/**
	 * @return boolean
	 * @throws ilException
	 */
	public function update()
	{
		if(!$this->id)
		{
			throw new ilException("Cannot update a bill without id");
		}

		$this->db->beginTransaction();

		try
		{
			if($this->isFinalized())
			{
				throw new ilException("Cannot update the bill because its already finalized");
			}
		}
		catch(ilException $e)
		{
			$this->db->rollback();
			throw $e;
		}

		$query = "UPDATE bill SET "
			. "bill_number=" . $this->db->quote($this->billnumber, 'text')
			. ",bill_recipient_name=" . $this->db->quote($this->recipientName, 'text')
			. ",bill_recipient_street=" . $this->db->quote($this->recipientStreet, 'text')
			. ",bill_recipient_hnr=" . $this->db->quote($this->recipientHousenumber, 'text')
			. ",bill_recipient_zip=" . $this->db->quote($this->recipientZipcode, 'text')
			. ",bill_recipient_city=" . $this->db->quote($this->recipientCity, 'text')
			. ",bill_recipient_cntry=" . $this->db->quote($this->recipientCountry, 'text')
			. ",bill_recipient_email=" . $this->db->quote($this->recipientEmail, 'text')
			. ",bill_date=" . $this->db->quote($this->date->get(IL_CAL_UNIX), 'integer')
			. ",bill_title=" . $this->db->quote($this->title, 'text')
			. ",bill_description=" . $this->db->quote($this->description, 'text')
			. ",bill_vat=" . $this->db->quote($this->VAT, 'float')
			. ",bill_cost_center=" . $this->db->quote($this->costCenter, 'text')
			. ",bill_currency=" . $this->db->quote($this->currency, 'text')
			. ",bill_usr_id=" . $this->db->quote($this->userId, 'text')
			. ",bill_year=" . $this->db->quote(date('Y'), 'text')
			. ",bill_final=" . $this->db->quote(false, 'integer')
			. ",bill_context_id=" . $this->db->quote($this->contextId, 'integer')
			. " WHERE bill_pk=" . $this->db->quote($this->id, 'integer');

		$this->db->manipulate($query);
		$this->db->commit();

		return true;
	}

	/**
	 * @return bool
	 * @throws ilException
	 */
	public function isFinalized()
	{
		$query  = "SELECT bill_final "
			. "FROM bill "
			. "WHERE bill_pk=" . $this->db->quote($this->getId(), 'integer') . "";
		$result = $this->db->query($query);
		$row    = $this->db->fetchAssoc($result);

		if(!$row)
		{
			throw new ilException("No Bill with ID:" . $this->id . " found");
		}

		return (bool)$row["bill_final"];
	}

	/**
	 * @return boolean
	 */
	public function finalize()
	{
		if(!$this->id)
		{
			throw new ilException("Cannot finalize a bill without id");
		}

		if($this->isFinalized())
		{
			return false;
		}

		$this->db->beginTransaction();

		$this->setBillNumber($this->generateUniqueBillNumber());
		$query = "
			UPDATE bill
			SET
			bill_final = %s,
			bill_number = %s
			WHERE bill_pk = %s
		";
		$this->db->manipulateF(
			$query,
			array('integer', 'text', 'integer'),
			array(1, $this->getBillNumber(), $this->getId())
		);

		$query = "
			UPDATE billitem
			SET
			billitem_final = %s
			WHERE bill_fk = %s
		";
		$this->db->manipulateF(
			$query,
			array('integer', 'integer'),
			array(1, $this->getId())
		);

		$this->db->commit();

		$GLOBALS['ilAppEventHandler']->raise('Billing', 'billFinalized', array('bill' => $this));

		return true;
	}

	/**
	 * @return string
	 */

	private function generateUniqueBillNumber()
	{
		do
		{
			$newBillNumber = $this->generateBillNumber();
			$query = 'SELECT bill_number FROM bill WHERE bill_number = ' . $newBillNumber;
		}
		while(($result = $this->db->query($query)) && $row = $this->db->fetchAssoc($result));
		return $newBillNumber;
	}

	protected function generateBillNumber()
	{
		$num_of_bills = $this->fetchNumberOfFinalizedBillsByYear($this->getBillyear());

		$prefix_from_settings = $GLOBALS["ilSetting"]->get('billing_number_prefix');
		$this->setBillNumberPrefix($prefix_from_settings);

		$number = ($prefix_from_settings ? $prefix_from_settings . '-' : '') . str_replace("-","",$this->date->get(IL_CAL_DATE)) . "-" . $this->fillUpBillNumberToRequiredNumberOfDigits($num_of_bills + 1);

		return $number;
	}

	/**
	 * @param string $number
	 * @return int
	 */
	private function checkIfBillNumberAlreadyInDatabase($number)
	{
		$result = $this->db->queryF("
			SELECT COUNT(*) cnt FROM bill WHERE bill_number = %s ",
			array('text'),
			array($number)
		);
		$row    = $this->db->fetchAssoc($result);
		return (int)$row['cnt'];
		if((int)$row['cnt'] == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param integer $year
	 * @return integer
	 */
	public function fetchNumberOfFinalizedBillsByYear($year)
	{
		$result = $this->db->queryF("
			SELECT COUNT(*) cnt FROM bill WHERE bill_final = %s AND bill_year = %s",
			array('integer', 'integer'),
			array(1, $year)
		);
		$row    = $this->db->fetchAssoc($result);
		return (int)$row['cnt'];
	}

	/**
	 * @param integer $bill_number
	 * @return string
	 */
	public function fillUpBillNumberToRequiredNumberOfDigits($bill_number)
	{
		$bill_number = str_pad($bill_number, self::BILL_NUMBER_PREFIX_PADDING_AMOUNT, '0', STR_PAD_LEFT);
		return $bill_number;
	}

	/**
	 * @return boolean
	 * @throws ilException
	 */
	public function delete()
	{
		if(!$this->id)
		{
			throw new ilException("Cannot delete a bill without id");
		}

		if($this->isFinalized())
		{
			$this->log("Bill " . $this->billnumber . " was already finalized");
			throw new ilException("Bill was already finalized");
		}

		$items = $this->loadExistingBillItems($this->id);
		if(count($items) > 0)
		{
			$this->log("Bill" . $this->id . "contained finalized items. They will be preserved");
		}

		$query = "DELETE "
			. "FROM billitem "
			. " WHERE bill_fk=" . $this->db->quote($this->getId(), 'integer') . " AND billitem_final='0'";
		$this->db->manipulate($query);


		$query1 = "DELETE "
			. "FROM bill "
			. " WHERE bill_pk=" . $this->db->quote($this->getId(), 'integer');
		$this->db->manipulate($query1);

		return true;
	}

	/**
	 * @param string $a_message
	 */
	private function log($a_message)
	{
		$this->logger->write(__CLASS__ . ':' . $a_message);
	}

	/**
	 * @param ilBillItem $item
	 * @throws ilException
	 */
	public function addItem(ilBillItem $item)
	{
		if($this->isFinalized())
		{
			throw new ilException("Bill was already finalized");
		}

		if($this->currency == "")
		{
			throw new ilException("Curreny of the bill not set. Set the currency of the bill first");
		}

		if($this->currency != $item->getCurrency() && $item->getCurrency() != "")
		{
			throw new ilException("Curreny of the bill not equal to the currency of the Item");
		}

		array_push($this->loadedBillItems, $item);
		$item->setBill($this);
	}
}
