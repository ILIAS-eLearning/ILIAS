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


class gevImportOldData {

	public $static = array(

	/*
		array(
			'name' => 'Adamzig',
			'vorname' => 'Nicole',
			'agenturnummer' => '103158',
			'id' => 7
		),
		array(
			'name' => 'Pack',
			'vorname' => 'Jörg',
			'geb' => '17.02.1964',
	    	'agenturnummer' => '111285',
			'id' => 1181 
		),

		array(
			'name' => 'Reinhardt',
			'vorname' => 'Dierk',
			'geb' => '03.03.1963',
			'id' => 1296 
		),

		array(
			'name' => 'Rohn',
			'vorname' => 'André',
			'geb' => '11.01.1969',
			'id' =>  1339
		),
		array(
			'name' => 'Rohn',
			'vorname' => 'Andre',
			'geb' => '11.01.1969',
			'id' =>  1339
		),

		array(
			'name' => 'Spar',
			'vorname' => 'Anton',
			'geb' => '25.08.1959',
			'id' =>  1577
		),

		array(
			'name' => 'Hoffmann',
			'vorname' => 'Berthold',
			'agenturnummer' => '391603',
			'id' =>  671

		),
	[name] => Krämer
    [vorname] => Martin
    [agenturnummer] => 861171

    [name] => Maier 
    [vorname] => Sascha
    [agenturnummer] => 504520
    
    [name] => Maier
    [vorname] => Thomas
    [agenturnummer] => 112986
    
	[name] => Maier 
    [vorname] => Sascha
    [agenturnummer] => 504520
    */


	);

	public function __construct() {
		global $ilUser, $ilDB;

		$this->db = &$ilDB;;
		$this->user = &$ilUser;

		$this->importdata = array();

	}

	public function getOldData(){
		$sql = 'SELECT * FROM wbd_altdaten ORDER BY name';
		$result = $this->db->query($sql);
		while($record = $this->db->fetchAssoc($result)) {
			$this->importdata[] = $record;
		}
	}


	public function matchUser($nr, $rec){

		$found_static = False;
		foreach ($this->static as $entry) {
			if(	   $entry['vorname'] == trim($rec['vorname'])
				&& $entry['name'] == trim($rec['name'])
			){
		
				if(
					($entry['geb'] && $entry['geb'] == $rec['geburtsdatum'])
					|| 
					($entry['agenturnummer'] && $entry['agenturnummer'] == $rec['agenturnummer'])
				) {
					$found_static = $entry['id'];
				}

			}
		}

		if($found_static){
			$sql = "SELECT * FROM usr_data` WHERE usr_id = " .$found_static;
		} else {
			$sql = "SELECT * FROM `usr_data` WHERE"; //user_table
			$sql .= " firstname = '" .trim($rec['vorname']) ."'";
			$sql .= " AND";
			$sql .= " lastname = '" .trim($rec['name']) ."'";
		}


		$ret = array();
		$result = $this->db->query($sql);
		while($record = $this->db->fetchAssoc($result)) {
			$ret[] = $record;
		}
		return $ret;
	}

	public function matchUserAgain($rec){

		$ret = array();
		
		if(trim($rec['geburtsdatum'])!=''){
			$sql = "SELECT * FROM `usr_data` WHERE";
			$sql .= " firstname = '" .trim($rec['vorname']) ."'";
			$sql .= " AND";
			$sql .= " lastname = '" .trim($rec['name']) ."'";
			$geb = explode('.', $rec['geburtsdatum']);
			$dat = $geb[2] .'-' .$geb[1] .'-' .$geb[0];
			$sql .= "AND birthday = '$dat'";
			
			$result = $this->db->query($sql);
			while($record = $this->db->fetchAssoc($result)) {
				$ret[] = $record;
			}
		} 

		print 'recheck on birthday: ' .count($ret);

		if(count($ret) != 1 && $rec['agenturnummer']) {
			//check agency
		}

		return $ret;
	}




}

$import = new gevImportOldData();
$import->getOldData();
print '<pre>';


$sem_too_many_user_matches = array();
$sem_no_user_matches = array();
$sem_ok = array();


foreach ($import->importdata as $nr => $rec) {

	print '<hr><b>' .$rec['vorname'] .' ' .$rec['name'] .'</b>';

	$recheck = False;
	$matches = $import->matchUser($nr, $rec);
	if(count($matches) > 1){
		print '<br>too many matches.';
		$recheck = True;
		$matches = $import->matchUserAgain($rec);
	}
	//still?
	if(count($matches) > 1){
		print '<br>still too many hits';
		print_r($rec);
		print '<br>';
		print_r($matches); 
	
		$sem_too_many_user_matches[] = $rec;
	} 

	if(count($matches) < 1){
		print '<br>no (more) matches';
		$sem_no_user_matches[] = $rec;
		if($recheck){
			print_r($rec);
		}
	}
	if(count($matches) == 1){
		print '<br>ok/resolved.';
		$sem_ok[] = $rec;
	}
	
	
}

print '<br>broken (too many): ' .count($sem_too_many_user_matches);
print '<br>broken (no user): ' .count($sem_no_user_matches);
print '<br>ok: ' .count($sem_ok);

die();
















$manyhits = array();
$nohits = array();

$unique_users = array();
$done_users = array();
$done_entries = array();


foreach ($import->importdata as $nr => $rec) {

	$usr = trim($rec['vorname']).'_'.trim($rec['name']);

	if(! in_array($usr, $unique_users)){

		print '<b>' .$rec['vorname'] .' ' .$rec['name'] .'</b><br>';


		$matches = $import->matchUser($rec);

		if(count($matches) == 0){
			$nohits[] = $rec;
			print 'NO HIT<br>';
			//print_r($matches);
		}
		if(count($matches) > 1){
			$manyhits[] = $rec;
			print 'TOO MANY HITS<br>';

			print_r($rec);
			print '<br> ------------------------------- <br>';
			print_r($matches);
		}
		
		if(count($matches) == 1){
			print 'ok.';
			//print_r($matches);
			$done_entries[] = $rec;
			$done_users[] = $usr;
		}
			
		print '<hr>';

		$unique_users[] = $usr; 
	} else {
		$done_entries[] = $rec;
	}

	/*if($nr > 100){
		die();
	}
	*/

}



print 'total seminars: ' .count($import->importdata);
print '<br>unique users: ' .count($unique_users);


print '<br>no hits: ' .count($nohits);
print '<br>many hits: ' .count($manyhits);

print '<br><br>done users: ' .count($done_users);
print '<br>done seminars: ' .count($done_entries);



print '<br><br><i>done.</i>';

?>
