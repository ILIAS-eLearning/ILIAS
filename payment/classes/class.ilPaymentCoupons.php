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
		
		if ($this->vendor_view)
		{
			$this->read();
		}
		
		$this->COUPON_VALID = 0;
		$this->COUPON_OUT_OF_DATE = 1;
		$this->COUPON_TOO_MUCH_USED = 2;
		$this->COUPON_NOT_FOUND = 3;			
	}
	
	private function read()
	{
		$this->coupons = array();

		$query = "SELECT * FROM payment_coupons WHERE 1 ";
		
		if ($_SESSION["pay_coupons"]["from"]["day"] != "" &&
			$_SESSION["pay_coupons"]["from"]["month"] != "" &&
			$_SESSION["pay_coupons"]["from"]["year"] != "")
		{
			$from = mktime(0, 0, 0, $_SESSION["pay_coupons"]["from"]["month"], 
						   $_SESSION["pay_coupons"]["from"]["day"], $_SESSION["pay_coupons"]["from"]["year"]);						
		}
		
		if ($_SESSION["pay_coupons"]["till"]["day"] != "" &&
			$_SESSION["pay_coupons"]["till"]["month"] != "" &&
			$_SESSION["pay_coupons"]["till"]["year"] != "")
		{
			$till = mktime(23, 59, 59, $_SESSION["pay_coupons"]["till"]["month"], 
						  $_SESSION["pay_coupons"]["till"]["day"], $_SESSION["pay_coupons"]["till"]["year"]);			
		}
		
		if ($from && $till)
		{
			$query .= " AND ((pc_from != '0000-00-00' AND pc_till != '0000-00-00' AND 
					  (UNIX_TIMESTAMP(pc_from) >= " . $this->db->quote($from). " AND UNIX_TIMESTAMP(pc_till) <= " . $this->db->quote($till). "
					  OR UNIX_TIMESTAMP(pc_till) >= " . $this->db->quote($from). " AND UNIX_TIMESTAMP(pc_till) <= " . $this->db->quote($till). "
					  OR UNIX_TIMESTAMP(pc_from) >= " . $this->db->quote($from). " AND UNIX_TIMESTAMP(pc_from) <= " . $this->db->quote($till). "
					  OR UNIX_TIMESTAMP(pc_from) <= " . $this->db->quote($from). " AND UNIX_TIMESTAMP(pc_till) >= " . $this->db->quote($till). "
						))
					  OR (pc_from != '0000-00-00' AND UNIX_TIMESTAMP(pc_from) <= " . $this->db->quote($till). ")
					  OR (pc_till != '0000-00-00' AND UNIX_TIMESTAMP(pc_till) >= " . $this->db->quote($from). ")					  					 
					  )";	
		}
		else if ($from)
		{
			$query .= " AND ((pc_till != '0000-00-00' AND UNIX_TIMESTAMP(pc_till) >= " . $this->db->quote($from). ") OR (pc_from != '0000-00-00' AND pc_till = '0000-00-00')) ";
		}
		else if ($till)
		{
			$query .= " AND ((pc_from != '0000-00-00' AND UNIX_TIMESTAMP(pc_from) <= " . $this->db->quote($till). ") OR (pc_from = '0000-00-00' AND pc_till != '0000-00-00')) ";			 
		}		
		
		if ($_SESSION["pay_coupons"]["title_value"] != "")
		{
			if ($_SESSION["pay_coupons"]["title_type"] == 0)
			{
				$query .= " AND pc_title LIKE '" . $_SESSION["pay_coupons"]["title_value"] . "%' ";
			}
			else if ($_SESSION["pay_coupons"]["title_type"] == 1)
			{
				$query .= " AND pc_title LIKE '%" . $_SESSION["pay_coupons"]["title_value"] . "' ";
			}
		}
		
		if ($_SESSION["pay_coupons"]["type"] != "")
		{
			$query .= " AND pc_type = " . $this->db->quote($_SESSION["pay_coupons"]["type"]) . " ";			
		}

		$vendors = $this->getVendorIds();		
		if (is_array($vendors) &&
			count($vendors) > 0)
		{
			$in = 'usr_id IN (';
			$in .= implode(',',$vendors);
			$in .= ')';

			$query .= " AND ".$in." ";
		}

		$res = $this->db->query($query);
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
			$this->coupons[$row->pc_pk]['pc_uses'] = $row->pc_uses;
			$this->coupons[$row->pc_pk]['number_of_codes'] = count($this->getCodesByCouponId($row->pc_pk));
			$this->coupons[$row->pc_pk]['usage_of_codes'] = count($this->getUsedCouponsByCouponId($row->pc_pk));
			$this->coupons[$row->pc_pk]['objects'] = $this->getObjectsByCouponId($row->pc_pk);
		}		
	}
	
	private function getVendorIds()
	{
		if (ilPaymentVendors::_isVendor($this->user_obj->getId()))
		{
			$vendors[] = $this->user_obj->getId();
		}
		if ($vend = ilPaymentTrustees::_getVendorsForObjects($this->user_obj->getId()))
		{
			foreach ($vend as $v)
			{
				if(ilPaymentTrustees::_hasCouponsPermissionByVendor($this->user_obj->getId(), $v))
				{
					$vendors[] = $v;
				}
			}
		}
		return $vendors ? $vendors : array();
	}
	
	// SET / GET
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
	public function setUses($a_uses)
	{
		$this->uses = $a_uses;
	}	
	public function getUses()
	{
		return $this->uses;
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
		$query = sprintf("INSERT INTO payment_coupons VALUES('', ".
						 " %s, %s, %s, %s, %s, %s, %s, %s)",
						 $this->db->quote($this->getCouponUser()),
						 $this->db->quote($this->getTitle()),
						 $this->db->quote($this->getDescription()),
						 $this->db->quote($this->getType()),
						 $this->db->quote($this->getValue()),
						 $this->db->quote($this->getFromDate()),
						 $this->db->quote($this->getTillDate()),
						 $this->db->quote($this->getUses())						 
						 );

		$this->db->query($query);

		return $this->db->getLastInsertId();
	}
						 
	public function update()
	{
		if ($this->getId())
		{
			$query = "UPDATE payment_coupons  
					  SET 
					  pc_title = ".$this->db->quote($this->getTitle()).",
					  pc_description = ".$this->db->quote($this->getDescription()).",
					  pc_type = ".$this->db->quote($this->getType()).",
					  pc_value = ".$this->db->quote($this->getValue()).",
					  pc_from = ".$this->db->quote($this->getFromDate()).",
  					  pc_till = ".$this->db->quote($this->getTillDate()).",
   					  pc_uses = ".$this->db->quote($this->getUses())."
					  WHERE pc_pk = ".$this->db->quote($this->getId())." ";
			$this->db->query($query);

			return true;
		}
		return false;
	}

	public function delete()
	{
		if ($this->getId())
		{
			$query = "DELETE FROM payment_coupons WHERE pc_pk = ".$this->db->quote($this->getId())." ";

			$this->db->query($query);

			return true;
		}
		return false;
	}
	
	public function getCoupons($update = false)
	{
		if ($update) $this->read();
		
		return $this->coupons ? $this->coupons : array();
	}
	
	public function getCoupon($a_coupon_id)
	{
		$query = "SELECT * 
				  FROM payment_coupons 
				  WHERE 1 
				  AND pc_pk = ".$this->db->quote($a_coupon_id)." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$coupon['pc_pk'] = $row->pc_pk;
			$coupon['usr_id'] = $row->usr_id;
			$coupon['pc_title'] = $row->pc_title;
			$coupon['pc_type'] = $row->pc_type;
			$coupon['pc_value'] = $row->pc_value;
			$coupon['pc_from'] = $row->pc_from;
			$coupon['pc_till'] = $row->pc_till;
			$coupon['pc_uses'] = $row->pc_uses;
			$coupon['objects'] = $this->getObjectsByCouponId($row->pc_pk);
		}
		return $coupon ? $coupon : array();
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
		
		$query = "SELECT * FROM payment_coupons_objects WHERE 1 AND pco_pc_fk = ".$this->db->quote($a_coupon_id);

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->objects[] = $row->ref_id;
		}		
		
		return $this->objects;		
	}
	
	public function getCodesByCouponId($a_coupon_id)
	{
		$this->codes = array();
		
		$query = "SELECT * FROM payment_coupons_codes WHERE 1 AND pcc_pc_fk = ".$this->db->quote($a_coupon_id);

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->codes[$row->pcc_pk]['pcc_pk'] = $row->pcc_pk;
			$this->codes[$row->pcc_pk]['pcc_code'] = $row->pcc_code; 
		}		
		
		return $this->codes;
	}
	
	public function getUsedCouponsByCouponId($a_coupon_id)
	{
		$this->used_codes = array();
		
		$query = "SELECT * 
				  FROM payment_coupons_tracking
				  INNER JOIN payment_coupons_codes ON pcc_pk = pct_pcc_fk
				  WHERE 1
				  AND pcc_pc_fk = ".$this->db->quote($a_coupon_id);

		$res = $this->db->query($query);
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
		$query = "SELECT * 
				  FROM payment_coupons_codes
				  INNER JOIN payment_coupons ON pc_pk = pcc_pc_fk
				  WHERE 1
				  AND pcc_code = ".$this->db->quote($a_coupon_code). " ";

		$res = $this->db->query($query);
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
		if ($coupon["pc_from"] != "0000-00-00" && $coupon["pc_till"] != "0000-00-00")
		{
			if (! ($coupon["pc_from"] <= $current_date && $current_date <= $coupon["pc_till"]))
			{		
				return $this->COUPON_OUT_OF_DATE;
			}			
		}
		else if ($coupon["pc_from"] != "0000-00-00")
		{
			if ($coupon["pc_from"] > $current_date)
			{
				return $this->COUPON_OUT_OF_DATE;
			}
		}
		else if ($coupon["pc_till"] != "0000-00-00")
		{
			if ($coupon["pc_till"] < $current_date)
			{
				return $this->COUPON_OUT_OF_DATE;
			}
		}		
		
		if (is_numeric($coupon["pc_uses"]) && $coupon["pc_uses"] > 0)
		{
			$query = "SELECT COUNT(*) AS used_coupons
					  FROM payment_coupons_tracking					  
					  WHERE pct_pcc_fk = ".$this->db->quote($coupon["pcc_pk"])." ";

			$this->db->query($query);
			$res = $this->db->query($query);
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			
			if ($row->used_coupons >= $coupon["pc_uses"]) return $this->COUPON_TOO_MUCH_USED;
		}
		
		return $this->COUPON_VALID;
	}
	
	public function deleteCode($a_code_id)
	{
		if ($a_code_id)
		{
			$query = "DELETE FROM payment_coupons_codes WHERE pcc_pk = ".$this->db->quote($a_code_id)." ";

			$this->db->query($query);

			return true;
		}		
		return false;
	}
	
	public function deleteAllCodesByCouponId($a_coupon_id)
	{
		if ($a_coupon_id)
		{
			$query = "DELETE FROM payment_coupons_codes WHERE pcc_pc_fk = ".$this->db->quote($a_coupon_id)." ";

			$this->db->query($query);

			return true;
		}		
		return false;
	}
	
	public function getCode($a_code_id)
	{
		$query = "SELECT * 
				  FROM payment_coupons_codes 
				  WHERE 1 
				  AND pcc_pk = ".$this->db->quote($a_code_id)." ";

		$res = $this->db->query($query);
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
			$query = sprintf("INSERT INTO payment_coupons_codes VALUES('', ".
						 " %s, %s)",						 
						 $this->db->quote($a_coupon_id),
						 $this->db->quote($a_code)					 
						 );

			$this->db->query($query);

			return $this->db->getLastInsertId();
		}
		return false;
	}	
	
	public function addCouponForBookingId($a_booking_id)
	{
		$current_coupon = $this->getCurrentCoupon();
		
		if ($a_booking_id && is_array($current_coupon))
		{
			$query = sprintf("INSERT INTO payment_coupons_tracking VALUES('', ".
						 " %s, %s, %s, %s)",						 
						 $this->db->quote($current_coupon["pcc_pk"]),
						 $this->db->quote($a_booking_id),
						 $this->db->quote($this->user_obj->getId()),
						 $this->db->quote(date("Y-m-d H:i:s"))		 
						 );

			$this->db->query($query);
			
			$query = sprintf("INSERT INTO payment_statistic_coupons VALUES(%s, %s, %s)",						 
						 $this->db->quote($a_booking_id),
						 $this->db->quote($current_coupon["pc_pk"]),
						 $this->db->quote($current_coupon["pcc_pk"])				 
						 );

			$this->db->query($query);

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
			$query = "SELECT * 
					  FROM payment_coupons_objects
					  WHERE 1
					  AND ref_id = ".$this->db->quote($a_ref_id)." 
					  AND pco_pc_fk = ".$this->db->quote($this->getId())." ";

			$res = $this->db->query($query);
			
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
			$query = sprintf("INSERT INTO payment_coupons_objects VALUES(%s, %s)",
						 $this->db->quote($this->getId()),
						 $this->db->quote($a_ref_id)											 
						 );

			$this->db->query($query);
			
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
			$query = "DELETE  
					  FROM payment_coupons_objects
					  WHERE 1
					  AND ref_id = ".$this->db->quote($a_ref_id)." 
					  AND pco_pc_fk = ".$this->db->quote($this->getId())." ";

			$this->db->query($query);
			
			return true;
		}		
		return false;
	}

}
?>