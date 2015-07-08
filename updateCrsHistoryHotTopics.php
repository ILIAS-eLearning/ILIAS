<?php

require_once "Services/GEV/Utils/classes/class.gevSettings.php";
require_once "Services/GEV/Utils/classes/class.gevAMDUtils.php";
require_once "Services/Init/classes/class.ilInitialisation.php";
ilInitialisation::initILIAS();


class updateCrsHistoryHotTopics {
	protected $fhandle;
	protected $db;
	protected $csv;
	protected $filename;
	protected $settings;
	protected $usr_utils;
	protected $amd_utils;
 
	public function __construct($filename) {
		global $ilUser;
		$this->filename = $filename;
		$this->settings = gevSettings::getInstance();
		$this->amd_utils = gevAMDUtils::getInstance();
		$this->usr_utils = gevUserUtils::getInstance($ilUser->getId());

		if(!$this->usr_utils->isAdmin()) {
			die("You need to be an admin to use this feature.");
		}
		global $ilDB;
		$this->db = $ilDB;
		$this->fhandle = fopen($filename,"r");
		$this->csv = fgetcsv($this->fhandle,0,";");
	}

	public function updateRow() {
		$sql = 	"UPDATE hist_course SET dbv_hot_topic = ? WHERE title = ? AND is_template = ? AND hist_historic = 0";
		$statement = $this->db->prepareManip($sql,array("text","text","text"));
		while($this->csv) {
			if($this->csv[2] == "-") {
				$this->nextCsv();
				continue;
			}
			$this->db->execute($statement,array($this->csv[2],$this->csv[0],$this->csv[1]));
			$this->nextCsv();
		}
		$sql = 	"SELECT crs_id, dbv_hot_topic FROM hist_course"
				." WHERE dbv_hot_topic IS NOT NULL AND dbv_hot_topic != '-empty-' AND crs_id > 0 AND hist_historic = 0";
		$res = $this->db->query($sql);
		while($rec = $this->db->fetchAssoc($res)) {
			$this->amd_utils->setField($rec["crs_id"], gevSettings::CRS_AMD_DBV_HOT_TOPIC, $rec["dbv_hot_topic"]);
			echo "updated crs ".$rec["crs_id"]." by topic: ".$rec["dbv_hot_topic"]."<br>";
		}
		echo "Done!";
	}


	public function checkExistence() {
		$sql = 	"SELECT * FROM hist_course WHERE title = ? AND is_template = ?";
		$statement = $this->db->prepare($sql,array("text","text"));
		while($this->csv) {
			//$sql = 	"SELECT * FROM hist_course WHERE title = ".$this->db->quote($this->csv[0],"text")
			//		." AND is_template = ".$this->db->quote($this->csv[1],"text");

			$res = $this->db->execute($statement,array($this->csv[0],$this->csv[1]));
			if($res) {
				echo $this->csv[0]." OK <br>";
			} else {
				echo $this->csv[0]." NICHT OK <br>";
			}
			$this->nextCsv();
		}
		$this->fhandle = fopen($filename,"r");
	}

	protected function nextCsv() {
		$this->csv = fgetcsv($this->fhandle,0,";");
	}
}

$crshist = new updateCrsHistoryHotTopics("DBV-Themen.csv");
$crshist->updateRow();
?>