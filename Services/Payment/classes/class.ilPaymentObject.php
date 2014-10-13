<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPaymentObject
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilPaymentObject.php 22181 2009-10-23 15:51:44Z jgoedvad $
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
	private $vat_id = 0;
	private $subtype = null;
	private $is_special = 0;


	public function __construct($user_obj, $a_pobject_id = null)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->user_obj = $user_obj;

		$this->STATUS_NOT_BUYABLE = 0;
		$this->STATUS_BUYABLE = 1;
		$this->STATUS_EXPIRES = 2;

		$this->PAY_METHOD_NOT_SPECIFIED = 0;

		$this->pobject_id = $a_pobject_id;

		include_once './Services/Payment/classes/class.ilPayMethods.php';
		$pmObj = new ilPayMethods();
		$tmp = $pmObj->readAll();
		
		foreach($tmp as $pm)
		{
			$tmp = strtoupper($pm['pm_title']);
			$this->PAY_METHOD_.$tmp = $pm['pm_id'];		
		}
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
	public function getVatId()
	{
		return $this->vat_id;
	}
	public function setVatId($a_vat_id)
	{
		$this->vat_id = $a_vat_id;
	}

	public function setSubtype($a_subtype)
	{
		$this->subtype = $a_subtype;
	}

	public function getSubtype()
	{
		return $this->subtype;
	}
	public function setSpecial($a_special)
	{
		$this->is_special = $a_special;
	}
	public function getSpecial()
	{
		return $this->is_special;
	}

	function getVat($a_amount = 0, $type = 'CALCULATION')
	{		
		$oVAT = new ilShopVats($this->getVatId());
		switch($type)
		{
			case 'CALCULATION':
				$val = (float)($a_amount - (round(($a_amount / (1 + ($oVAT->getRate() / 100))) * 100) / 100));
				return number_format((float)$val,'2','.','');
			default:
				$val = (float)($a_amount - (round(($a_amount / (1 + ($oVAT->getRate() / 100))) * 100) / 100));
				$val = ilShopUtils::_formatFloat($val);
				return $val; 
		}
	}
	
	public function add()
	{	
		$next_id = $this->db->nextId('payment_objects');

		$this->db->insert('payment_objects',array(
			'pobject_id'	=> array('integer', $next_id),
			'ref_id'		=> array('integer', $this->getRefId()),
			'status'		=> array('integer', $this->getStatus()),
			'pay_method'	=> array('integer', $this->getPayMethod()),
			'vendor_id'		=> array('integer', $this->getVendorId()),
			'pt_topic_fk'	=> array('integer', $this->getTopicId()),
			'vat_id'		=> array('integer', $this->getVatId()),
			'subtype'		=> array('text', $this->getSubtype()),
			'is_special'	=> array('integer', $this->getSpecial())
		));
		return $next_id;
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
			$this->db->update('payment_objects',array(
				'ref_id'		=> array('integer', $this->getRefId()),
				'status'		=> array('integer', $this->getStatus()),
				'pay_method'	=> array('integer', $this->getPayMethod()),
				'vendor_id'		=> array('integer', $this->getVendorId()),
				'pt_topic_fk'	=> array('integer', $this->getTopicId()),
				'vat_id'		=> array('integer', $this->getVatId()),
				'subtype'		=> array('text', $this->getSubtype()),
				'is_special'	=> array('integer', $this->getSpecial())
			),
			array('pobject_id'	=> array('integer', $this->getPobjectId())));

			return true;
		}
		else
		return false;
	}
	// STATIC
	public static function _lookupPobjectId($a_ref_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_objects
			WHERE ref_id = %s',
			array('integer'),
			array($a_ref_id));
		
		while($row = $ilDB->fetchAssoc($res))
		{
			return $row['pobject_id'];
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

	public static function _getCountObjectsByPayMethod($a_id)
	{
		global $ilDB;

			
		$result = $ilDB->queryf('SELECT COUNT(pay_method) pm FROM payment_objects WHERE pay_method = %s',
				 	array('integer'), array($a_id));

		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (int)$row->pm;
		}
		
		return 0;
	}

	public static function _getObjectsData($a_user_id)
	{
		global $ilDB;

		// get all vendors user is assigned to
		include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
		include_once './Services/Payment/classes/class.ilPaymentVendors.php';

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
		
		
		while($row = $ilDB->fetchObject($res))
		{
			$objects[$row->pobject_id]['pobject_id'] = $row->pobject_id;
			$objects[$row->pobject_id]['ref_id'] = $row->ref_id;
			$objects[$row->pobject_id]['status'] = $row->status;
			$objects[$row->pobject_id]['pay_method'] = $row->pay_method;
			$objects[$row->pobject_id]['vendor_id'] = $row->vendor_id;
			$objects[$row->pobject_id]['topic_id'] = $row->pt_topic_fk;
			$objects[$row->pobject_id]['vat_id'] = $row->vat_id;
			$objects[$row->pobject_id]['subtype'] = $row->subtype;
			$objects[$row->pobject_id]['is_special'] = $row->is_special;
		}
		return $objects ? $objects : array();
	}
	
	public static function _getAllObjectsData()
	{
		global $ilDB;
		
		$data_types = array();
		$data_values = array();
		
		$query = 'SELECT * FROM payment_objects ';
		
		if ($_SESSION['pay_objects']['title_value'] != '')
		{
			$query .= ', object_reference obr, object_data od ';
		}
		
		if ($_SESSION['pay_objects']['vendor'] != '')
		{
			$query .= ', usr_data ud ';
		}
		
		$query .= ' WHERE 1 = 1 ';		
		
		if ($_SESSION['pay_objects']['title_value'])
		{			
			$query .= ' AND obr.ref_id = payment_objects.ref_id AND od.obj_id = obr.obj_id ';
			
			$search_string = '';
			
			$title_search = explode(' ', trim($_SESSION['pay_objects']['title_value']));
			for ($i = 0; $i < count($title_search); $i++)
			{
				$title_search[$i] = trim($title_search[$i]);
				
				if ($title_search[$i] != '')
				{
					//$search_string .= " od.title LIKE ".$ilDB->quote("%".$title_search[$i]."%")."  ";
					$search_string .= ' od.title LIKE %s '; // ".$ilDB->quote("%".$title_search[$i]."%")."  ";
					array_push($data_types, 'text');
					array_push($data_values,'%'.$title_search[$i].'%');
					
					switch ($_SESSION['pay_objects']['title_type'])
					{
						case 'or' :
								if ($i < count($title_search) - 1) 
								{
									$search_string .= ' OR ';
								}
								break;
						case 'and' :
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
		
		if ($_SESSION['pay_objects']['vendor'] != '')
		{
			$query .= ' AND ud.usr_id = payment_objects.vendor_id AND login = %s';
			array_push($data_types, 'text');
			array_push($data_values, $_SESSION['pay_objects']['vendor']);
		}

		if($_SESSION['pay_objects']['pay_method'] > 0)
		{
			$query .= ' AND pay_method = %s';
			array_push($data_types, 'integer');
			array_push($data_values, $_SESSION['pay_objects']['pay_method']);
		}		

		$res = $ilDB->queryf($query, $data_types, $data_values);
		$objects = array();
		while($row = $ilDB->fetchObject($res))
		{
			$objects[$row->pobject_id]['pobject_id'] = $row->pobject_id;
			$objects[$row->pobject_id]['ref_id'] = $row->ref_id;
			$objects[$row->pobject_id]['status'] = $row->status;
			$objects[$row->pobject_id]['pay_method'] = $row->pay_method;
			$objects[$row->pobject_id]['vendor_id'] = $row->vendor_id;
			$objects[$row->pobject_id]['topic_id'] = $row->pt_topic_fk;
			$objects[$row->pobject_id]['vat_id'] = $row->vat_id;
			$objects[$row->pobject_id]['subtype'] = $row->subtype;
			$objects[$row->pobject_id]['is_special'] = $row->is_special;
		}
		return $objects ? $objects : array();
	}

	public static function _getObjectData($a_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_objects
			WHERE pobject_id = %s',
			array('integer'), array($a_id));
			
		if (is_object($res))
		{
			return $ilDB->fetchAssoc($res);

		}

		return false;
	}

	public static function _isPurchasable($a_ref_id, $a_vendor_id = 0, $a_check_trustee = false, $a_check_status = false)
	{
		global $ilDB;

		// In the moment it's not possible to sell one object twice
	
		$data = array();
		$data_types = array();

	
		$query = 'SELECT * FROM payment_objects WHERE ref_id = %s ';
		$data_types[] = 'integer';
		$data[]= $a_ref_id;	
		
		// check if object is buyable !!
		if($a_check_status)
		{
			$query .= 'AND status > %s ';
			$data_types[] = 'integer';
			$data[] = 0;
		}	
		
		if ($a_vendor_id > 0)
		{
			$query .= 'AND vendor_id = %s'; 
			$data_types[] = 'integer';
			$data[] = $a_vendor_id;
			
			if($a_check_trustee)
			{
				include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
				include_once './Services/Payment/classes/class.ilPaymentVendors.php';
 
				$vendors = ilPaymentTrustees::_getVendorsForObjects($a_vendor_id);
				
				// @todo $a_user_id not defined?!?!
				if(ilPaymentVendors::_isVendor($a_user_id))
				{
					$vendors[] = $a_user_id;
				}
 
				if(is_array($vendors) && count($vendors))
				{ 
					$query  .= ' OR '.$ilDB->in('vendor_id', $vendors, false, 'integer');
				}                
			}
		}
		
		$res = $ilDB->queryf($query, $data_types, $data);
		$rows = $ilDB->numRows($res);

		return $rows ? true : false;
		
	}
	
	// base method to check access for a specific object
	public static function _hasAccess($a_ref_id, $a_transaction = 0, $a_subtype ='')
	{
		include_once './Services/Payment/classes/class.ilPaymentBookings.php';
		include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
		include_once './Services/Payment/classes/class.ilPaymentVendors.php';

		global $rbacsystem,$ilDB, $ilUser;

		// check write access
		if($rbacsystem->checkAccess('write', $a_ref_id))
		{
			return true;
		}
		// check if user is vendor/trustee
		$vendors_of_trustee = ilPaymentTrustees::_getVendorIdsByTrustee($ilUser->getId());
		
		if(!IS_PAYMENT_ENABLED)
		{
			return true;
		}

		if($a_subtype)
		{
			$result = $ilDB->queryf('
				SELECT * FROM payment_objects
				WHERE ref_id = %s AND (status = %s or status = %s)
				AND subtype = %s',
				array('integer', 'integer', 'integer','text'),
				array($a_ref_id, '1', '2',$a_subtype));
		}
		else
		{
			$result = $ilDB->queryf('
				SELECT * FROM payment_objects
				WHERE ref_id = %s
				AND (status = %s OR status = %s)
				OR (vendor_id = %s)',
				array('integer', 'integer', 'integer','integer'),
				array($a_ref_id, '1', '2',$ilUser->getId()));
		}
		while($row = $ilDB->fetchObject($result))
		{
			if($row->vendor_id == $ilUser->getId() || in_array($row->vendor_id, $vendors_of_trustee))
			{
				return true;
			}
			else
			if(!ilPaymentBookings::_hasAccess($row->pobject_id, '', $a_transaction))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		return false;
	}

	public static function _isBuyable($a_ref_id, $a_subtype = '')
	{
		global $ilDB;

		
		if(!IS_PAYMENT_ENABLED)
		{
			return false;
		}

		if($a_subtype)
		{
			$result = $ilDB->queryf('
				SELECT * FROM payment_objects
				WHERE ref_id = %s
				AND (status = %s or status = %s)
				AND subtype = %s',
				array('integer', 'integer', 'integer','text'),
				array($a_ref_id, '1', '2',$a_subtype));
		}
		else
		{
			$result = $ilDB->queryf('
				SELECT * FROM payment_objects
				WHERE ref_id = %s AND (status = %s or status = %s)',
				array('integer', 'integer', 'integer'),
				array($a_ref_id, '1', '2'));
		}

		while($row = $ilDB->fetchObject($result))
		{
			return true;
		}
		return false;
	}
	
	// checks if this new object already exists in payment_objects
	public static function _isNewObject($a_ref_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM payment_objects WHERE ref_id = %s',
		array('integer'), array($a_ref_id));
		
		$rows = $ilDB->numRows($res);
		
		return $rows ? false : true;
	}
	

	/**
	 * this function should be used by all buyable repository objects !!
	 *
	 * @param <integer> $a_ref_id
	 * @param <text> $a_purchasetype  ('buy', 'demo')
	 * @return <bool>
	 */
	public static function _requiresPurchaseToAccess($a_ref_id, $a_purchasetype = '')
	{
		/* Check:
		 * - User has no Access -> return false
		 * - User has Access but there are also Extension-Prices available -> User is able to buy the Object AGAIN to extend duration
		 */

		if( (self::_isBuyable($a_ref_id) && !self::_hasAccess($a_ref_id))
		  || (self::_isBuyable($a_ref_id) && self::_hasAccess($a_ref_id)
				&& self::_hasExtensions($a_ref_id) && isset($a_purchasetype)))
		{
			return true;
		}
		else return false;
	}
	
	public static function _isInCart($a_ref_id)
	{
		global $ilDB, $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$result = $ilDB->queryf('
				SELECT psc_id FROM payment_objects po, payment_shopping_cart psc
				WHERE ref_id = %s 
				AND session_id = %s 
				AND po.pobject_id = psc.pobject_id',
		        array('integer', 'text'),
		        array($a_ref_id, session_id()));
		}
		else
		{
			$result = $ilDB->queryf('
				SELECT psc_id FROM payment_objects po, payment_shopping_cart psc
				WHERE ref_id = %s 
				AND customer_id = %s 
				AND po.pobject_id = psc.pobject_id',
		        array('integer', 'integer'),
		        array($a_ref_id, $ilUser->getId()));
		}
		while($row = $ilDB->fetchObject($result))
		{
			return true;
		}
		
		return false;
	}

	public static function _hasExtensions($a_ref_id)
	{
		global $ilDB;

		// user has already access to current object
		// object contain buyable extension-prices

		$res = $ilDB->queryf('
			SELECT * FROM payment_prices pp, payment_objects po
			WHERE po.ref_id = %s
			AND pp.pobject_id = po.pobject_id
			AND pp.extension = %s',
			array('integer', 'integer'), array($a_ref_id, 1));

		if($row = $ilDB->numRows($res))
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
			
			while($row = $this->db->fetchObject($result))
			{
				$this->setRefId($row->ref_id);
				$this->setStatus($row->status);
				$this->setPayMethod($row->pay_method);
				$this->setVendorId($row->vendor_id);
				$this->setTopicId($row->pt_topic_fk);
				$this->setVatId($row->vat_id);
				$this->setSubtype($row->subtype);
				$this->setSpecial($row->is_special);
				
				return true;
			}
		}
		
		return false;
	}

	public static function _checkExcSubtype($a_ref_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM payment_objects WHERE
			ref_id = %s', array('integer'), array($a_ref_id));
		$subtypes = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$subtypes[] = $row['subtype'];
		}
		return $subtypes;
	}

	public static function getAllBuyableObjects()
	{
		global $ilDB;


		$res = $ilDB->queryF('SELECT ref_id FROM payment_objects WHERE status = %s',
				array('integer'), array(1));

		$obj = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$obj[] = $row['ref_id'];
		}
		return $obj;
	}

	public static function _getSpecialObjects()
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM payment_objects
			WHERE is_special = %s AND status = %s
			ORDER BY pt_topic_fk',
			array('integer', 'integer'), array(1,1));

		$obj = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$obj[] = $row;
		}
		return $obj;
	}

	/**
	 * @param null|integer $topic_id
	 * @return array
	 */
	public static function _getTopicsObjects($topic_id = NULL)
	{
		global $ilDB;

		if($topic_id === NULL)
		{
			$res = $ilDB->queryF('
				SELECT * FROM payment_objects
				WHERE status = %s
				ORDER BY pt_topic_fk', array('integer'), array(1));
		}
		else
		{
			$res = $ilDB->queryF('
				SELECT * FROM payment_objects
				WHERE pt_topic_fk = %s AND status = %s', 
				array('integer', 'integer'), array((int)$topic_id, 1));
		}	
		$obj = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$obj[] = $row;
		}
		return $obj;
	}
	
	public static function _getContainerObjects($a_ref_id)
	{
		global $ilDB, $tree;

		$filter = array( "lm", "sahs", "htlm",'file', 'exc', 'tst', 'icrs','crs','crsr','rcrs', 'glo');
		$objects = $tree->getChildsByTypeFilter($a_ref_id, $filter);

		$counter = 0;
		$obj_res = array();
		foreach($objects as $object)
		{
			if(self::_isBuyable($object['ref_id']))
			{
				$res = $ilDB->queryF('SELECT * FROM payment_objects
					WHERE ref_id = %s AND status = %s
					ORDER BY pt_topic_fk',
					array('integer', 'integer'), array($object['ref_id'],1));

				while($row = $ilDB->fetchAssoc($res))
				{
					$obj_res[$counter] = $row;
					#$obj_res[$counter]['ref_id'] = 	$object['ref_id'];
					$obj_res[$counter]['obj_id'] = 	$object['obj_id'];
					$obj_res[$counter]['type'] = 	$object['type'];
					$obj_res[$counter]['title'] = 	$object['title'];
					$obj_res[$counter]['child'] = 	$object['child'];
					$obj_res[$counter]['description'] = $object['description'];
				}
			}

			$counter++;
		}
		return $obj_res;
	}

} // END class.ilPaymentObject
?>
