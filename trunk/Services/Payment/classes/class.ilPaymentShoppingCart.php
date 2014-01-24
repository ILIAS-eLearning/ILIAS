<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPaymentShoppingCart
*
* @author Stefan Meyer
* @version $Id: class.ilPaymentShoppingCart.php 22184 2009-10-23 16:24:27Z jgoedvad $
*
* @package core
*/
include_once './Services/Payment/classes/class.ilPaymentPrices.php';
include_once './Services/Payment/classes/class.ilPaymentObject.php';
include_once './Services/Payment/classes/class.ilPaymentCoupons.php';

class ilPaymentShoppingCart
{
	/*
	 * id of vendor, admin or trustee
	 */
	public $user_obj = null;
	public $db = null;
	
	public $coupon_obj = null;

	public $sc_entries = array();
	public $session_id =null;
	
	public $pobject_id = null;
	public $price_id = null;
	public $total_amount = null;
	public $vat_id = null;
	public $vat_rate = 0;
	
	

	public function __construct($user_obj)
	{
		global $ilDB;

		$this->user_obj = $user_obj;
		$this->db = $ilDB;
		$this->session_id = session_id();
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);

		$this->__deleteDoubleEntries();
		$this->__read();
	}

	public function setSessionId($a_session_id)
	{
		$this->session_id = $a_session_id;
	}
	
	public function getSessionId()
	{
		return $this->session_id;
	}
	
	public function setPobjectId($a_pobject_id)
	{
		$this->pobject_id = $a_pobject_id;
	}
	public function getPobjectId()
	{
		return $this->pobject_id;
	}
	public function setPriceId($a_price_id)
	{
		$this->price_id = $a_price_id;
	}
	public function getPriceId()
	{
		return $this->price_id;
	}

	public function getEntries($a_pay_method = 0)
	{
		if ($a_pay_method == 0)
		{
			return $this->sc_entries ? $this->sc_entries : array();
		}
		else
		{
			$tmp_entries = array();
			foreach($this->sc_entries as $entry)
			{
			if ($entry['pay_method'] == $a_pay_method)
				{
					$tmp_entries[$entry['psc_id']] = $entry;
				}
			}
			return $tmp_entries;
		}
	}
	public function setTotalAmount($a_total_amount)
	{
		$this->total_amount = $a_total_amount;
	}
	public function getTotalAmount()
	{
		return $this->total_amount;
	}

	public static function _assignObjectsToUserId($a_user_id)
	{
		global  $ilDB;

		$session_id = session_id();

		$ilDB->update('payment_shopping_cart',
		array('customer_id' => array('integer', (int) $a_user_id)),
		array('session_id'=> array('text', $session_id)));

		return true;
	}
	
	public static function _migrateShoppingcart($a_old_sessid, $a_new_sessid)
	{
		global $ilDB;

		$ilDB->update('payment_shopping_cart',
		array('session_id' => array('text', $a_new_sessid)),
		array('session_id'=> array('text', $a_old_sessid)));

	}
	
	public function isInShoppingCart($a_pobject_id)
	{ 
		global $ilUser;
		
		$session_id = $this->getSessionId();
	
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_shopping_cart
				WHERE session_id = %s
				AND pobject_id = %s',
				array('text', 'integer'),
				array($session_id, $a_pobject_id));
		}
		else
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_shopping_cart
				WHERE customer_id = %s
				AND pobject_id = %s',
				array('integer', 'integer'),
				array($this->user_obj->getId(), $a_pobject_id));
		}
		return $res->numRows() ? true : false;
	}

	public function getEntry($a_pobject_id)
	{
		global $ilUser;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_shopping_cart
				WHERE session_id = %s
				AND pobject_id = %s',
				array('text', 'integer'),
				array($this->getSessionId(), $a_pobject_id));
		}
		else
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_shopping_cart
				WHERE customer_id = %s
				AND pobject_id = %s',
				array('integer', 'integer'),
				array($this->user_obj->getId(), $a_pobject_id));
		}
		if (is_object($res))
		{
			return $this->db->fetchAssoc($res);
		}
		return array();
	}

	public function add()
	{
		global $ilUser;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			// Delete old entries for same pobject_id
			$statement = $this->db->manipulateF('
				DELETE FROM payment_shopping_cart
				WHERE session_id = %s
				AND pobject_id = %s',
				array('integer', 'integer'),
				array($this->getSessionId(), $this->getPobjectId()));
			
		}
		else
		{
			// Delete old entries for same pobject_id
			$statement = $this->db->manipulateF('
				DELETE FROM payment_shopping_cart
				WHERE customer_id = %s
				AND pobject_id = %s',
				array('integer', 'integer'),
				array($this->user_obj->getId(), $this->getPobjectId()));
		}
		
		$next_id = $this->db->nextId('payment_shopping_cart');

		$this->db->insert('payment_shopping_cart',
		array('psc_id'	 => array('integer', $next_id),
			'customer_id'=> array('integer', $this->user_obj->getId()),
			'pobject_id' => array('integer', $this->getPobjectId()),
			'price_id'   => array('integer', $this->getPriceId()),
			'session_id' => array('text', $this->getSessionId()))
		);

		$this->__read();

		return true;
	}

	public function update($a_psc_id)
	{
		global $ilUser;
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$this->db->update('payment_shopping_cart',
			array('pobject_id'=> array('integer', $this->getPobjectId()),
				  'price_id'  => array('integer', $this->getPriceId()),
				  'session_id'=> array('text', $this->getSessionId())),
			array('psc_id'	  => array('integer', (int)$a_psc_id)));

		}
		else
		{
			$this->db->update('payment_shopping_cart',
			array('customer_id'	=> array('integer', $this->user_obj->getId()),
				  'pobject_id'	=> array('integer', $this->getPobjectId()),
				  'price_id'	=> array('integer', $this->getPriceId()),
				  'session_id'	=> array('text', $this->getSessionId())),
			array( 'psc_id'		=> array('integer', (int)$a_psc_id)));
		}
		$this->__read();

		return true;
	}
			
	public function delete($a_psc_id)
	{
		$statement = $this->db->manipulateF('
			DELETE FROM payment_shopping_cart
			WHERE psc_id = %s',
			array('integer'), array($a_psc_id));

		$this->__read();
	}

	public function emptyShoppingCart()
	{
		global $ilUser;
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_shopping_cart
				WHERE session_id = %s',
				array('text'), array($this->getSessionId())
				);
		}
		else
		{
			$statement = $this->db->manipulateF('
				DELETE FROM payment_shopping_cart
				WHERE customer_id = %s',
				array('integer'), array($this->user_obj->getId())
			);
		}
		$this->__read();
	
		return true;
	}

	// STATIC
	public static function _hasEntries($a_user_id)
	{
		global $ilDB, $ilUser;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$res = $ilDB->queryf('
			SELECT * FROM payment_shopping_cart
			WHERE session_id = %s',
			array('text'), array(self::getSessionId()));		
		}
		else
		{
			$res = $ilDB->queryf('
			SELECT * FROM payment_shopping_cart
			WHERE customer_id = %s',
			array('integer'), array($a_user_id));		
		}
		
		return $ilDB->numRows($res) ? true : false;
	}


	// PRIVATE
	private function __deleteDoubleEntries()
	{
		global $ilUser;
		if(ANONYMOUS_USER_ID != $ilUser->getId())
		{
			$res = $this->db->queryf('
			SELECT pobject_id, count(pobject_id) count FROM payment_shopping_cart
			WHERE customer_id = %s
			GROUP BY pobject_id',
			array('integer'), array($this->user_obj->getId()));

			while($row = $this->db->fetchAssoc($res))
			{
				if($row['count'] > 1)
				{
					$this->db->setLimit(1);
					$this->db->manipulateF('
					DELETE FROM payment_shopping_cart
					WHERE customer_id = %s
					AND pobject_id = %s',
						array('integer','integer'),
						array($this->user_obj->getId(),$row['pobject_id']));
				}
			}
		}
	}


	private function __read()
	{
		global $ilUser;		
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';

		$this->sc_entries = array();

		if(isset($_SESSION['shop_user_id']) 
		&& $_SESSION['shop_user_id'] != ANONYMOUS_USER_ID
		|| $this->user_obj->getId() != ANONYMOUS_USER_ID)
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_shopping_cart
				WHERE customer_id = %s',
				array('integer'), array($this->user_obj->getId()));
		}
		else if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$res = $this->db->queryf('
				SELECT * FROM payment_shopping_cart
				WHERE session_id = %s',
				array('text'), array($this->getSessionId()));			
		}
			
		while($row = $this->db->fetchObject($res))
		{
			$this->sc_entries[$row->psc_id]["psc_id"] = $row->psc_id;
			$this->sc_entries[$row->psc_id]["customer_id"] = $row->customer_id; 
			$this->sc_entries[$row->psc_id]["pobject_id"] = $row->pobject_id; 
			$this->sc_entries[$row->psc_id]["price_id"] = $row->price_id;
			$this->sc_entries[$row->psc_id]['session_id'] = $row->session_id;
		}

		// Delete all entries with not valid prices or pay_method
		unset($prices);
		$prices = array();
		foreach($this->sc_entries as $entry)
		{
			// check if price_id exists for pobject
			if(!ilPaymentPrices::_priceExists($entry['price_id'],$entry['pobject_id']))
			{
				$this->delete($entry['psc_id']);
				return false;
			}
			
			// check pay method
			$tmp_pobj = new ilPaymentObject($this->user_obj, $entry['pobject_id']);
			
			$pay_method = $tmp_pobj->getPayMethod();
			if($pay_method == $tmp_pobj->PAY_METHOD_NOT_SPECIFIED)
			{
				$this->delete($entry['psc_id']);
				return false;
			}
			
			// if payment is expired
			if($tmp_pobj->getStatus() == $tmp_pobj->STATUS_EXPIRES)
			{ 
				$this->delete($entry['psc_id']);

				return false;
			}


			$this->sc_entries[$entry['psc_id']]['pay_method'] = $pay_method;

			$prices[] = array(
				'id' => $entry['price_id'],
				'pay_method' => $pay_method
			);
			unset($tmp_pobj);
		}

		// set total amount
		$this->setTotalAmount(ilPaymentPrices::_getTotalAmount($prices ? $prices : array()));
		
		$this->setPobjectId($entry['pobject_id']);
		
		return true;
	}
		
	function getShoppingCart($a_pay_method = 0)
	{

		if(!count($items = $this->getEntries($a_pay_method)))
		{
			return 0;
		}

		$counter = 0;
		$f_result = array();
		foreach($items as $item)
		{
			$tmp_pobject = new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj = ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId(), false);

			$f_result[$counter]["psc_id"] = $item['psc_id'];
			$f_result[$counter]["pobject_id"] = $item['pobject_id'];
			if($tmp_obj)
			{
				$f_result[$counter]["obj_id"] = $tmp_obj->getId();
				$f_result[$counter]["type"] = $tmp_obj->getType();
				$f_result[$counter]["object_title"] = $tmp_obj->getTitle();
			}
			else
			{
				global $lng;
				$f_result[$counter]["obj_id"] = '';
				$f_result[$counter]["type"] = '';
				$f_result[$counter]["object_title"] = $lng->txt('object_deleted');
			}

			$price_data = ilPaymentPrices::_getPrice($item['price_id']);
			$price_string = ilPaymentPrices::_getPriceString($item['price_id']);

			$price = number_format($price_data['price'], 2, '.', '');

			$f_result[$counter]["price"] =  $price;
			$f_result[$counter]["price_string"] = $price_string;
			$f_result[$counter]['extension'] = $price_data['extension'];
 
     		 require_once './Services/Payment/classes/class.ilShopVats.php';
			$oVAT = new ilShopVats((int)$tmp_pobject->getVatId());						
			$f_result[$counter]['vat_rate'] = $oVAT->getRate();
			$f_result[$counter]['vat_unit'] = $tmp_pobject->getVat($price);
	
			$f_result[$counter]["duration"] = $price_data["duration"];
			$f_result[$counter]['unlimited_duration'] = $price_data['unlimited_duration'];
			
            $f_result[$counter]["price_type"] = $price_data["price_type"];
            $f_result[$counter]["duration_from"] = $price_data["duration_from"];
            $f_result[$counter]["duration_until"] = $price_data["duration_until"];
            $f_result[$counter]["description"] = $price_data["description"];
			unset($tmp_obj);
			unset($tmp_pobject);

			++$counter;
		}
		return $f_result;
	}

	function getTotalAmountValue($a_pay_method = 0)
	{
		$amount = 0.0;

		if (is_array($result = $this->getShoppingCart($a_pay_method)))
		{
			for ($i = 0; $i < count($result); $i++)
			{
				$amount += $result[$i]["price"];
			}
		}
		return (float) $amount;
	}

	function getVat($a_amount = 0, $a_pobject_id = 0)
	{	
		global $ilDB;
		
		include_once './Services/Payment/classes/class.ilShopVats.php';
		
		$res = $ilDB->queryF('
		SELECT * FROM payment_objects WHERE pobject_id = %s',
		array('integer'), array($a_pobject_id));
			
		while($row = $ilDB->fetchObject($res))
		{
			$this->vat_id = $row->vat_id;
		}
		
		$res = $ilDB->queryF('
			SELECT * FROM payment_vats WHERE vat_id = %s',
			array('integer'),array($this->vat_id));
			
		while($row = $ilDB->fetchObject($res))
		{
			$this->vat_rate = $row->vat_rate;
		}
		return (float) ($a_amount - (round(($a_amount / (1 + ($this->vat_rate / 100.0))) * 100) / 100));		
	}
	
	function clearCouponItemsSession()
	{
		if (!empty($_SESSION['coupons']))
		{													
			foreach ($_SESSION['coupons'] as $payment_type => $coupons_array)
			{
				if (is_array($coupons_array))
				{
					foreach ($coupons_array as $coupon_key => $coupon)
					{
						$_SESSION['coupons'][$payment_type][$coupon_key]['total_objects_coupon_price'] = 0.0;
						$_SESSION['coupons'][$payment_type][$coupon_key]['items'] = array();						
					}	
				}	
			}
		}
	}
	
	function calcDiscountPrices($coupons)
	{
		if (is_array($coupons))
		{
			$r_items = array();
			
			foreach ($coupons as $coupon)
			{	
				$this->coupon_obj->setId($coupon['pc_pk']);
				$this->coupon_obj->setCurrentCoupon($coupon);				

				if (is_array($coupon['items']) && $coupon['total_objects_coupon_price'] > 0)
				{					
					$bonus = ($this->coupon_obj->getCouponBonus($coupon['total_objects_coupon_price']));	

					foreach ($coupon['items'] as $item)
					{
						if (!array_key_exists($item['pobject_id'], $r_items))
						{				
							$r_items[$item['pobject_id']] = $item;
							$r_items[$item['pobject_id']]['discount_price'] = (float) $item['math_price'];
						}							
						
						$ratio = (float) $item['math_price'] / $coupon['total_objects_coupon_price'];
						$r_items[$item['pobject_id']]['discount_price'] += ($ratio * $bonus * (-1));												
					}
				}
			}

			return $r_items;
		}
		
		return array();
	}

	public static function _deleteExpiredSessionsPSC()
	{
		global $ilDB;
		
		$query = "DELETE FROM payment_shopping_cart "
			   . "WHERE psc_id IN " 
			   . "	(SELECT psc_id FROM payment_shopping_cart " 
			   . "	 LEFT JOIN usr_session ON usr_session.session_id = payment_shopping_cart.session_id "
			   . "	 WHERE customer_id = %s AND usr_session.session_id IS NULL)";
		$ilDB->manipulateF($query, array('integer'), array(ANONYMOUS_USER_ID));	
		
	}

	public static function _deleteShoppingCartEntries($a_pobject_id)
	{
		global $ilDB;

		$ilDB->manipulateF('
			DELETE FROM payment_shopping_cart 
			WHERE pobject_id = %s',
				array('integer'), array($a_pobject_id));
	}


	/**
	 * @param integer|null $a_paymethod
	 * @return bool
	 */
	public static function getShoppingcartEntries($a_paymethod = null)
	{
		global $ilUser, $ilDB;	
		
		$user_id = $ilUser->getId();
		
		if($user_id == ANONYMOUS_USER_ID)
		{
			return false;
		}
		else
		{
			if($a_paymethod != null)
			{
				$res = $ilDB->queryF('
				SELECT psc_id, ref_id, vat_rate, duration, currency, price, unlimited_duration, extension, duration_from, duration_until, description,price_type, pay_method
				FROM payment_shopping_cart psc
				LEFT JOIN payment_prices pp ON psc.price_id
				LEFT JOIN payment_objects po ON psc.pobject_id
				LEFT JOIN payment_vats pv ON po.vat_id
				WHERE customer_id = %s
				AND status = %s
				AND pay_method = %s',
				array('integer', 'integer', 'integer'), array($user_id, 1, $a_paymethod));
			}
			else
			{
				// select all entries for current user
				$res = $ilDB->queryF('
				SELECT psc_id, ref_id, vat_rate, duration, currency, price, unlimited_duration, extension, duration_from, duration_until, description, price_type, pay_method
				FROM payment_shopping_cart psc
				LEFT JOIN payment_prices pp ON psc.price_id
				LEFT JOIN payment_objects po ON psc.pobject_id
				LEFT JOIN payment_vats pv ON po.vat_id
				WHERE customer_id = %s
				AND status = %s',
				array('integer', 'integer'), array($user_id, 1));				
			}
			
			$entries = array();
			while($row = $ilDB->fetchAssoc($res))
			{
				$entries[] = $row;
			}
			return $entries;
		}
	}
}

