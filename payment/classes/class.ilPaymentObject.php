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

define ('PAY_METHOD_NOT_SPECIFIED', 0);
define ('PAY_METHOD_BILL', 1);
define ('PAY_METHOD_BMF', 2);
define ('PAY_METHOD_PAYPAL', 3);

/**
* Class ilPaymentObject
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ilPaymentObject
{
	private $db = null;
	private $user_obj = null;
	private $pobject_id = null;
	private $ref_id = null;
	private $status = null;
	private $pay_method = null;
	private $vendor_id = null;
	private $topic_id = 0;

	public function __construct($user_obj, $a_pobject_id = null)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->user_obj = $user_obj;

		$this->STATUS_NOT_BUYABLE = 0;
		$this->STATUS_BUYABLE = 1;
		$this->STATUS_EXPIRES = 2;

		$this->PAY_METHOD_NOT_SPECIFIED = PAY_METHOD_NOT_SPECIFIED;
		$this->PAY_METHOD_BILL = PAY_METHOD_BILL;
		$this->PAY_METHOD_BMF = PAY_METHOD_BMF;
		$this->PAY_METHOD_PAYPAL = PAY_METHOD_PAYPAL;
		
		$this->pobject_id = $a_pobject_id;
		$this->__read();
	}

	// SETTER GETTER	
	public function getTopicId()
	{
		return $this->topic_id;
	}
	public function setTopicId($a_topic_id)
	{
		$this->topic_id = $a_topic_id;
	}
	public function getPobjectId()
	{
		return $this->pobject_id;
	}
	public function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	public function getRefId()
	{
		return $this->ref_id;
	}
	public function setStatus($a_status)
	{
		$this->status = $a_status;
	}
	public function getStatus()
	{
		return $this->status;
	}
	public function setPayMethod($a_method)
	{
		$this->pay_method = $a_method;
	}
	public function getPayMethod()
	{
		return $this->pay_method;
	}
	public function setVendorId($a_vendor_id)
	{
		$this->vendor_id = $a_vendor_id;
	}
	public function getVendorId()
	{
		return $this->vendor_id;
	}
	
	public function add()
	{	
		$statement = $this->db->prepareManip(
			'INSERT INTO payment_objects
			 SET
			 ref_id = ?,
			 status = ?,
			 pay_method = ?,
			 vendor_id = ?,
			 pt_topic_fk = ?', 
			array('integer', 'integer', 'integer', 'integer', 'integer'));
		$data = array($this->getRefId(), 
					  $this->getStatus(),
					  $this->getPayMethod(),
					  $this->getVendorId(),
					  $this->getTopicId());
		$this->db->execute($statement, $data);
		
		return (int)$this->db->getLastInsertId();
	}
	
	public function delete()
	{
		if($this->getPobjectId())
		{
			include_once 'Services/Payment/classes/class.ilFileDataShop.php';
			$oFileData = new ilFileDataShop($this->getPobjectId());
			$oFileData->deassignFileFromPaymentObject();
			
			$statement = $this->db->prepareManip('DELETE FROM payment_objects WHERE pobject_id = ?', 
				array('integer'));
			$data = array($this->getPobjectId());
			$this->db->execute($statement, $data);			
			
			return true;
		}
		
		return false;
	}

	public function update()
	{
		if((int)$this->getPobjectId())
		{		
			$statement = $this->db->prepareManip(
				'UPDATE payment_objects
				 SET
				 ref_id = ?,
				 status = ?,
				 pay_method = ?,
				 vendor_id = ?,
				 pt_topic_fk = ?
				 WHERE pobject_id = ?', 
				array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'));
			$data = array($this->getRefId(), 
						  $this->getStatus(),
						  $this->getPayMethod(),
						  $this->getVendorId(),
						  $this->getTopicId(),
						  $this->getPobjectId());
			$this->db->execute($statement, $data);	
	
			return true;
		}
		
		return false;
	}
	// STATIC
	public function _lookupPobjectId($a_ref_id)
	{
		global $ilDB;

		$query = "SELECT * FROM payment_objects ".
			"WHERE ref_id = '".$a_ref_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->pobject_id;
		}
		return 0;
	}
	
	public static function _lookupTopicId($a_ref_id)
	{
		global $ilDB;
		
		static $cache = array();
		if(isset($cache[$a_ref_id]))
		{
			return $cache[$a_ref_id];
		}

		$statement = $ilDB->prepare('SELECT pt_topic_fk FROM payment_objects WHERE ref_id = ?',
		        	 	array('integer'));
		$result = $ilDB->execute($statement, array($a_ref_id));
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cache[$a_ref_id] = $row->pt_topic_fk;
			return (int)$cache[$a_ref_id];
		}
		
		return 0;
	}

	function _getCountObjectsByPayMethod($a_type)
	{
		global $ilDB;

		switch($a_type)
		{
			case 'pm_bill':
				$pm = 1;
				break;

			case 'pm_bmf':
				$pm = 2;
				break;

			case 'pm_paypal':
				$pm = 3;
				break;

			default:
				$pm = -1;
		}		
		
		$statement = $ilDB->prepare('SELECT COUNT(pay_method) AS pm FROM payment_objects WHERE pay_method = ?',
				 	array('integer'));
		$result = $ilDB->execute($statement, array($pm));
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (int)$row->pm;
		}
		
		return 0;
	}

	function _getObjectsData($a_user_id)
	{
		global $ilDB;

		// get all vendors user is assigned to
		include_once './payment/classes/class.ilPaymentTrustees.php';
		include_once './payment/classes/class.ilPaymentVendors.php';

		$vendors = ilPaymentTrustees::_getVendorsForObjects($a_user_id);

		if(ilPaymentVendors::_isVendor($a_user_id))
		{
			$vendors[] = $a_user_id;
		}

		if(!count($vendors))
		{
			return array();
		}
		$in = " IN ('";
		$in .= implode("','",$vendors);
		$in .= "')";

		$query = "SELECT * FROM payment_objects ".
			"WHERE vendor_id ".$in;

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objects[$row->pobject_id]['pobject_id'] = $row->pobject_id;
			$objects[$row->pobject_id]['ref_id'] = $row->ref_id;
			$objects[$row->pobject_id]['status'] = $row->status;
			$objects[$row->pobject_id]['pay_method'] = $row->pay_method;
			$objects[$row->pobject_id]['vendor_id'] = $row->vendor_id;
			$objects[$row->pobject_id]['topic_id'] = $row->pt_topic_fk;
		}
		return $objects ? $objects : array();
	}
	
	function _getAllObjectsData()
	{
		global $ilDB;

		$query = "SELECT * FROM payment_objects ";
		
		if ($_SESSION["pay_objects"]["title_value"] != "")
		{
			$query .= ", object_reference AS obr ";
			$query .= ", object_data AS od ";
		}
		
		if ($_SESSION['pay_objects']['vendor'] != "")
		{
			$query .= ", usr_data AS ud ";
		}
		
		$query .=	" WHERE 1 ";		
		
		if ($_SESSION["pay_objects"]["title_value"])
		{			
			$query .= " AND obr.ref_id = payment_objects.ref_id AND od.obj_id = obr.obj_id ";
			
			$search_string = "";
			
			$title_search = explode(" ", trim($_SESSION["pay_objects"]["title_value"]));
			for ($i = 0; $i < count($title_search); $i++)
			{
				$title_search[$i] = trim($title_search[$i]);
				
				if ($title_search[$i] != "")
				{
					$search_string .= " od.title LIKE ".$ilDB->quote("%".$title_search[$i]."%")."  ";
					
					switch ($_SESSION["pay_objects"]["title_type"])
					{
						case "or" :
								if ($i < count($title_search) - 1) $search_string .= " OR ";
								break;
						case "and" :
								if ($i < count($title_search) - 1) $search_string .= " AND ";
								break;
					}
				}
			}
			
			if ($search_string != "")
			{
				$query .= " AND (" . $search_string . ") ";
			}
		}
		
		if ($_SESSION['pay_objects']['vendor'] != "")
		{
			$query .= " AND ud.usr_id = payment_objects.vendor_id AND login = ".$ilDB->quote($_SESSION["pay_objects"]["vendor"])." ";
		}
		
			
		if ($_SESSION["pay_objects"]["pay_method"] == "1" ||
			$_SESSION["pay_objects"]["pay_method"] == "2" ||
			$_SESSION["pay_objects"]["pay_method"] == "3")
		{
			$query .= " AND pay_method = '" . $_SESSION["pay_objects"]["pay_method"] . "' ";
		}		
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objects[$row->pobject_id]['pobject_id'] = $row->pobject_id;
			$objects[$row->pobject_id]['ref_id'] = $row->ref_id;
			$objects[$row->pobject_id]['status'] = $row->status;
			$objects[$row->pobject_id]['pay_method'] = $row->pay_method;
			$objects[$row->pobject_id]['vendor_id'] = $row->vendor_id;
			$objects[$row->pobject_id]['topic_id'] = $row->pt_topic_fk;
		}
		return $objects ? $objects : array();
	}

	function _getObjectData($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM payment_objects ".
			"WHERE pobject_id = '".$a_id."'";

		$res = $ilDB->query($query);

		if (is_object($res))
		{
			return $res->fetchRow(DB_FETCHMODE_ASSOC);
		}

		return false;
	}

	function _isPurchasable($a_ref_id, $a_vendor_id = 0)
	{
		global $ilDB;

		// In the moment it's not possible to sell one object twice
		$query = "SELECT * FROM payment_objects ".
			"WHERE ref_id = '".$a_ref_id."' ";
		if ($a_vendor_id > 0)
		{
			$query .= "AND vendor_id = '".$a_vendor_id."' ";
		}
		#"AND status = '1' OR status = '3' ";
		
		$res = $ilDB->query($query);

		return $res->numRows() ? false : true;
	}

	// base method to check access for a specific object
	function _hasAccess($a_ref_id)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		global $rbacsystem,$ilDB;

		// check write access
		if($rbacsystem->checkAccess('write', $a_ref_id))
		{
			return true;
		}
		
		include_once 'payment/classes/class.ilGeneralSettings.php';
		if(!(bool)ilGeneralSettings::_getInstance()->get('shop_enabled'))
		{
			return true;
		}
		
		$query = "SELECT * FROM payment_objects ".
			"WHERE ref_id = '".$a_ref_id."' ".
			"AND (status = '1' OR status = '2')";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!ilPaymentBookings::_hasAccess($row->pobject_id))
			{
				return false;
			}
		}
		return true;
	}
	
	// base method to check access for a specific object
	function _getActivation($a_ref_id)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		global $rbacsystem,$ilDB;

		$query = "SELECT * FROM payment_objects ".
			"WHERE ref_id = '".$a_ref_id."' ".
			"AND (status = '1' OR status = '2')";

		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		return ilPaymentBookings::_getActivation($row->pobject_id);
	}
	
	public static function _isBuyable($a_ref_id)
	{
		global $ilDB;
		
		include_once 'payment/classes/class.ilGeneralSettings.php';
		if(!(bool)ilGeneralSettings::_getInstance()->get('shop_enabled'))
		{
			return false;
		}
		
		$statement = $ilDB->prepare('SELECT * FROM payment_objects
									 WHERE ref_id = ? AND (status = 1 or status = 2)',
	        	 	 	array('integer'));
		$result = $ilDB->execute($statement, array($a_ref_id));
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		
		return false;
	}
	
	public static function _isInCart($a_ref_id)
	{
		global $ilDB, $ilUser;
		
		$statement = $ilDB->prepare('SELECT psc_id
									 FROM payment_objects AS po, payment_shopping_cart AS psc
									 WHERE ref_id = ? AND customer_id = ? AND po.pobject_id = psc.pobject_id',
	        	 	 	array('integer', 'integer'));
		$result = $ilDB->execute($statement, array($a_ref_id, $ilUser->getId()));
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		
		return false;
	}

	private function __read()
	{
		if($this->getPobjectId())
		{
			$statement = $this->db->prepare('SELECT * FROM payment_objects WHERE pobject_id = ?',
		        	 	 	array('integer'));
			$result = $this->db->execute($statement, array($this->getPobjectId()));
			while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setRefId($row->ref_id);
				$this->setStatus($row->status);
				$this->setPayMethod($row->pay_method);
				$this->setVendorId($row->vendor_id);
				$this->setTopicId($row->pt_topic_fk);
				
				return true;
			}
		}
		
		return false;
	}
} // END class.ilPaymentObject
?>
