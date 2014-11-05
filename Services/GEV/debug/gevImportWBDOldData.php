<?php
/**
* Debug stuff.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

$LIVE = True;


die();


//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/debug', '', $basedir);
chdir($basedir);

if(! $LIVE) {
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

		
	
/*
	select row_id, crs_id,title, type, begin_date, end_date, wbd_topic  from hist_course where hist_historic=0 and crs_id in (
	SELECT distinct crs_id  FROM `hist_usercoursestatus` WHERE crs_id < 0 and  `row_id`   IN (9070,10295,10297,10296,8413,10450,8303,8302,9775,6965,6729,4410,7393,10100,10514,7300,4371,10909,8090,9285,5983,5980,5981,8868,8098,9939,10582,6484,6843,8907,9237,6063,8496,8258,6099,5460,7047,7128,6091,6090,7124,8812,7731,6097,10350,6797,4730,4731,4736,5262,10190,4735,5300,6630,6798,6752,10804,5917,10801,8233,8553,8230,6989,8234,8602,8601,6982,6981,4522,8783,4656,10055,6382,8989,7638,10458,9501,8292,7637,7635,7888,8738,8735,10332,8042,8043,8512,8666,5004,9524,9170,9255,9252,5670,7909,6931,7863,8698,7861,5771,8125,9480,8123,8122,5777,8692,4620,4428,6056,4817,7703,4815,7701,5574,7908,6506,5570,9558,4958,9413,10171,10960,9846,9555,9554,6079,9556,9731,8361,9733,9732,9736,9738,4378,10419,10422,7344,9329,5139,7341,9322,9631,10797,5126,5130,8941,8940,8943,7785,7269,8194,8431,7264,5707,8712,9312,6204,6206,7558,7417,7415,7418,7419,7555,10858,5675,10008,5082,6802,10009,9745,6610,4772,10565,7005,9931,10093,6891,10097,10847,5223,5221,5189,5224,9942,7373,8499,8931,8933,5810,9948,8830,8235,7395,10854,7397,5906,4619,6894,4610,4611,6898,4613,7675,7674,4617,4616,7078,10059,5351,5350,6106,10058,6103,5455,7072,6100,8439,9189,5929,6976,6974,6975,10194,6971,9445,6076,9374,7540,5531,7828,10723,5535,4469,8242,7821,4462,7542,9819,6109,7110,7963,4917,6152,4663,8814,9581,8763,6033,7181,7182,7201,5756,5172,7077,9200,8389,10208,9178,6625,7957,10698,10348,4228,4229,8983,10593,5748,7228,4226,4227,8013,10346,8011,10344,6570,9839,9651,6574,4380,7592,7591,9983,8248,4983,9563,7759,4984,4989,7754,4860,10744,10743,7750,7753,4865,9242,9066,9068,10284,10766,10965,10650,8332,9666,9665,5990,10768,9700,10963,10446,7472,8501,6711,10961,6168,9557,5873,7313,10592,8979,7905,7521,5697,4482,10595,9036,10598,6855,4310,7878,6853,10261,6459,7855,6858,6142,9016,9010,10236,4793,7037,5494,7133,7135,7134,5492,5493,9384,5319,7910,4729,6299,9382,5871,5826,4724,6661,6666,4720,9873,5968,8805,5844,5843,5840,7011,5965,6350,9575,5947,8203,4549,4621,4622,7626,7623,7789,7786,8486,4543,6979,7629,7783,8357,7140,7141,10465,8720,8725,8053,9248,4537,8057,8504,9127,10461,10302,10652,4435,4436,4430,6739,8299,7912,7911,7859,4509,6920,9870,6539,4945,6336,5548,7712,4538,4539,7717,4535,5541,6932,4531,8615,8614,9401,8611,8599,8596,6362,10622,8595,6219,8591,9335,8377,8375,5073,10157,9332,10159,9338,5890,7988,8576,5893,9238,8478,8572,5897,10398,5103,9236,8605,8246,10016,8472,7566,6403,8082,6404,6384,5717,7211,10099,8186,8446,10511,6216,6386,7408,6211,8329,10495,7403,10276,10272,10270,6811,6813,9058,6744,4765,6629,6626,9057,10121,6620,6621,7325,10337,7327,10088,10089,10084,8321,10087,10080,10082,8922,8920,5802,9714,5807,8929,6937,7503,5416,7669,7102,6493,9922,6862,7666,5363,9600,4710,5365,4888,7064,8226,5892,10827,5934,5933,5931,10394,8570,6101,10557,10946,6005,4301,6962,8251,8250,6098,6966,9477,8150,7389,7249,4578,5100,9249,8476,7873,6232,4677,7331,9369,10011,6009,8658,4769,8652,9379,5064,5067,5066,10729,10351,10352,5345,10725,8438,10010,10721,5068,4886,9376,10186,7961,9820,5755,6913,9176,9984,9174,9982,6919,8536,8537,4875,7440,5593,7442,8447,7444,7446,10017,7448,6339,6563,7728,8986,9577,9576,6314,4974,8981,5136,8187,8548,5393,10533,10534,9096,7215,9652,10129,9718,8440,8328,8016,9304,6700,8327,9710,9300,6704,8322,4282,4283,4281,4284,4285,8885,8886,5094,5096,7248,4497,4495,7204,4491,6590,9488,5324,6676,6179,5482,9397,9396,4750,6777,5241,8817,8816,5870,9967,5876,9961,5875,10863,10865,8079,8129,8100,8748,8747,6640,5488,8786,7651,7655,9124,9458,5589,7795,5432,7097,5430,7092,7093,5435,5439,7153,8690,8070,8479,6681,9565,10318,7292,4276,7920,7921,10310,4279,10645,9506,6509,8060,5557,4379,4447,8264,6528,6952,6481,9865,10745,4374,4816,10341,4861,4680,5648,4938,6296,6295,6294,7798,6292,4885,8436,8664,6191,6192,7994,10192,6741,7998,6050,9430,10004,5117,4225,5110,10169,10472,10163,10162,10574,8565,5880,10369,5640,4859,9591,9590,7514,9592,4994,6556,8274,7574,7577,7576,9815,9816,6558,8815,5724,9813,7777,6221,4848,4597,4594,8286,7470,4845,4846,6229,6517,4843,4923,4922,8810,6639,6731,8316,8317,10463,6905,8541,10501,7320,4790,9046,4792,4694,8084,5837,9291,7333,9294,8088,5838,4957,8911,4954,8914,9880,8853,7205,8919,7168,9888,9730,4775,8335,6531,7112,7917,7115,6089,9222,6081,4707,10898,4705,4704,4703,5271,4701,7052,7051,7056,6641,10813,10817,10815,10148,5332,10819,10957,9643,8225,7807,7808,6163,8190,6991,5518,4566,4565,5138,6014,7608,4936,4643,7605,8876,8549,8394,5805,6230,6491,9963,7890,7891,9263,10324,10179,10328,10041,8134,10380,10732,4894,9852,8681,6903,7978,5768,8685,7975,5764,8689,9146,8526,8132,8133,8978,6264,5568,6263,7523,5564,6511,10076,5563,9637,5168,10644,5691,6062,9549,8050,6304,9544,7349,6300,8192,7237,10132,9613,9084,9083,9082,9728,8358,8359,7690,9319,7918,10034,8352,10036,7659,10030,9311,9310,8956,7276,8953,8898,8897,4294,5143,8890,7426,10023,7422,7420,4959,7232,9799,10321,6834,5048,5701,6600,6601,6603,9307,9035,10253,9039,10255,10256,10856,8547,4748,5941,7756,6764,6763,4746,5236,5237,9971,4892,8829,5861,10734,10043,8039,8771,6886,7641,6880,7649,6888,7080,7083,5429,7084,5343,7167,7165,5039,10922,10921,10927,6690,7749,6495,8171,8173,8174,7286,7285,7583,9106,7938,7289,10670,10436,4698,4450,10376,8277,8276,4459,6940,8273,6944,4691,4690,4693,5765,4275,4925,6346,4699,8527,4929,8102,10770,4515,9429,6185,8677,4471,6180,9420,6022,9424,5160,5288,9603,9350,5164,5165,9607,5167,10079,10078,9359,8390,8391,7094,7236,9190,8550,5736,8008,10316,4856,8004,6262,5040,10779,7588,5639,9809,4853,7586,4394,4442,6467,7469,9516,6330,10777,7744,7745,7463,4584,4854,7467,9518,9691))
*/	
	
	
	}




	
}




print '<pre>';

$sem_many_matches = array();






$import = new gevImportOldData();

$import->rematchWBDTopic();

die();

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
	/*
	if(count($matches) == 1){
		$sem_ok[] = $matches[0];
	}
	*/

}

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



//print_r( array_diff_assoc($import->sem_bday_matches, $import->sem_ok));

// !!!!!!!!!!!
	//$import->resetDB();
// !!!!!!!!



foreach($import->sem_ok as $rec){
	$crs_id = $import->importSeminar($rec);

	$import->assignUserToSeminar($rec, $crs_id);
	$import->setReported($rec['id']);
}


printToTable($import->sem_ok);




/*
//update (old) courses for historizign of wbd study_contents
require_once("Modules/Course/classes/class.ilObjCourse.php");

$res = $ilDB->query("SELECT DISTINCT crs_id FROM hist_course");
while ($rec = $ilDB->fetchAssoc($res)) {
	$crs = new ilObjCourse($rec["crs_id"], false);
	$crs->update();
}

*/


?>
