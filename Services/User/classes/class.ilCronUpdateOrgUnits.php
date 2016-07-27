<?php
require_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Class ilCronUpdateOrgUnits
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilCronUpdateOrgUnits extends ilCronJob
{

	/**
	 * @var ilDB
	 */
	protected $db;
	/**
	 * @var ilLog
	 */
	protected $log;
	/**
	 * @var ilTree
	 */
	protected $tree;

	public function getId() {
		return "user_orgunits";
	}

	public function getTitle()
	{
		global $DIC;
		$lng = $DIC['lng'];

		return $lng->txt("update_orgunits");
	}

	public function getDescription() {
		global $DIC;
		$lng = $DIC['lng'];

		return $lng->txt("update_orgunits_desc");
	}

	public function hasAutoActivation() {
		return false;
	}


	public function hasFlexibleSchedule() {
		return true;
	}


	public function getDefaultScheduleType() {
		return self::SCHEDULE_TYPE_DAILY;
	}

	function getDefaultScheduleValue() {
		return;
	}

	/**
	 * @return ilCronJobResult
	 */
	public function run() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilLog = $DIC['ilLog'];
		$tree = $DIC['tree'];
		$this->db = $ilDB;
		$this->log = $ilLog;
		$this->tree = $tree;

		$map_role_orgu = $this->fetchRoleOrgUnitMapping();
		$users_orgunits = $this->createUsersOrgUnitsMapping($map_role_orgu);

		foreach ($users_orgunits as $usr_id => $org_units) {
			$this->checkDeleted($org_units);
			$org_units_string = implode(',', array_unique($org_units));
			$this->db->query("UPDATE usr_data SET org_units = " . $this->db->quote($org_units_string, 'text') .
				" WHERE usr_id = " . $this->db->quote($usr_id, 'integer'));
			$this->log->write('CRON - ilCronUpdateOrgUnits::run(), assigned orgunits ' . $org_units_string .' to user ' . $usr_id);
		}

		$result = new ilCronJobResult();
		$result->setStatus(ilCronJobResult::STATUS_OK);
		return $result;
	}



	/**
	 * @return array    all superior and employee roles ids as keys and the corresponding orgunits ref_id as value
	 */
	protected function fetchRoleOrgUnitMapping() {
		$res = $this->db->query("SELECT obj_id, title FROM object_data 
			WHERE type = " . $this->db->quote('role', 'text') .
			"AND (title LIKE " . $this->db->quote('il_orgu_employee_%' , 'text') .
			" OR title LIKE " . $this->db->quote('il_orgu_superior_%', 'text') . ')');
		//create mapping role_id <-> orgu_ref_id
		$map_role_orgu = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$map_role_orgu[$rec['obj_id']] = substr($rec['title'], strrpos($rec['title'], '_') + 1);
		}

		return $map_role_orgu;
	}

	/**
	 * @param $map_role_orgu array  return of fetchRoleOrgUnitMapping
	 *
	 * @return array    create mapping of users (keys) assigned to orgunits (values)
	 */
	protected function createUsersOrgUnitsMapping($map_role_orgu) {
		$res = $this->db->query("SELECT * FROM rbac_ua WHERE rol_id IN (" . implode(',', array_keys($map_role_orgu)) . ")");
		$users_orgunits = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			if (!isset($users_orgunits[$rec['usr_id']])) {
				$users_orgunits[$rec['usr_id']] = array();
			}
			$users_orgunits[$rec['usr_id']][] = $map_role_orgu[$rec['rol_id']];
		}
		return $users_orgunits;
	}
	
	/**
	 * unset deleted orgunits
	 *
	 * @param $org_units
	 */
	protected function checkDeleted(&$org_units){
		foreach ($org_units as $key => $orgu) {
			if ($this->tree->isDeleted($orgu)) {
				unset($org_units[$key]);
			}
		}
	}
}