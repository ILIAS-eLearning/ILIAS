<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* trait for gev WBD-Success
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
trait gevWBDRequest{
	static $USR_STREET = "street";
	static $USR_HOUSE_NUMBER = "house_number";
	static $USR_MOBILE_PHONE = "mobile_phone_nr";
	static $USR_PHONE_NUMBER = "phone_nr";

	/**
	* Changes internal data to the correct format
	* i.e. PhoneNumbers or Streets/Numbers
	* 
	* @param array 		$data 		data array
	*
	* @return array 	$data
	*/
	final static function polishInternalData(&$data) {
		foreach ($data as $key => $value) {
			if($value === "-empty-") {
				$data[$key] = "";
			}
		}

		if(array_key_exists(self::$USR_STREET,$data)) {
			$street_and_number = self::extractHouseNumber($data[self::$USR_STREET]);
			$data[self::$USR_STREET] = $street_and_number["street"];
			$data[self::$USR_HOUSE_NUMBER] = $street_and_number["nr"];
		}
		
		if(array_key_exists(self::$USR_MOBILE_PHONE, $data)) {
			$data[self::$USR_MOBILE_PHONE] = self::polishPhoneNumber($data[self::$USR_MOBILE_PHONE]);
		}
		
		if(array_key_exists(self::$USR_PHONE_NUMBER, $data)) {
			$data[self::$USR_PHONE_NUMBER] = self::polishPhoneNumber($data[self::$USR_PHONE_NUMBER]);
		}

		return $data;
	}

	/**
	* Seperates Streetname and Number
	* 
	* @param string 	$streetnr	string of street
	*
	* @return array 				street and nummber seperatet
	*/
	final static function extractHouseNumber($streetnr){

		//special cases:
		//Mannheim, Q5
		$i = 0 ;
		if(strtoupper(substr(trim($streetnr), 0, 2)) == 'Q5') {
		    $i = 2;
		}
		if(strtoupper(substr(trim($streetnr), 0, 3)) == 'Q 5') {
		    $i = 3;
		}
		if(strtolower(substr(trim($streetnr), 0, 4)) == '55er') {
		    $i = 4;
		}		
		if(strtolower(substr(trim($streetnr), 0, 5)) == '55-er') {
		    $i = 5;
		}
		
		if(strtolower(substr(trim($streetnr), 0, 9)) == 'straße 4') {
		    return array(
				'street' => 'Straße 4',
				'nr' => trim(substr($streetnr, 9))
			);
		}

		//find first number in string
	    $len = strlen($streetnr);
	    $pos = False;
	    for($i; $i < $len; $i++) {
	        if(is_numeric($streetnr[$i])) {
	        	$pos = $i;
	        	break;
	        }
	    }
	    $street = trim(substr($streetnr, 0, $pos));
	    $nr = trim(substr($streetnr, $pos));
		return array(
			'street' => trim($street), 
			'nr' =>trim($nr)
		);
	}

	/**
	* Change the phone numbers to an requestet format
	* 
	* @param string 		$phone_nr 		internal phone number
	*
	* @return string 		$phone_nr		polished phone number
	*/
	final static function polishPhoneNumber($phone_nr){
		if($phone_nr == '' || preg_match(self::$TELNO_REGEXP, $phone_nr)){
			//all well, return
			
			return $phone_nr;
		}
		$nr_raw = $phone_nr;

		//strip country-code
		if(in_array(substr($nr_raw, 0, 4), array('++49', '0049'))){
			$nr_raw = substr($nr_raw, 4);
		}
		if(in_array(substr($nr_raw, 0, 3), array('+49', '049'))){
			$nr_raw = substr($nr_raw, 3);
		}
		//Lösungansatz auf dem Weg zur WBD wenn in der hist_user das "+" oder die "00" fehlen
		if(in_array(substr($nr_raw, 0, 2), array('49'))){
			$nr_raw = substr($nr_raw, 2);
		}
		$nr_raw = trim($nr_raw);

		//nr is in "raw" - w/o country code
		//it hopefully still starts with 0...
		if(substr($nr_raw, 0, 1) == '0'){
			$nr_raw = substr($nr_raw, 1);
		} else {
			//no city-code, nothing we ca don
			$phone_nr = '+49 ' .$nr_raw;
			return $phone_nr;
		}

		//is there a separation for city-code/nr?
		if( strpos($nr_raw, ' ') === false &&
			strpos($nr_raw, '/') === false &&
			strpos($nr_raw, '-') === false 
		){
			//guess city-code for mobile numbers:
			if( in_array(
					substr($nr_raw, 0, 4), 
					array(
						'1511','1512','1513','1514','1515','1516','1517','1518','1519','1510',
						'1521','1522','1523','1524','1525','1526','1527','1528','1529','1520',
						'1571','1572','1573','1574','1575','1576','1577','1578','1579','1570',
						'1591','1592','1593','1594','1595','1596','1597','1598','1599','1590'
					)
				)
			){
				$nr_raw = substr($nr_raw, 0, 4) . ' ' .substr($nr_raw, 4);
			}			
			if( in_array(
					substr($nr_raw, 0, 3), 
					array(
						'160','170','171','175',
						'162','172','173','174',
						'163','177','178',
						'176','179'
					)
				)
			){
				$nr_raw = substr($nr_raw, 0, 3) . ' ' .substr($nr_raw, 3);
			}
		}

		$phone_nr = '+49 ' .$nr_raw;
		return $phone_nr;
	}

	/**
	* creates a new gevWBDError
	*
	* @param string 		$message		error message
	*
	* @return gevWBDError
	*/
	static public function createError($reason, $error_group, $user_id, $row_id, $crs_id = 0) {
		return new gevWBDError($reason, $error_group, static::$request_type, $user_id, $row_id, $crs_id);
	}

	/**
	* gets the dictionary
	*
	* @return gevWBDDictionary
	*/
	public function getDictionary() {
		if($this->dictionary === null) {
			$this->dictionary = new gevWBDDictionary();
		}

		return $this->dictionary;
	}

	/**
	* Checks the Data for each szenario
	*
	*
	* @return bool 						true if no error
	*									false if errer
	*/
	protected function checkSzenarios() {
		$errors = array();
		$wbd_data = $this->getWBDPropertys();

		$this->crs_id = (isset($this->crs_id) && $this->crs_id !== null) ? $this->crs_id : 0;

		if($wbd_data[self::WBD_VALUE_SEMINAR_FROM] && $wbd_data[self::WBD_VALUE_SEMINAR_TO]) {
			$from = new DateTime($wbd_data[self::WBD_VALUE_SEMINAR_FROM]);
			$till = new DateTime($wbd_data[self::WBD_VALUE_SEMINAR_TO]);
			if($from > $till){
				$errors[] = self::createError("dates implausible: begin > end <br>", $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
			}
		}

		
		foreach (static::$check_szenarios as $field => $szenario) {
			if(!array_key_exists($field, $wbd_data)) {
				throw new LogicException("Key not found in data".$field);
			}

			$value = $wbd_data[$field];
			foreach ($szenario as $rule => $setting) {
				switch ($rule) {
					case "mandatory":
						if($setting==1 && (!is_bool($value) && trim($value) == "")){
							$errors[] = self::createError("mandatory field missing: ".$field, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						break;
					case "maxlen":
						if(strlen($value) > $setting){
							$errors[] = self::createError("too long: ".$field." length: ".$length, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						break;
					case "list":
						if($value == ""){
							$errors[] = self::createError( "empty value not in list: ".$field, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						if(!in_array($value, $setting)){
							$errors[] = self::createError("not in list: ".$field, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						break;
					case "form":
						if(!preg_match($setting, $value) && $value != ""){
							$errors[] = self::createError("not well formed: ".$field, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						break;
					case "min_int_value":
						if((int)$value < $setting) {
							$errors[] = self::createError("integer to smaller then $setting: ".$field, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						break;
					case "custom":
						$r = self::$setting($value);
						$result = $r[0];
						$err = $r[1];
						if(!$result){
							$errors[] = self::createError( "$err ( $field )", $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
						}
						break;
				}
			}
		}

		return $errors;
	}
}