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

class ilPaymentShoppingCart
{
	/*
	 * id of vendor, admin or trustee
	 */
	var $user_obj = null;
	var $db = null;

	var $sc_entries = array();

	function ilPaymentShoppingCart(&$user_obj)
	{
		global $ilDB;

		$this->user_obj =& $user_obj;
		$this->db =& $ilDB;

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

	function getEntries()
	{
		return $this->sc_entries ? $this->sc_entries : array();
	}

	function add()
	{
		$query = "INSERT INTO payment_shopping_cart ".
			"SET customer_id = '".$this->user_obj->getId()."', ".
			"pobject_id = '".$this->getPobjectId()."', ".
			"price_id = '".$this->getPriceId()."'";

		$this->db->query($query);

		$this->__read();

		return true;
	}

	function update($a_psc_id)
	{
		$query = "UPDATE payment_shopping_cart ".
			"SET customer_id = '".$this->user_obj->getId()."',' ".
			"pobject_id = '".$this->getPobjectId()."',' ".
			"price_id = '".$this->getPriceId()."' ".
			"WHERE psc_id = '".$a_psc_id."'";

		$this->db->query($query);

		$this->__read();

		return true;
	}
			



	function delete($a_psc_id)
	{
		$query = "DELETE FROM payment_shopping_cart ".
			"WHERE psc_id = '".$a_psc_id."'";

		$this->db->query($query);

		$this->__read();
	}


	// STATIC
	function _hasEntries($a_user_id)
	{
		global $ilDB;

		$query = "SELECT * FROM payment_shopping_cart ".
			"WHERE customer_id = '".$a_user_id."'";

		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}


	// PRIVATE
	function __read()
	{
		$this->sc_entries = array();

		$query = "SELECT * FROM payment_shopping_cart ".
			"WHERE customer_id = '".$this->user_obj->getId()."'";
		
		$res = $this->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->sc_entries[$row->psc_id]["psc_id"] = $row->psc_id;
			$this->sc_entries[$row->psc_id]["customer_id"] = $row->customer_id; 
			$this->sc_entries[$row->psc_id]["pobject_id"] = $row->pobject_id; 
			$this->sc_entries[$row->psc_id]["price_id"] = $row->price_id;
		}

		// Delete all entries with not valid prices or pay_method
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
			if($tmp_pobj->getPayMethod() == $tmp_pobj->PAY_METHOD_BILL)
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
			unset($tmp_pobj);
		}
		return true;
	}
		
}
?>