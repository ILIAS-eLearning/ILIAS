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
		$query = "INSERT INTO payment_trustees ".
			"SET vendor_id = '".$this->user_obj->getId()."', ".
			"trustee_id = '".$this->__getTrusteeId()."', ".
			"perm_stat = '".$this->__getStatisticPermissionStatus()."', ".
			"perm_coupons = '".$this->__getCouponsPermissisonStatus()."', ".			
			"perm_obj = '".$this->__getObjectPermissisonStatus()."'";
		
		$this->db->query($query);
		$this->__read();

		return true;
	}
	function modify()
	{
		if(!$this->__getTrusteeId())
		{
			die("ilPaymentTrustees::modify() no id given");
		}

		$query = "UPDATE payment_trustees SET ".
			"trustee_id = '".$this->__getTrusteeId()."', ".
			"perm_stat = '".$this->__getStatisticPermissionStatus()."', ".
			"perm_obj = '".$this->__getObjectPermissisonStatus()."', ".
			"perm_coupons = '".$this->__getCouponsPermissisonStatus()."' ".
			"WHERE vendor_id = '".$this->user_obj->getId()."' ".
			"AND trustee_id = '".$this->__getTrusteeId()."'";

		$this->db->query($query);
		$this->__read();

		return true;
	}
	function delete()
	{
		if(!$this->__getTrusteeId())
		{
			die("ilPaymentTrustees::delete() no id given");
		}
		$query = "DELETE FROM payment_trustees ".
			"WHERE vendor_id = '".$this->user_obj->getId()."' ".
			"AND trustee_id = '".$this->__getTrusteeId()."'";

		$this->db->query($query);
		$this->__read();

		return true;
	}
	
	function deleteAll()
	{
		$query = "DELETE FROM payment_trustees ".
			"WHERE vendor_id = '".$this->user_obj->getId()."'";

		$this->db->query($query);
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

		$query = "SELECT * FROM payment_trustees ".
			"WHERE vendor_id = '".$this->user_obj->getId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
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

		$query = "DELETE FROM payment_trustees ".
			"WHERE vendor_id = '".$a_vendor_id."'";

		$ilDB->query($query);

		return true;
	}

	function _hasStatisticPermission($a_trustee)
	{
		global $ilDB;
		
		$query = "SELECT * FROM payment_trustees ".
			"WHERE trustee_id = '".$a_trustee."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
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
		
		$query = "SELECT * FROM payment_trustees ".
			"WHERE trustee_id = '".$a_trustee."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
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
		
		$query = "SELECT * FROM payment_trustees ".
			"WHERE trustee_id = '".$a_trustee."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
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

		$query = "SELECT * FROM payment_trustees ".
			"WHERE trustee_id = '".$a_trustee."' ".
			"AND vendor_id = '".$a_vendor."' ".
			"AND perm_stat = '1'";

		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	function _hasObjectPermissionByVendor($a_trustee,$a_vendor)
	{
		global $ilDB;

		$query = "SELECT * FROM payment_trustees ".
			"WHERE trustee_id = '".$a_trustee."' ".
			"AND vendor_id = '".$a_vendor."' ".
			"AND perm_obj = '1'";

		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	function _hasCouponsPermissionByVendor($a_trustee,$a_vendor)
	{
		global $ilDB;

		$query = "SELECT * FROM payment_trustees ".
			"WHERE trustee_id = '".$a_trustee."' ".
			"AND vendor_id = '".$a_vendor."' ".
			"AND perm_coupons = '1'";

		$res = $ilDB->query($query);

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

		$query = "SELECT vendor_id FROM payment_trustees ".
			"WHERE perm_obj = '1' ".
			"AND trustee_id = '".$a_usr_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}
	
	function _getVendorsForCouponsByTrusteeId($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT vendor_id FROM payment_trustees ".
			"WHERE perm_coupons = '1' ".
			"AND trustee_id = '".$a_usr_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$vendors[] = $row->vendor_id;
		}

		return $vendors ? $vendors : array();
	}
	
	function _getTrusteesForCouponsByVendorId($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT trustee_id FROM payment_trustees ".
			"WHERE perm_coupons = '1' ".
			"AND vendor_id = '".$a_usr_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$trustees[] = $row->trustee_id;
		}

		return $trustees ? $trustees : array();
	}

} // END class.ilPaymentTrustees
?>
