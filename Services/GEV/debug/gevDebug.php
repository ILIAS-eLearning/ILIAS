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
			SELECT * 
			FROM 
				hist_course 
			WHERE 
				crs_id 
			NOT IN 
				(
					SELECT 
						obj_id 
					FROM 
						object_data
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


}






$debug = new gevDebug();
printToTable($debug->getDeletedCourses());


?>
