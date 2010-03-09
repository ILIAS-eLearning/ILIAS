<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* Class ilPaymentCurrency
* 
* @author Stefan Meyer <meyer@leifos.com>
* @author Nadia Krzywon <nkrzywon@databay.de>
* @version $Id: class.ilPaymentCurrency.php 22133 2009-10-16 08:09:11Z nkrzywon $
*
* @package ilias-core
*/



class ilPaymentCurrency
{
	 
	var $currency_id;
	var $unit;
	var $iso_code;
	var $symbol;
	var $conversion_rate;

	public function ilPaymentCurrency($a_currency_id = '')
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		$this->currency_id = $a_currency_id;
		
	}
	
	
	public function setCurrencyId($a_currency_id)
	{
		$this->currency_id = $a_currency_id;
	}
	public function getCurrencyId()
	{
		return $this->currency_id;
	}	

	public function setUnit($a_unit)
	{
		$this->unit = $a_unit;
	}
	public function getUnit()
	{
		return $this->unit;
	}
	public function setIsoCode($a_iso_code)
	{
		$this->iso_code = $a_iso_code;
	}
	public function getIsoCode()
	{
		return $this->iso_code;
	}
		
	public function setSymbol($a_symbol)
	{
		$this->symbol = $a_symbol;
	}
	public function getSymbol()
	{
		return $this->symbol;
	}
	public function setConversionRate($a_conversion_rate)
	{
		$this->conversion_rate = (float)$a_conversion_rate;
	}
	public function getConversionRate()
	{
		return $this->conversion_rate;
	}
			
	public function addCurrency()
	{
		$nextId = $this->db->nextID('payment_currencies');
		
		$this->db->manipulateF('INSERT INTO payment_currencies
		(currency_id, unit, iso_code, symbol, conversion_rate) 
		VALUES (%s,%s,%s,%s,%s)',
		array('integer', 'text','text','text','float'),
		array($nextId, $this->getUnit(), $this->getIsoCode(), $this->getSymbol(), $this->getConversionRate()));
		return true;
	}
	
	public function deleteCurrency()
	{
		$this->db->manipulateF('DELETE FROM payment_currencies WHERE currency_id = %s',
		array('integer'), array($this->getCurrencyId()));
		
	}
	public function updateCurrency()
	{
		$this->db->manipulateF('UPDATE payment_currencies 
		SET unit = %s,	
			iso_code = %s,
			symbol = %s,
			conversion_rate = %s
		WHERE currency_id = %s',
		array('text','text','text','float','integer'), 
		array($this->getUnit(), $this->getIsoCode(), $this->getSymbol(), 
			$this->getConversionRate(), $this->getCurrencyId()));
	}
	
	public static function _getAvailableCurrencies()
	{
		global $ilDB;

		$res = $ilDB->query('SELECT * FROM payment_currencies');

		
		while($row = $ilDB->fetchAssoc($res))
		{
			$currencies[$row['currency_id']] = $row;
/*			$currencies[$row->currency_id]['currency_id']		= $row->currency_id;
			$currencies[$row->currency_id]['unit']				= $row->unit;
			$currencies[$row->currency_id]['iso_code']			= $row->subunit;
			*/
		}
		return $currencies ? $currencies : array();
	}
	
	public static function _getCurrency($a_currency_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM payment_currencies WHERE currency_id = %s',
			array('integer'), array($a_currency_id));

		
		while($row = $ilDB->fetchAssoc($res))
		{
			$currencies[$row['currency_id']] = $row;
	
	/*	while($row = $ilDB->fetchObject($res))
		{
			$currencies['currency_id']		= $row->currency_id;
			$currencies['unit']				= $row->unit;
			$currencies['subunit']			= $row->subunit;*/
		}
		return $currencies;
	}
	
	public static function _getUnit($a_currency_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT unit FROM payment_currencies WHERE currency_id = %s',
			array('integer'), array($a_currency_id));
		
		while($row = $ilDB->fetchObject($res))
		{
			return $row->unit;
		}
		return false;
	}
	
	public static function _getSymbol($a_currency_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT symbol FROM payment_currencies WHERE currency_id = %s',
			array('integer'), array($a_currency_id));
		
		while($row = $ilDB->fetchObject($res))
		{
			return $row->symbol;
		}
		return false;
	}
	
	public static function _getConversionRate($a_currency_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('
			SELECT conversion_rate FROM payment_currencies WHERE currency_id = %s',
					array('integer'), array($a_currency_id));
					
		while($row = $ilDB->fetchObject($res))
		{
			return (float)$row->conversion_rate;
		}
		return false;			
	}
	public static function _getCurrencyBySymbol($a_currency_symbol)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM payment_currencies WHERE symbol = %s',
		array('text'), array($a_currency_symbol));
		$row = $ilDB->fetchAssoc($res);

		return $row;
	}
	
	public static function _getDefaultCurrency()
	{
		global $ilDB;

		$res = $ilDB->query('SELECT * FROM payment_currencies WHERE is_default = 1');
		$row = $ilDB->fetchAssoc($res);

		return $row;
	}
	
	public static function _updateIsDefault($a_currency_id, $a_is_default)
	{
		global $ilDB;
		

		if($a_is_default == 1)
		{
			$res = $ilDB->manipulateF('UPDATE payment_currencies
			SET is_default = %s,
				conversion_rate = %s 
			WHERE currency_id = %s',
			array('integer', 'integer', 'integer'), array($a_is_default,1,$a_currency_id));
			
		}
		else
		{
			$res = $ilDB->manipulateF('UPDATE payment_currencies
			SET is_default = %s WHERE currency_id = %s',
			array('integer', 'integer'), array($a_is_default,$a_currency_id));
			
		}
	}
	
	
	static public function _getDecimalSeparator()
	{ 
		global $ilUser;	

		$user_lang = $ilUser->getLanguage();
		
		// look for ISO 639-1  
		$comma_countries = array(
		'sq','es','fr','pt', 'bg','de','da','et','fo','fi','el','id','is','it',
		'hr','lv','lt','lb','mk','mo','nl','no','pl','pt','ro','ru','sv','sr',
		'sk','sl','af','ce','cs','tr','uk','hu');		

		in_array($user_lang, $comma_countries) ? $separator = ',' : $separator = '.';
		
		return $separator;
	}
	
	public static function _formatPriceToString($a_price, $a_currency_symbol = false)
	{
		if(!$a_currency_symbol)
		{
			$currency_obj = $_SESSION['payment_currency'];
			$currency_symbol = $currency_obj['symbol'];
		}
		else $currency_symbol = $a_currency_symbol;
		$separator= ilPaymentCurrency::_getDecimalSeparator();
		
		$price_string = number_format($a_price,'2',$separator,'');
		
		return $price_string . ' ' . $currency_symbol;
	}
		

}

?>