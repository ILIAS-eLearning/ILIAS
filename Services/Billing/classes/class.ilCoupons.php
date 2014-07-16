<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilCoupon.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilCoupons
{
	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 *
	 */
	private function __construct()
	{
		$this->db = $GLOBALS['ilDB'];
	}

	/**
	 * @static
	 * @return self
	 */
	public static function getSingleton()
	{
		if(null === self::$instance)
		{
			self::$instance = new ilCoupons();
		}
		return self::$instance;
	}

	/**
	 * @return array
	 */
	public function getStatistics()
	{
		$value  = 0;
		$amount = 0;

		$result = $this->db->queryF(
						   'SELECT coupon_value FROM coupon WHERE coupon_expires >= %s AND coupon_active = %s',
							   array('integer', 'integer'),
							   array(time(), 1)
		);
		while($row = $this->db->fetchAssoc($result))
		{
			$value += $row['coupon_value'];
			++$amount;
		}

		return array(
			'value'  => $value,
			'amount' => $amount
		);
	}

	/**
	 * @param integer $a_user_id
	 * @return array
	 */
	public function getCouponsOfUser($a_user_id)
	{
		$result  = $this->db->queryF(
							'SELECT * FROM coupon WHERE coupon_usr_id = %s AND coupon_active = %s',
								array('integer', 'integer'),
								array($a_user_id, 1)
		);
		$coupons = array();
		while($row = $this->db->fetchAssoc($result))
		{
			$coupons[] = array(
				'code'    => $row['coupon_code'],
				'value'   => $row['coupon_value'],
				'expires' => $row['coupon_expires']
			);
		}
		return $coupons;
	}

	/**
	 * @param integer     $a_amount
	 * @param float       $a_value
	 * @param integer     $a_expires
	 * @param string|null $a_prefix
	 * @return array
	 * @throws ilException
	 */
	public function createCoupons($a_amount, $a_value, $a_expires, $a_prefix = null)
	{
		if(!is_numeric($a_amount) || (int)$a_amount != $a_amount || $a_amount < 0)
		{
			throw new ilException('ilCoupons::createCoupons: The passed amount parameter must be a positive integer '
								 .' but is "'.$a_amount.'"');
		}

		if(!is_numeric($a_value) || (float)$a_value != $a_value || $a_value < 0)
		{
			throw new ilException('ilCoupons::createCoupons: The passed value parameter must be a positive floating point number '
								 .'but is "'.$a_value.'"');
		}

		$coupons         = array();
		$coupons_objects = array();
		for($i = 0; $i < $a_amount; $i++)
		{
			$coupon            = $this->createNewCoupon($a_value, $a_expires, $a_prefix);
			$coupons[]         = $coupon->getCode();
			$coupons_objects[] = $coupon;
		}

		$GLOBALS['ilAppEventHandler']->raise('Billing', 'couponsCreated', array('coupons' => $coupons_objects));

		return $coupons;
	}
	
	/**
	 * @param integer		$a_value
	 * @param integer 		$a_expires
	 * @param string|null	$a_prefix
	 * @return ilCoupon
	 * @throws ilException
	 */
	public function createCoupon($a_value, $a_expires, $a_prefix = null) {
		$coupons = $this->createCoupons(1, $a_value, $a_expires, $a_prefix);
		return $coupons[0];
	}

	/**
	 * @param string $a_code
	 * @return bool
	 */
	public function isValidCode($a_code)
	{
		$result = $this->db->queryF('
			SELECT *
			FROM coupon
			WHERE coupon_code = %s AND coupon_active = %s',
			array('text', 'integer'),
			array($a_code, 1)
		);
		
		if ($this->db->fetchAssoc($result)) {
			return true;
		}
		return false;
	}

	/**
	 * @param integer $a_value
	 * @param integer $a_expires
	 * @param string  $a_prefix
	 * @return ilCoupon
	 */
	private function createNewCoupon($a_value, $a_expires, $a_prefix)
	{
		$coupon = new ilCoupon();
		$coupon->setExpires($a_expires);
		$coupon->setCreationTime();
		if($a_prefix == null)
		{
			$coupon->generateNewCode("");
		}
		else
		{
			$this->ensurePrefixIsValid($a_prefix);
			$coupon->generateNewCode($a_prefix);
		}
		$coupon->addValue($a_value);
		return $coupon;
	}

	/**
	 * @param string $a_prefix
	 * @throws ilException
	 */
	private function ensurePrefixIsValid($a_prefix)
	{
		$characters = "0123456789ahjkmnpz";

		for($i = 0; $i < strlen($a_prefix); $i++)
		{
			$char = substr($a_prefix, $i, 1);

			if(strpos($characters, $char) == false)
			{
				throw new ilException("Prefix format not in Range of [0123456789ahjkmnpz]");
			}
		}
	}
}
