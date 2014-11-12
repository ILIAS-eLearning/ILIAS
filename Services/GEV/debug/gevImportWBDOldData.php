<?php
/**
* Debug stuff.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

$LIVE = True;


//die();


//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/debug', '', $basedir);
chdir($basedir);

if( $LIVE) {
	//context w/o user
	require_once "./Services/Context/classes/class.ilContext.php";
	ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
	require_once("./Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();
}

require_once("./include/inc.header.php");



$UMLAUT_REPLACEMENT = array(
	'ä' => 'ae',
	'ü' => 'ue',
	'ö' => 'oe',
	'ß' => 'ss',
	'é' => 'e'
);


$CORRECTIONS = array(
			'Privat-Vorsorge-Lebens-/Rentenversicherung' => 'Privat-Vorsorge-Lebens-/Rentenversicherung',
			'Privat-Vorsorge-Lebens-/Rentenverischerung' => 'Privat-Vorsorge-Lebens-/Rentenversicherung',
			'Privat-Vorsorge-Kranken-/Pflegeversicherung' => 'Privat-Vorsorge-Kranken-/Pflegeversicherung',

			'Firmenkunden -Sach-/Schadenversicherung' => 'Firmenkunden-Sach-/Schadenversicherung',
			'Firmenkunden-Sach-/Schadenversicherung' => 'Firmenkunden-Sach-/Schadenversicherung',

			'Spartenübergreifend' => 'Spartenübergreifend',
			'spartenübergreifend' => 'Spartenübergreifend',

			'Firmenkunden-Vorsorge (bAV/Personenversicherung' => 'Firmenkunden-Vorsorge (bAV/Personenversicherung)',
			'Firmenkunden-Vorsorge (bAV/Personenversicherung)' => 'Firmenkunden-Vorsorge (bAV/Personenversicherung)',
			'Firmenkunden-Vorsorge (bAV/Personenversicheurng' => 'Firmenkunden-Vorsorge (bAV/Personenversicherung)',
			'Firmenkunden-Vorsorge (bav/Personenversicherung)'=> 'Firmenkunden-Vorsorge (bAV/Personenversicherung)',


			'Firmenkunden-Vorsorge-Lebens-/Rentenersicherung'  => 'Firmenkunden-Vorsorge (bAV/Personenversicherung)',
			'Firmenkunden-Vorsorge-Lebens-/Rentenversicherung'  => 'Firmenkunden-Vorsorge (bAV/Personenversicherung)',

			'Beratungskompetenz' => 'Beratungskompetenz',

			'Privat-Sach-/Schadenversicherung' => 'Privat-Sach-/Schadenversicherung'
		);



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


class gevImportOldData {

	public function __construct() {
		global $ilUser, $ilDB;
		global $ilClientIniFile;
		global $LIVE;

		$this->db = &$ilDB;
		$this->user = &$ilUser;

		$this->importdata = array();


		$this->sem_no_user_matches = array();
		$this->sem_name_matches = array();
		$this->sem_bday_matches = array();
		$this->sem_nr_matches = array();
		$this->sem_both_matches = array();

		$this->sem_ok = array();


		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');
/*
		if(! $LIVE){
			$host = "localhost";
			$user = "root";
			$pass = "s09e10";
			$name = "gev_ivimport";
		}
*/
		$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$this->importDB = $mysql;
	}


	public function fuzzyName($name){
		$name = strtolower($name);
		global $UMLAUT_REPLACEMENT;
		foreach ($UMLAUT_REPLACEMENT as $char=>$rep) {
			if (strpos($name, $char) !== False){
				$name = str_replace($char, $rep, $name);
			}
		}
		return $name;
	}

	public function getOldData(){
		$sql = 'SELECT * FROM wbd_altdaten 
				WHERE reported != 1
				ORDER BY name';
		//$sql .= ' LIMIT 250';

		$result = mysql_query($sql, $this->importDB);
		while($record = mysql_fetch_assoc($result)) {
			$this->importdata[] = $record;
		}
	}

	public function setReported($id){
		$sql = 'UPDATE wbd_altdaten 
				SET  reported = 1
				WHERE id=' .$id;

		mysql_query($sql, $this->importDB);
	}


	public function matchUser($rec){

		//users that match the name
		//$sql = "SELECT * FROM usr_data_import WHERE"; //user_table
		$sql = "SELECT * FROM usr_data WHERE"; //user_table
		if($LIVE){
			$sql = "SELECT * FROM usr_data WHERE"; //user_table
		}
		$sql .= " (LOWER(firstname) = '" .strtolower(trim($rec['Vorname'])) ."'";
		$sql .= " OR LOWER(firstname) = '" .$this->fuzzyName(trim($rec['Vorname'])) ."')";
		$sql .= " AND";
		$sql .= " (LOWER(lastname) = '" .strtolower(trim($rec['Name'])) ."'";
		$sql .= " OR LOWER(lastname) = '" .$this->fuzzyName(trim($rec['Name'])) ."')";

		//print $sql .'<br>';
		$ret = array();
		$result = $this->db->query($sql);

		if($this->db->numRows($result) == 0){
			$this->sem_no_user_matches[] = $rec;
		}else{
			$rec['matched_user_id'] = array();
			$this->sem_name_matches[] = $rec;
		}

		while($record = $this->db->fetchAssoc($result)) {
			$match_bday = False;
			$match_nr = False;

			$rec['match_bday'] = 0;
			$rec['match_nr'] = 0;
			$rec['matched_user_id'][] = $record['usr_id'];

			if($rec['Geburtsdatum']){
				$geb = explode('.', $rec['Geburtsdatum']);
				$dat = $geb[2] .'-' .$geb[1] .'-' .$geb[0];
				if ($record['birthday'] == $dat) {
					$match_bday = True;
					$rec['match_bday'] = 1;
				}
			} 

			if($rec['Agenturnummer']){
				// has user job_nr that matches?
				require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
				$uutils = gevUserUtils::getInstanceByObjOrId($record['usr_id']);
				if((string)$uutils->getJobNumber() == $rec['Agenturnummer']){
					$match_nr = True;
					$rec['match_nr'] = 1;
				}
			}


			if($match_bday){
				$this->sem_bday_matches[] = $rec;
			}
			if($match_nr){
				$this->sem_nr_matches[] = $rec;
			}

			if($match_bday && $match_nr){
				$this->sem_both_matches[] = $rec;
			}

			
			if($match_bday || $match_nr){
				$ret[] = $rec;
			}

		}
		
		if(count($ret) == 1){
			$this->sem_ok[] = $ret[0];
		}

		return $ret;
	}

	public function resetDB(){
		$tables = array(
			'hist_course',
			'hist_usercoursestatus'
		);
		foreach ($tables as $table) {
			$sql = "DELETE FROM $table WHERE crs_id < 0";
			//$this->db->query($sql);
		}
		
		$sql = 'UPDATE wbd_altdaten 
				SET  reported = 0';
		//mysql_query($sql, $this->importDB);

	}


	/*
	new entry for hist_course (! with negative id)
	//returns course_id
	*/
	public function importSeminar($rec){
		global $CORRECTIONS;
		$title = $rec['Titel'];
		
		$type = $rec['Lernart']; //validate/check/map
		$wbd_topic = $rec['Inhalt']; //validate/check/map

		$wbd_topic = $CORRECTIONS[$wbd_topic];


		$begin_date = date('Y-m-d', strtotime($rec['Beginn']));
		$end_date = date('Y-m-d', strtotime($rec['Ende']));
		$creator_id = $rec['creator_id'];

		$sql = "SELECT crs_id FROM hist_course WHERE 
			title = '$title'
			AND
			begin_date = '$begin_date'
			AND 
			end_date = '$end_date'
		";
		$result = $this->db->query($sql);
		if($this->db->numRows($result) > 0){

			$record = $this->db->fetchAssoc($result);
			return $record['crs_id'];
		}
		
		//new seminar
		$sql = "SELECT crs_id FROM hist_course WHERE 
				crs_id < 0
				ORDER BY crs_id ASC
				LIMIT 1
		";	
		$result = $this->db->query($sql);
		$record = $this->db->fetchAssoc($result);
		
		$crs_id = $record['crs_id'] - 1;
		//start with 4 digits
		if($crs_id == -1){
			$crs_id = -1000;
		}

		$next_id = $this->db->nextId('hist_course');

		
		/*
		hours
 		venue
 		provider
 		*/
		$sql = "INSERT INTO hist_course
			(
				row_id,
				hist_version,
				created_ts,
				creator_user_id,
		 		is_template,
		 		crs_id,
		 		title,
		 		type, 
		 		wbd_topic,
		 		begin_date,
		 		end_date,
		 		
		 		custom_id,
		 		template_title,
		 		max_credit_points
			) 
			VALUES 
			(
				$next_id,
				0,
				NOW(),
				$creator_id,
				'Nein',
				$crs_id,
				'$title',
				'$type',
		 		'$wbd_topic',
		 		'$begin_date',
		 		'$end_date',
		 		'-empty-',
		 		'-empty-',
		 		'-empty-'
			)";

			
			if(! $this->db->query($sql)){
				die($sql);
			}




		return $crs_id;


	}


	/*
	hist_usercoursestatus
	*/
	public function assignUserToSeminar($rec, $crs_id){


		$usr_id = $rec['matched_user_id'][0];
		$creator_id = $rec['creator_id'];
		$begin_date = date('Y-m-d', strtotime($rec['Beginn']));
		$end_date = date('Y-m-d', strtotime($rec['Ende']));
		$next_id = $this->db->nextId('hist_usercoursestatus');

		$credit_points = $rec['WP'];
		if(!is_numeric($credit_points)){
			$credit_points = 0;
		}

		$sql = "INSERT INTO hist_usercoursestatus
			(
				row_id,
				created_ts,
				creator_user_id,
				usr_id,
		 		crs_id,
		 		credit_points,
		 		hist_historic,
		 		hist_version,
		 		function,
		 		booking_status,
		 		participation_status,
		 		begin_date,
		 		end_date,
		 		bill_id,
		 		certificate
			) 
			VALUES 
			(
				$next_id,
				UNIX_TIMESTAMP(),
				$creator_id,
				$usr_id,
				$crs_id,
				$credit_points,
				0,
				0,
				'Mitglied',
				'gebucht',
				'teilgenommen',
				'$begin_date',
				'$end_date',
				-1,
				-1
			)";
		
			if(! $this->db->query($sql)){
				die($sql);
			}

	
	}


	public function rematchWBDTopic(){
		global $CORRECTIONS;
		$sql = "SELECT row_id, title, type, begin_date, end_date FROM hist_course 
			WHERE wbd_topic = ''
			AND
			crs_id < 0";

		print $sql;
		
		$result = $this->db->query($sql);
		while($rec = $this->db->fetchAssoc($result)){
			$title =  $rec['title'];
			$begin = date('d.m.Y', strtotime($rec['begin_date']));
			$end = date('d.m.Y', strtotime($rec['end_date']));
			
			$q = "SELECT Inhalt FROM wbd_altdaten WHERE
				Titel = '$title'
				AND 
				Beginn = '$begin'
				AND
				Ende = '$end'
			";
			$result2 = mysql_query($q, $this->importDB);
			$record = mysql_fetch_assoc($result2);
			$topic = $record['Inhalt'];
			
			if(! array_key_exists($topic, $CORRECTIONS)){
				print "<br>############ $topic ###############<br>";
				print_r($CORRECTIONS);
			}else{
				$row_id = $rec['row_id'];
				$correct_topic = $CORRECTIONS[$topic];
				$upSql = "UPDATE hist_course SET wbd_topic = '$correct_topic' WHERE row_id=$row_id";
				print '<br>';
				print $upSql;
				$this->db->query($upSql);
			}
			
		}
	}



	public function rectifyOKZforAltdaten(){

		$sql = 'SELECT hist_usercoursestatus.row_id as usrcrsRow, hist_usercoursestatus.usr_id, '
			. " hist_user.okz AS userOKZ"
			. " FROM hist_usercoursestatus"
			. " INNER JOIN hist_user ON hist_usercoursestatus.usr_id = hist_user.user_id"
			. " WHERE hist_user.hist_historic = 0 "
			. " AND hist_user.okz != '-empty-' "
			. " AND hist_usercoursestatus.creator_user_id = -100 "
			. " AND hist_usercoursestatus.OKZ = '' "
			. " AND hist_usercoursestatus.function = 'Mitglied' ";
		

		$result = $this->db->query($sql);
		while($record = $this->db->fetchAssoc($result)) {
			$okz = $record['userokz'];
			$row_id = $record['usrcrsrow'];
			$q = "UPDATE hist_usercoursestatus SET okz='$okz' WHERE row_id=$row_id;";
			print $q;
			print '<br>';
			$this->db->query($q);
		}
		return $ret;
	}


	
}




/*
------------------------------------
run: 
*/



$sem_many_matches = array();

//die();

$import = new gevImportOldData();



$import->getOldData();

foreach ($import->importdata as $rec) {
	$matches = $import->matchUser($rec);

	if(count($matches) > 1){
		$sem_many_matches[] = $rec;
	}
}

print '<pre>';
print '<hr><hr>';

print '<br>sem_no_user_matches: ' .count($import->sem_no_user_matches);
print '<br>sem_name_matches: ' .count($import->sem_name_matches);
print '<br>sem_bday_matches: ' .count($import->sem_bday_matches);
print '<br>sem_nr_matches: ' .count($import->sem_nr_matches);
print '<br>sem_both_matches: ' .count($import->sem_both_matches);

print '<br>';
print '<br>sem_many_matches: ' .count($sem_many_matches);
print '<br>sem_ok: ' .count($import->sem_ok);
print '<hr>';


// !!!!!!!!!!!
//	//$import->resetDB();
// !!!!!!!!!!!


die();

foreach($import->sem_ok as $rec){
	$crs_id = $import->importSeminar($rec);

	$import->assignUserToSeminar($rec, $crs_id);
	$import->setReported($rec['id']);
}

$import->rectifyOKZforAltdaten();
//$import->rematchWBDTopic();


printToTable($import->sem_ok);

?>
