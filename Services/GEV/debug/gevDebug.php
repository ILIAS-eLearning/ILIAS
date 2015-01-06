<?php
/**
* Debug stuff.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

//die();

$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/debug', '', $basedir);
chdir($basedir);

require "./Customizing/global/skin/genv/Services/GEV/simplePwdSec.php";



//context w/o user
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


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
		if(!is_array($entry)){
			$entry = array($entry);
		}
		print join(array_values($entry),'</td><td>');
		print '</td>';
		print '</tr>';
	}
	print '</table>';
}



class gevDebug {
	public function __construct() {

		global $ilUser, $ilDB;
		global $ilClientIniFile;

		$this->db = &$ilDB;
		$this->user = &$ilUser;


		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

	
		$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$this->importDB = $mysql;

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


	public function getAllUsers($ids=array()){
		require_once("Services/User/classes/class.ilObjUser.php");
		
		$ret = array();
		$sql = 'SELECT usr_id FROM usr_data';
		
		if(count($ids) > 0){
			$sql .=	" WHERE usr_id in (" .implode(',', $ids) .")";
		}

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


	public function updateHistoryForUserIfStellung($usr){
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$uutils = gevUserUtils::getInstanceByObjOrId($usr->getId());
		if($uutils->getRawWBDAgentStatus() == '0 - aus Stellung'){
			print '<br>new hist case'.
			self::updateHistoryForUser($usr);
		}
	}



	
	public function setAgentStateForUser($user_id){
		//global $rbacreview;
		//$user_roles = $rbacreview->assignedRoles($user_id);
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$roles = gevRoleUtils::getInstance()->getGlobalRolesOf($user_id);

		foreach($roles as $key => $value) {
			$roles[$key] = ilObject::_lookupTitle($value);
		}
		print_r($roles);
		print '<br>-----<br>';
		print_r($uutils->getWBDAgentStatus());

		if(
			in_array("OD/LD/BD/VD/VTWL", $roles) ||
			in_array("DBV/VL-EVG", $roles) ||
			in_array("DBV-UVG", $roles) 
		){
			print '<br>new state: 1 - Angestellter Außendienst' ;
			$uutils->setWBDAgentStatus('1 - Angestellter Außendienst');
		}
		if(
			in_array("AVL", $roles) ||
			in_array("HA", $roles) ||
			in_array("BA", $roles) ||
			in_array("NA", $roles) 
		){
			print '<br>new state: 2 - Ausschließlichkeitsvermittler' ;
			$uutils->setWBDAgentStatus('2 - Ausschließlichkeitsvermittler');
		}
		if(
			in_array("VP", $roles) 
		){
			print '<br>new state: 3 - Makler' ;
			$uutils->setWBDAgentStatus('3 - Makler');
		}

		/*
		$roles = gevRoleUtils::getInstance()->getGlobalRolesOf($this->user_id);

		foreach($roles as $key => $value) {
			$roles[$key] = ilObject::_lookupTitle($value);
		}
		*/
	}

	public function revertSetAgentStateForUser($user_id){
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$uutils = gevUserUtils::getInstanceByObjOrId($user_id);
		$possibleNewRoles = array(
			'1 - Angestellter Außendienst',
			'2 - Ausschließlichkeitsvermittler',
			'3 - Makler'
		);
		if(in_array($uutils->getRawWBDAgentStatus(), $possibleNewRoles)){
			//if roles also match:
			$roles = gevRoleUtils::getInstance()->getGlobalRolesOf($user_id);
			foreach($roles as $key => $value) {
				$roles[$key] = ilObject::_lookupTitle($value);
			}
			if(
				in_array("OD/LD/BD/VD/VTWL", $roles) ||
				in_array("DBV/VL-EVG", $roles) ||
				in_array("DBV-UVG", $roles) ||
				in_array("AVL", $roles) ||
				in_array("HA", $roles) ||
				in_array("BA", $roles) ||
				in_array("NA", $roles) || 
				in_array("VP", $roles) 
			){
				//revert
				print '<br>reverting.';
				$uutils->setWBDAgentStatus('0 - aus Stellung');
			}

		}
	}


	public function getUsersWithRole($roles = array()){
		require_once("Services/GEV/Utils/classes/class.gevGeneralUtils.php");
		$utils = new gevGeneralUtils();

		return $utils->getUsersWithGlobalRole($roles);
	}


	public function getIdsReported($reported = 1){
		$ret = array();
		$sql = "SELECT id FROM wbd_altdaten WHERE reported = " .$reported;
		$result = mysql_query($sql, $this->importDB);
		while($record = mysql_fetch_assoc($result)) {
			$ret[] = $record['id'];
		}
		return $ret;
	}

	public function getDistinctNames(){
		$ret = array();
		$sql= "SELECT DISTINCT concat( vorname, ' ', name ) AS fullname FROM wbd_altdaten";
		$result = mysql_query($sql, $this->importDB);
		while($rec = mysql_fetch_assoc($result)) {
			$ret[] = $rec['fullname'];
		}
		return $ret;
	}


	


	//move this into the WBD-DataConnector
	public function fix_hist_users_calculated_fields(){

		/*
		$sql = "UPDATE hist_user SET wbd_agent_status ='aus Rolle'"
		." WHERE wbd_agent_status IN ('aus Stellung', '0 - aus Stellung')";

		$this->db->manipulate($sql);

		$sql = "UPDATE hist_user SET wbd_agent_status='-empty-'"
		." WHERE wbd_agent_status='Ohne Zuordnung'";

		$this->db->manipulate($sql);
		*/

		$sql = "SELECT row_id, user_id FROM hist_user"
			." WHERE wbd_agent_status = 'aus Rolle'"
			." AND hist_historic=0";
		$result = $this->db->query($sql);
		while($record=$this->db->fetchAssoc($result)){
			$uutils = gevUserUtils::getInstanceByObjOrId($record['user_id']);
			$agent_status = $uutils->getWBDAgentStatus();
			$sql = "UPDATE hist_user SET"
				." wbd_agent_status='" .$agent_status ."'"
				." WHERE row_id = " .$record['row_id'];
			$this->db->manipulate($sql);	
			print "\n";
			print $sql;
		}
		
	}



	public function find_users_with_missing_roles(){

		$head = array(
			'user_id', 'firstname', 'lastname', 'org_unit', 'position_key', 'wbd_type',
			'interim_id', 'interim_vkey_gev', 'interim_vkey_vfs', 
			'interim_old_roles'
		);
		print implode(';', $head);
		print '<br>';

		$sql = "SELECT user_id, firstname, lastname, org_unit, position_key, wbd_type FROM hist_user"
			." WHERE wbd_agent_status = 'aus Rolle'"
			." AND hist_historic = 0";

		$result = $this->db->query($sql);
		while($record=$this->db->fetchAssoc($result)){
			

			$sql = "SELECT id, vkey_gev, vkey_vfs FROM interimUsers"
			." WHERE interimUsers.ilid = " .$record['user_id'];
			$res = mysql_query($sql, $this->importDB);
			
			$record['interim_id'] = '';
			$record['interim_vkey_gev'] = '';
			$record['interim_vkey_vfs'] = '';
			$record['interim_old_roles'] = '';
			
			if(mysql_num_rows($res) > 0){

				$record['interim_id'] = $rec['id'];
				$record['interim_vkey_gev'] = $rec['vkey_gev'];
				$record['interim_vkey_vfs'] = $rec['vkey_vfs'];

				$rec = mysql_fetch_assoc($res);

				//print_r($rec);

				$sql="SELECT interimRoles.title AS role_title FROM interimRoles"
				." INNER JOIN interimUserRoles ON interimRoles.id=interimUserRoles.interim_role_id"
				." WHERE interimUserRoles.interim_usr_id = "
				.$rec['id'];


				$res = mysql_query($sql, $this->importDB);
				while($role_rec = mysql_fetch_assoc($res)){
					$record['interim_old_roles'] .= $role_rec['role_title'] .' # ';
				}
			}
			
			//print_r($record);
			print implode(';', array_values($record));
			print '<br>';
		}


	}


	





}
print '<pre>';


//die('online');
$debug = new gevDebug();
//$debug->fix_hist_users_calculated_fields();
$debug->find_users_with_missing_roles();



print '<br><br><i>done.</i>';
?>
