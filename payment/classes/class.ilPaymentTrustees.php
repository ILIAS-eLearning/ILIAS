<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilPaymentTrustees
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

class ilPaymentTrustees
{
	var $db = null;

	var $user_obj;
	var $trustees = array();

	/**
	* Constructor
	* @access	public
	*/
	function ilPaymentTrustees(&$user_obj)
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->user_obj =& $user_obj;

		$this->PERM_STATISTIC = 1;
		$this->PERM_OBJECT = 2;

		$this->__read();
	}
	
	function getTrustees()
	{
		return $this->trustees ? $this->trustees : array();
	}
	function getTrustee($a_usr_id)
	{
		return isset($this->trustees[$a_usr_id]) ? $this->trustees[$a_usr_id] : array();
	}
	function isTrustee($a_usr_id)
	{
		return isset($this->trustees[$a_usr_id]);
	}
	
	function toggleStatisticPermission($a_on)
	{
		$this->perm_stat = (bool) $a_on;
	}
	function toggleObjectPermission($a_on)
	{
		$this->perm_obj = (bool) $a_on;
	}
	function toggleCouponsPermission($a_on)
	{
		$this->perm_coupons = (bool) $a_on;
	}
	function setTrusteeId($a_id)
	{
		$this->trustee_id = $a_id;
	}

	function add()
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
	function modify()
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
	function delete()
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
	
	function deleteAll()
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
	function __getTrusteeId()
	{
		return $this->trustee_id;
	}
	function __getStatisticPermissionStatus()
	{
		return (int) $this->perm_stat;
	}
	function __getObjectPermissisonStatus()
	{
		return (int) $this->perm_obj;
	}
	function __getCouponsPermissisonStatus()
	{
		return (int) $this->perm_coupons;
	}
	function __read()
	{

		$this->trustees = array();

		$res = $this->db->queryf('
			SELECT * FROM payment_trustees 
			WHERE vendor_id = %s',
			array('integer'),
			array($this->user_obj->getId()));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->trustees[$row->trustee_id]['trustee_id'] = $row->trustee_id;
			$this->trustees[$row->trustee_id]['perm_stat'] = $row->perm_stat;
			$this->trustees[$row->trustee_id]['perm_obj'] = $row->perm_obj;
			$this->trustees[$row->trustee_id]['perm_coupons'] = $row->perm_coupons;
		}
	}

	// STATIC
	function _deleteTrusteesOfVendor($a_vendor_id)
	{
		global $ilDB;
		
		$statement = $ilDB->manipulateF('
			DELETE FROM payment_trustees 
			WHERE vendor_id = %s',
			array('integer'), array($a_vendor_id));
		
		return true;
	}

	function _hasStatisticPermission($a_trustee)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s',
			array('integer'), array($a_trustee));

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if((bool) $row->perm_stat)
			{
				return true;
			}
		}
		return false;
	}
	
	function _hasObjectPermission($a_trustee)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s',
			array('integer'),
			array($a_trustee));

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if((bool) $row->perm_obj)
			{
				return true;
			}
		}
		return false;
	}
	
	function _hasCouponsPermission($a_trustee)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s',
			array('integer'),
			array($a_trustee));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if((bool) $row->perm_coupons)
			{
				return true;
			}
		}
		return false;
	}
	
	function _hasStatisticPermissionByVendor($a_trustee,$a_vendor)
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

	function _hasObjectPermissionByVendor($a_trustee,$a_vendor)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_trustees 
			WHERE trustee_id = %s
			AND vendor_id = %s
			AND perm_obj = %s',
			array('integer', 'integer', 'integer' ),
			array($a_trustee, $a_vendor, '1'));
		
		return $res->numRows() ? true : false;
	}

	function _hasCouponsPermissionByVendor($a_trustee,$a_vendor)
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

	function _hasAccess($a_usr_id)
	{
		return ilPaymentTrustees::_hasStatisticPermission($a_usr_id) or 
			ilPaymentTrustees::_hasObjectPermission($a_usr_id) or 
			ilPaymentTrustees::_hasCouponsPermission($a_usr_id);
	}

	function _getVendorsForObjects($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT vendor_id FROM payment_trustees 
			WHERE trustee_id = %s
			AND perm_obj = %s ',
			array('integer', 'integer'),
			array($a_usr_id, '1'));

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}
	
	function _getVendorsForCouponsByTrusteeId($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT vendor_id FROM payment_trustees 
			WHERE trustee_id = %s
			AND perm_coupons = %s ',
			array('integer', 'integer'),
			array($a_usr_id, '1'));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}
	
	function _getTrusteesForCouponsByVendorId($a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT trustee_id FROM payment_trustees 
			WHERE vendor_id = %s
			AND perm_coupons = %s ',
			array('integer', 'integer'), array($a_usr_id, '1'));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$trustees[] = $row->trustee_id;
		}

		return $trustees ? $trustees : array();
	}

} // END class.ilPaymentTrustees
?>
