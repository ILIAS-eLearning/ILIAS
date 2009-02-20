<?php
include_once './payment/classes/class.ilPaymentVendors.php';
include_once './payment/classes/class.ilPaymentTrustees.php';

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

	public function ilPaymentCoupons(&$user_obj, $a_vendor_view = false)
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->vendor_view = $a_vendor_view;
		$this->user_obj =& $user_obj;
		
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

		$query = 'SELECT * FROM payment_coupons WHERE 1 ';
		
		
		if ($this->getSearchFromDay() != "" &&
			$this->getSearchFromMonth() != "" &&
			$this->getSearchFromYear() != "" &&
			$this->getSearchFromDateEnabled()
		)
		{
			$from = mktime(0, 0, 0, $this->getSearchFromMonth(), $this->getSearchFromDay(), $this->getSearchFromYear());						
		}
		
		if ($this->getSearchTillDay() != "" &&
			$this->getSearchTillMonth() != "" &&
			$this->getSearchTillYear() != "" &&
			$this->getSearchTillDateEnabled()
		)
		{		
			$till = mktime(23, 59, 59, $this->getSearchTillMonth(), $this->getSearchTillDay(), $this->getSearchTillYear());			
		}

		if ($from && $till)
		{
			$query .= ' 
				AND ((	pc_from != %s
						AND pc_till != %s
						AND pc_from_enabled = %s 
						AND pc_till_enabled = %s 
						AND UNIX_TIMESTAMP(pc_from) >= %s 
						AND UNIX_TIMESTAMP(pc_till) <= %s
						
						OR UNIX_TIMESTAMP(pc_till) >= %s
						AND UNIX_TIMESTAMP(pc_till) <= %s
						
						OR UNIX_TIMESTAMP(pc_from) >= %s
						AND UNIX_TIMESTAMP(pc_from) <= %s
		  				
						OR UNIX_TIMESTAMP(pc_from) <= %s
		  				AND UNIX_TIMESTAMP(pc_till) >= %s
					))
		  		OR (pc_from != %s AND pc_from_enabled = %s AND UNIX_TIMESTAMP(pc_from) <= %s)
		  		OR (pc_till != %s AND pc_till_enabled = %s AND UNIX_TIMESTAMP(pc_till) >= %s)					  					 
			)';
			array_push($data, '0000-00-00','0000-00-00', '1', '1', $from, $till, 
						$from, $till, $from, $till, $from, $till,
						'0000-00-00', '1', $till,
						'0000-00-00', '1', $from); 
			array_push($data_types, 'date', 'date', 'integer', 'integer', 'date', 
						'date', 'date', 'date', 'date', 'date', 'date', 'date',
						'date', 'integer', 'date',
						'date', 'integer', 'date');
		}
		else if ($from)
		{	
			$query .= ' AND ((pc_till != %s AND pc_till_enabled = %s AND UNIX_TIMESTAMP(pc_till) >= %s) 
						OR (pc_from != %s AND pc_till = %s)) ';
			array_push($data, '0000-00-00', '1', $from,
						'0000-00-00', '0000-00-00');
			array_push($data_types, 'date', 'integer', 'date',
						'date', 'date');
		}
		else if ($till)
		{
			$query .= ' AND ((pc_from != %s AND pc_from_enabled = %s AND UNIX_TIMESTAMP(pc_from) <= %s) 
						OR (pc_from = %s AND pc_till != %s)) ';			 
			array_push($data, '0000-00-00', '1', $from,
						'0000-00-00', '0000-00-00');
			array_push($data_types, 'date', 'integer', 'date',
						'date', 'date');		
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
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
		$this->from_date_enabled = $a_from_date_enabled;
	}	
	public function getFromDateEnabled()
	{
		return $this->from_date_enabled;
	}
	public function setTillDateEnabled($a_till_date_enabled = 0)
	{
		$this->till_date_enabled = $a_till_date_enabled;
	}	
	public function getTillDateEnabled()
	{
		return $this->till_date_enabled;
	}
	public function setChangeDate($a_date)
	{
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

		$statement = $this->db->manipulateF('
			INSERT INTO payment_coupons
			SET usr_id = %s,
				pc_title = %s,
				pc_description = %s,
				pc_type = %s,
				pc_value = %s,
				pc_from = %s,
				pc_till = %s,
				pc_from_enabled = %s,
				pc_till_enabled = %s,
				pc_uses = %s,
				pc_last_change_usr_id = %s,
				pc_last_changed = %s', 
			array(	'integer', 
					'text', 
					'text', 
					'text',
					'text', 
					'decimal',
					'date',
					'date',
					'integer',
					'integer',
					'integer',
					'integer',
					'datetime'),
			array(	$this->getCouponUser(),
						$this->getTitle(),
						$this->getDescription(),
						$this->getType(),
						$this->getValue(),
						$this->getFromDate(),
						$this->getTillDate(),
						$this->getFromDateEnabled(),
						$this->getTillDateEnabled(),
						$this->getUses(),
						'',
						'')
		);
	
		return $this->db->getLastInsertId();
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
						'decimal',
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
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->objects[] = $row->ref_id;
		}		
		
		return $this->objects;		
	}
	
	public function getCodesByCouponId($a_coupon_id)
	{
		$this->codes = array();
	
		$res = $this->db->queryf('
			SELECT payment_coupons_codes.*, COUNT(pct_pcc_fk) pcc_used 
		 	FROM payment_coupons_codes
		 	LEFT JOIN payment_coupons_track ON  pct_pcc_fk = pcc_pk 
		  	WHERE 1 AND pcc_pc_fk = %s
		  	GROUP BY pcc_pk', 
			array('integer'), 
			array($a_coupon_id));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
			WHERE 1
			AND pcc_pc_fk = %s', 
			array('integer'),
			array($a_coupon_id));
			
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
			WHERE 1
			AND pcc_code = %s', 	
			array('text'),
			array($a_coupon_code));

				
		if (is_object($row = $res->fetchRow(DB_FETCHMODE_OBJECT)))
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
			
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			
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
			WHERE 1 
			AND pcc_pk = %s',
			array('integer'),
			array($a_code_id));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
			$statement = $this->db->manipulateF('
				INSERT INTO payment_coupons_codes
				SET pcc_pc_fk = %s,
					pcc_code = %s',
				array('integer', 'text'),array($a_coupon_id, $a_code));
			
			return $this->db->getLastInsertId();
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
				SET psc_ps_fk = %s,
					psc_pc_fk = %s,
					psc_pcc_fk = %s',
				array('integer', 'integer', 'integer'),
				array($a_booking_id, $current_coupon['pc_pk'], $current_coupon['pcc_pk']));
			
			return $this->db->getLastInsertId();
		}
		return false;
	}
	
	public function addTracking()
	{
		$current_coupon = $this->getCurrentCoupon();
		
		if (is_array($current_coupon))
		{
			$statement = $this->db->manipulateF('
				INSERT INTO payment_coupons_track
				SET pct_pcc_fk = %s,
					usr_id = %s,
					pct_date = %s',
				array('integer', 'integer', 'timestamp'),
				array($current_coupon['pcc_pk'], $this->user_obj->getId(), date("Y-m-d H:i:s")));
		
			return $this->db->getLastInsertId();
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
				WHERE 1
				AND ref_id = %s 
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
				SET pco_pc_fk = %s,
					ref_id = %s',
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
				WHERE 1
				AND ref_id = %s
				AND pco_pc_fk = %s',
				array('integer', 'integer'),
				array($a_ref_id, $this->getId()));

			return true;
		}		
		return false;
	}
}
?>