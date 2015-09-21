<?php
require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
require_once "Services/GEV/Utils/classes/class.gevUserUtils.php";
require_once "Modules/Course/classes/class.ilCourseCertificateAdapter.php";
require_once "Services/Certificate/classes/class.ilCertificate.php";
require_once "Services/UserCourseStatusHistorizing/classes/class.ilUserCourseStatusHistorizing.php";
require_once "Services/ParticipationStatus/classes/class.ilParticipationStatus.php";

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
	private $usr_utils;
	private $opt;

	public function __construct() {
		global $ilUser;
		$this->usr_utils = gevUserUtils::getInstance($ilUser->getId());

		if(!$this->usr_utils->isAdmin()) {
			die("You need to be an admin to use this feature.");
		}

		$this->getParams();

		if(isset($this->opt["h"])) {
			die("You may use c=$crs_id, u=$usr_id f=filename as get-parameters, "
				."to specify a course a user or the corresponding intersection." 
				."Please use o to overwrite old certificates. \n"
				."Operating on csv save data as \n"
				."crs_id;usr_id\n");
		}
		if(!$this->opt["u"] && !$this->opt["c"] && !$this->opt["f"]) {
			die("No data given. Please use the get-prameter h to get help.");
		}

		$set_usr = is_numeric($this->opt["u"]);
		$set_crs = is_numeric($this->opt["c"]);
		$set_path = isset($this->opt["f"]);
		$crs_id = $this->opt["c"];
		$usr_id = $this->opt["u"];
		$path = $this->opt["f"];

		if($set_path) {
			$this->addCrsUsrFromCSV($path);
		}
		if($set_crs && !$set_usr) {
			$this->addCrsUsrFromCrs($crs_id);
		}
		if($set_usr && !$set_crs) {
			$this->addCrsUsrFromUsr($usr_id);
		}
		if($set_usr && $set_crs) {
			$this->addCrsUsrFromCrsUsr($crs_id, $usr_id);
		}

		$this->uniqueUsrs();

		$this->filterSuccessfullParticipations();
		$this->updateCertificates();
		die(var_dump($this->crs_usrs));
	}

	private function addCrsUsrFromCrs($crs_id) {
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		$this->crs_usrs[$crs_id] = $crs_utils->getSuccessfullParticipants();
	}

	private function addCrsUsrFromUsr($usr_id) {
		global $ilDB;
		$status_success = ilParticipationStatus::STATUS_SUCCESSFUL;
		$sql = "SELECT crs_id, user_id FROM crs_pstatus_usr WHERE user_id = ".$ilDB->quote($usr_id,"integer")
				." AND status = ".$ilDB->quote($status_success,"integer");
		$res = $ilDB->query($sql);
		while($rec = $ilDB->fetchAssoc($res)) {
			$this->crs_usrs[$rec["crs_id"]][] = $rec["user_id"];
		}
	}

	private function addCrsUsrFromCrsUsr($crs_id, $usr_id) {
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		$crs = $crs_utils->getSuccessfullParticipants();
		if(in_array($usr_id, $crs)) {
			$this->crs_usrs[$crs_id][] = $usr_id;
		}
	}

	private function addCrsUsrFromCSV($path) {
		$csv = fopen($path,"r");
		while ( $data = fgetcsv($csv,1000,";")) {
			$this->crs_usrs[$data[0]] = $data[1];
		}
		fclose($csv);
	}

	private function getParams() {
		$this->opt = $_GET;
	}

	private function uniqueUsrs() {
		foreach($this->crs_usrs as $crs_id => &$usrs) {
			$usrs = array_unique($usrs);
		} 
	}

	private function addCrsUsr($crs_id, $usr_id) {
		$this->crs_usrs[$crs_id][] = $usr_id;
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
			$usr_ids = array_intersect($usr_ids, $this->crs_successfull_usrs[$crs_id]);
		}

	}

	private function createCertificate($crs_id, $usr_id, $certificate) {
		return  $certificate->outCertificate(array("user_id" => $usr_id), false);

	}

	private function refreshCertificate($crs_id, $usr_id, $certificate, $overwrite) {
		if($overwrite) {
			$cert = $this->createCertificate($crs_id, $usr_id, $certificate);
		} elseif (!$this->hasCertificateHistorized($crs_id, $usr_id)) {

			$cert = $this->createCertificate($crs_id, $usr_id, $certificate);
		}

		if($cert) {
			echo "creating cert. for user: ".$usr_id;
			$this->rehistorizeCertificate($crs_id, $usr_id, $cert);
		}
	}

	private function rehistorizeCertificate($crs_id, $usr_id, $cert) {
		$case_id = array("crs_id" => $crs_id, "usr_id" => $usr_id);
		$data = array("certificate" => $cert);
		rehistorizeCertificate::updateHistorizedData($case_id, $data);
	}

	private function iterate(closure $at_crs) {
		foreach($this->crs_usrs as $crs_id => $usrs) {
			$iterate_usrs = function ($at_usr, $additional = null) use ($crs_id, $usrs) {
				foreach ($usrs as $usr_id) {
					$at_usr($crs_id, $usr_id, $additional);
				}
			};
			$at_crs($crs_id, $iterate_usrs);
		}
	}

	private function hasCertificateHistorized($crs_id, $usr_id) {
		global $ilDB;
		$sql = "SELECT certificate FROM hist_usercoursestatus WHERE crs_id = ".$ilDB->quote($crs_id, "text")
				." AND usr_id = ".$ilDB->quote($usr_id, "text")." AND hist_historic = 0 ";

		$rec = $ilDB->fetchAssoc($ilDB->query($sql));
		if($rec["certificate"] > 0) {
			return true;
		}
	}

	private function updateCertificates() {
		$overwrite = isset($this->opt["o"]);

		$at_crs = function ($crs_id, $iterate_usrs) use ($overwrite) {

			$course_class = ilObjectFactory::getClassByType('crs');
			$course_obj = new $course_class($crs_id, false);
			$certificate_adapter = new ilCourseCertificateAdapter($course_obj);
			$certificate = new ilCertificate($certificate_adapter);
			$is_complete = $certificate->isComplete();

			if($is_complete) {
				echo "crs: ".$crs_id." certificate complete<br>";
				$iterate_usrs(function ($crs_id, $usr_id, $certificate) use ($overwrite) {
								$this->refreshCertificate($crs_id, $usr_id, $certificate, $overwrite);
								}, 
								$certificate);
			}
		};
		$this->iterate($at_crs);
	}
}

new gevRefreshCertificates();
?>