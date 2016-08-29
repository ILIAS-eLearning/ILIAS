<?php

namespace CaT\Plugins\TalentAssessment\Observator;

class ilDB implements DB {
	public function __construct($db) {
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createLocalRoleTemplate($tpl_title, $tpl_description) {
		include_once("./Services/AccessControl/classes/class.ilObjRoleTemplate.php");
		$roltObj = new \ilObjRoleTemplate();
		$roltObj->setTitle($tpl_title);
		$roltObj->setDescription($tpl_description);
		$roltObj->create();
	}

	/**
	 * @inheritdoc
	 */
	public function getRoltId() {
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_admin'";

		$res = $this->db->getRow($query, DB_FETCHMODE_ASSOC);

		return $res["obj_id"];
	}
}