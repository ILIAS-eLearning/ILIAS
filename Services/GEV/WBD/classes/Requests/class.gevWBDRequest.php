<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Request Interface
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
abstract class gevWBDRequest implements WBDRequest {

	protected $wbd_success;
	protected $wbd_error;
	protected $xml_tmpl_path;
	protected $wbd_service_name;
	protected $xml_tmpl_file_name;

	protected $dictionary;
	static $error_log = "";

	static $TELNO_MOBILE_REGEXP = "/^((00|[+])49((\s|[-\/])?)|0)1[5-7][0-9]([0-9]?)((\s|[-\/])?)([0-9 ]{7,12})$/";
	static $TELNO_REGEXP = "/^(00|[+])49[\s-\/][0-9]*/";
	static $USER_ID = "user_id";
	static $ROW_ID = "row_id";
	static $CRS_ID = "crs_id";
	static $CRS_BEGIN = "from";
	static $CRS_END = "till";
	static $USR_STREET = "street";
	static $USR_HOSE_NUMBER = "house_number";
	static $USR_MOBILE_PHONE = "mobile_phone_nr";
	static $USR_PHONE_NUMBER = "phone_nr";
	static $PARENT_NODE_WBD_ERROR = "fault";
	static $NODE_WBD_ERROR = "faultstring";

	protected function __construct() {
		$this->dictionary = new gevWBDDictionary();
		$this->xml_tmpl_path = "/Users/shecken/Documents/projects/new_wbd_connector/wbd_connector/src/messages";
		$this->crs_id = 0;
	}

	/**
	* parses the error values out of the response xml
	*
	* @param xml 		$response 			repsonse xml
	*
	*/
	public function createWBDError($response) {
		$this->wbd_error = new gevWBDError($this->parseReason($response),static::$request_type,$this->user_id,$this->row_id,$this->crs_id);
		return true;
	}

	/**
	* replaces the placeholder with mapped values
	*
	* @param string 	$template 	XML Template String
	* @return string 				XML String with replaced palceholders
	*/
	public function replaceArguments($template) {
		$reflect = new ReflectionClass($this);
		$props = $reflect->getProperties(ReflectionProperty::IS_PROTECTED);

		foreach ($props as $key => $value) {
			if($this->{$value->name} instanceof gevWBDData) {
				$template = str_replace("{".$this->{$value->name}->WBDTagName()."}"
									, $this->{$value->name}->WBDValue()
									, $template);
			}
		}

		return $template;
	}

	/**
	* Get the error Values
	*
	* @throws LogicException 			no error values are available
	*
	* @return gevWBDError 				values on error
	*/
	public function getWBDError() {
		if($this->wbd_error === null) {
			throw new LogicException("WBDRequest::getWBDError:wbd_error is null");
		}

		return $this->wbd_error;
	}
	
	/**
	* Get the success Values
	*
	* @throws LogicException 			no error values are available
	*
	* @return gevWBDSuccess				values on error
	*/
	public function getWBDSuccess() {
		if($this->wbd_success === null) {
			throw new LogicException("WBDRequest::getWBDSuccess:wbd_success is null");
		}

		return $this->wbd_success;
	}

	/***********************
	**** STATIC CONTENT ****
	***********************/
	/**
	* Checks the Data for each szenario
	*
	*
	* @return bool 						true if no error
	*									false if errer
	*/
	final static function checkSzenarios($data) {
		$usr_id = $data[self::$USER_ID];
		$row_id = $data[self::$ROW_ID];

		$crs_id = (array_key_exists(self::$CRS_ID, $data)) ? $data[self::$CRS_ID] : 0; 

		if(array_key_exists(self::$CRS_BEGIN, $data) && array_key_exists(self::$CRS_END, $data)) {
			$from = new DateTime($edu_record[self::$CRS_BEGIN]);
			$till = new DateTime($edu_record[self::$CRS_END]);
			if($from > $till){
				self::createErrorLogEntry("dates implausible: begin > end <br>",$usr_id, $row_id, $crs_id);
				return false;
			}
		}
		$errors = array();
		foreach (static::$check_szenarios as $field => $szenario) {

			if(!array_key_exists($field, $data)) {
				throw new LogicException("Key not found in data".$field);
			}

			$value = $data[$field];
			foreach ($szenario as $rule => $setting) {
				switch ($rule) {
					case "mandatory":
						if($setting==1 && (!is_bool($value) && trim($value) == "")){
							$errors[] =  new gevWBDError("mandatory field missing: ".$field, static::$request_type, $usr_id, $row_id, $crs_id);
						}
						break;
					case "maxlen":
						if(strlen($value) > $setting){
							$errors[] =  new gevWBDError("too long: ".$field, static::$request_type, $usr_id, $row_id, $crs_id);
						}
						break;
					case "list":
						if($value == ""){
							$errors[] =  new gevWBDError( "empty value not in list", static::$request_type, $usr_id, $row_id, $crs_id);
						}
						if(!in_array($value, $setting)){
							$errors[] =  new gevWBDError("not in list: ".$field, static::$request_type, $usr_id, $row_id, $crs_id);
						}
						break;
					case "form":
						if(!preg_match($setting, $value) && $value != ""){
							$errors[] =  new gevWBDError("not well formed: ".$field, static::$request_type, $usr_id, $row_id, $crs_id);
						}
						break;
					case "min_int_value":
						if((int)$value < $setting) {
							$errors[] =  new gevWBDError("integer to smaller then $setting: ".$field, static::$request_type, $usr_id, $row_id, $crs_id);
						}
						break;
					case "custom":
						$r = self::$setting($value);
						$result = $r[0];
						$err = $r[1];
						if(!$result){
							$errors[] =  new gevWBDError( "$err ( $field )", static::$request_type, $usr_id, $row_id, $crs_id);
						}
						break;
				}
			}
		}

		return $errors;
	}

	/**
	* Checks the date is before 2000
	* 
	* @param string 	$date
	*
	* @return $array
	*/
	final static function datebefore2000($date){
		$dat = explode('-',$date);
		if(	(int)$dat[0] < 2000 && 
			(int)$dat[0] > 1900) {
			return array(true, 'OK');
		}
		return array(false, 'date not between 1900 and 2000');
	}
	
	/**
	* Checks the date is later then 2013-09-01
	* 
	* @param string 	$date
	*
	* @return $array
	*/
	final static function dateAfterSept2013($date){
		$dat = explode('-',$date);
		$val = strtotime($dat[2] . '-' .$dat[1] .'-' .$dat[0]);
		$limit = strtotime('2013-09-01');//Sat, 31 Aug 2013 22:00:00 GMT
		
		if(	$val >= $limit) {
			return array(true, 'OK');
		}
		return array(false, 'date before 09/2013');
	}

	/**
	* Checks the date is not older then one year
	* 
	* @param string 	$date
	*
	* @return $array 	
	*/
	final static function dateInLastYear($date){
		$dat = explode('-',$date);
		$val = strtotime($dat[2] . '-' .$dat[1] .'-' .$dat[0]);
		$limit = mktime(0, 0, 0, date("m"), date("d"), date("Y")-1);

		if(	$val >= $limit) {
			return array(true, 'OK');
		}
		return array(false, 'date older than one year');
	}

	/**
	* Checks the mobile phonenumber if it is correct
	* 
	* @param string 	$mobile_phone_number
	*
	* @return $array 	
	*/
	final static function regexpMobilePhone($mobile_phone_number) {
		if($mobile_phone_number != "" && preg_match(self::$TELNO_MOBILE_REGEXP, $mobile_phone_number)){
			return array(true, 'OK');
		}

		return array(false,"not well formed mobile_phone_nr");
	}

	/**
	* Checks the phonenumber if it is correct
	* 
	* @param string 	$phone_number
	*
	* @return $array 	
	*/
	final static function regexpPhone($phone_number) {
		if($phone_number == "" || preg_match(self::$TELNO_REGEXP, $phone_number)){
			return array(true, 'OK');
		}

		return array(false,"not well formed mobile_phone_nr");
	}

	/**
	* Checks the value if it is a boolen
	* 
	* @param string 	$value
	*
	* @return $array
	*/
	final static function isBool($value){
		if(is_bool($value)) {
			return array(true, 'OK');
		}
		return array(false, 'not a boolean');
	}

	/**
	* Writes an Error Log entry
	*
	* @param string 	$failure 	message string
	* @param integer 	$usr_id 	id of the user
	* @param integer 	$row_id 	id of the historic row
	* @param integer 	$crs_id 	id of the course
	*/
	final static function createErrorLogEntry($failure, $usr_id, $row_id, $crs_id = 0) {
		
		throw new Exception("Ha da haben wir den Salat:\n $failure, $usr_id, $row_id, $crs_id");
		/*self::$error_log->storeWBDError(static::$request_type,
							str_replace("<br>","", $failure),
							1,
							$usr_id,
							$crs_id,
							$row_id
						);*/
	}

	/**
	* Changes internal data to the correct format
	* i.e. PhoneNumbers or Streets/Numbers
	* 
	* @param array 		$data 		data array
	*
	* @return array 	$data
	*/
	final static function polishInternalData($data) {
		if(array_key_exists(self::$USR_STREET,$data)) {
			$street_and_number = self::extractHouseNumber($data[self::$USR_STREET]);
			$data[self::$USR_STREET] = $street_and_number["street"];
			$data[self::$USR_HOSE_NUMBER] = $street_and_number["nr"];
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

	/***********************
	******* CONTENT ********
	***********************/
	/**
	* Gets the real fault string
	*
	* @param string 	$reason_xml 	response xml on error
	*/
	final function parseReason($reason_xml) {
		$reason = $reason_xml;

		foreach($reason_xml->xpath("//".self::$PARENT_NODE_WBD_ERROR) as $event) {
 			$error_node = self::$NODE_WBD_ERROR;
 			$reason = $event->$error_node[0];
		}

		return $reason;
	}

	

	/**
	* Get the content of the Service XML File
	*
	* @return string 	$template_xml
	*/
	final function getServiceXML() {
		$fp = $this->xml_tmpl_path."/".$this->xml_tmpl_file_name;
		$template_xml = file_get_contents($fp);
		return $template_xml;
	}

	/**
	* creates the unsigned XML Data
	*
	* @return string 					XML Data String
	*/
	public function getXML() {
		$xml_tmpl = $this->getServiceXML();
		return $this->replaceArguments($xml_tmpl);
	}
}