<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilPaymentTrustees
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilPaymentTrustees.php 22133 2009-10-16 08:09:11Z nkrzywon $
*
* @extends ilObject
* @package ilias-core
*/

class ilPaymentTrustees
{
	public $db = null;

	public $user_obj;
	public $trustees = array();
	
	public $perm_stat = null;
	public $perm_obj = null;
	public $perm_coupons = null;
	
	public $trustee_id = 0;

	/**
	* Constructor
	* @access	public
	*/
	public function __construct($user_obj)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->user_obj = $user_obj;

		$this->PERM_STATISTIC = 1;
		$this->PERM_OBJECT = 2;

		$this->__read();
	}
	
	public function getTrustees()
	{
		return $this->trustees ? $this->trustees : array();
	}
	public function getTrustee($a_usr_id)
	{
		return isset($this->trustees[$a_usr_id]) ? $this->trustees[$a_usr_id] : array();
	}
	public function isTrustee($a_usr_id)
	{
		return isset($this->trustees[$a_usr_id]);
	}
	
	public function toggleStatisticPermission($a_on)
	{
		$this->perm_stat = (bool) $a_on;
	}
	public function toggleObjectPermission($a_on)
	{
		$this->perm_obj = (bool) $a_on;
	}
	public function toggleCouponsPermission($a_on)
	{
		$this->perm_coupons = (bool) $a_on;
	}
	public function setTrusteeId($a_id)
	{
		$this->trustee_id = $a_id;
	}

	public function add()
	{
		$statement = $this->db->manipulateF('
			INSERT INTO payment_trustees
			( 	vendor_id,
				trustee_id,
				perm_stat,
				perm_coupons,
				perm_obj
			)
			VALUES (%s,%s,%s,%s,%s)',
			array('integer', 'integer', 'integer', 'integer', 'integer'),
			array(	$this->user_obj->getId(), 
					$this->__getTrusteeId(),
					$this->__getStatisticPermissionStatus(),
					$this->__getCouponsPermissisonStatus(),
					$this->__getObjectPermissisonStatus()
		));		
		
		
		$this->__read();

		return true;
	}
	public function modify()
	{
		if(!$this->__getTrusteeId())
		{
			die("ilPaymentTrustees::modify() no id given");
		}

		$statement = $this->db->manipulateF('
			UPDATE payment_trustees
			SET trustee_id = %s,
				perm_stat = %s,
				perm_obj = %s,
				perm_coupons = %s
			WHERE vendor_id = %s
			AND trustee_id = %s',
			array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
			array(	$this->__getTrusteeId(),
					$this->__getStatisticPermissionStatus(),
					$this->__getObjectPermissisonStatus(),
					$this->__getCouponsPermissisonStatus(),
					$this->user_obj->getId(),
					$this->__getTrusteeId()
		));
	
		
		$this->__read();

		return true;
	}
	public function delete()
	{
		if(!$this->__getTrusteeId())
		{
			die("ilPaymentTrustees::delete() no id given");
		}
		
		$statement = $this->db->manipulateF('
			DELETE FROM payment_trustees
			WHERE vendor_id = %s
			AND trustee_id = %s ',
			array('integer', 'integer'),
			array($this->user_obj->getId(), $this->__getTrusteeId()));
		
		$this->__read();

		return true;
	}
	
	public function deleteAll()
	{
		$statement = $this->db->manipulateF('
			DELETE FROM payment_trustees
			WHERE vendor_id = %s',
			array('integer'),
			array($this->user_obj->getId()));

		$this->__read();

		return true;
	}
			

	// PRIVATE
	private function __getTrusteeId()
	{
		return $this->trustee_id;
	}
	private function __getStatisticPermissionStatus()
	{
		return (int) $this->perm_stat;
	}
	private function __getObjectPermissisonStatus()
	{
		return (int) $this->perm_obj;
	}
	private function __getCouponsPermissisonStatus()
	{
		return (int) $this->perm_coupons;
	}
	private function __read()
	{

		$this->trustees = array();

		$res = $this->db->queryf('
			SELECT * FROM payment_trustees 
			WHERE vendor_id = %s',
			array('integer'),
			array($this->user_obj->getId()));
		
		while($row = $this->db->fetchObject($res))
		{
			$this->trustees[$row->trustee_id]['trustee_id'] = $row->trustee_id;
			$this->trustees[$row->trustee_id]['perm_stat'] = $row->perm_stat;
			$this->trustees[$row->trustee_id]['perm_obj'] = $row->perm_obj;
			$this->trustees[$row->trustee_id]['perm_coupons'] = $row->perm_coupons;
		}
	}

	// STATIC
	public static function _deleteTrusteesOfVendor($a_vendor_id)
	{
		global $ilDB;
		
		$statement = $ilDB->manipulateF('
			DELETE FROM payment_trustees 
			WHERE vendor_id = %s',
			array('integer'), array($a_vendor_id));
		
		return true;
	}

	public static function _hasStatisticPermission($a_trustee)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s',
			array('integer'), array($a_trustee));

		while($row = $ilDB->fetchObject($res))
		{
			if((bool) $row->perm_stat)
			{
				return true;
			}
		}
		return false;
	}
	
	public static function _hasObjectPermission($a_trustee)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s',
			array('integer'),
			array($a_trustee));

		while($row = $ilDB->fetchObject($res))
		{
			if((bool) $row->perm_obj)
			{
				return true;
			}
		}
		return false;
	}
	
	public static function _hasCouponsPermission($a_trustee)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s',
			array('integer'),
			array($a_trustee));
		
		while($row = $ilDB->fetchObject($res))
		{
			if((bool) $row->perm_coupons)
			{
				return true;
			}
		}
		return false;
	}
	
	public static function _hasStatisticPermissionByVendor($a_trustee,$a_vendor)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s
			AND vendor_id = %s
			AND perm_stat = %s',
			array('integer', 'integer', 'integer' ),
			array($a_trustee, $a_vendor, '1'));
		
		return $res->numRows() ? true : false;
	}

	public static function _hasObjectPermissionByVendor($a_trustee,$a_vendor)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s
			AND vendor_id = %s
			AND perm_obj = %s',
			array('integer', 'integer', 'integer' ),
			array($a_trustee, $a_vendor, '1'));
		
		return $ilDB->numRows($res) ? true : false;
	}

	public static function _hasCouponsPermissionByVendor($a_trustee,$a_vendor)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s
			AND vendor_id = %s
			AND perm_coupons = %s',
			array('integer', 'integer', 'integer' ),
			array($a_trustee, $a_vendor, '1'));

		return $res->numRows() ? true : false;
	}

	public static function _hasAccess($a_usr_id)
	{
		return ilPaymentTrustees::_hasStatisticPermission($a_usr_id) or 
			ilPaymentTrustees::_hasObjectPermission($a_usr_id) or 
			ilPaymentTrustees::_hasCouponsPermission($a_usr_id);
	}

	public static function _getVendorsForObjects($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT vendor_id FROM payment_trustees 
			WHERE trustee_id = %s
			AND perm_obj = %s ',
			array('integer', 'integer'),
			array($a_usr_id, '1'));

		while($row = $ilDB->fetchObject($res))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}

	public static function _getVendorsForStatisticsByTrusteeId($a_trustee_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT vendor_id FROM payment_trustees 
			WHERE trustee_id = %s
			AND perm_stat = %s ',
			array('integer', 'integer'),
			array($a_trustee_id, '1'));

		while($row = $ilDB->fetchObject($res))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}

	public static function _getVendorsForCouponsByTrusteeId($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT vendor_id FROM payment_trustees 
			WHERE trustee_id = %s
			AND perm_coupons = %s ',
			array('integer', 'integer'),
			array($a_usr_id, '1'));
		
		while($row = $ilDB->fetchObject($res))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}
	
	public static function _getTrusteesForCouponsByVendorId($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT trustee_id FROM payment_trustees 
			WHERE vendor_id = %s
			AND perm_coupons = %s ',
			array('integer', 'integer'), array($a_usr_id, '1'));
		
		while($row = $ilDB->fetchObject($res))
		{
			$trustees[] = $row->trustee_id;
		}

		return $trustees ? $trustees : array();
	}
	public static function _getVendorIdsByTrustee($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT vendor_id FROM payment_trustees WHERE trustee_id = %s',
			array('integer'), array($a_usr_id));
			while($row = $ilDB->fetchObject($res))
		{
			$vendors[] = $row->vendor_id;
		}
		return $vendors ? $vendors : array();
	}	
} // END class.ilPaymentTrustees
?>
