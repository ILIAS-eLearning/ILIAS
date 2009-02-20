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

		$statement = $this->db->manipulateF(
			'INSERT INTO payment_objects
			 SET
			 ref_id = %s,
			 status = %s,
			 pay_method = %s,
			 vendor_id = %s,
			 pt_topic_fk = %s', 
			array('integer', 'integer', 'integer', 'integer', 'integer'),
			array($this->getRefId(), 
					  $this->getStatus(),
					  $this->getPayMethod(),
					  $this->getVendorId(),
					  $this->getTopicId()));
		
		return (int)$this->db->getLastInsertId();
	}
	
	public function delete()
	{
		if($this->getPobjectId())
		{
			include_once 'Services/Payment/classes/class.ilFileDataShop.php';
			$oFileData = new ilFileDataShop($this->getPobjectId());
			$oFileData->deassignFileFromPaymentObject();
			
			$statement = $this->db->manipulateF('DELETE FROM payment_objects WHERE pobject_id = %s', 
				array('integer'), array($this->getPobjectId()));

			
			return true;
		}
		
		return false;
	}

	public function update()
	{
		if((int)$this->getPobjectId())
		{		
			$statement = $this->db->manipulateF(
				'UPDATE payment_objects
				 SET
				 ref_id = %s,
				 status = %s,
				 pay_method = %s,
				 vendor_id = %s,
				 pt_topic_fk = %s
				 WHERE pobject_id = %s', 
				array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
				array($this->getRefId(), 
						  $this->getStatus(),
						  $this->getPayMethod(),
						  $this->getVendorId(),
						  $this->getTopicId(),
						  $this->getPobjectId()));
	
			return true;
		}
		
		return false;
	}
	// STATIC
	public function _lookupPobjectId($a_ref_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_objects
			WHERE ref_id = %s',
			array('integer'),
			array($a_ref_id));
		
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

		$result = $ilDB->queryf('SELECT pt_topic_fk FROM payment_objects WHERE ref_id = %s',
		        	 	array('integer'),array($a_ref_id));
		        	 	
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
		
		$result = $ilDB->queryf('SELECT COUNT(pay_method) pm FROM payment_objects WHERE pay_method = %s',
				 	array('integer'), array($pm));

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

		$data_types = array();
		$data_values = array();
		$cnt_vendors = count($vendors);
		
		$query = 'SELECT * FROM payment_objects WHERE vendor_id IN';

		if (is_array($vendors) &&
			$cnt_vendors > 0)
		{
			$in = '(';
			$counter = 0;			
			foreach($vendors as $vendor)
			{
				array_push($data_values, $vendor);
				array_push($data_types, 'integer');
				
				if($counter > 0) $in .= ',';
				$in .= '%s';								
				++$counter;				
			}
			$in .= ')';
			$query .= $in;
		}

		$res= $ilDB->queryf($query, $data_types, $data_values);
		
		
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
		
		$data_types = array();
		$data_values = array();
		
		$query = 'SELECT * FROM payment_objects ';
		
		if ($_SESSION["pay_objects"]["title_value"] != "")
		{
			$query .= ', object_reference obr, object_data od ';
		}
		
		if ($_SESSION['pay_objects']['vendor'] != "")
		{
			$query .= ', usr_data ud ';
		}
		
		$query .= ' WHERE 1 ';		
		
		if ($_SESSION["pay_objects"]["title_value"])
		{			
			$query .= ' AND obr.ref_id = payment_objects.ref_id AND od.obj_id = obr.obj_id ';
			
			$search_string = "";
			
			$title_search = explode(" ", trim($_SESSION["pay_objects"]["title_value"]));
			for ($i = 0; $i < count($title_search); $i++)
			{
				$title_search[$i] = trim($title_search[$i]);
				
				if ($title_search[$i] != "")
				{
					//$search_string .= " od.title LIKE ".$ilDB->quote("%".$title_search[$i]."%")."  ";
					$search_string .= ' od.title LIKE %s '; // ".$ilDB->quote("%".$title_search[$i]."%")."  ";
					array_push($data_types, 'text');
					array_push($data_values,'%'.$title_search[$i].'%');
					
					switch ($_SESSION["pay_objects"]["title_type"])
					{
						case "or" :
								if ($i < count($title_search) - 1) 
								{
									$search_string .= ' OR ';
								}
								break;
						case "and" :
								if ($i < count($title_search) - 1) 
								{
									$search_string .= ' AND ';
								}
								break;
					}
				}
			}
			
			if ($search_string != '')
			{
				$query .= ' AND (' . $search_string . ') ';
			}
		}
		
		if ($_SESSION['pay_objects']['vendor'] != "")
		{
			$query .= ' AND ud.usr_id = payment_objects.vendor_id AND login = %s';
			array_push($data_types, 'text');
			array_push($data_values, $_SESSION['pay_objects']['vendor']);
		}
		
			
		if ($_SESSION['pay_objects']['pay_method'] == "1" ||
			$_SESSION['pay_objects']['pay_method'] == "2" ||
			$_SESSION['pay_objects']['pay_method'] == "3")
		{
			$query .= ' AND pay_method = %s';
			array_push($data_types, 'integer');
			array_push($data_values, $_SESSION['pay_objects']['pay_method']);
		}		
		
		$res = $ilDB->queryf($query, $data_types, $data_values);
		
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

		$res = $ilDB->queryf('
			SELECT * FROM payment_objects
			WHERE pobject_id = %s',
			array('integer'), array($a_id));
			
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
/*		$query = "SELECT * FROM payment_objects ".
			"WHERE ref_id = '".$a_ref_id."' ";
		if ($a_vendor_id > 0)
		{
			$query .= "AND vendor_id = '".$a_vendor_id."' ";
		}
		#"AND status = '1' OR status = '3' ";
		
		$res = $ilDB->query($query);
*/
		
		$data = array();
		$data_types = array();
		
		$query = 'SELECT * FROM payment_objects WHERE ref_id = %s ';
		array_push($data_types, 'integer');
		array_push($data, $a_ref_id);	
		
		if ($a_vendor_id > 0)
		{
			$query .= 'AND vendor_id = %s'; 
			array_push($data_types, 'integer');
			array_push($data, $a_vendor_id);
		}
		
		$res = $ilDB->queryf($query, $data_types, $data);
		
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
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_objects 
			WHERE ref_id = %s
			AND (status = %s OR status = %s)',
			array('integer', 'integer', 'integer'),
			array($a_ref_id, '1', '2'));		
	
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

		$res = $ilDB->queryf('
			SELECT * FROM payment_objects 
			"WHERE ref_id = %s 
			AND (status = %s OR status = %s)', 
			array('integer', 'integer', 'integer'),
			array($a_ref_id, '1', '2'));
		
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
		
		$result = $ilDB->queryf('
			SELECT * FROM payment_objects
			WHERE ref_id = %s AND (status = %s or status = %s)',
	        array('integer', 'integer', 'integer'),
	        array($a_ref_id, '1', '2'));   
	        
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		
		return false;
	}
	
	public static function _requiresPurchaseToAccess($a_ref_id)
	{
		return (bool)(self::_isBuyable($a_ref_id) && !self::_hasAccess($a_ref_id));
	}
	
	public static function _isInCart($a_ref_id)
	{
		global $ilDB, $ilUser;
		
		$result = $ilDB->queryf('
			SELECT psc_id FROM payment_objects po, payment_shopping_cart psc
			WHERE ref_id = %s 
			AND customer_id = %s 
			AND po.pobject_id = psc.pobject_id',
	        array('integer', 'integer'),
	        array($a_ref_id, $ilUser->getId()));
	
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
			$result = $this->db->queryf('SELECT * FROM payment_objects WHERE pobject_id = %s',
		        	 	 	array('integer'), array($this->getPobjectId()));
			
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
