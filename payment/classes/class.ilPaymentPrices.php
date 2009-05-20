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
* Class ilPaymentPrices
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ilPaymentPrices
{
	var $ilDB;

	var $pobject_id;

	var $price;
	var $currency;
	var $duration;
	var $unlimited_duration = 0;


	private $prices = array();
	
	function ilPaymentPrices($a_pobject_id = 0)
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->pobject_id = $a_pobject_id;

		$this->__read();
	}

	// SET GET
	function getPobjectId()
	{
		return $this->pobject_id;
	}

	function getPrices()
	{
		return $this->prices ? $this->prices : array();
	}
	function getPrice($a_price_id)
	{
		return $this->prices[$a_price_id] ? $this->prices[$a_price_id] : array();
	}

	
	// STATIC
	function _getPrice($a_price_id)
	{
		global $ilDB, $ilSettings;
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_prices 
			WHERE price_id = %s',
			array('integer'), array($a_price_id));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$price['duration'] = $row->duration;
			$price['unlimited_duration'] = $row->unlimited_duration;
			$price['currency'] = $row->currency;
			$price['price'] = $row->price;

		}	
		return count($price) ? $price : array();
	}

	function _countPrices($a_pobject_id)
	{
		global $ilDB;		
	
		$res = $ilDB->queryf('
			SELECT count(price_id) FROM payment_prices 
			WHERE pobject_id = %s',
			array('integer'),
			array($a_pobject_id));
				
		$row = $res->fetchRow(DB_FETCHMODE_ARRAY);

		return ($row[0]);
	}

	function _getPriceString($a_price_id)
	{
		include_once './payment/classes/class.ilPaymentCurrency.php';
		
		global $lng;
		
		$price = ilPaymentPrices::_getPrice($a_price_id);
		

		return self::_formatPriceToString($price['price']);	
		
	}
	
	
//	public static function _formatPriceToString($unit_value, $subunit_value)
	public static function _formatPriceToString($a_price)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';
		
		$genSet = new ilGeneralSettings();
		$currency_unit = $genSet->get('currency_unit');

		return $a_price . ' ' . $currency_unit;
	}
	

	function _getPriceStringFromAmount($a_price)
	{
		include_once './payment/classes/class.ilPaymentCurrency.php';
		include_once './payment/classes/class.ilGeneralSettings.php';

		global $lng;

		$genSet = new ilGeneralSettings();
		$currency_unit = $genSet->get("currency_unit");

		$pr_str = '';		

		$pr_str = number_format($a_price , 2, ",", ".");
		return $pr_str . " " . $currency_unit;		
	}
	
		
	function _getTotalAmount($a_price_ids)
	{

		include_once './payment/classes/class.ilPaymentPrices.php';
#		include_once './payment/classes/class.ilPaymentCurrency.php';
		include_once './payment/classes/class.ilGeneralSettings.php';

		global $ilDB,$lng;

		$genSet = new ilGeneralSettings();
		$currency_unit = $genSet->get("currency_unit");

		$amount = array();

		if (is_array($a_price_ids))
		{
			for ($i = 0; $i < count($a_price_ids); $i++)
			{
				$price_data = ilPaymentPrices::_getPrice($a_price_ids[$i]["id"]);

				$price = (float) $price_data["price"];
				$amount[$a_price_ids[$i]["pay_method"]] += (float) $price;
			}
		}

		return $amount;
	}
		

/*	function setUnitValue($a_value = 0)
	{
		// substitute leading zeros with ''
		$this->unit_value = preg_replace('/^0+/','',$a_value);
	}
	function setSubUnitValue($a_value = 0)
	{
		$this->sub_unit_value = $a_value;
	}
*/	
	function setPrice($a_price = 0)
	{
		$this->price = preg_replace('/^0+/','',$a_price);

		$this->price = $a_price;
	}

	function setCurrency($a_currency_id)
	{
		$this->currency = $a_currency_id;
	}
	function setDuration($a_duration)
	{
		if($this->unlimited_duration == '1' && ($a_duration == '' || null)) 
		$a_duration = 0;
		
		$this->duration = (int)$a_duration;
	}
	
	function setUnlimitedDuration($a_unlimited_duration)
	{
		if($a_unlimited_duration) 
			$this->unlimited_duration = (int)$a_unlimited_duration;
		else
			$this->unlimited_duration = 0;
	}
	
	function add()
	{
		$next_id = $this->db->nextId('payment_prices');
		
		$res = $this->db->manipulateF('
			INSERT INTO payment_prices 
			(	price_id,
				pobject_id,
				currency,
				duration,
				unlimited_duration,
				price
				)
			VALUES (%s, %s, %s, %s, %s, %s)',

			array('integer','integer', 'integer', 'integer', 'integer', 'float'),
			array(	$next_id,
					$this->getPobjectId(),
					$this->__getCurrency(),
					$this->__getDuration(),
					$this->__getUnlimitedDuration(),
					$this->__getPrice()
		));
		
		$this->__read();
		
		return true;
	}
	function update($a_price_id)
	{
		$res = $this->db->manipulateF('
			UPDATE payment_prices SET
			currency = %s,
			duration = %s,
			unlimited_duration = %s,
			price = %s			
			WHERE price_id = %s',

			array('integer', 'integer','integer', 'float', 'integer'),
			array(	$this->__getCurrency(),
					$this->__getDuration(),
					$this->__getUnlimitedDuration(),
					$this->__getPrice(),
					$a_price_id
		));

		$this->__read();

		return true;
	}
	function delete($a_price_id)
	{
		$statement = $this->db->manipulateF('
			DELETE FROM payment_prices
			WHERE price_id = %s',
			array('integer'), array($a_price_id));

		$this->__read();

		return true;
	}
	function deleteAllPrices()
	{
		$statement = $this->db->manipulateF('
			DELETE FROM payment_prices
			WHERE pobject_id = %s',
			array('integer'),
			array($this->getPobjectId()));
		
		$this->__read();

		return true;
	}

	function validate()
	{	
		
		$duration_valid = false;
		$price_valid = false; 
		
		if(preg_match('/^(([1-9][0-9]{0,1})|[0])?$/',$this->__getDuration())	
		|| ((int)$this->__getDuration() == 0 && $this->__getUnlimitedDuration() == 1))
		{
			$duration_valid = true;
		}

		if(preg_match('/[0-9]/',$this->__getPrice()))
		{
			
			$price_valid = true;
		}
			
	if($duration_valid == true && $price_valid == true)
	{
		return true;
		
	}

	else return false;
	
	}
	// STATIC
	function _priceExists($a_price_id,$a_pobject_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_prices
			WHERE price_id = %s
			AND pobject_id = %s',
			array('integer', 'integer'),
			array($a_price_id, $a_pobject_id));
		
		return $res->numRows() ? true : false;
	}
				  
	// PRIVATE

	function __getPrice()
	{
		return $this->price;
	}	
	function __getCurrency()
	{
		return $this->currency;
	}
	function __getDuration()
	{
		return $this->duration;
	}
	function __getUnlimitedDuration()
	{
		return $this->unlimited_duration;
	}

	function __read()
	{
		$this->prices = array();

		$res = $this->db->queryf('
			SELECT * FROM payment_prices
			WHERE pobject_id = %s
			ORDER BY duration', 
		array('integer'),
		array($this->getPobjectId()));
		
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->prices[$row->price_id]['pobject_id'] = $row->pobject_id;
			$this->prices[$row->price_id]['price_id'] = $row->price_id;
			$this->prices[$row->price_id]['currency'] = $row->currency;
			$this->prices[$row->price_id]['duration'] = $row->duration;
			$this->prices[$row->price_id]['unlimited_duration'] = $row->unlimited_duration;
			$this->prices[$row->price_id]['price'] = $row->price;
		}
	}
	
	public function getNumberOfPrices()
	{
		return count($this->prices);
	}
	
	public function getLowestPrice()
	{				
		$lowest_price_id = 0;
		$lowest_price = 0;

		foreach ($this->prices as $price_id => $data)
		{
			$current_price = $data['price'];

			if($lowest_price  == 0|| 
			   $lowest_price > (float)$current_price)
			{
				$lowest_price = (float)$current_price;
				$lowest_price_id = $price_id;
			}
		}
		
		return is_array($this->prices[$lowest_price_id]) ? $this->prices[$lowest_price_id] : array();
	}
}
?>
