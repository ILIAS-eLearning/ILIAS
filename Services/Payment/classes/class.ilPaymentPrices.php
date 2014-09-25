<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* Class ilPaymentPrices
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilPaymentPrices.php 22133 2009-10-16 08:09:11Z nkrzywon $
*
* @package ilias-core
*/
class ilPaymentPrices
{
	
	const TYPE_DURATION_MONTH = 1;
	const TYPE_DURATION_DATE = 2;
	const TYPE_UNLIMITED_DURATION = 3;
	
	private $pobject_id;

	private $price;
	private $currency;
	private $duration = 0;
	private $unlimited_duration = 0;
	private $extension = 0;

	private $duration_from;
	private $duration_until;
	private $description;
	public $price_type = self::TYPE_DURATION_MONTH;

	private $prices = array();
	
	public function __construct($a_pobject_id = 0)
	{
		global $ilDB;

		$this->db = $ilDB;

		$this->pobject_id = $a_pobject_id;

		$this->__read();
	}

	public function setPriceType($a_price_type)
	{
		$this->price_type = $a_price_type;

		return $this;
	}

	public function getPriceType()
	{
		return $this->price_type;
	}

//
	// SET GET
	public function getPobjectId()
	{
		return $this->pobject_id;
	}

	public function getPrices()
	{
		return $this->prices ? $this->prices : array();
	}
	function getPrice($a_price_id)
	{
		return $this->prices[$a_price_id] ? $this->prices[$a_price_id] : array();
	}

	
	// STATIC
	public static function _getPrice($a_price_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM payment_prices 
			WHERE price_id = %s',
			array('integer'), array($a_price_id));
		
		while($row = $ilDB->fetchObject($res))
		{
			$price['duration'] = $row->duration;
			$price['duration_from'] = $row->duration_from;
			$price['duration_until'] = $row->duration_until;
			$price['description'] = $row->description;
			$price['unlimited_duration'] = $row->unlimited_duration;
			$price['currency'] = $row->currency;
			$price['price'] = number_format($row->price, 2, '.', '');
			$price['extension'] = $row->extension;
			$price['price_type'] = $row->price_type;
		}
	
		return count($price) ? $price : array();
	}

	public static function _countPrices($a_pobject_id)
	{
		global $ilDB;		
	
		$res = $ilDB->queryf('
			SELECT count(price_id) FROM payment_prices 
			WHERE pobject_id = %s',
			array('integer'),
			array($a_pobject_id));

		$row = $ilDB->fetchAssoc($res);

		return ($row[0]);
	}

	public static function _getPriceString($a_price_id)
	{
		$price = ilPaymentPrices::_getPrice($a_price_id);
		$gui_price = self::_getGUIPrice($price['price']);

		return $gui_price;
	}

	public static function _getGUIPrice($a_price)
	{
		global $lng;

		$system_lng = $lng->getDefaultLanguage();

		// CODES: ISO 639
		$use_comma_seperator = array('ar','bg','cs','de','da','es','et','it',
			'fr','nl','el','sr','uk','ru','ro','tr','pl','lt','pt','sq','hu');

//		$use_point_separator = array('en','ja','zh','vi');

		if(in_array($system_lng, $use_comma_seperator))
		{
			$gui_price = number_format((float)$a_price, 2, ',', '');
		}
		else
			$gui_price = number_format((float)$a_price, 2, '.', '');

		return $gui_price;
	}

	public static function _formatPriceToString($a_price)
	{
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		
		$genSet = ilPaymentSettings::_getInstance();
		$currency_unit = $genSet->get('currency_unit');

		$gui_price = self::_getGUIPrice($a_price);

		return $gui_price . ' ' . $currency_unit;

	}
	

	public static function _getPriceStringFromAmount($a_price)
	{
		include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';

		$genSet = ilPaymentSettings::_getInstance();
		$currency_unit = $genSet->get("currency_unit");

		$pr_str = '';		
		$pr_str .= number_format($a_price , 2, ",", ".");
 		
		return $pr_str . " " . $currency_unit;		
	}
	
		
	public static function _getTotalAmount($a_price_ids)
	{
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';

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
		

	public function setPrice($a_price = 0)
	{
		$this->price = preg_replace('/,/','.',$a_price);
		$this->price = (float)$a_price;
	}

	public function setCurrency($a_currency_id)
	{
		$this->currency = $a_currency_id;
	}
	public function setDuration($a_duration)
	{
		if($this->unlimited_duration == '1' && ($a_duration == '' || null)) 
		$a_duration = 0;
		
		$this->duration = (int)$a_duration;
	}
	
	public function setUnlimitedDuration($a_unlimited_duration)
	{
		if($a_unlimited_duration) 
			$this->unlimited_duration = (int)$a_unlimited_duration;
		else
			$this->unlimited_duration = 0;
	}
	

	public function setExtension($a_extension)
	{
		$this->extension = (int)$a_extension;
	}

	public function getExtension()
	{
		return $this->extension;
	}


	public function add()
	{
		$next_id = $this->db->nextId('payment_prices');
		
		$res = $this->db->insert('payment_prices', array(
				'price_id'		=> array('integer', $next_id),
				'pobject_id'	=> array('integer', $this->getPobjectId()),
				'currency'		=> array('integer', $this->__getCurrency()),
				'duration'		=> array('integer', $this->__getDuration()),
				'unlimited_duration'=> array('integer', $this->__getUnlimitedDuration()),
				'price'			=> array('float', $this->__getPrice()),
				'extension'		=> array('integer', $this->getExtension()),
				'duration_from' => array('date', $this->__getDurationFrom()),
				'duration_until' => array('date', $this->__getDurationUntil()),
				'description' => array('text', $this->__getDescription()),
				'price_type' => array('integer', $this->getPriceType())
		));
		
		$this->__read(true);
		return true;
	}
	public function update($a_price_id)
	{
		$this->db->update('payment_prices',
			array(	'pobject_id'	=> array('integer', $this->getPobjectId()),
					'currency'		=> array('integer', $this->__getCurrency()),
					'duration'		=> array('integer', $this->__getDuration()),
					'unlimited_duration'=> array('integer', $this->__getUnlimitedDuration()),
					'price'			=> array('float', $this->__getPrice()),
					'extension'		=> array('integer', $this->getExtension()),
					'duration_from' => array('date', $this->__getDurationFrom()),
					'duration_until' => array('date', $this->__getDurationUntil()),
					'description' => array('text', $this->__getDescription()),
					'price_type' => array('integer', $this->getPriceType())
					),
			array('price_id'=> array('integer', $a_price_id)));

		$this->__read(true);

		return true;
	}
	public function delete($a_price_id)
	{
		$statement = $this->db->manipulateF('
			DELETE FROM payment_prices
			WHERE price_id = %s',
			array('integer'), array($a_price_id));

		$this->__read(true);

		return true;
	}
	public function deleteAllPrices()
	{
		$statement = $this->db->manipulateF('
			DELETE FROM payment_prices
			WHERE pobject_id = %s',
			array('integer'),
			array($this->getPobjectId()));
		
		$this->__read(true);

		return true;
	}

	/** 
	* Validates a price before database manipulations
	*
	* @access	public
	* @throws	ilShopException
	*/
	function validate()
	{
		global $lng;

		include_once 'Services/Payment/exceptions/class.ilShopException.php';

		switch($this->getPriceType())
		{
			case self::TYPE_DURATION_MONTH:
				if(!preg_match('/^[1-9][0-9]{0,1}$/', $this->__getDuration()))
					throw new ilShopException($lng->txt('paya_price_not_valid'));
				break;

			case self::TYPE_DURATION_DATE:
				if(!preg_match('/^[0-9]{4,4}-[0-9]{1,2}-[0-9]{1,2}$/', $this->__getDurationFrom()))
					throw new ilShopException($lng->txt('payment_price_invalid_date_from'));

				$from_date = explode('-', $this->__getDurationFrom());
				if(!checkdate($from_date[1], $from_date[2], $from_date[0]))
	    			throw new ilShopException($lng->txt('payment_price_invalid_date_from'));

				if(!preg_match('/^[0-9]{4,4}-[0-9]{1,2}-[0-9]{1,2}$/', $this->__getDurationUntil()))
					throw new ilShopException($lng->txt('payment_price_invalid_date_until'));

				$till_date = explode('-', $this->__getDurationUntil());
				if(!checkdate($till_date[1], $till_date[2], $till_date[0]))
	    			throw new ilShopException($lng->txt('payment_price_invalid_date_until'));

	    		$from = mktime(12, 12, 12, $from_date[1], $from_date[2], $from_date[0]);
	    		$till = mktime(12, 12, 12, $till_date[1], $till_date[2], $till_date[0]);

	    		if($from >= $till)
					throw new ilShopException($lng->txt('payment_price_invalid_date_from_gt_until'));
				break;

			case self::TYPE_UNLIMITED_DURATION:
				return true;
				break;

			default:
				throw new ilShopException($lng->txt('payment_no_price_type_selected_sdf'));
				break;
		}

		if(preg_match('/[0-9]/',$this->__getPrice()))
		{
			return true;
		}
		throw new ilShopException($lng->txt('payment_price_invalid_price'));
	}

	// STATIC
	public static function _priceExists($a_price_id,$a_pobject_id)
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

	private function __getPrice()
	{
		return $this->price;
	}	
	private function __getCurrency()
	{
		/*TODO: CURRENCY  not finished yet -> return 1 as default */
		if($this->currency == null)
		$this->currency = 1;
		return $this->currency;
	}
	private function __getDuration()
	{
		return $this->duration;
	}
	private function __getUnlimitedDuration()
	{
		return $this->unlimited_duration;
	}

	private function __read($with_extension_prices = false)
	{
		$this->prices = array();

		if(!$with_extension_prices)
		{
		$res = $this->db->queryf('
			SELECT * FROM payment_prices
			WHERE pobject_id = %s
				AND extension = %s
			ORDER BY duration', 
			array('integer','integer'),
			array($this->getPobjectId(), 0));
		}
		else
		{
			// needed for administration view
			$res = $this->db->queryf('
				SELECT * FROM payment_prices
				WHERE pobject_id = %s
				ORDER BY duration',
		array('integer'),
		array($this->getPobjectId()));
		}
		
		while($row = $this->db->fetchObject($res))
		{
			$this->prices[$row->price_id]['pobject_id'] = $row->pobject_id;
			$this->prices[$row->price_id]['price_id'] = $row->price_id;
			$this->prices[$row->price_id]['currency'] = $row->currency;
			$this->prices[$row->price_id]['duration'] = $row->duration;
			$this->prices[$row->price_id]['unlimited_duration'] = $row->unlimited_duration;
			$this->prices[$row->price_id]['price'] = $row->price;
			$this->prices[$row->price_id]['extension'] = $row->extension;
			$this->prices[$row->price_id]['duration_from'] = $row->duration_from;
			$this->prices[$row->price_id]['duration_until'] = $row->duration_until;
			$this->prices[$row->price_id]['description'] = $row->description;
			$this->prices[$row->price_id]['price_type'] = $row->price_type;
		}
	}
	
	public function getExtensionPrices()
	{
		$prices = array();

		$res = $this->db->queryf('
			SELECT * FROM payment_prices
			WHERE pobject_id = %s
			AND extension = %s
			ORDER BY duration',
		array('integer','integer'),
		array($this->getPobjectId(), 1));

		while($row = $this->db->fetchObject($res))
		{
			$prices[$row->price_id]['pobject_id'] = $row->pobject_id;
			$prices[$row->price_id]['price_id'] = $row->price_id;
			$prices[$row->price_id]['currency'] = $row->currency;
			$prices[$row->price_id]['duration'] = $row->duration;
			$prices[$row->price_id]['unlimited_duration'] = $row->unlimited_duration;
			$prices[$row->price_id]['price'] = $row->price;
			$prices[$row->price_id]['extension'] = $row->extension;
			$prices[$row->price_id]['duration_from'] = $row->duration_from;
			$prices[$row->price_id]['duration_until'] = $row->duration_until;
			$prices[$row->price_id]['description'] = $row->description;
			$prices[$row->price_id]['price_type'] = $row->price_type;
		}
		return $prices;
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

	public function setDurationFrom($a_duration_from)
	{
		// $a_duration_from = "dd.mm.YYYY HH:ii:ss"
		$this->duration_from = $a_duration_from;
	}

	public function setDurationUntil($a_duration_until)
	{
		$this->duration_until = $a_duration_until;
	}

	public function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	private function __getDurationFrom()
	{
		return $this->duration_from;
	}

	private function __getDurationUntil()
	{
		return $this->duration_until;
	}
	private function __getDescription()
	{
		return $this->description;
	}
}
?>
