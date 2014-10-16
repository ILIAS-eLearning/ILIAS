<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* interface for the (external) WBD-Connector
* retrieve and set eduPoint-relevant data
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*/

abstract class wbdDataConnector {

	public $ilDB;

	public $WBD_USER_RECORD;
	public $WBD_EDU_RECORD;
	public $CSV_LABELS;
	public $VALUE_MAPPINGS;
	public $USER_RECORD_VALIDATION;

	public $csv_text_delimiter = '"';
	public $csv_field_delimiter = ';';

	public function __construct() {
		global $ilDB;
		$this->ilDB = &$ilDB;

		require_once("./Services/WBDData/wbdBlueprints.php");
		$this->WBD_USER_RECORD = $WBD_USER_RECORD;
		$this->WBD_EDU_RECORD = $WBD_EDU_RECORD;
		$this->CSV_LABELS = $CSV_LABELS;
		$this->VALUE_MAPPINGS = $VALUE_MAPPINGS;
		$this->USER_RECORD_VALIDATION = $WBD_USER_RECORD_VALIDATION;
		$this->TELNO_REGEXP = $TELNO_REGEXP;
		$this->FAKEDATA = $FAKEDATA;
	}

	/**
	* BLUEPRINTS
	**/
	protected function new_user_record($data=array()){
		$user_record = $this->WBD_USER_RECORD;
		foreach ($data as $key => $value) {
			$user_record[$key] = $value;
		}

		return $user_record;
	}

	protected function new_edu_record($data=array()){
		$edu_record = $this->WBD_EDU_RECORD;
		foreach ($data as $key => $value) {
			$edu_record[$key] = $value;
		}

		return $edu_record;
	}

	/**
	* TESTDATA
	**/

	public function fill_format_nr($format){
		$str_out = '';
		$len = strlen($format);
	    for($i = 0; $i < $len; $i++) {
	        if(substr($format,$i, 1) == 'X'){
	        	$str_out .= rand(0,9);
	        }else{
	        	$str_out .= substr($format,$i, 1);
	        }
	    }
	   	return $str_out;		
	}
	public function fake_string($min, $max){
		$len = rand($min, $max);
		$str_out = '';
		for($i = 0; $i < $len; $i++) {
			$use_normal_char = rand(0,12);
			
			if(!$use_normal_char){
				$base = $this->FAKEDATA['special_chars'];
				$str_out .= $base[rand(0, count($base)-1)];
			}else{
				$base = $this->FAKEDATA['chars'];
				$str_out .= substr($base, rand(0, strlen($base) - 1), 1);
			}
		}
		return ucfirst($str_out);
	}
	public function fake_fon(){
		$format = $this->FAKEDATA['fon_formats'][rand(0, count($this->FAKEDATA['fon_formats'])-1)];
	   	return $this->fill_format_nr($format);
	}
	public function fake_streetnr($list){
		$street = $this->fake_string(5, 22);
		
		$format = $this->FAKEDATA['housenr_formats'][rand(0, count($this->FAKEDATA['housenr_formats'])-1)];
		$nr = $this->fill_format_nr($format);

		return $street .' ' .$nr;
	}
	public function fake_listentry($list){
		return $list[rand(0, count($list)-1)];
	}



	/**
	* VALIDATION
	**/

	protected function validateUserRecord($user_record){
		foreach($this->USER_RECORD_VALIDATION  as $field => $validation){
			$value = $user_record[$field];
			foreach ($validation as $rule => $setting) {
				switch ($rule) {
					
					case 'mandatory':
						if($setting==1 && trim($value) == ''){
							return 'mandatory field missing: ' .$field .'<br>';
							//return false;
						}
						break;
					
					case 'maxlen':
						if(strlen($value) > $setting){
							return 'too long: ' .$field .'<br>';
							//return false;
						}
						break;
					
					case 'list':
						if(! in_array($value, $setting)){
							return 'not in list: ' .$field .'<br>';
							//return false;
						}
						break;

					case 'form':
						if(!preg_match($setting, $value) && $value != ''){
							return 'not well formed: ' .$field .'<br>';
						}
						break;
				}
			}
		}
		return true;
	}



	/**
	* EXPORT FUNCTIONS, CSV and HTML
	**/

	private function csv_dump($data, $header=False, $as_file=False){
		if($header) {
			//data must have at least one entry!
			$headerrow = $this->csv_labels(array_keys($data[0]));
			array_unshift($data, $headerrow);
		}

		if( $as_file) {
			//set header
			header("Content-Type: application/csv; charset=ISO-8859-1");
			header("Content-Disposition:attachment; filename=\"".$as_file.".csv\"");
		} else {
			header("Content-Type: text/plain, charset=utf-8");
		}

		foreach ($data as $row){
			$r = $this->csv_text_delimiter
				.join(   $this->csv_text_delimiter
						.$this->csv_field_delimiter
						.' '
						.$this->csv_text_delimiter,
						 $row)
				.$this->csv_text_delimiter
				."\n";

			print $r;
		}

		/*
		Fabi goes like this:

		function escape_quotes($str) {
			return str_replace("\"", "\"\"", $str); // This seems to be the way how excel likes it....
		}

		// Output
		foreach ($ret as $row) {
			echo mb_convert_encoding("\"".implode("\";\"", array_map("escape_quotes", $row))."\"\n", "ISO-8859-1", "UTF-8");
		}

		*/
	}


	private function html_dump($data){
		$headerrow = $this->csv_labels(array_keys($data[0]));
		array_unshift($data, $headerrow);

		header("Content-Type: text/html, charset=utf-8");

		print '<table border=1>';
		foreach ($data as $row){
			print '<tr>';
			foreach ($row as $key => $value) {
				print '<td>';
				print $value;
				print '</td>';
			}
			print '</tr>';
		}
		print '</table>';

	}


	private function csv_labels($keys){
		$ret = array();
		foreach ($keys as $key) {
			$ret[] = $this->CSV_LABELS[$key];
		}
		return $ret;
	}


	public function export_get_new_users($out='csv', $as_file=False){
		$data = $this->get_new_users();
		if($out == 'csv'){
			$this->csv_dump($data, True, $as_file);
		}else{
			$this->html_dump($data);
		}
	}
	public function export_get_updated_users($out='csv', $as_file=False){
		$data = $this->get_updated_users();
		if($out == 'csv'){
			$this->csv_dump($data, True, $as_file);
		}else{
			$this->html_dump($data);
		}
	}
	public function export_get_new_edu_records($out='csv', $as_file=False){
		$data = $this->get_new_edu_records();
		if($out == 'csv'){
			$this->csv_dump($data, True, $as_file);
		}else{
			$this->html_dump($data);
		}
	}
	public function export_get_changed_edu_records($out='csv', $as_file=False){
		$data = $this->get_changed_edu_records();
		if($out == 'csv'){
			$this->csv_dump($data, True, $as_file);
		}else{
			$this->html_dump($data);
		}
	}




	/*
	* ------------- IMPLEMENT THE FOLLOWING ------------
	*/

	/**
	* EXPORT FUNCTIONS
	**/

	/**
	 * get users that do not have a BWV-ID yet
	 *
	 * @param
	 * @return array of user-records
	 */

	public function get_new_users() {}


	/**
	 * get users with outdated records in BWV-DB:
	 * userdata changed after last reporting
	 *
	 * @param
	 * @return array of user-records
	 */

	public function get_updated_users() {}


	/**
	 * get edu-records for courses that
	 * started 3 months ago (or more)
	 * and have not been submitted to the WBD
	 *
	 *
	 * @param
	 * @return array of edu-records
	 */

	public function get_new_edu_records() {}


	/**
	 * get edu-records for courses that
	 * started 3 months ago (or more)
	 * if the current record differs from a record
	 * that was allready sent to the WBD
	 *
	 * @param
	 * @return array of edu-records
	 */

	public function get_changed_edu_records() {}


	/**
	* IMPORT FUNCTIONS
	**/

	/**
	 * set BWV-ID for user
	 *
	 * @param string $user_id
	 * @param string $bwv_id
	 * @param date $certification_begin
	 * @return boolean
	 */

	public function set_bwv_id($user_id, $bwv_id, $certification_begin) {}


	/**
	 * set booking ID for edu record
	 *
	 * @param string $row_id
	 * @param string $booking_id
	 * @return boolean
	 */

	public function set_booking_id($row_id, $booking_id) {}


	/**
	 * save external edu-record for user
	 *
	 * @param array $edu_record
	 * @return boolean
	 */

	public function save_external_edu_record($edu_record) {}


}

?>
