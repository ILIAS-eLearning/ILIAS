<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBDData-interface
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*/


//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/WBD/classes', '', $basedir);
chdir($basedir);
require_once("./Services/WBDData/classes/class.wbdDataConnector.php");


class gevWBDDataConnector extends wbdDataConnector {

	public function __construct() {
		parent::__construct();
	}


	/**
	 * get users that do not have a BWV-ID yet
	 * 
	 * @param 
	 * @return array of user-records
	 */
	public function get_new_users() {
		$sql = "
			SELECT
				user_id,
				firstname AS first_name,
				lastname AS last_name

			FROM 
				hist_user
			WHERE
				hist_historic = 0
			AND 
				bwv_id = '-empty-'
			";

		$result = $this->ilDB->query($sql);

		while($record = $this->ilDB->fetchAssoc($result)) {
			$ret[] = wbdDataConnector::new_user_record($record);
		}
		return $ret;
	}





}



//print '<pre>';
$cls = new gevWBDDataConnector();
//print_r($cls);
$cls->export_get_new_users();


/*
$a = $cls->new_user_record();
$b = $cls->new_user_record();
$c = $cls->new_user_record(array('first_name'=>'C'));

$a['first_name'] = 'A';
$b['last_name'] = 'B';



print_r($a);
print '<hr>';
print_r($b);
print '<hr>';
print_r($c);
*/
?>
