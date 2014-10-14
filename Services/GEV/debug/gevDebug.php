<?php
/**
* Debug stuff.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/debug', '', $basedir);
chdir($basedir);


//context w/o user
//require_once "./Services/Context/classes/class.ilContext.php";
//ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
//require_once("./Services/Init/classes/class.ilInitialisation.php");
//ilInitialisation::initILIAS();
require_once("./include/inc.header.php");

//require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
//require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
//require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");



function printToTable($ar){
	$header = false;
	print '<table border=1>';
	foreach ($ar as $entry) {
		print '<tr>';
		if(! $header){
			print '<td><b>';
			print join(array_keys($entry),'</b></td><td><b>');
			print '</b></td>';
			$header = true;
			print '</tr>';
			print '<tr>';
		}
		print '<td>';
		print join(array_values($entry),'</td><td>');
		print '</td>';
		print '</tr>';
	}
	print '</table>';
}



class gevDebug {
	public function __construct() {
		global $ilUser, $ilDB;

		$this->db = &$ilDB;;
		$this->user = &$ilUser;

	}

	public function getDeletedCourses() {
		/*
			Courses that are in historizing, but not in objects
		*/
		$query = "
			SELECT * FROM 
				hist_course 
			WHERE 
				crs_id 
			NOT IN 
				(
					SELECT obj_id FROM object_data
				)
			AND
				hist_historic=0
		";

		print $query;
		$ret = array();
		$res = $this->db->query($query);
		while($rec = $this->db->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}

	public function getDeletedCoursesBookings() {
		/*
			Courses that are in historizing, but not in objects
		*/
		$query = "
			SELECT * FROM 
				hist_usercoursestatus
			WHERE 
				crs_id 
			NOT IN 
				(
					SELECT obj_id FROM object_data
				)
			AND
				hist_historic=0
		";

		print $query;
		$ret = array();
		$res = $this->db->query($query);
		while($rec = $this->db->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}


	public function getDeletedCoursesOtherFragments() {
		$ret = array(
			 'crs_book' => array()
			,'crs_settings' => array()
			,'crs_acco' => array()
			,'crs_waiting_list' => array()
			//,'crs_items' = array()
		);

		//crs_book.crs_id, crs_book.user_id
		//crs_acco.crs_id, crs_acco.user_id,
		//crs_waiting_list.obj_id, crs_waiting_list.usr_id, 
		//crs_settings.obj_id
		//crs_items.parent_id (crs_items.obj_id)

		$queries = array();
		$queries['crs_book'] = "
			SELECT * FROM 
				crs_book
			WHERE 
				crs_id 
			NOT IN 
				(
					SELECT obj_id FROM object_data
				)
		";
		$queries['crs_acco'] = "
			SELECT * FROM 
				crs_acco
			WHERE 
				crs_id 
			NOT IN 
				(
					SELECT obj_id FROM object_data
				)
		";
		$queries['crs_waiting_list'] = "
			SELECT * FROM 
				crs_waiting_list
			WHERE 
				obj_id 
			NOT IN 
				(
					SELECT obj_id FROM object_data
				)
		";

		$queries['crs_settings'] = "
			SELECT * FROM 
				crs_settings
			WHERE 
				obj_id 
			NOT IN 
				(
					SELECT obj_id FROM object_data
				)
		";


		foreach ($queries as $table => $query) {
			print $query;
			print '<br>';
			$res = $this->db->query($query);
			while($rec = $this->db->fetchAssoc($res)) {
				$ret[$table][] = $rec;
			}
			
		}

		return $ret;
	}




	public function createBill($payment_data){
		/*	
		  $payment_data["user_id"]
		, $payment_data["crs_id"]
		, $payment_data["recipient"]
		, $payment_data["agency"]
		, $payment_data["street"]
		, $payment_data["housenumber"]
		, $payment_data["zipcode"]
		, $payment_data["city"]
		, $payment_data["costcenter"]
		, $payment_data["coupons"]
		, $payment_data["email"]
		*/
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		$billing_utils = gevBillingUtils::getInstance();
		$billing_utils->createCourseBill( 
				  $payment_data["user_id"]
				, $payment_data["crs_id"]
				, $payment_data["recipient"]
				, $payment_data["agency"]
				, $payment_data["street"]
				, $payment_data["housenumber"]
				, $payment_data["zipcode"]
				, $payment_data["city"]
				, $payment_data["costcenter"]
				, $payment_data["coupons"]
				, $payment_data["email"]
			);
	}


	public function getCurrentUserData(){
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$uutils = gevUserUtils::getInstanceByObjOrId($this->user);

		print_r(
		 array(
			'biz_street' => $this->user->getStreet(),
			'biz_zipcode' => $this->user->getZipcode(),
			'biz_city' => $this->user->getCity(),
			'biz_phone' => $this->user->getPhoneOffice(),
			//'biz_mobile' => $this->user->getPrivatePhone(),
			'mobile' => $uutils->getPrivatePhone(),
			'Status' => $uutils->getStatus(),
			'AgentStatus' => $uutils->getWBDAgentStatus(),
		));
		return $this->user;
	}


	public function getAllUsers(){
		require_once("Services/User/classes/class.ilObjUser.php");
		
		$ret = array();
		$sql = 'SELECT usr_id FROM usr_data';
		$result = $this->db->query($sql);
		while($record = $this->db->fetchAssoc($result)) {
			$ret[$record['usr_id']] = new ilObjUser($record['usr_id']);
		}
		return $ret;
	}

	public function updateHistoryForUser($usr){
		global $ilAppEventHandler;
		$ilAppEventHandler->raise("Services/User", "afterUpdate", array("user_obj" => $usr));
	}
}


//crs_book.crs_id, crs_book.user_id
//crs_settings.obj_id
//crs_acco.crs_id, crs_acco.user_id,
//crs_waiting_list.obj_id, crs_waiting_list.usr_id, 
//crs_items.parent_id (crs_items.obj_id)

$debug = new gevDebug();
/*printToTable($debug->getDeletedCourses());
printToTable($debug->getDeletedCoursesBookings());
$fragments = $debug->getDeletedCoursesOtherFragments();
foreach ($fragments as $table => $res) {
	print "<h1>$table</h1>";
	printToTable($res);
}
*/

$payment_data = array(
		'user_id'=>'',
		'crs_id'=>'',
		'recipient'=>'',
		'agency'=>'',
		'street'=>'',
		'housenumber'=>'',
		'zipcode'=>'',
		'city'=>'',
		'costcenter'=>'',
		'coupons'=>'',
		'email'=>''
);

//$debug->createBill($payment_data);

print '<pre>';
//print_r($debug->getCurrentUserData());


foreach ($debug->getAllUsers() as $id=>$usr) {
	$debug->updateHistoryForUser($usr);

	print "<h2>$id</h2>";
	print_r($usr->getLogin());
	print '<hr>';
}

print '<br><br><i>done.</i>';

?>
