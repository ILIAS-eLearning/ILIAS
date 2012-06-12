<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Payment/classes/class.ilPaymentVendors.php';
include_once './Services/Payment/classes/class.ilPaymentTrustees.php';

/** 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* 
* @ingroup payment
*/
class ilPaymentCoupons
{
	private $db = null;
	
	private $user_obj = null;
	private $vendor_view = false;
	
	private $coupons = array();
	private $current_coupon = array();
	private $codes = array();
	private $used_codes = array();
	private $objects = array();	

	private $id = null;
	private $coupon_user = null;
	private $title = null;
	private $description = null;
	private $type = null;
	private $value = null;
	private $from = null;
	private $till = null;
	private $from_date_enabled = null;
	private $till_date_enabled = null;
	private $change_date = null;
	private $uses = null;
	private $search_title_type = null;
	private $search_title_value = null;
	private $search_type = null;
	private $search_from_day = null;
	private $search_from_month = null;
	private $search_from_year = null;
	private $search_till_day = null;
	private $search_till_month = null;
	private $search_till_year = null;
	private $search_from_enabled = null;
	private $search_till_enabled = null;

	
	
	public function __construct($user_obj, $a_vendor_view = false)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->vendor_view = $a_vendor_view;
		$this->user_obj = $user_obj;
		
		$this->COUPON_VALID = 0;
		$this->COUPON_OUT_OF_DATE = 1;
		$this->COUPON_TOO_MUCH_USED = 2;
		$this->COUPON_NOT_FOUND = 3;			
	}
	
	public function getCoupons()
	{

		$this->coupons = array();
		
		$data = array();
		$data_types = array();

		$query = 'SELECT * FROM payment_coupons WHERE 1 = 1 ';

		if ($_SESSION['pay_coupons']['from']['date']['d'] != '' &&
			$_SESSION['pay_coupons']['from']['date']['m'] != '' &&
			$_SESSION['pay_coupons']['from']['date']['y'] != '')
		{
			
			$from = date('Y-m-d',mktime(0, 0, 0, $_SESSION['pay_coupons']['from']['date']['m'], 
						   $_SESSION['pay_coupons']['from']['date']['d'], 
						   $_SESSION['pay_coupons']['from']['date']['y']));
			

			$query .= 'AND pc_from >= %s ';
			$data_types[] = 'date';
			$data[] =  $from;				
		}
		if ($_SESSION['pay_coupons']['til']['date']['d'] != '' &&
			$_SESSION['pay_coupons']['til']['date']['m'] != '' &&
			$_SESSION['pay_coupons']['til']['date']['y'] != '')
		{
			$til = date('Y-m-d',mktime(23, 59, 59, $_SESSION['pay_coupons']['til']['date']['m'], 
						  $_SESSION['pay_coupons']['til']['date']['d'], 
						  $_SESSION['pay_coupons']['til']['date']['y']));
			$query .= 'AND pc_till <= %s '; 
			$data_types[] = 'date';
			$data[] =  $til;
		}		
		
		if ($this->getSearchTitleValue() != "")
		{
			if ($this->getSearchTitleType() == 0)
			{
				$query .= " AND pc_title LIKE %s ";
				array_push($data, $this->getSearchTitleValue().'%');
				array_push($data_types, 'text');
			}
			else if ($this->getSearchTitleType() == 1)
			{
				$query .= " AND pc_title LIKE %s ";
				array_push($data, '%'.$this->getSearchTitleValue());
				array_push($data_types,'text');				
			}
		}
		
		if ($this->getSearchType() != "")
		{
			$query .= ' AND pc_type = %s';
			array_push($data, $this->getSearchType());
			array_push($data_types, 'text');			
		}

		$vendors = $this->getVendorIds();		
		if (is_array($vendors) &&
			count($vendors) > 0)
		{
			$in = 'usr_id IN (';
			$counter = 0;			
			foreach($vendors as $vendor)
			{
				array_push($data, $vendor);
				array_push($data_types, 'integer');
				
				if($counter > 0) $in .= ',';
				$in .= '%s';								
				++$counter;				
			}
			$in .= ')';
			
			$query .= ' AND '.$in;			
		}

	
		$cnt_data = count($data);
		$cnt_data_types = count($data_types);

		if($cnt_data == 0 && $cnt_data_types == 0)
		{
			$res = $this->db->query($query);
		}
		else
		{
			$res= $this->db->queryf($query, $data_types, $data);
		} 
		
		while($row = $this->db->fetchObject($res))
		{
			$this->coupons[$row->pc_pk]['pc_pk'] = $row->pc_pk;
			$this->coupons[$row->pc_pk]['usr_id'] = $row->usr_id;
			$this->coupons[$row->pc_pk]['pc_title'] = $row->pc_title;
			$this->coupons[$row->pc_pk]['pc_description'] = $row->pc_description;
			$this->coupons[$row->pc_pk]['pc_type'] = $row->pc_type;
			$this->coupons[$row->pc_pk]['pc_value'] = $row->pc_value;
			$this->coupons[$row->pc_pk]['pc_from'] = $row->pc_from;
			$this->coupons[$row->pc_pk]['pc_till'] = $row->pc_till;
			$this->coupons[$row->pc_pk]['pc_from_enabled'] = $row->pc_from_enabled;
			$this->coupons[$row->pc_pk]['pc_till_enabled'] = $row->pc_till_enabled;
			$this->coupons[$row->pc_pk]['pc_uses'] = $row->pc_uses;
			$this->coupons[$row->pc_pk]['pc_last_change_usr_id'] = $row->pc_last_change_usr_id;
			$this->coupons[$row->pc_pk]['pc_last_changed'] = $row->pc_last_changed;					
			$this->coupons[$row->pc_pk]['number_of_codes'] = count($this->getCodesByCouponId($row->pc_pk));
			$this->coupons[$row->pc_pk]['usage_of_codes'] = count($this->getUsedCouponsByCouponId($row->pc_pk));
			$this->coupons[$row->pc_pk]['objects'] = $this->getObjectsByCouponId($row->pc_pk);
		}
		
		return $this->coupons;
	}
	
	private function getVendorIds()
	{
		$vendors[] = $this->user_obj->getId();
		if (ilPaymentVendors::_isVendor($this->user_obj->getId()))
		{
			$ptObj = new ilPaymentTrustees($this->user_obj);
			
			if ($trustees = $ptObj->getTrustees())
			{
				foreach ($trustees as $trustee)
				{
					if ((bool) $trustee["perm_coupons"])
					{
						$vendors[] = $trustee["trustee_id"];
					}
				}
			}
		}		
		if ($vend = ilPaymentTrustees::_getVendorsForCouponsByTrusteeId($this->user_obj->getId()))
		{
			foreach ($vend as $v)
			{
				$vendors[] = $v;				
				if ($trustees = ilPaymentTrustees::_getTrusteesForCouponsByVendorId($v))
				{
					foreach ($trustees as $t)
					{
						$vendors[] = $t;
					}
				}
			}
		}
		
		return $vendors ? $vendors : array();
	}
	
	// Object Data
	public function setId($a_id)
	{
		return $this->id = $a_id;
	}
	public function getId()
	{
		return $this->id;
	}
	public function setCouponUser($a_user_id)
	{
		$this->coupon_user = $a_user_id;
	}	
	public function getCouponUser()
	{
		return $this->coupon_user;
	}
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}	
	public function getTitle()
	{
		return $this->title;
	}
	public function setDescription($a_description)
	{
		$this->description = $a_description;
	}	
	public function getDescription()
	{
		return $this->description;
	}
	public function setType($a_type)
	{
		$this->type = $a_type;
	}	
	public function getType()
	{
		return $this->type;
	}
	public function setValue($a_value)
	{
		$this->value = $a_value;
	}	
	public function getValue()
	{
		return $this->value;
	}
	public function setFromDate($a_from)
	{
		$this->from = $a_from;
	}	
	public function getFromDate()
	{
		return $this->from;
	}
	public function setTillDate($a_till)
	{
		$this->till = $a_till;
	}	
	public function getTillDate()
	{
		return $this->till;
	}
	public function setFromDateEnabled($a_from_date_enabled = 0)
	{
		if($a_from_date_enabled == NULL) $a_from_date_enabled = 0;		
		$this->from_date_enabled = $a_from_date_enabled;
	}	
	public function getFromDateEnabled()
	{
		return $this->from_date_enabled;
	}
	public function setTillDateEnabled($a_till_date_enabled = 0)
	{
		if($a_till_date_enabled == NULL) $a_till_date_enabled = 0;
		$this->till_date_enabled = $a_till_date_enabled;
	}	
	public function getTillDateEnabled()
	{
		return $this->till_date_enabled;
	}
	public function setChangeDate($a_date)
	{
		if($a_date == '0000-00-00 00:00:00')
			$this->change_date = NULL;
		else
			$this->change_date = $a_date;
	}	
	public function getChangeDate()
	{
		return $this->change_date;
	}
	public function setUses($a_uses)
	{
		$this->uses = $a_uses;
	}	
	public function getUses()
	{
		return $this->uses;
	}
	
	// Search Data
	public function setSearchTitleType($a_title_type)
	{
		$this->search_title_type = $a_title_type;
	}
	public function getSearchTitleType()
	{
		return $this->search_title_type;
	}
	public function setSearchTitleValue($a_title_value)
	{
		$this->search_title_value = $a_title_value;
	}
	public function getSearchTitleValue()
	{
		return $this->search_title_value;
	}
	public function setSearchType($a_type)
	{
		$this->search_type = $a_type;
	}
	public function getSearchType()
	{
		return $this->search_type;
	}
	public function setSearchFromDay($a_day)
	{
		$this->search_from_day = $a_day;
	}
	public function getSearchFromDay()
	{
		return $this->search_from_day;
	}
	public function setSearchFromMonth($a_month)
	{

		$this->search_from_month = $a_month;
	}
	public function getSearchFromMonth()
	{	
		return $this->search_from_month;
	}
	public function setSearchFromYear($a_year)
	{
		$this->search_from_year = $a_year;
	}
	public function getSearchFromYear()
	{
		return $this->search_from_year;
	}
	public function setSearchTillDay($a_day)
	{
		$this->search_till_day = $a_day;
	}
	public function getSearchTillDay()
	{
		return $this->search_till_day;
	}
	public function setSearchTillMonth($a_month)
	{
		$this->search_till_month = $a_month;
	}
	public function getSearchTillMonth()
	{
		return $this->search_till_month;
	}
	public function setSearchTillYear($a_year)
	{
		$this->search_till_year = $a_year;
	}
	public function getSearchTillYear()
	{
		return $this->search_till_year;
	}
	public function setSearchFromDateEnabled($a_from_enabled)
	{
		$this->search_from_enabled = $a_from_enabled;
	}
	public function getSearchFromDateEnabled()
	{
		return $this->search_from_enabled;
	}
	public function setSearchTillDateEnabled($a_till_enabled)
	{
		$this->search_till_enabled = $a_till_enabled;
	}
	public function getSearchTillDateEnabled()
	{
		return $this->search_till_enabled;
	}
	
	public function setCurrentCoupon($coupon = array())
	{
		$this->current_coupon = $coupon;
	}
	public function getCurrentCoupon()
	{
		return $this->current_coupon;
	}
	
	public function add()
	{
		$next_id = $this->db->nextId('payment_coupons');		
		
		$statement = $this->db->manipulateF('
			INSERT INTO payment_coupons
			(	pc_pk,
				usr_id, 
				pc_title,
				pc_description, 
				pc_type, 
				pc_value, 
				pc_from,
				pc_till, 
				pc_from_enabled,
				pc_till_enabled, 
				pc_uses,  
				pc_last_change_usr_id, 
				pc_last_changed				
			)
			VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', 
			array(	'integer', 
					'integer', 
					'text', 
					'text', 
					'text',
					'float',
					'date',
					'date',
					'integer',
					'integer',
					'integer',
					'integer',
					'timestamp'),
			array(	$next_id,
					$this->getCouponUser(),
					$this->getTitle(),
					$this->getDescription(),
					$this->getType(),
					$this->getValue(),
					$this->getFromDate(),
					$this->getTillDate(),
					$this->getFromDateEnabled(),
					$this->getTillDateEnabled(),
					$this->getUses(),
					$this->getCouponUser(),
					$this->getChangeDate()		)
		);
	
		return $next_id;
	}
						 
	public function update()
	{
		if ($this->getId())
		{
			$statement = $this->db->manipulateF('
				UPDATE payment_coupons
				SET	pc_title = %s,
					pc_description = %s,
					pc_type = %s,
					pc_value = %s,
					pc_from = %s,
					pc_till = %s,
					pc_from_enabled = %s,
					pc_till_enabled = %s,
					pc_uses = %s,
					pc_last_change_usr_id = %s,
					pc_last_changed = %s
				WHERE pc_pk = %s',
				array(	'text', 
						'text', 
						'text',
						'float',
						'date',
						'date',
						'integer',
						'integer',
						'integer',
						'integer',
						'timestamp',
						'integer'),
				array(	$this->getTitle(),
							$this->getDescription(),
							$this->getType(),
							$this->getValue(),
							$this->getFromDate(),
							$this->getTillDate(),
							$this->getFromDateEnabled(),
							$this->getTillDateEnabled(),
							$this->getUses(),	
							$this->getCouponUser(),
							$this->getChangeDate(),		
							$this->getId()
			));
				
			return true;
		}
		return false;
	}

	public function delete()
	{
		if ($this->getId())
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_coupons WHERE pc_pk = %s',
				array('integer'),
				array($this->getId()));
		
			return true;
		}
		return false;
	}
		
	public function getCouponById($a_coupon_id)
	{
		$res = $this->db->queryf('
			SELECT * FROM payment_coupons
			WHERE pc_pk = %s', array('integer'), array($a_coupon_id));
		
		while($row = $this->db->fetchObject($res))
		{			
			$this->setId($row->pc_pk);
			$this->setCouponUser($row->usr_id);
			$this->setTitle($row->pc_title);
			$this->setDescription($row->pc_description);			
			$this->setType($row->pc_type);
			$this->setValue($row->pc_value);			
			$this->setFromDate($row->pc_from);
			$this->setTillDate($row->pc_till);
			$this->setFromDateEnabled($row->pc_from_enabled);
			$this->setTillDateEnabled($row->pc_till_enabled);
			$this->setUses($row->pc_uses);			
			$this->setChangeDate(date("Y-m-h H:i:s"));			
		}	
	}
	
	public function getCouponBonus($a_item_price)
	{
		if (is_array($coupon = $this->getCurrentCoupon()))
		{
			switch ($coupon["pc_type"])
			{
				case "fix":
					return (float) $coupon["pc_value"];
				case "percent":	
					return (float) $a_item_price * ($coupon["pc_value"] / 100);					
			}
		}
		
		return 0;
	}
	
	public function getObjectsByCouponId($a_coupon_id)
	{
		$this->objects = array();

		$res = $this->db->queryf('
			SELECT * FROM payment_coupons_obj
			WHERE pco_pc_fk = %s', 
			array('integer'), 
			array($a_coupon_id));

		while($row = $this->db->fetchObject($res))
		{
			$this->objects[] = $row->ref_id;
		}		
		
		return $this->objects;		
	}
	
	public function getCodesByCouponId($a_coupon_id)
	{
		$this->codes = array();
	
		$res = $this->db->queryf('
			SELECT payment_coupons_codes.*, COUNT(pct_pcc_fk) pcc_used, pcc_pk 
		 	FROM payment_coupons_codes
		 	LEFT JOIN payment_coupons_track ON  pct_pcc_fk = pcc_pk 
		  	WHERE pcc_pc_fk = %s
		  	GROUP BY pcc_pk, payment_coupons_codes.pcc_pc_fk ,pcc_code', 
			array('integer'), 
			array($a_coupon_id));
		
		while($row = $this->db->fetchObject($res))
		{
			$this->codes[$row->pcc_pk]['pcc_pk'] = $row->pcc_pk;
			$this->codes[$row->pcc_pk]['pcc_code'] = $row->pcc_code;
			$this->codes[$row->pcc_pk]['pcc_used'] = $row->pcc_used; 
		}		
		
		return $this->codes;
	}
	
	public function getUsedCouponsByCouponId($a_coupon_id)
	{
		$this->used_codes = array();

		$res = $this->db->queryf('
			SELECT * FROM payment_coupons_track
			INNER JOIN payment_coupons_codes ON pcc_pk = pct_pcc_fk
			WHERE pcc_pc_fk = %s', 
			array('integer'),
			array($a_coupon_id));
			
		while($row = $this->db->fetchObject($res))
		{
			$this->used_codes[$row->pct_pk]['pct_pk'] = $row->pct_pk;
			$this->used_codes[$row->pct_pk]['pcc_code'] = $row->pcc_code;
			$this->used_codes[$row->pct_pk]['usr_id'] = $row->usr_id;
			$this->used_codes[$row->pct_pk]['pct_date'] = $row->pct_date;
			$this->used_codes[$row->pct_pk]['pct_ps_fk'] = $row->pct_ps_fk; 
		}
		
		return $this->used_codes;
	}
	
	public function getCouponByCode($a_coupon_code)
	{
		$res = $this->db->queryf('
			SELECT * FROM payment_coupons_codes
			INNER JOIN payment_coupons ON pc_pk = pcc_pc_fk
			WHERE pcc_code = %s', 	
			array('text'),
			array($a_coupon_code));

		$coupon = array();
		
		if (is_object($row = $this->db->fetchObject($res)))
		{
			$coupon['pc_pk'] = $row->pc_pk;			
			$coupon['pc_title'] = $row->pc_title;
			$coupon['pc_description'] = $row->pc_description;
			$coupon['pc_type'] = $row->pc_type;
			$coupon['pc_value'] = $row->pc_value; 
			$coupon['pc_type'] = $row->pc_type;
			$coupon['pc_from'] = $row->pc_from;
			$coupon['pc_till'] = $row->pc_till;
			$coupon['pc_uses'] = $row->pc_uses;
			$coupon['pcc_pk'] = $row->pcc_pk;
			$coupon['pcc_code'] = $row->pcc_code;
			$coupon['objects'] = $this->getObjectsByCouponId($row->pc_pk);
		}
		
		$this->setId($coupon['pc_pk']);
		$this->setCurrentCoupon($coupon);
		
		return $coupon ? $coupon : array();
	}
	
	public function checkCouponValidity()
	{
		$coupon = $this->getCurrentCoupon();
		
		if (empty($coupon)) return $this->COUPON_NOT_FOUND;		
		
		$current_date = date("Y-m-d");
		if ($coupon["pc_from"] != "0000-00-00" && $coupon["pc_from_enabled"] == '1' &&
		    $coupon["pc_till"] != "0000-00-00" && $coupon["pc_till_enabled"] == '1'
		) 
		{
			if (! ($coupon["pc_from"] <= $current_date && $current_date <= $coupon["pc_till"]))
			{		
				return $this->COUPON_OUT_OF_DATE;
			}			
		}
		else if ($coupon["pc_from"] != "0000-00-00" && $coupon["pc_from_enabled"] == '1')
		{
			if ($coupon["pc_from"] > $current_date)
			{
				return $this->COUPON_OUT_OF_DATE;
			}
		}
		else if ($coupon["pc_till"] != "0000-00-00" && $coupon["pc_till_enabled"] == '1')
		{
			if ($coupon["pc_till"] < $current_date)
			{
				return $this->COUPON_OUT_OF_DATE;
			}
		}		
		
		if (is_numeric($coupon["pc_uses"]) && $coupon["pc_uses"] > 0)
		{
			$res = $this->db->queryf('
				SELECT COUNT(*) used_coupons
				FROM payment_coupons_track					  
				WHERE pct_pcc_fk = %s', 
				array('integer'),
				array($coupon['pcc_pk']));
			
			$row = $this->db->fetchObject($res);
			
			if ($row->used_coupons >= $coupon["pc_uses"]) return $this->COUPON_TOO_MUCH_USED;
		}
		
		return $this->COUPON_VALID;
	}
	
	public function deleteCode($a_code_id)
	{
		if ($a_code_id)
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_coupons_codes WHERE pcc_pk = %s',
				array('integer'), array($a_code_id));
			
			return true;
		}		
		return false;
	}
	
	public function deleteAllCodesByCouponId($a_coupon_id)
	{
		if ($a_coupon_id)
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_coupons_codes WHERE pcc_pc_fk = %s',
				array('integer'),array($a_coupon_id));
			
			return true;
		}		
		return false;
	}
	
	public function getCode($a_code_id)
	{
		$res = $this->db->queryf('
			SELECT * FROM payment_coupons_codes 
			WHERE pcc_pk = %s',
			array('integer'),
			array($a_code_id));
		
		$code = array();
		
		while($row = $this->db->fetchObject($res))
		{
			$code['pcc_pk'] = $row->pcc_pk;
			$code['pcc_pc_fk'] = $row->pcc_pc_fk;
			$code['pcc_code'] = $row->pcc_code;
		}		
		return $code ? $code : array();
	}
	
	public function addCode($a_code, $a_coupon_id)
	{
		if ($a_code && $a_coupon_id)
		{
			$next_id = $this->db->nextId('payment_coupons_codes');
			$statement = $this->db->manipulateF('
				INSERT INTO payment_coupons_codes
				(	pcc_pk,
					pcc_pc_fk,
					pcc_code
				)
				VALUES (%s,%s,%s)',
				array('integer','integer', 'text'),
				array($next_id, $a_coupon_id, $a_code));
			
			return $next_id;
		}
		return false;
	}	
	
	public function addCouponForBookingId($a_booking_id)
	{
		$current_coupon = $this->getCurrentCoupon();
		
		if ($a_booking_id && is_array($current_coupon))
		{	
			$statement = $this->db->manipulateF('
				INSERT INTO payment_statistic_coup 
				( 	psc_ps_fk,
					psc_pc_fk,
					psc_pcc_fk
				) VALUES(%s,%s,%s)',
				array('integer', 'integer', 'integer'),
				array($a_booking_id, $current_coupon['pc_pk'], $current_coupon['pcc_pk']));
		}
		return false;
	}
	
	public function addTracking()
	{
		$current_coupon = $this->getCurrentCoupon();
		
		if (is_array($current_coupon))
		{
			$next_id = $this->db->nextId('payment_coupons_track');
			$statement = $this->db->manipulateF('
				INSERT INTO payment_coupons_track
				(	pct_pk,
					pct_pcc_fk ,
					usr_id,
					pct_date
				)
				VALUES (%s, %s, %s, %s)',
				array('integer','integer', 'integer', 'timestamp'),
				array($next_id, $current_coupon['pcc_pk'], $this->user_obj->getId(), date("Y-m-d H:i:s")));
		
			return $next_id;
		}
		return false;
	}
	
	/** 
	 * Checks if an object is assigned to the current coupon
	 *
	 * @access	public
	 * @return	bool
	 */
	public function isObjectAssignedToCoupon($a_ref_id)
	{
		if ($a_ref_id && is_numeric($this->getId()))
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_coupons_obj
				WHERE ref_id = %s 
				AND pco_pc_fk = %s',
				array('integer', 'integer'),
				array($a_ref_id, $this->getId()));
			
			if ($res->numRows()) return true;
			
			return false;
		}
		return false;
	}
	
	/** 
	 * Assigns an object to the current coupon
	 *
	 * @access	public
	 * @return	bool
	 */
	public function assignObjectToCoupon($a_ref_id)
	{
		if ($a_ref_id && is_numeric($this->getId()))
		{
			$statement = $this->db->manipulateF('
				INSERT INTO payment_coupons_obj
				( 	pco_pc_fk,
					ref_id
				) VALUES(%s, %s)',
				array('integer', 'integer'),
				array($this->getId(), $a_ref_id));

				return true;
		}		
		return false;
	}	
	
	/** 
	 * Unassigns an object from the current coupon
	 *
	 * @access	public
	 * @return	bool
	 */
	public function unassignObjectFromCoupon($a_ref_id)
	{
		if ($a_ref_id && is_numeric($this->getId()))
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_coupons_obj
				WHERE ref_id = %s
				AND pco_pc_fk = %s',
				array('integer', 'integer'),
				array($a_ref_id, $this->getId()));

			return true;
		}		
		return false;
	}
	/**
	 *  deletes all coupon relevant data and tracking 
	 * 
	 * @param integer $a_pc_pk 
	 */
	public function deleteCouponByCouponId($a_pc_pk)
	{
		global $ilDB;
		
		$res =  $ilDB->queryF('
			SELECT pcc_pk 
			FROM payment_coupons_codes
			WHERE pcc_pc_fk = %s',
			array('integer'),array($a_pc_pk));			
			
		$code_ids = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$code_ids[] = $row['pcc_pk'];
		}

		$ilDB->manipulate('
			DELETE FROM payment_coupons_track
			WHERE '. $ilDB->in('pct_pcc_fk', $code_ids, false, 'integer'));

		$ilDB->manipulate('
			DELETE FROM payment_statistic_coup
			WHERE '.$ilDB->in('psc_pcc_fk', $code_ids, false, 'integer'));
		
		$ilDB->manipulateF('
			DELETE FROM payment_coupons
			WHERE pc_pk = %s',
			array('integer'),array($a_pc_pk));				
			
		$ilDB->manipulateF('
			DELETE FROM payment_coupons_obj 
			WHERE pco_pc_fk = %s',
			array('integer'),array($a_pc_pk));			

		$ilDB->manipulateF('
			DELETE FROM payment_coupons_codes
			WHERE pcc_pk = %s',
			array('integer'),array($a_pc_pk));				
		
	}
	
	public static function _lookupTitle($a_coupon_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('SELECT pc_title FROM payment_coupons WHERE pc_pk = %s',
				array('integer'), array($a_coupon_id));
				
		$row = $ilDB->fetchAssoc($res);
		return $row['pc_title'];
	}
}
?>
