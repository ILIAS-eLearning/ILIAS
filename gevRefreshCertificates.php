<?php
require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
require_once "Modules/Course/classes/class.ilCourseCertificateAdapter.php";
include_once "Services/Certificate/classes/class.ilCertificate.php";
require_once "Services/UserCourseStatusHistorizing/classes/class.ilUserCourseStatusHistorizing.php";

require_once "Services/Init/classes/class.ilInitialisation.php";
ilInitialisation::initILIAS();

class rehistorizeCertificate extends ilUserCourseStatusHistorizing {
	static protected function maybeUpdateCertificate($current, $a_data) {
		return true;
	}
}

class gevRefreshCertificates {
	private $crs_successfull_usrs;
	private $crs_usrs;
	private $opt;

	public function __construct() {
		$this->opt = getopt("f::u::c::ho");
		if(isset($this->opt["h"])) {
			die("help...");
		}

		if(!$this->opt["f"] && !$this->opt["u"] && !$this->opt["c"]) {
			die("No data given. Please use -h to get help.");
		}
		if(isset($opt["f"])) {
			$path = $opt["f"];
			$this->addCrsUsrFromCSV($path);
		} 
		$this->uniqueUsrs();
		$this->filterSuccessfullParticipations();
		$this->updateCertificates();
		
	}

	private function uniqueUsrs() {
		foreach($this->crs_usrs as $crs_id => &$usrs) {
			$usrs = array_unique($usrs);
		} 
	}

	private function addCrsUsr($crs_id, $usr_id) {
		$this->crs_usrs[$crs_id][] = $usr_id;
	}

	private function addCrsUsrFromCSV($path_to_csv) {
		$data = file($path_to_csv);
		foreach($data as &$csv_line) {
			$csv_line = str_getcsv($csv_line,",");
		}
		foreach($data as $crs_usr) {
			$this->addCrsUsr($crs_usr[0],$crs_usr[1]);
		}
	}

	private function getAllSuccessfullParticipations() {
		foreach ($this->crs_usrs as $crs_id => $usrs) {
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$this->crs_successfull_usrs[$crs_id] = $crs_utils->getSuccessfullParticipants();
		}
	}

	private function filterSuccessfullParticipations() {
		if(!$this->crs_usrs) {
			return;
		}
		$this->getAllSuccessfullParticipations();
		foreach ($this->crs_usrs as $crs_id => &$usr_ids) {
			$usr_ids = array_intersect($usr_ids, $crs_successfull_usrs[$crs_id]);
		}
	}

	private function createCertificate($usr_id, $crs_id, $certificate, $overwrite = 0) {
		$cert = 0;
		if($overwrite) {
			$cert = $certificate->outCertificate(array("user_id" => $usr_id), false);
		} elseif (!$this->hasCertificateHistorized($ust_id, $crs_id)) {
			$cert = $certificate->outCertificate(array("user_id" => $usr_id), false);
		}

		if($cert) {
			$case_id = array("crs_id" => $crs_id, "usr_id" => $usr_id);
			$cert = base64_encode($cert);
			$data = array("certificate" => $cert);
			rehistorizeCertificate::updateHistorizedData($case_id, $data);
		}
	}

	private function iterate(closure $at_crs) {
		foreach($this->crs_usrs as $crs_id) {
			$iterate_usrs = function ($at_usr, $additional = null) use ($crs_id) {
				foreach ($this->crs_usrs[$crs_id] as $usr_id) {
					$at_usr($crs_id, $usr_id, $additional);
				}
			};
			$at_crs($crs_id,$iterate_usrs);
		}
	}

	private function hasCertificateHistorized($usr_id, $crs_id) {
		global $ilDB;
		$sql = "SELECT certificate FROM hist_usercoursestatus WHERE crs_id = ".$ilDB->quote($crs_id)
				." AND usr_id = ".$ilDB->quote($usr_id)." AND hist_historic = 0 AND certificate > 0";
		$rec = $ilDB->query($sql);
		if($rec) {
			return true;
		}
	}

	private function updateCertificates() {
		$overwrite = isset($this->opt["o"]);
		$at_usr = function ($crs_id, $usr_id, $certificate) use ($overwrite) {
			$this->createCertificate($crs_id, $usr_id, $certificate, $overwrite);
		};

		$at_crs = function ($crs_id, $iterate_usrs) {

			$course_class = ilObjectFactory::getClassByType('crs');
			$course_obj = new $course_class($crs_id, false);
			$certificate_adapter = new ilCourseCertificateAdapter($course_obj);
			$certificate = new ilCertificate($certificate_adapter);
			$is_complete = $certificate->isComplete();

			if($is_complete) {
				$iterate_usrs($at_usr, $certificate);
			}
		};
	}

	public function getPassedCoursesForUser ($user_id) {
		return;
	}
}

$crs_id = 0;

$course_class = ilObjectFactory::getClassByType('crs');
$course_obj = new $course_class($crs_id, false);
$certificate_adapter = new ilCourseCertificateAdapter($course_obj);
$certificate = new ilCertificate($certificate_adapter);
$is_complete = $certificate->isComplete();

?>