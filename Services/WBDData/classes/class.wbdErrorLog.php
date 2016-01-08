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

class wbdErrorLog {

	public $ilDB;


	/*
	values of this array will be checked against;
	key is returned and stored in DB
	*/
	static $WBDERRORS = array(
		//'-undefined-' => '#this_string_must_not_be_contained_in_any_errormsg#',
		'USER_EXISTS' => 'Der Benutzer wurde bereits angelegt:',
		'USER_EXISTS_TP' => 'Der Benutzer wurde von einem anderen TP angelegt:',
		
		'USER_UNKNOWN' => array(
			'Die VermittlerID ist nicht vorhanden.',
			'Die Vermittler-ID ist nicht vorhanden.'
		),
		'USER_DIFFERENT_TP' => array(
			'beginswith'=>'Der TP 95473000 ist dem Vermittler',
			'endswith'=>'nicht zugeordnet',
		),		
		'USER_DEACTIVATED' => array(
			'Der Vermittler ist deaktiviert.',
			'Der Vermittler ist deaktiviert',
			"' ist deaktiviert",

		),

		'USER_SERVICETYPE' => array(
			'nicht zugeordnet. VV-Selbstverwalter'
		),

		'USER_SERVICETYPE' => array(
			'beginswith' => 'Keine Berechtigung zum Bearbeiten des Vermittlers',
			'endswith' => ''
		),

		'USER_NOT_IN_POOL' => array(
			'Der Vermittler ist nicht transferfähig.'
		),

		'WRONG_USERDATA' => array(
			'not well formed: auth_phone_nr',
			'mandatory field missing: street',
			'not in list: agency_work',
			'date not between 1900 and 2000 (birthday)',
			'Daten sind nicht plausibel: 1 Ungültiges Feld'
		),


		'WRONG_COURSEDATA' => array(
			'Daten sind nicht plausibel: 1 Das Ende des Seminars liegt in der Zukunft',
			'Daten sind nicht plausibel: 2 Der Anfang des Seminars liegt in der Zukunft',

			'dates implausible: begin > end',
			'mandatory field missing: study_content',
			'mandatory field missing: study_type_selection',
		),

		'TOO_OLD' => array( //separate this for easier filtering
			'date older than one year',
			'liegt vor dem ersten gültigen Meldungsdatum (Sep 1, 2013)'
		),

		'NO_RELEASE' => array(
			'Die Organisation ist nicht berechtigt den Vermittler transferfähig zu machen'
		),

		'CREATE_DUPLICATE' => array(
			'Der Nutzer konnte nicht im ISTS geändert werden. Status Code: 100'
		)
	);

	/*
	after user update, call resolveWBDErrorsForUser;
	will resolve all entries for user, but those:
	*/
	static $RESOLVE_EXCEPTIONS_USER = array(
		'WRONG_COURSEDATA',
		'TOO_OLD'
	);

	/*
	after course update, call resolveWBDErrorsForCourse;
	will resolve all entries for course, but those:
	*/
	static $RESOLVE_EXCEPTIONS_COURSE = array(
		'WRONG_USERDATA',
		'USER_UNKNOWN',
		'USER_DEACTIVATED',
		'USER_SERVICETYPE'
	);



	static function _install() {
		$sql = " CREATE TABLE IF NOT EXISTS wbd_errors ("
		  ." id int(11) NOT NULL AUTO_INCREMENT,"
		  ." ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,"
		  ." resolved tinyint(1) NOT NULL DEFAULT '0',"
		  ." internal tinyint(1) DEFAULT NULL COMMENT 'if true, internal filters blocked this record',"
		  ." action varchar(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'which action triggered the error, i.e. ''new_user'' or ''update_edurecord''',"
		  ." usr_id int(11) NOT NULL,"
		  ." crs_id int(11) NOT NULL,"
		  ." internal_booking_id int(11) NOT NULL COMMENT '== row_id of according table (hist_user, hist_usercoursestatus)',"
		  ." reason varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Error-constants',"
		  ." reason_full text COLLATE utf8_unicode_ci NOT NULL,"
		  ." PRIMARY KEY (id),"
		  ." KEY resolved (resolved),"
		  ." KEY usr_id (usr_id),"
		  ." KEY crs_id (crs_id),"
		  ." KEY reason (reason)"
		  ." ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0";
		global $ilDB;
		$ilDB->manipulate($sql);
	}


	public function __construct() {
		global $ilDB;
		$this->ilDB = &$ilDB;

	}


	private	function startsWith($haystack, $needle) {
	     $length = strlen($needle);
	     return (substr($haystack, 0, $length) === $needle);
	}

	private function endsWith($haystack, $needle) {
	    $length = strlen($needle);
	    if ($length == 0) {
	        return true;
	    }

	    return (substr($haystack, -$length) === $needle);
	}


	private function wbdError_parseReason($reason_xml) {
		$spos = strpos($reason_xml,'<faultstring>') + strlen('<faultstring>');
		$epos = strpos($reason_xml,'</faultstring>');
		if($spos === false || $epos === false) {
			return $reason_xml;
		}
		$reason = substr($reason_xml, $spos, $epos-$spos);
		return $reason;
	}

	private function wbdError_getReason($reason_str) {
		foreach(self::$WBDERRORS as $err=>$entry) {

			$entry = (array) $entry;

			if (array_key_exists('beginswith', $entry) || 
				array_key_exists('endswith', $entry)
			) {

				//check both for now...
				if ($this->startsWith(trim($reason_str), $entry['beginswith']) &&
					$this->endsWith(trim($reason_str), $entry['endswith']) ) {

					return $err;
				}

			} else {

				foreach ($entry as  $full_err) {
					if (strpos($reason_str, $full_err) !== false){
						return $err;
					}
				}
			}

		}
		return '-undefined-';
	}







	public function storeWBDError(	$action, 
									$reason_str, 
									$internal=0,
									$usr_id=0, 
									$crs_id=0, 
									$booking_id=0
									) {

		$reason_str = $this->wbdError_parseReason($reason_str);
		$reason = $this->wbdError_getReason($reason_str);

		$sql = "INSERT INTO wbd_errors ("
			."action, internal, usr_id, crs_id, "
			."internal_booking_id, reason, reason_full"
			.") VALUES ("
			."'$action',"
			.$this->ilDB->quote($internal, 'boolean') .','
			.$usr_id .','
			.$crs_id .','
			.$booking_id .','
			."'$reason',"
			.$this->ilDB->quote($reason_str, 'text')
			.")";

		$this->ilDB->manipulate($sql);

	}




	public function resolveWBDErrorById($id) {
		$sql = "UPDATE wbd_errors SET resolved=1 WHERE id="
			.$this->ilDB->quote($id, 'integer');
		$this->ilDB->manipulate($sql);
	}

	public function resolveWBDErrorsForCourse($crs_id) {
		$sql = "UPDATE wbd_errors SET resolved=1 WHERE crs_id="
			.$this->ilDB->quote($crs_id, 'integer')
			." AND reason NOT IN ('"
			.implode("', '" , self::$RESOLVE_EXCEPTIONS_COURSE)
			."')";

		$this->ilDB->manipulate($sql);
	}

	public function resolveWBDErrorsForUser($usr_id) {
		$sql = "UPDATE wbd_errors SET resolved=1 WHERE usr_id="
			.$this->ilDB->quote($usr_id, 'integer')
			." AND reason NOT IN ('"
			.implode("', '" , self::$RESOLVE_EXCEPTIONS_USER)
			."')";

		$this->ilDB->manipulate($sql);

	}


}
?>