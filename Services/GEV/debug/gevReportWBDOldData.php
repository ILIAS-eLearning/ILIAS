<?php
/**
* Debug stuff.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

//settings and imports
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/debug', '', $basedir);
chdir($basedir);


require "simplePwdSec.php";

//if( !$LIVE) {
	//context w/o user
	require_once "./Services/Context/classes/class.ilContext.php";
	ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
	require_once("./Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();
//}

require_once("./include/inc.header.php");



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
		//print '<td>';
		//print join(array_values($entry),'</td><td>');
		foreach (array_values($entry) as $val){
			print '<td>';
			if(is_array($val) && count($val) == 1){
				print_r($val[0]);
			}else{
				print_r($val);
			}
			print '</td>';
		}
		
		//print '</td>';
		print '</tr>';
	}
	print '</table>';
}


class gevReportOldData {

	public function __construct() {
		global $ilUser, $ilDB;
		global $ilClientIniFile;
		global $LIVE;

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


	public function doQuery($sql){

		$result = mysql_query($sql, $this->importDB);
		$count = mysql_num_rows($result);
		$ret = array();
		while($rec = mysql_fetch_assoc($result)) {
			$ret[] = $rec;
		}

		return array(
			$count,
			$ret
		);
	}

	public function doIlQuery($sql){
		$result = $this->db->query($sql);
		$count = $this->db->numRows($result);
		$ret = array();
		while($rec = $this->db->fetchAssoc($result)) {
			$ret[] = $rec;
		}

		return array(
			$count,
			$ret
		);
	}



	public function getEntries($reported=False){
		$sql = "SELECT * FROM wbd_altdaten";
		if($reported === 0){
			$sql .= " WHERE reported=0";
		}
		if($reported === 1){
			$sql .= " WHERE reported=1";
		}

		return $this->doQuery($sql);
	}

	public function getDistinctUsers($reported=False){

		$sql = "SELECT DISTINCT name, vorname FROM wbd_altdaten";
		if($reported === 0){
			$sql .= " WHERE reported=0";
		}
		if($reported === 1){
			$sql .= " WHERE reported=1";
		}

		return $this->doQuery($sql);
		
	}

	public function getDistinctSeminars($reported=False){

		$sql = "SELECT DISTINCT titel, beginn, ende FROM wbd_altdaten";
		if($reported === 0){
			$sql .= " WHERE reported=0";
		}
		if($reported === 1){
			$sql .= " WHERE reported=1";
		}

		return $this->doQuery($sql);
	}

	public function getSummedUpWP($reported=False){

		$sql = "SELECT SUM(WP) as wps FROM wbd_altdaten";
		if($reported === 0){
			$sql .= " WHERE reported=0";
		}
		if($reported === 1){
			$sql .= " WHERE reported=1";
		}

		$result = mysql_query($sql, $this->importDB);
		$rec = mysql_fetch_assoc($result);
		return $rec['wps'];
	}

	public function getUsersWithPartialReports(){
		$ret = array();
		//$reported = $this->getEntries(1)[1];
		$data = $this->getDistinctUsers(1);
		$reported = $data[1];

		//print_r($reported);
		foreach($reported as $pos=>$entry){
			//find user:
			$sql = "SELECT * FROM wbd_altdaten WHERE "
			."name='". $entry['name']."'"
			." AND "
			."vorname='". $entry['vorname']."'"
			." AND reported=0";
			//print $sql;
			$result = mysql_query($sql, $this->importDB);
			$count = mysql_num_rows($result);
			if($count > 0){
				$tmp = array();
				while($rec = mysql_fetch_assoc($result)) {
					$tmp[] = $rec;
				}
				$entry['not_reported'] = $tmp;
				$ret[] = $entry;
			}
		}
		return array(count($ret), $ret);
	}


	public function getWBDReportedUsers($service=False){
		$sql = "SELECT * FROM hist_user WHERE"
			." hist_historic = 0"
			." AND bwv_id != '-empty-'"
			." AND wbd_type NOT IN ('-empty-', '0 - kein Service', 'kein Service')";

		if($service === 'TPService'){
			$sql .= " AND wbd_type IN ('3 - TP-Service','TP-Service')";
		}
		if($service === 'TPBasis'){
			$sql .= " AND wbd_type IN ('2 - TP-Basis', 'TP-Basis')";
		}
		if($service === 'Bildungsdienstleister'){
			$sql .= " AND wbd_type IN ('1 - Bildungsdienstleister', 'Bildungsdienstleister')";
		}

		return $this->doIlQuery($sql);
	}

	public function getWBDReportedSeminars($service=False){
		$sql = "SELECT 
				hist_user.firstname,
				hist_user.lastname,
				hist_user.birthday,
				hist_user.bwv_id,
				hist_user.wbd_type,
				hist_course.title as trainingstitle,
				hist_course.begin_date,
				hist_course.end_date,
				hist_usercoursestatus.credit_points,
				hist_usercoursestatus.wbd_booking_id
				FROM 
					hist_usercoursestatus
				INNER JOIN
					hist_course
				ON
					hist_usercoursestatus.crs_id = hist_course.crs_id
				INNER JOIN
					hist_user
				ON
					hist_usercoursestatus.usr_id = hist_user.user_id
				WHERE 
					hist_usercoursestatus.wbd_booking_id IS NOT NULL
				AND hist_user.hist_historic = 0
				AND hist_course.hist_historic =0";

		if($service === 'TPService'){
			$sql .= " AND hist_user.wbd_type IN ('3 - TP-Service','TP-Service')";
		}
		if($service === 'TPBasis'){
			$sql .= " AND hist_user.wbd_type IN ('2 - TP-Basis', 'TP-Basis')";
		}
		if($service === 'Bildungsdienstleister'){
			$sql .= " AND hist_user.wbd_type IN ('1 - Bildungsdienstleister', 'Bildungsdienstleister')";
		}

		return $this->doIlQuery($sql);
	}


	public function getWBDReportedDistinctSeminars($service=False){
		$sql = "SELECT DISTINCT hist_usercoursestatus.crs_id
				FROM 
					hist_usercoursestatus
				INNER JOIN
					hist_course
				ON
					hist_usercoursestatus.crs_id = hist_course.crs_id
				INNER JOIN
					hist_user
				ON
					hist_usercoursestatus.usr_id = hist_user.user_id
				WHERE 
					hist_usercoursestatus.wbd_booking_id IS NOT NULL
				AND hist_user.hist_historic = 0
				AND hist_course.hist_historic =0";

		if($service === 'TPService'){
			$sql .= " AND hist_user.wbd_type IN ('3 - TP-Service','TP-Service')";
		}
		if($service === 'TPBasis'){
			$sql .= " AND hist_user.wbd_type IN ('2 - TP-Basis', 'TP-Basis')";
		}
		if($service === 'Bildungsdienstleister'){
			$sql .= " AND hist_user.wbd_type IN ('1 - Bildungsdienstleister', 'Bildungsdienstleister')";
		}

		return $this->doIlQuery($sql);
	}

	public function getWBDReportedDistinctSeminarUsers($service=False){
		$sql = "SELECT DISTINCT hist_usercoursestatus.usr_id
				FROM 
					hist_usercoursestatus
				INNER JOIN
					hist_course
				ON
					hist_usercoursestatus.crs_id = hist_course.crs_id
				INNER JOIN
					hist_user
				ON
					hist_usercoursestatus.usr_id = hist_user.user_id
				WHERE 
					hist_usercoursestatus.wbd_booking_id IS NOT NULL
				AND hist_user.hist_historic = 0
				AND hist_course.hist_historic =0";

		if($service === 'TPService'){
			$sql .= " AND hist_user.wbd_type IN ('3 - TP-Service','TP-Service')";
		}
		if($service === 'TPBasis'){
			$sql .= " AND hist_user.wbd_type IN ('2 - TP-Basis', 'TP-Basis')";
		}
		if($service === 'Bildungsdienstleister'){
			$sql .= " AND hist_user.wbd_type IN ('1 - Bildungsdienstleister', 'Bildungsdienstleister')";
		}

		return $this->doIlQuery($sql);
	}

	public function getWBDReportedWPs($service=False){
		$sql = "SELECT SUM(hist_usercoursestatus.credit_points) AS wps
				FROM 
					hist_usercoursestatus
				INNER JOIN
					hist_course
				ON
					hist_usercoursestatus.crs_id = hist_course.crs_id
				INNER JOIN
					hist_user
				ON
					hist_usercoursestatus.usr_id = hist_user.user_id
				WHERE 
					hist_usercoursestatus.wbd_booking_id IS NOT NULL
				AND hist_user.hist_historic = 0
				AND hist_course.hist_historic =0";

		if($service === 'TPService'){
			$sql .= " AND hist_user.wbd_type IN ('3 - TP-Service','TP-Service')";
		}
		if($service === 'TPBasis'){
			$sql .= " AND hist_user.wbd_type IN ('2 - TP-Basis', 'TP-Basis')";
		}
		if($service === 'Bildungsdienstleister'){
			$sql .= " AND hist_user.wbd_type IN ('1 - Bildungsdienstleister', 'Bildungsdienstleister')";
		}
	
		$result = $this->db->query($sql);
		$rec = $this->db->fetchAssoc($result);

		return $rec['wps'];
	}


	public function getNotWBDReportedUser(){
		$sql = "SELECT * FROM hist_user"
			." WHERE hist_user.wbd_type IN ('3 - TP-Service', '2 - TP-Basis', '1 - Bildungsdienstleister')"
			." AND hist_user.hist_historic=0"
			." AND hist_user.bwv_id='-empty-'";

		return $this->doIlQuery($sql);	
	}
	
	public function getNotWBDReportedUsersWithRegistration(){

		$sql_ids = "SELECT usr_id FROM udf_text"
			." WHERE field_id=25"
			." AND udf_text.value = '1 - Ja'";

		$sql = "SELECT * FROM hist_user"
			." WHERE user_id IN ("
			.$sql_ids
			.")"
			." AND hist_user.wbd_type IN ('0 - kein Service', '-empty-')"
			." AND hist_user.hist_historic=0"
			." AND hist_user.bwv_id='-empty-'";

		return $this->doIlQuery($sql);	
	}



	public function getSeminarsForUser($usr_id){
		$sql = "SELECT * FROM hist_usercoursestatus"
		    ." INNER JOIN hist_course on hist_usercoursestatus.crs_id = hist_course.crs_id AND hist_course.hist_historic=0"
			." WHERE usr_id = " .$usr_id
			." AND hist_usercoursestatus.hist_historic=0";
		return $this->doIlQuery($sql);	
	}


	public function getGlobalRoles(){
		$sql = "SELECT obj_id, title FROM object_data WHERE type='role'"
			." AND title NOT LIKE 'il_%'"
			." AND title NOT LIKE 'loc_%'";
		$r = $this->doIlQuery($sql);
		$ret = array();
		foreach ($r[1] as $no=>$entry) {
			$ret[$entry['obj_id']] = $entry['title'];
		}
		return $ret;
	}

	public function getGlobalRolesForUser($usr_id){
		$global_roles = $this->getGlobalRoles();
		
		$sql = "SELECT rol_id FROM rbac_ua"
			." WHERE rol_id IN ("
			.implode(',', array_keys($global_roles))
			.')'
			.'AND usr_id = ' .$usr_id;

		$r = $this->doIlQuery($sql);

		$ret = array();
		foreach ($r[1] as $entry) {
			$ret[] = $global_roles[$entry['rol_id']];
		}
		return $ret;
	}

	
}




/*
------------------------------------
run: 
*/

$report = new gevREportOldData();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>

    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    <style type="text/css">
    	body {
    		font-family: Arial, Sans;
    	}
    	th {
    		font-size: 12px;
    	}
    	td{
    		vertical-align: top;
    	}

    	.subtable,
    	.subtable td,
    	.subtable th {
    		border: 1px solid #cecece;
    		border-collapse: collapse;
    	}
    </style>

</head>
<body>

<?php

?>


<h1>WBD</h1>
<h2>User mit BWV-Id</h2>
	<table border="1" width="800">
		<tr>
			<td valign="top">
				<b>Gesamt</b>
			</td>
			<td valign="top">
				<b>  TP-Service</b>
			</td>
			<td valign="top">
				<b>  TP-Basis</b>
			</td>
			<td valign="top">
				<b>  Bildungsdienstleister</b>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
				<?php 
					$users = $report->getWBDReportedUsers();
					print $users[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$users = $report->getWBDReportedUsers('TPService');
					print $users[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$users = $report->getWBDReportedUsers('TPBasis');
					print $users[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$users = $report->getWBDReportedUsers('Bildungsdienstleister');
					print $users[0];
				?>
			</td>
		</tr>
	</table>



<h2>Gemeldete Seminare</h2>

	<table border="1" width="800">
		<tr>
			<td valign="top">

			</td>
			<td valign="top">
				<b>Gesamt</b>
			</td>
			<td valign="top">
				<b>  TP-Service</b>
			</td>
			<td valign="top">
				<b>  TP-Basis</b>
			</td>
			<td valign="top">
				<b>  Bildungsdienstleister</b>
			</td>
		</tr>

		<tr>
			<td valign="top" align="left">
				Meldungen <br>(User/Seminar-Beziehung)
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedSeminars();
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedSeminars('TPService');
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedSeminars('TPBasis');
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php
					$sem = $report->getWBDReportedSeminars('Bildungsdienstleister');
					print $sem[0];
					
				?>
			</td>
		</tr>

		<tr>
			<td valign="top" align="left">
				Unterschiedliche Seminare
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedDistinctSeminars();
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedDistinctSeminars('TPService');
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedDistinctSeminars('TPBasis');
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php
					$sem = $report->getWBDReportedDistinctSeminars('Bildungsdienstleister');
					print $sem[0];
					
				?>
			</td>
		</tr>

		<tr>
			<td valign="top" align="left">
				Unterschiedliche User
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedDistinctSeminarUsers();
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedDistinctSeminarUsers('TPService');
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$sem = $report->getWBDReportedDistinctSeminarUsers('TPBasis');
					print $sem[0];
				?>
			</td>
			<td valign="top" align="right">
				<?php
					$sem = $report->getWBDReportedDistinctSeminarUsers('Bildungsdienstleister');
					print $sem[0];
					
				?>
			</td>
		</tr>


		<tr>
			<td valign="top" align="left">
				Weiterbildungspunkte (Summe)
			</td>
			<td valign="top" align="right">
				<?php 
					$wps = $report->getWBDReportedWPs();
					print $wps;
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$wps = $report->getWBDReportedWPs('TPService');
					print $wps;
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$wps = $report->getWBDReportedWPs('TPBasis');
					print $wps;
				?>
			</td>
			<td valign="top" align="right">
				<?php 
					$wps = $report->getWBDReportedWPs('Bildungsdienstleister');
					print $wps;
				?>
			</td>
		</tr>

	</table>






<br><br><hr>

<h1>Altdaten</h1>

	<h2>Gesamt</h2>
	<table border="1" width="400">
		<tr>
			<td valign="top">
				<b>Einträge</b>
			</td>
			<td align="right">
				<?php
					$entries = $report->getEntries();
					print $entries[0];
				?>
			</td>
		</tr>	
		<tr>
			<td valign="top">
				<b>Unterschiedliche User</b>
			</td>
			<td align="right">
				<?php
					$distinct_users = $report->getDistinctUsers();
					print $distinct_users[0];
				?>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<b>Unterschiedliche Seminare</b>
			</td>
			<td align="right">
				<?php
					$distinct_seminars = $report->getDistinctSeminars();
					print $distinct_seminars[0];
				?>
			</td>
		</tr>

		<tr>
			<td valign="top">
				<b>WP (Summe)</b>
			</td>
			<td align="right">
				<?php
					$wps = $report->getSummedUpWP();
					print $wps;
				?>
			</td>
		</tr>
	</table>	

	<h2>Importiert</h2>
	<table border="1" width="400">
		<tr>
			<td valign="top">
				<b>Einträge</b>
			</td>
			<td align="right">
				<?php
					$entries = $report->getEntries(1);
					print $entries[0];
				?>
			</td>
		</tr>	
		<tr>
			<td valign="top">
				<b>Unterschiedliche User</b>
			</td>
			<td align="right">
				<?php
					$distinct_users = $report->getDistinctUsers(1);
					print $distinct_users[0];
				?>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<b>Unterschiedliche Seminare</b>
			</td>
			<td align="right">
				<?php
					$distinct_seminars = $report->getDistinctSeminars(1);
					print $distinct_seminars[0];
				?>
			</td>
		</tr>

		<tr>
			<td valign="top">
				<b>WP (Summe)</b>
			</td>
			<td align="right">
				<?php
					$wps = $report->getSummedUpWP(1);
					print $wps;
				?>
			</td>
		</tr>
	</table>	


<h2>Nicht importiert</h2>
	<table border="1" width="400">
		<tr>
			<td valign="top">
				<b>Einträge</b>
			</td>
			<td align="right">
				<?php
					$entries = $report->getEntries(0);
					print $entries[0];
				?>
			</td>
		</tr>	
		<tr>
			<td valign="top">
				<b>Unterschiedliche User</b>
			</td>
			<td align="right">
				<?php
					$distinct_users = $report->getDistinctUsers(0);
					print $distinct_users[0];
				?>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<b>Unterschiedliche Seminare</b>
			</td>
			<td align="right">
				<?php
					$distinct_seminars = $report->getDistinctSeminars(0);
					print $distinct_seminars[0];
				?>
			</td>
		</tr>

		<tr>
			<td valign="top">
				<b>WP (Summe)</b>
			</td>
			<td align="right">
				<?php
					$wps = $report->getSummedUpWP(0);
					print $wps;
				?>
			</td>
		</tr>
	</table>




<h2>Unterschiedliche User mit importierten <br>
	und weiteren (nicht importierten) Seminaren</h2>

	<?php
		$distinct_users_plus =$report->getUsersWithPartialReports();
	?>			

	<table border="1" width="400">
		<tr>
			<td valign="top">
				<b>Unterschiedliche User</b>
			</td>
			<td align="right">
				<?php
					print $distinct_users_plus[0];
				?>
			</td>
		</tr>
	</table>

	
	<hr>

	<table border=1>
	<?php
		foreach ($distinct_users_plus[1] as $pos=>$entry){
			print '<tr>';
				print '<td valign="top">' .$entry['id'] .'</td>';
				print '<td valign="top">' .$entry['name'] .'</td>';
				print '<td valign="top">' .$entry['vorname'] .'</td>';
				print '<td>';

				print '<table class="subtable"';
				print '<tr><th>';
				print implode('</th><th>', array(
					'id', 'Name', 'Vorname',
					'goa_user', 'AgenturNr', 'Geb', 'Titel', 'WP'
				));
				print '</th></tr>';

				foreach ($entry['not_reported'] as $nr=>$norep){
				
						print '<tr><td>';
						$buf = implode('</td><td width=50>', array(
							$norep['id'],
							$norep['Name'],
							$norep['Vorname'],
							$norep['goa_username'],
							$norep['Agenturnummer'],
							$norep['Geburtsdatum'],
							'<nobr>'.$norep['Titel'].'</nobr>',
							$norep['WP']
						));
						print $buf;
						print '</td></tr>';
				
					}
				
				print '</table>';

				print '</td>';

				

			print '</tr>';
		}
	?>
	</table>


<?php

?>


<h2>User mit WBD-Typ Service/Basis/BDL <u>ohne</u> BWV-ID</h2>

	<?php
		$not_reported_users = $report->getNotWBDReportedUser();
		
		print '<b>insgesamt: </b>' .$not_reported_users[0];
		print '<br><br>';
		print '<table border=1><tr>';
		print '<th>Vorname</th>';
		print '<th>Nachname</th>';
		print '<th>eMail</th>';
		print '<th>status</th>';		
		print '<th>type</th>';		
		print '</tr>';
		
		foreach ($not_reported_users[1] as $entry) {
			print '<tr>';
			
			if(! $entry['is_active']){
				print '<td><strike>' .$entry['firstname'] .'</strike></td>';
				print '<td><strike>' .$entry['lastname'] .'</strike></td>';

			} else {
				print '<td>' .$entry['firstname'] .'</td>';
				print '<td>' .$entry['lastname'] .'</td>';
			}


			print '<td>' .$entry['email'] .'</td>';
			print '<td>' .$entry['agent_status'] .'</td>';
			print '<td>' .$entry['wbd_type'] .'</td>';
			print '</tr><tr><td colspan=2></td>';
			print '<td colspan=3><table border=1 width="100%">';
				$sems = $report->getSeminarsForUser($entry['user_id'])[1];
				foreach ($sems as $sem){
					print '<tr>';
					print '<td>' .$sem['title'] .'</td>';
					print '<td>' .$sem['begin_date'] .'</td>';
					print '<td>' .$sem['end_date'] .'</td>';					
					print '<td>' .$sem['credit_points'] .'</td>';					
					print '</tr>';
				}
			
			print '</table></td></tr>';
			
		}
		
		print '</table>';
		
	?>			


<h2>User, die die WBD-Anmeldung bejaht haben und auf "kein Service" bzw. "-empty-" stehen</h2>
<?php
		$not_reported_users = $report->getNotWBDReportedUsersWithRegistration();
		
		print '<b>insgesamt: </b>' .$not_reported_users[0];
		print '<br><br>';
		print '<table border=1><tr>';
		print '<th>Vorname</th>';
		print '<th>Nachname</th>';
		print '<th>eMail</th>';
		print '<th>status</th>';		
		print '<th>type</th>';
		print '<th>Rollen</th>';
		print '</tr>';

		foreach ($not_reported_users[1] as $entry) {
			print '<tr>';
			if(! $entry['is_active']){
				print '<td><strike>' .$entry['firstname'] .'</strike></td>';
				print '<td><strike>' .$entry['lastname'] .'</strike></td>';

			} else {
				print '<td>' .$entry['firstname'] .'</td>';
				print '<td>' .$entry['lastname'] .'</td>';
			}


			print '<td>' .$entry['email'] .'</td>';
			
			print '<td>';
			if($entry['agent_status'] == '-empty-'){
				$user_id = $entry['user_id'];
				//$lnk = "https://generali-onlineakademie.de/ilias.php?ref_id=7&admin_mode=settings&obj_id=$user_id&cmd=view&cmdClass=ilobjusergui&cmdNode=1v:i8&baseClass=ilAdministrationGUI";
				$lnk = "https://generali-onlineakademie.de/ilias.php?ref_id=7&admin_mode=settings&obj_id=$user_id&cmd=view&cmdClass=ilobjusergui&cmdNode=1z:ih&baseClass=ilAdministrationGUI";
				print '<a href="' .$lnk .'"">';
				print $entry['agent_status'];
				print '</a>';
					
			} else {
				print $entry['agent_status'];
			}
			
			print '</td>';
			
			//print '<td>' .$entry['wbd_type'] .'</td>';
			print '<td>';
			if($entry['wbd_type'] == '-empty-'){
				$user_id = $entry['user_id'];
				//$lnk = "https://generali-onlineakademie.de/ilias.php?ref_id=7&admin_mode=settings&obj_id=$user_id&cmd=view&cmdClass=ilobjusergui&cmdNode=1v:i8&baseClass=ilAdministrationGUI";
				$lnk = "https://generali-onlineakademie.de/ilias.php?ref_id=7&admin_mode=settings&obj_id=$user_id&cmd=view&cmdClass=ilobjusergui&cmdNode=21:ij&baseClass=ilAdministrationGUI";
				print '<a href="' .$lnk .'"">';
				print $entry['wbd_type'];
				print '</a>';
					
			} else {
				print $entry['wbd_type'];
			}


			print '<td>';
			print implode('<br>', array_values($report->getGlobalRolesForUser($entry['user_id'])));
			print '</td>';
			print '</tr>';
			
		}
		
		print '</table>';
		
	?>			





</body>
</html>

