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
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


require_once("./include/inc.header.php");

$UMLAUT_REPLACEMENT = array(
	'ä' => 'ae',
	'ü' => 'ue',
	'ö' => 'oe',
	'ß' => 'ss',
	'é' => 'e'
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
		print '<td>';
		print join(array_values($entry),'</td><td>');
		print '</td>';
		print '</tr>';
	}
	print '</table>';
}


class gevImportOldData {

	public function __construct() {
		global $ilUser, $ilDB;
		global $ilClientIniFile;

		$this->db = &$ilDB;
		$this->user = &$ilUser;

		$this->importdata = array();


		$this->sem_no_user_matches = array();
		$this->sem_name_matches = array();
		$this->sem_bday_matches = array();
		$this->sem_nr_matches = array();
		$this->sem_both_matches = array();


		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$host = "localhost";
		$user = "root";
		$pass = "s09e10";
		$name = "gev_ivimport";

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
		$sql = 'SELECT * FROM wbd_altdaten ORDER BY name';
		$result = mysql_query($sql, $this->importDB);
		while($record = mysql_fetch_assoc($result)) {
			$this->importdata[] = $record;
		}
	}


	public function matchUser($rec){

		//users that match the name
		$sql = "SELECT * FROM usr_data_import WHERE"; //user_table
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
			$this->sem_name_matches[] = $rec;
		}

		while($record = $this->db->fetchAssoc($result)) {
			$match_bday = False;
			$match_nr = False;


			if($rec['Geburtsdatum']){
				$geb = explode('.', $rec['Geburtsdatum']);
				$dat = $geb[2] .'-' .$geb[1] .'-' .$geb[0];
				if ($record['birthday'] == $dat) {
					$match_bday = True;
					$record['match_bday'] = 1;
				}
			} 

			if($rec['Agenturnummer']){
				// has user job_nr that matches?
				require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
				$uutils = gevUserUtils::getInstanceByObjOrId($record['usr_id']);
				if((string)$uutils->getJobNumber() == $rec['Agenturnummer']){
					$match_nr = True;
					$record['match_nr'] = 1;
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
				$ret[] = $record;
			}
			
		}



		return $ret;

	}

	
}




print '<pre>';


$sem_ok = array();
$sem_many_matches = array();

$import = new gevImportOldData();
$import->getOldData();

foreach ($import->importdata as $rec) {
/*
	print '<hr><b>' .$rec['Vorname'] .' ' .$rec['Name'] .'</b>';
	print '<br>' .$rec['Geburtsdatum'] .' - ' .$rec['Agenturnummer'];
*/
	$matches = $import->matchUser($rec);

	if(count($matches) > 1){
		$sem_many_matches[] = $rec;
		//print_r($matches);
	}
	if(count($matches) == 1){
		$sem_ok[] = $rec;
	}

}

print '<hr><hr>';

print '<br>sem_no_user_matches: ' .count($import->sem_no_user_matches);
print '<br>sem_name_matches: ' .count($import->sem_name_matches);
print '<br>sem_bday_matches: ' .count($import->sem_bday_matches);
print '<br>sem_nr_matches: ' .count($import->sem_nr_matches);
print '<br>sem_both_matches: ' .count($import->sem_both_matches);

print '<br>';
print '<br>sem_many_matches: ' .count($sem_many_matches);
print '<br>sem_ok: ' .count($sem_ok);



die();







?>
