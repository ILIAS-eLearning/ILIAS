<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilBillItem.php';
require_once 'Services/Billing/classes/class.ilBillingFloatParser.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBillItem
{
	/**
	 * @var string
	 */
	private $title = '';

	/**
	 * @var string
	 */
	private $description = '';

	/**
	 * @var float
	 */
	private $pre_tax_amount = 0.00;

	/**
	 * @var
	 */
	private $VAT;

	/**
	 * @var string
	 */
	private $currency = '';

	/**
	 * @var int
	 */
	private $context_id;

	/**
	 * @var int
	 */
	private $parent_bill_id = 0;

	/**
	 * @var int
	 */
	private $final = 0;

	/**
	 * /**
	 * @var ilBill|null
	 */
	private $bill;

	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var integer
	 */
	private $id;

	/**
	 *
	 */
	public function __construct()
	{
		$this->db     = $GLOBALS['ilDB'];
		$this->logger = $GLOBALS['ilLog'];
	}

	/**
	 * @return string
	 */
	public function getBillNumber()
	{
		return $this->bill->getBillNumber();
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param ilBill $ilBill
	 */
	public function setBill(ilBill $bill)
	{
		$this->bill = $bill;
	}

	/**
	 * @return ilBill
	 */
	public function getBill()
	{
		$this->ensureBillExists();
		return $this->bill;
	}

	/**
	 * @throws ilException
	 */
	private function ensureBillExists()
	{
		if(!($this->bill instanceof ilBill))
		{
			throw new ilException("No bill was set for the item");
		}

	}

	/**
	 * @throws ilException
	 */
	private function ensureBillIsNotFinalized()
	{
		if($this->bill->isFinalized())
		{
			throw new ilException('Cannot add a bill item because the bill is already finalized');
		}
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
	public function getPreTaxAmount()
	{
		return round($this->pre_tax_amount, 2);
	}

	/**
	 * @return float
	 */
	public function getVAT()
	{
		if(null === $this->VAT)
		{
			if($this->bill instanceof ilBill)
			{
				$this->VAT = $this->bill->getVAT();
			}
			else
			{
				$this->VAT = 0.00;
			}
		}

		return round($this->VAT, 2);
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @return int
	 */
	public function getContextId()
	{
		return $this->context_id;
	}

	/**
	 * @return float
	 */
	public function getAmount()
	{
		return round($this->pre_tax_amount + $this->getTaxAmount(), 2);
	}

	/**
	 * @return float
	 */
	public function getTaxAmount()
	{
		return round($this->pre_tax_amount * ($this->getVAT() / 100), 2);
	}

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
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
	 * @param float $preTaxAmount
	 */
	public function setPreTaxAmount($preTaxAmount)
	{
		$parser               = new ilBillingFloatParser();
		$preTaxAmount         = $parser->getFloatForNegative($preTaxAmount);
		$this->pre_tax_amount = $preTaxAmount;
	}

	/**
	 * @param float $VAT
	 */
	public function setVAT($VAT)
	{
		$parser    = new ilBillingFloatParser();
		$VAT       = $parser->getFloat($VAT);
		$this->VAT = $VAT;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * @param integer $context_id
	 */
	public function setContextId($context_id)
	{
		$this->context_id = $context_id;
	}

	/**
	 * @param integer $id
	 */
	private function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @param boolean $final
	 */
	private function setFinal($final)
	{
		$this->final = $final;
	}

	/**
	 * @throws ilException
	 */
	public function create()
	{


		if($this->getId())
		{
			throw new ilException('Cannot create a bill item with an already existing id');
		}


		$this->ensureBillExists();

		$this->ensureBillIsNotFinalized();

		$query = "
			INSERT INTO billitem
			(
				billitem_pk, bill_fk, billitem_title,
				billitem_description, billitem_pta, billitem_vat,
				billitem_currency, billitem_context_id, billitem_final
			)
			VALUES
			(
				%s, %s, %s,
				%s, %s, %s,
				%s, %s, %s
			)
		";
		$id    = $this->db->nextId('billitem');
		$this->db->manipulateF(
				 $query,
					 array(
						 'integer', 'integer', 'text',
						 'text', 'float', 'float',
						 'text', 'integer', 'integer'
					 ),
					 array(
						 $id, $this->bill->getId(), $this->title,
						 $this->description, $this->pre_tax_amount, $this->VAT,
						 $this->currency, $this->context_id, $this->final
					 )
		);
		$this->setId($id);
	}

	/**
	 * @param array      $data
	 * @param ilBillItem $item
	 * @return ilBillItem
	 */
	public function seedInstanceWithData(array $data, ilBillItem $item)
	{
		return $this->createInstanceFromArray($data, $item);
	}

	/**
	 * @param array      $data
	 * @param ilBillItem $item
	 * @return ilBillItem
	 */
	private function createInstanceFromArray(array $data, ilBillItem $item)
	{
		$item->setId($data['billitem_pk']);
		$item->setTitle($data['billitem_title']);
		$item->setDescription($data['billitem_description']);
		$item->setVAT($data['billitem_vat']);
		$item->setCurrency($data['billitem_currency']);
		$item->setpreTaxAmount($data['billitem_pta']);
		$item->setFinal($data['billitem_final']);
		$item->setContextId($data['billitem_context_id']);
		

		return $item;
	}

	/**
	 * @throws ilException
	 */
	public function update()
	{
		if(!$this->getId())
		{
			throw new ilException('Cannot update a bill item without an id');
		}

		$this->ensureBillExists();

		$this->ensureBillIsNotFinalized();

		$this->db->beginTransaction();

		try
		{
			if($this->isFinalized())
			{
				throw new ilException('Cannot update the bill item because it is already finalized');
			}
		}
		catch(ilException $e)
		{
			$this->db->rollback();
			throw $e;
		}

		$this->parent_bill_id = $this->bill->getId();
		$query                = "
			UPDATE billitem SET 
			bill_fk = %s,
			billitem_title = %s,
			billitem_description = %s,
			billitem_pta = %s,
			billitem_vat = %s,
			billitem_currency = %s,
			billitem_context_id = %s,
			billitem_final = %s
			WHERE billitem_pk = %s";

		$this->db->manipulateF(
				 $query,
					 array(
						 'integer', 'text', 'text',
						 'float', 'float', 'text',
						 'integer', 'integer', 'integer'
					 ),
					 array(
						 $this->parent_bill_id, $this->title, $this->description,
						 $this->pre_tax_amount, $this->VAT, $this->currency,
						 $this->context_id, $this->final, $this->id
					 )
		);

		$this->db->commit();
	}

	/**
	 * @return boolean
	 * @throws ilException
	 */
	public function finalize()
	{
		if(!$this->getId())
		{
			throw new ilException('Cannot finalize a bill item without an id');
		}

		$this->db->beginTransaction();

		try
		{
			if($this->isFinalized())
			{
				throw new ilException('Cannot finalize the bill item because it is already finalized');
			}
		}
		catch(ilException $e)
		{
			$this->db->rollback();
			throw $e;
		}

		$query = "UPDATE billitem SET billitem_final = %s WHERE billitem_pk = %s";
		$this->db->manipulateF(
				 $query,
					 array('integer', 'integer'),
					 array(1, $this->getId())
		);

		$this->setFinal(true);

		$this->db->commit();

		return true;
	}

	/**
	 * @return boolean
	 * @throws ilException
	 */
	public function delete()
	{
		if(!$this->getId())
		{
			throw new ilException('Cannot delete a bill item without an id');
		}

		$this->ensureBillExists();

		$this->ensureBillIsNotFinalized();

		$this->db->beginTransaction();

		try
		{
			if($this->isFinalized())
			{
				throw new ilException('Cannot delete the bill item because it is already finalized');
			}
		}
		catch(ilException $e)
		{
			$this->db->rollback();
			throw $e;
		}

		$query = "DELETE FROM billitem WHERE billitem_pk = %s";
		$this->db->manipulateF(
				 $query,
					 array('integer'),
					 array($this->getId())
		);

		$this->db->commit();

		return true;
	}

	/**
	 * @return boolean
	 * @throws ilException
	 */
	public function isFinalized()
	{
		$query  = "SELECT billitem_final FROM billitem WHERE billitem_pk = %s";
		$result = $this->db->queryF(
						   $query,
							   array('integer'),
							   array($this->getId())
		);
		$row    = $this->db->fetchAssoc($result);
		return (bool)$row['billitem_final'];
	}
}
