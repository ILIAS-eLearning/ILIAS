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
* Class ilPaymentShoppingCart
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentPrices.php';
include_once './payment/classes/class.ilPaymentObject.php';
include_once './payment/classes/class.ilPaymentCoupons.php';

class ilPaymentShoppingCart
{
	/*
	 * id of vendor, admin or trustee
	 */
	var $user_obj = null;
	var $db = null;
	
	var $coupon_obj = null;

	var $sc_entries = array();

	function ilPaymentShoppingCart(&$user_obj)
	{
		global $ilDB;

		$this->user_obj =& $user_obj;
		$this->db =& $ilDB;
		
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);

		$this->__read();
	}

	function setPobjectId($a_pobject_id)
	{
		$this->pobject_id = $a_pobject_id;
	}
	function getPobjectId()
	{
		return $this->pobject_id;
	}
	function setPriceId($a_price_id)
	{
		$this->price_id = $a_price_id;
	}
	function getPriceId()
	{
		return $this->price_id;
	}

	function getEntries($a_pay_method = 0)
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
				if ($entry["pay_method"] == $a_pay_method)
				{
					$tmp_entries[$entry["psc_id"]] = $entry;
				}
			}
			return $tmp_entries;
		}
	}
	function setTotalAmount($a_total_amount)
	{
		$this->total_amount = $a_total_amount;
	}
	function getTotalAmount()
	{
		return $this->total_amount;
	}

	function isInShoppingCart($a_pobject_id)
	{
		$statement = $this->db->prepare('
			SELECT * FROM payment_shopping_cart
			WHERE customer_id = ?
			AND pobject_id = ?',
			array('integer', 'integer')
		);

		$data = array($this->user_obj->getId(), $a_pobject_id);
		$res = $this->db->execute($statement, $data);
		
		return $res->numRows() ? true : false;
	}

	function getEntry($a_pobject_id)
	{
		$statement = $this->db->prepare('
			SELECT * FROM payment_shopping_cart
			WHERE customer_id = ?
			AND pobject_id = ?',
			array('integer', 'integer')
		);
		$data = array($this->user_obj->getId(), $a_pobject_id);
		$res = $this->db->execute($statement, $data);
		
		if (is_object($res))
		{
			return $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
	}

	function add()
	{


	
		// Delete old entries for same pobject_id
		$statement = $this->db->prepareManip('
			DELETE FROM payment_shopping_cart
			WHERE customer_id = ?
			AND pobject_id = ?',
			array('integer', 'integer')
		);
		$data = array($this->user_obj->getId(), $this->getPobjectId());
		$this->db->execute($statement, $data);

		$statement = $this->db->prepareManip('
			INSERT INTO payment_shopping_cart
			SET customer_id = ?,
				pobject_id = ?,
				price_id = ?', 
			array('integer', 'integer', 'integer')
		);
		
		$data = array($this->user_obj->getId(), $this->getPobjectId(), $this->getPriceId());
		$this->db->execute($statement, $data);
		
		$this->__read();

		return true;
	}

	function update($a_psc_id)
	{
		$statement = $this->db->prepareManip('
			UPDATE payment_shopping_cart
			SET customer_id = ?,
				pobject_id = ?,
				price_id = ?
			WHERE psc_id = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array(	$this->user_obj->getId(), 
						$this->getPobjectId(),
						$this->getPriceId(),
						$a_psc_id
		);
		
		$this->db->execute($statement, $data);
		
		$this->__read();

		return true;
	}
			
	function delete($a_psc_id)
	{
		$statement = $this->db->prepareManip('
			DELETE FROM payment_shopping_cart
			WHERE psc_id = ?',
			array('integer')
		);	

		$data = array($a_psc_id);
		$this->db->execute($statement, $data);
		
		$this->__read();
	}

	function emptyShoppingCart()
	{
		$statement = $this->db->prepareManip('
			DELETE FROM payment_shopping_cart
			WHERE customer_id = ?',
			array('integer')
		);	

		$data = array($this->user_obj->getId());
		
		$this->db->execute($statement, $data);
		$this->__read();

		return true;
	}

	// STATIC
	function _hasEntries($a_user_id)
	{
		global $ilDB;

		$statement = $ilDB->prepare('
			SELECT * FROM payment_shopping_cart
			WHERE customer_id = ?',
			array('integer')
		);		
		$data = array($a_user_id);
		$res = $ilDB->execute($statement, $data);
		return $res->numRows() ? true : false;
	}


	// PRIVATE
	function __read()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';

		$this->sc_entries = array();

		$statement = $this->db->prepare('
			SELECT * FROM payment_shopping_cart
			WHERE customer_id = ?',
			array('integer')
		);		
		$data = array($this->user_obj->getId());
		$res = $this->db->execute($statement, $data);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->sc_entries[$row->psc_id]["psc_id"] = $row->psc_id;
			$this->sc_entries[$row->psc_id]["customer_id"] = $row->customer_id; 
			$this->sc_entries[$row->psc_id]["pobject_id"] = $row->pobject_id; 
			$this->sc_entries[$row->psc_id]["price_id"] = $row->price_id;
		}

		// Delete all entries with not valid prices or pay_method
		unset($prices);
		foreach($this->sc_entries as $entry)
		{
			// check if price_id exists for pobject
			if(!ilPaymentPrices::_priceExists($entry['price_id'],$entry['pobject_id']))
			{
				$this->delete($entry['psc_id']);
				return false;
			}
			
			// check pay method
			$tmp_pobj =& new ilPaymentObject($this->user_obj,$entry['pobject_id']);
			
			$pay_method = $tmp_pobj->getPayMethod();
/*			if(($pay_method = $tmp_pobj->getPayMethod()) == $tmp_pobj->PAY_METHOD_BILL)
			{
				$this->delete($entry['psc_id']);
				return false;
			}
*/
			// if payment is expired
			if($tmp_pobj->getStatus() == $tmp_pobj->STATUS_EXPIRES)
			{ 
				$this->delete($entry['psc_id']);

				return false;
			}


			$this->sc_entries[$entry["psc_id"]]["pay_method"] = $pay_method;

			$prices[] = array(
				"id" => $entry['price_id'],
				"pay_method" => $pay_method
			);
			unset($tmp_pobj);
		}

		// set total amount
		$this->setTotalAmount(ilPaymentPrices::_getTotalAmount($prices ? $prices : array()));
		
		return true;
	}
		
	function getShoppingCart($a_pay_method = 0)
	{
		if(!count($items = $this->getEntries($a_pay_method)))
		{
			return 0;
		}

		$counter = 0;
		foreach($items as $item)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId());

			
			$f_result[$counter]["psc_id"] = $item['psc_id'];
			$f_result[$counter]["pobject_id"] = $item['pobject_id'];
			$f_result[$counter]["obj_id"] = $tmp_obj->getId();
			$f_result[$counter]["typ"] = $tmp_obj->getType();
			$f_result[$counter]["buchungstext"] = $tmp_obj->getTitle();

			$price_data = ilPaymentPrices::_getPrice($item['price_id']);
			$price_string = ilPaymentPrices::_getPriceString($item['price_id']);

			$price = ((int) $price_data["unit_value"]) . "." . sprintf("%02d", ((int) $price_data["sub_unit_value"]));

			$f_result[$counter]["betrag"] = (float) $price;
			$f_result[$counter]["betrag_string"] = $price_string;
			$f_result[$counter]["dauer"] = $price_data["duration"];

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
				$amount += $result[$i]["betrag"];
			}
		}

		return (float) $amount;
	}

	function getVat($a_amount = 0)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		return (float) ($a_amount - (round(($a_amount / (1 + ($genSet->get("vat_rate") / 100.0))) * 100) / 100));
	}
	
	function clearCouponItemsSession()
	{
		if (!empty($_SESSION["coupons"]))
		{													
			foreach ($_SESSION["coupons"] as $payment_type => $coupons_array)
			{
				if (is_array($coupons_array))
				{
					foreach ($coupons_array as $coupon_key => $coupon)
					{
						$_SESSION["coupons"][$payment_type][$coupon_key]["total_objects_coupon_price"] = 0.0;
						$_SESSION["coupons"][$payment_type][$coupon_key]["items"] = array();						
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
				$this->coupon_obj->setId($coupon["pc_pk"]);
				$this->coupon_obj->setCurrentCoupon($coupon);				
				
				if (is_array($coupon["items"]) && $coupon["total_objects_coupon_price"] > 0)
				{					
					$bonus = ($this->coupon_obj->getCouponBonus($coupon["total_objects_coupon_price"]));	
					
					foreach ($coupon["items"] as $item)
					{
						if (!array_key_exists($item["pobject_id"], $r_items))
						{
							$r_items[$item["pobject_id"]] = $item;
							$r_items[$item["pobject_id"]]["discount_price"] = (float) $item["math_price"];							
						}							
						
						$ratio = (float) $item["math_price"] / $coupon["total_objects_coupon_price"];
						$r_items[$item["pobject_id"]]["discount_price"] += ($ratio * $bonus * (-1));												
					}
				}
			}

			return $r_items;
		}
		
		return array();
	}
}
?>