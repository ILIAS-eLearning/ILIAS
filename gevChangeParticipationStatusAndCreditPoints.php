<?php
require_once("Services/Init/classes/class.ilInitialisation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/UserCourseStatusHistorizing/classes/class.ilUserCourseStatusHistorizingAppEventListener.php");
ilInitialisation::initILIAS();
global $ilUser;
if(!gevUserUtils::getInstance($ilUser->getId())->isAdmin()) {
	echo '<span style="background-color:#8B0000; color:#FFF">Sie sind kein Admin!</span><br/><br/>';
	exit;
}

class gevChangeParticipationStatusAndCreditPoints {

	public function __construct() {
		global $ilDB, $ilLog;

		$this->gIlDB = $ilDB;
		$this->gIlLog = $ilLog;
		$this->txtPath = "stateAndPoints.txt";
	}

	public function run() {
		echo "Daten werden aus der Textdatei gelesen<br/>";
		$crs_ids = $this->readTxt();
		echo "Daten erfolgreich gelesen<br/><br/>";

		if(empty($crs_ids)) {
			echo "Keine Daten zur &Auml;nderung gefunden!<br/><br/>";
			return;
		}

		foreach ($crs_ids as $key => $value) {
			if($value["crs_id"] === null) {
				echo '<span style="background-color:#8B0000; color:#FFF">Dieser Kurs existiert nicht. Reihe im Textdokument: '.$key.'</span><br/><br/>';
				$this->gIlLog->write("gevChangeParticipationStatusAndCreditPoints::run: crs_id was NULL at row ".$key);
				continue;
			}

			echo "####################################<br/>";
			echo "####### Kurs ".$value["crs_id"]."<br/>";
			echo "####################################<br/>";
			$this->crs_utils =  gevCourseUtils::getInstance($value["crs_id"]);

			if($value["cpoints"] === null) {
				echo '<span style="background-color:#8B0000; color:#FFF">Weiterbildungspunkte sind NULL. Reihe im Textdokument: '.$key.'</span><br/><br/>';
				$this->gIlLog->write("gevChangeParticipationStatusAndCreditPoints::run: cpoints was NULL at row ".$key);
				continue;
			}

			if($value["state"] === null) {
				echo '<span style="background-color:#8B0000; color:#FFF">Teilnehmerstatus ist NULL. Reihe im Textdokument: '.$key.'</span><br/><br/>';
				$this->gIlLog->write("gevChangeParticipationStatusAndCreditPoints::run: state was NULL at row ".$key);
				continue;
			}

			$state = $this->getStateCode($value["state"]);
			if($state === null) {
				echo '<span style="background-color:#8B0000; color:#FFF">Unbekannter Teilnehmerstatus. Reihe im Textdokument: '.$key.'</span><br/><br/>';
				$this->gIlLog->write("gevChangeParticipationStatusAndCreditPoints::run: unknown state at row ".$key);
				continue;
			}

			if($state != gevSettings::CRS_URS_STATE_SUCCESS_VAL && $value["cpoints"] != 0) {
				echo '<span style="background-color:#8B0000; color:#FFF">Kurs nicht erfolgreich abgeschlossen und trotzdem sollen Punkte gesetzt werden: '.$key.'</span><br/><br/>';
				$this->gIlLog->write("gevChangeParticipationStatusAndCreditPoints::run: points with no success ".$key);
				continue;
			}

			$max_credit_points = $this->crs_utils->getCreditPoints();
			if($value["cpoints"] > $max_credit_points) {
				echo '<span style="background-color:#8B0000; color:#FFF">Punkte gr&ouml;&szlig;er als maximal M&ouml;glich: '.$key.'</span><br/><br/>';
				$this->gIlLog->write("gevChangeParticipationStatusAndCreditPoints::run: points greater then max ".$key);
				continue;
			}

			

			if($value["user_ids"] === null) {
				echo "Laden aller Teilnhemer, da keine vorgeben wurden.<br/>";
				$usr_ids = $this->crs_utils->getParticipants();

				if($usr_ids === null || empty($usr_ids)) {
					echo '<span style="background-color:#8B0000; color:#FFF">Keine Teilnehmer gefunden.</span><br/><br/>';
					continue;
				}

				echo "Laden der Teilnehmer erfolgreich.<br/><br/>";
			} else {
				$usr_ids = $value["user_ids"];
			}

			$this->changeStatusAndPoints($usr_ids, $state, $value["cpoints"]);
		}
	}

	private function changeStatusAndPoints($usr_ids, $state, $cpoints) {
		foreach ($usr_ids as $key => $usr_id) {
			echo "Daten f&uuml;r Benutzer $usr_id werden aktualisiert.<br/>";

			if(!gevUserUtils::userIdExists($usr_id)) {
				echo '<span style="background-color:#8B0000; color:#FFF">Benutzer nicht in der Datenbank gefunden.</span><br/><br/>';
				continue;
			}

			$this->updateDataTable($usr_id, $state, $cpoints);
			$this->raiseEvent($usr_id);
			echo "Aktualisierung erfolgreich<br/><br/>";
		}
	}

	private function updateDataTable($usr_id, $state, $cpoints) {
		$this->crs_utils->setParticipationStatusAndPoints($usr_id, $state, $cpoints);
	}

	private function raiseEvent($usr_id) {
		$params = array("crs_obj_id" => $this->crs_utils->getId()
						,"user_id" => $usr_id);

		ilUserCourseStatusHistorizingAppEventListener::handleEvent("Services/ParticipationStatus", "setStatusAndPoints", $params);
	}

	private function getStateCode($state) {
		/*const CRS_URS_STATE_SUCCESS		= "erfolgreich";
		const CRS_URS_STATE_SUCCESS_VAL		= "2";
		const CRS_URS_STATE_EXCUSED			= "entschuldigt";
		const CRS_URS_STATE_EXCUSED_VAL		= "3";
		const CRS_URS_STATE_NOT_EXCUSED		= "unentschuldigt";
		const CRS_URS_STATE_NOT_EXCUSED_VAL	= "4";*/

		switch(strtolower($state)) {
			case gevSettings::CRS_URS_STATE_SUCCESS:
					return gevSettings::CRS_URS_STATE_SUCCESS_VAL;
			case gevSettings::CRS_URS_STATE_EXCUSED:
					return gevSettings::CRS_URS_STATE_EXCUSED_VAL;
			case gevSettings::CRS_URS_STATE_NOT_EXCUSED:
					return gevSettings::CRS_URS_STATE_NOT_EXCUSED_VAL;
			default: 
					return null;
		}
	}

	/**
	*FORMAT DER TEXTDATEI
	*
	* CRS_REF_ID#CPOINTS#STATE#USER_ID|USER_ID|USER_ID
	*/
	private function readTxt() {
		$row_regex = "/^([0-9])+#([0-9])+#(erfolgreich|entschuldigt|unentschuldigt)+#([0-9])*(\|([0-9])+)*$/";

		if(!file_exists($this->txtPath)) {
			throw new Exception("gevChangeParticipationStatusAndCreditPoints::run::readText: file not found.");
		}

		$fhandle = fopen($this->txtPath, "r");

		$ret = array();

		while($row = fgets($fhandle)) {
			
			if(!preg_match($row_regex, $row)) {
				throw new Exception("gevChangeParticipationStatusAndCreditPoints::run::readText: row not in required format");
			}

			$data = explode("#",$row);
			
			$crs_id = ($data[0] == "") ? null : gevObjectUtils::getObjId($data[0]);

			$cpoints = ($data[1] == "") ? null : $data[1];
			if($cpoints !== null && !is_numeric($cpoints)) {
				$cpoints = null;
			}

			$state = ($data[2] == "") ? null : $data[2];

			$user_ids_string = str_replace("\n", "", $data[3]);
			$user_ids = null;

			if($user_ids_string != "") {
				$user_ids = explode("|",$user_ids_string);
			}

			$crs = array("crs_id" => $crs_id
						 ,"cpoints" => $cpoints
						 ,"state" => $state
						 ,"user_ids" => $user_ids
						);

			$ret[] = $crs;
		}

		fclose($fhandle);
		return $ret;
	}
}

$module = new gevChangeParticipationStatusAndCreditPoints();
$module->run();