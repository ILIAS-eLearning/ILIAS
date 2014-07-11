<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilBillingFloatParser.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilCoupon
{
	/**
	 * @var string
	 */
	private $code = '';

	/**
	 * @var float
	 */
	private $value = 0.00;

	/**
	 * @var null|string
	 */
	private $last_change;

	/**
	 * @var int
	 */
	private $user_id = 0;

	/**
	 * @var null|string
	 */
	private $expires;

	/**
	 * @var null|string
	 */
	private $created;

	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var boolean
	 */
	private $active;

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
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return string|null
	 */
	public function getLastChange()
	{
		return $this->last_change;
	}

	/**
	 * @return integer
	 */
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * @param string $a_prefix
	 */
	public function generateNewCode($a_prefix)
	{
		$this->code = $this->getUniqueCodeWithPrefix($a_prefix);
	}

	/**
	 * @param string $a_prefix
	 * @return string
	 */
	private function getUniqueCodeWithPrefix($a_prefix)
	{
		do
		{
			$code_suffix = $this->generateRandomString();
			$code        = $a_prefix . $code_suffix;
			$query       = 'SELECT coupon_pk FROM coupon WHERE coupon_code = ' . $this->db->quote($code, 'text');
		}
		while(($result = $this->db->query($query)) && $row = $this->db->fetchAssoc($result));
		return $code;
	}

	/**
	 * @return string
	 */
	private function generateRandomString()
	{
		$randomString = "";
		$charset      = "0123456789ahjkmnpz";

		for($i = 0; $i < 8; $i++)
		{
			$random_int = mt_rand();
			$randomString .= $charset[$random_int % strlen($charset)];
		}

		return $randomString;
	}

	/**
	 * @return boolean
	 */
	public function getActive()
	{
		return (bool)$this->active;
	}

	/**
	 * @return null|string
	 */
	public function getCreationTimestamp()
	{
		return $this->created;
	}

	/**
	 * @return null|string
	 */
	public function getExpirationTimestamp()
	{
		return $this->expires;
	}

	/**
	 * @param string $a_code
	 * @return ilCoupon
	 * @throws ilException
	 */
	public function getInstance($a_code)
	{
		$result = $this->db->queryF('
			SELECT *
			FROM coupon
			WHERE coupon_code = %s AND coupon_active = %s',
			array('text', 'integer'),
			array($a_code, 1)
		);

		$row = $this->db->fetchAssoc($result);
		if(!$row)
		{
			throw new ilException("No coupon with code: $a_code found");
		}

		$coupon = $this->createInstanceFromArray($row);
		return $coupon;
	}

	/**
	 * @return float
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return array
	 */
	public function getHistory()
	{
		$history = array();
		$result  = $this->db->queryF(
							'SELECT * FROM coupon WHERE coupon_code = %s ORDER BY coupon_last_change',
								array('text'),
								array($this->code)
		);
		foreach($result as $row)
		{
			$history[] = array(
				'value'     => $row['coupon_value'],
				'timestamp' => $row['coupon_last_change'],
				'user_id'   => $row['coupon_usr_id']
			);
		}
		return $history;
	}


	/**
	 * @param string $code
	 */
	private function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @param float $value
	 */
	private function setValue($value)
	{
		$parser = new ilBillingFloatParser();
		$value  = $parser->getFloat($value);

		$this->value = $value;
	}

	/**
	 * @param null|string $last_change
	 */
	private function setLastChange($last_change)
	{
		$this->last_change = $last_change;
	}

	/**
	 * @param string|null $created
	 */
	private function setCreated($created)
	{
		$this->created = $created;
	}

	/**
	 * @param string|null $expires
	 */
	public function setExpires($expires)
	{
		$this->expires = $expires;
	}

	/**
	 * @param $user_id
	 */
	private function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * @param boolean $active
	 */
	private function setActive($active)
	{
		$this->active = (bool)$active;
	}

	/**
	 *
	 */
	public function setCreationTime()
	{
		$this->created = time();
	}

	/**
	 *
	 */
	private function setAllOldCouponsForHistoryToDeactivated()
	{
		$this->db->manipulateF(
				 'UPDATE coupon SET coupon_active = %s WHERE coupon_code = %s',
					 array('integer', 'text'),
					 array(0, $this->code)
		);
	}

	/**
	 * @param array $data
	 * @return ilCoupon
	 */
	private function createInstanceFromArray(array $data)
	{
		$instance = new self();
		$instance->setCode($data['coupon_code']);
		$instance->setValue($data['coupon_value']);
		$instance->setLastChange($data['coupon_last_change']);
		$instance->setCreated($data['coupon_created']);
		$instance->setExpires($data['coupon_expires']);
		$instance->setUserId($data['coupon_usr_id']);
		$instance->setActive($data['coupon_active']);
		return $instance;
	}

	/**
	 * @param float $a_value
	 * @throws ilException
	 */
	public function addValue($a_value)
	{
		if($this->isExpiredOnCreation())
		{
			throw new ilException('Cannot add value because the coupon is already expired');
		}

		$parser = new ilBillingFloatParser();
		$value  = $parser->getFloatForCoupons($a_value);

		if($value < 0)
		{
			throw new ilException('The added coupon value must not be negative');
		}

		$this->value += $a_value;
		$this->setAllOldCouponsForHistoryToDeactivated();
		$this->insertCoupon();
	}

	/**
	 * @param float $a_value
	 * @throws ilException
	 */
	public function subtractValue($a_value)
	{
		if($this->isExpiredOnCreation())
		{
			throw new ilException('Cannot subtract value because the coupon is already expired');
		}

		$parser = new ilBillingFloatParser();
		$value  = $parser->getFloatForCoupons($a_value);

		if($value < 0)
		{
			throw new ilException('The coupon value to be subtracted must not be negative');
		}

		$value *= -1;

		if($this->value + $value < 0)
		{
			throw new ilException('The coupon value must not be negative after subtraction');
		}

		$this->value += $value;
		$this->setAllOldCouponsForHistoryToDeactivated();
		$this->insertCoupon();
	}

	/**
	 * @return bool
	 */
	private function isExpiredOnCreation()
	{
		if($this->expires < time())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 *
	 */
	private function insertCoupon()
	{
		$id           = $this->db->nextId('coupon');
		$insert_value = round($this->value, 2);

		$this->setLastChange(time());

		$query = "INSERT INTO coupon"
			. " (coupon_pk"
			. ",coupon_code"
			. ",coupon_value"
			. ",coupon_last_change"
			. ",coupon_created"
			. ",coupon_expires"
			. ",coupon_usr_id"
			. ",coupon_active) "
			. "VALUES ( "
			. $this->db->quote($id, 'integer')
			. "," . $this->db->quote($this->code, 'text')
			. "," . $this->db->quote($insert_value, 'float')
			. "," . $this->db->quote($this->getLastChange(), 'integer')
			. "," . $this->db->quote($this->created, 'integer')
			. "," . $this->db->quote($this->expires, 'integer')
			. "," . $this->db->quote($this->user_id, 'integer')
			. "," . $this->db->quote(1, 'integer')
			. ")";
		$this->db->manipulate($query);
	}

	/**
	 * @return bool
	 */
	public function isExpired()
	{
		$result = $this->db->queryF(
						   'SELECT coupon_expires FROM coupon WHERE coupon_code = %s AND coupon_active = %s',
							   array('text', 'integer'),
							   array($this->code, 1)
		);
		$row    = $this->db->fetchAssoc($result);
		return ($row['coupon_expires'] && $row['coupon_expires'] < time());
	}
}
