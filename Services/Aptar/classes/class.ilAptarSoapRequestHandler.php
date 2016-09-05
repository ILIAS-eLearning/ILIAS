<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './webservice/soap/classes/class.ilSoapAdministration.php';

/**
 * Class ilAptarSoapRequestHandler
 */
class ilAptarSoapRequestHandler extends ilSoapAdministration
{
	/**
	 * @var string
	 */
	const UDF_NAME_SEGMENT = 'Segment/Corporate';

	/**
	 * @var string
	 */
	const UDF_NAME_EXT_INT = 'Internal/External';

	/**
	 * @var string
	 */
	const UDF_NAME_SUP = 'Superior';

	/**
	 * @var string
	 */
	const UDF_NAME_APTER_SITE = 'Aptar Site';

	/**
	 * @var string
	 */
	const DEFAULT_ORG_ROLE_NAME = '04 Employees';

	/**
	 * @var int[]
	 */
	protected static $udf_ids_by_name = array();

	/**
	 * @var string[]
	 */
	protected static $public_names_by_matriculation = array();

	/**
	 * @var array[]
	 */
	protected static $org_units_by_segment_and_site = array();

	/**
	 * @var int
	 */
	const DEFAULT_ROLE_ID = 4;

	/**
	 * @var array
	 */
	protected static $is_global_role_cache = array();

	/**
	 * @var array
	 */
	protected static $fallback_org_unit = array(
		'ou_import_id' => '',
		'ou_title'     => '',
		'ou_id'        => 0
	);

	/**
	 * @var ilObjUser|null
	 */
	protected $current_user = null;

	/**
	 * @var array
	 */
	protected $org_roles = array();

	/**
	 * @var ilAptarLog
	 */
	protected $log;

	/**
	 * @var array
	 */
	public $lng_mapping = array();

	/**
	 * @var array
	 */
	protected $installed_languages = array();

	/**
	 * ilAptarSoapRequestHandler constructor.
	 */
	public function __construct()
	{
		define("IL_SOAPMODE_NUSOAP", 0);
		define("IL_SOAPMODE_INTERNAL", 1);
		define("IL_SOAPMODE", IL_SOAPMODE_INTERNAL);

		parent::ilSoapAdministration();
	}

	/**
	 * @param ilObjUser $user
	 */
	protected function assignDefaultRole(ilObjUser $user)
	{
		/**
		 * @var $rbacadmin       ilRbacAdmin
		 * @var $rbacreview      ilRbacReview
		 * @var $ilClientIniFile ilIniFile
		 */
		global $rbacadmin, $rbacreview, $ilClientIniFile;

		$role_id = self::DEFAULT_ROLE_ID;
		if($ilClientIniFile->readVariable('aptar', 'import_default_role'))
		{
			$role_id = $ilClientIniFile->readVariable('aptar', 'import_default_role');
		}

		if(self::isGlobalRole($role_id) && !$rbacreview->isAssigned($user->getId(), $role_id))
		{
			$rbacadmin->assignUser($role_id, $user->getId());
		}
	}

	/**
	 * @param $role_id
	 * @return bool
	 */
	protected static function isGlobalRole($role_id)
	{
		/**
		 * @var $rbacreview  ilRbacReview
		 */
		global $rbacreview;

		if(!isset(self::$is_global_role_cache[$role_id]))
		{
			self::$is_global_role_cache[$role_id] = $rbacreview->isGlobalRole($role_id);
		}

		return self::$is_global_role_cache[$role_id];
	}

	/**
	 * @param stdClass $document
	 * @return SoapFault
	 */
	public function EmployeeReplicateRequest_Asynch($document)
	{
		$ini               = @parse_ini_file('ilias.ini.php', true);
		$default_client_id = '';
		if(
			is_array($ini) &&
			isset($ini['clients']) &&
			isset($ini['clients']['default']) &&
			strlen($ini['clients']['default'])
		)
		{
			$default_client_id = $ini['clients']['default'];
		}

		$existing_clients = array();
		$client_dirs      = glob("./data/*", GLOB_ONLYDIR);
		foreach((array)$client_dirs as $dir)
		{
			if(file_exists($dir . '/client.ini.php') && is_readable($dir. '/client.ini.php'))
			{
				$ini = @parse_ini_file($dir. '/client.ini.php', true);
				if(
					is_array($ini) &&
					isset($ini['client']) &&
					isset($ini['client']['name']) &&
					strlen($ini['client']['name'])
				)
				{
					$existing_clients[] = $ini['client']['name'];
				}
			}
		}

		$sid = $document->sid;

		$sid_parts = explode('::', $sid);
		if(strpos($sid, '::') === false || count($sid_parts) !== 2 || !in_array($sid_parts[1], $existing_clients))
		{
			$sid = $sid_parts[0] . '::' . $default_client_id;
		}

		$this->initAuth($sid);
		$this->initIlias();

		require_once 'Services/User/classes/class.ilObjUser.php';
		require_once 'Services/User/classes/class.ilUserDefinedFields.php';
		require_once 'Services/OrgUnit/classes/class.ilOrgRoleHelper.php';
		require_once 'Services/OrgUnit/classes/class.ilOrgUnit.php';
		require_once 'Services/Aptar/classes/class.ilAptarLog.php';

		$this->log       = ilAptarLog::getInstance();

		$this->log->info("Entered EmployeeReplicateRequest_Asynch ...");
		$this->log->info("Got request:");
		$this->log->info(file_get_contents('php://input'));

		if(!$this->__checkSession($sid))
		{
			$this->log->info("Invalid authentication. Please provide a valid session token!");
			return;
		}

		$this->initLngMapping();

		$response        = ilOrgRoleHelper::getListOfRoleTemplates();
		$this->org_roles = (array)$response->entries;

		if($document->Employee)
		{
			if(is_array($document->Employee))
			{
				$this->processEmployees($document->Employee);
			}
			else
			{
				$this->processEmployees(array($document->Employee));
			}
		}
	}

	/**
	 * @param array $employees
	 */
	protected function processEmployees(array $employees)
	{
		foreach($employees as $employee)
		{
			$this->processEmployee($employee);
		}
	}

	/**
	 * 
	 */
	protected function initLngMapping()
	{
		/**
		 * @var $ilClientIniFile ilIniFile
		 * @var $lng             ilLanguage
		 */
		global $ilClientIniFile, $lng;

		$lng_mapping = $ilClientIniFile->readVariable('aptar', 'import_lng_mapping');
		$this->log->info('Read language mapping from INI file: ' . $lng_mapping);

		$lng_mapping = array_filter(array_map('trim', explode(',', $lng_mapping)), function($mapping) {
			$is_not_empty  = strlen($mapping) > 0;
			$has_two_parts = count(explode('|', $mapping)) == 2;

			return $is_not_empty && $has_two_parts;
		});

		$this->lng_mapping = array();
		$that = $this;
		array_walk($lng_mapping, function($mapping) use ($that) {
			$parts = explode('|', $mapping);
			$that->lng_mapping[strtolower($parts[0])] = strtolower($parts[1]);
		});
		$this->installed_languages = $lng->getInstalledLanguages();
	}

	/**
	 * @param stdClass $employee
	 */
	protected function processEmployee(stdClass $employee)
	{
		if(!strlen($employee->TechnicalData->USRID))
		{
			$this->log->warn("Did not find a USRID, skipped record.");
			return;
		}

		$usr_id = ilObjUser::_lookupId($employee->TechnicalData->USRID);
		if($usr_id > 0)
		{
			$user = new ilObjUser($usr_id);
		}
		else
		{
			$user = new ilObjUser();
			$user->setApproveDate(date('Y-m-d H:i:s'));
			$user->setTimeLimitOwner(USER_FOLDER_ID);
			$user->setTimeLimitUnlimited(1);
			$user->setTimeLimitMessage(0);
			$user->setTitle($user->getFullname());
			$user->setDescription($user->getEmail());

			$user->setLogin($employee->TechnicalData->USRID);
			$user->setExternalAccount($employee->TechnicalData->USRID);

			$password = current(ilUtil::generatePasswords(1));
			$user->setPasswd($password);
		}

		$user->setAuthMode('saml');

		if(3 == $employee->BasicData->STAT2)
		{
			$user->setActive(1);
		}
		else if(in_array($employee->BasicData->STAT2, array(0, 1, 2)))
		{
			$user->setActive(0);
			if(!$usr_id)
			{
				$this->log->info(sprintf("Ignored new inactive employee: %s.", var_export($employee->BasicData->STAT2, 1)));
				return;
			}
		}
		else
		{
			$this->log->warn(sprintf("Found an undefined value for STAT2: %s.", var_export($employee->BasicData->STAT2, 1)));
		}

		$user->setEmail($employee->TechnicalData->USRID_LONG);
		$user->setFirstname($employee->BasicData->VORNA);
		$user->setLastname($employee->BasicData->NACHN);
		$user->setMatriculation($employee->BasicData->PERNR);
		$user->setCountry($employee->BasicData->LAND1);
		$user->setSelectedCountry(strtolower($employee->BasicData->LAND1));
		$user->setPhoneOffice($employee->BasicData->TELNR);

		switch($employee->BasicData->GESCH)
		{
			case 'm':
			case 1:
				$user->setGender('m');
				break;

			case 'f':
			case 2:
			default:
				$user->setGender('f');
				break;
		}

		// Language
		if(!$usr_id)
		{
			$language            = 'en';

			if(strlen($employee->BasicData->NATIO) > 0 && isset($this->lng_mapping[strtolower($employee->BasicData->NATIO)]))
			{
				$mapped_lng = $this->lng_mapping[strtolower($employee->BasicData->NATIO)];
				$this->log->info(
					sprintf(
						"Mapped value '%s' to language '%s' for user '%s'.",
						$employee->BasicData->NATIO,
						$mapped_lng,
						$employee->TechnicalData->USRID
					)
				);

				if(in_array($mapped_lng, array_map('strtolower', $this->installed_languages)))
				{
					$language = $mapped_lng;
				}
				else
				{
					$this->log->warn(
						sprintf(
							"The mapped language '%s' for value '%s' (user: '%s') is not installed, used fallback language '%s'.",
							$mapped_lng,
							$employee->BasicData->NATIO,
							$employee->TechnicalData->USRID,
							$language
						)
					);
				}
			}
			else
			{
				$this->log->warn(
					sprintf(
						"Used fallback language '%s' for user '%s', could not find value for field 'NATIO'.",
						$language,
						$employee->TechnicalData->USRID
					)
				);
			}

			$user->setPref('language', $language);
		}

		$unit = $this->getOrgUnitDataBySegmentAndAptarSite(
			$employee->BasicData->ZSEGM,
			$employee->BasicData->WERKS
		);

		// UDF
		$udf_aptar_site_id = $this->getUdfIdByName(self::UDF_NAME_APTER_SITE);
		if($udf_aptar_site_id > 0)
		{
			$user->setUserDefinedData(array(
				$udf_aptar_site_id => $unit['ou_title']
			));
		}
		else
		{
			$this->log->crit(sprintf("Could not find UDF with name: %s.", var_export(self::UDF_NAME_APTER_SITE, 1)));
		}

		$udf_name_segm_id = $this->getUdfIdByName(self::UDF_NAME_SEGMENT);
		if($udf_name_segm_id > 0)
		{
			$user->setUserDefinedData(array(
				$udf_name_segm_id => $employee->BasicData->ZSEGM
			));
		}
		else
		{
			$this->log->crit(sprintf("Could not find UDF with name: %s.", var_export(self::UDF_NAME_SEGMENT, 1)));
		}

		$udf_name_ext_int_id = $this->getUdfIdByName(self::UDF_NAME_EXT_INT);
		if($udf_name_ext_int_id > 0)
		{
			switch($employee->BasicData->PERSG)
			{
				case 1:
					$user->setUserDefinedData(array(
						$udf_name_ext_int_id => 'Internal'
					));
					break;

				case 9:
					$user->setUserDefinedData(array(
						$udf_name_ext_int_id => 'External'
					));
					break;

				default:
					$this->log->warn(sprintf("Found an undefined value for PERSG: %s.", var_export($employee->BasicData->PERSG, 1)));
					break;
			}
		}
		else
		{
			$this->log->crit(sprintf("Could not find UDF with name: %s.", var_export(self::UDF_NAME_EXT_INT, 1)));
		}

		$udf_name_superior = $this->getUdfIdByName(self::UDF_NAME_SUP);
		if($udf_name_superior > 0)
		{
			$superior = '';

			if(strlen($employee->BasicData->ZMNGR))
			{
				$superior = $this->getFirstAndLastNameByMatriculationNumber($employee->BasicData->ZMNGR);
			}

			$user->setUserDefinedData(array(
				$udf_name_superior => $superior
			));
		}
		else
		{
			$this->log->crit(sprintf("Could not find UDF with name: %s.", var_export(self::UDF_NAME_SUP, 1)));
		}

		if($usr_id > 0)
		{
			$user->setTitle($user->getFullname());
			$user->setDescription($user->getEmail());

			$user->update();
			$this->removeAllAutoGeneratedOrgUnitAssignments($user);
		}
		else
		{
			$user->create();
			$user->saveAsNew(false);

			$user->writePrefs();
		}

		$this->assignDefaultRole($user);

		$this->log->info(
			sprintf(
				"Successfully imported user %s/%s",
				$user->getLogin(), $user->getId()
			),
			array('import_success' => $user->getId())
		);

		$import_id = $unit['ou_import_id'];
		if(!strlen($import_id))
		{
			$this->log->warn(sprintf(
				"Skipped organisational unit assignment. Could not determine an import_id for the passed segment/size in context of user %s/%s.",
				$user->getLogin(), $user->getId()
			));
			return;
		}

		$org_unit_id = ilOrgUnit::lookupIdByImportId($import_id);
		if($org_unit_id < 1)
		{
			$this->log->warn(sprintf(
				"Skipped organisational unit assignment. Could not find an organisational unit with import_id %s in context of user %s/%s.",
				$import_id, $user->getLogin(), $user->getId()
			));
			return;
		}

		$org_role_id   = 0;
		foreach($this->org_roles as $template)
		{
			if(self::DEFAULT_ORG_ROLE_NAME == $template['template_name'])
			{
				$org_role_id = $template['id'];
				break;
			}
		}

		if($org_role_id < 1)
		{
			$this->log->crit(sprintf(
				"Skipped organisational unit assignment. Could not find an organisational unit role with name %s in context of user %s/%s.",
				self::DEFAULT_ORG_ROLE_NAME, $user->getLogin(), $user->getId()
			));
			return;
		}

		$unit = ilOrgUnit::getInstanceById($org_unit_id, true);
		$unit->ensureAssignsInitialised();
		if(!$unit->isUserAssigned($user->getId()))
		{
			$assignment = new ilOrgUnitAssignment();
			$assignment->setOrgUnitId($unit->getId());
			$assignment->setUserId($user->getId());
			$assignment->setOwnerId(ROLE_FOLDER_ID);
			$unit->getAssignmentList()->addAssignment($assignment);

			ilOrgRoleHelper::setOrgRoleDataForAssignmentByNamedImporter(
				$user->getId(), $unit->getId(), $org_role_id, ROLE_FOLDER_ID
			);
		}
	}

	/**
	 * @param string $a_name
	 * @return int
	 */
	protected function getUdfIdByName($a_name)
	{
		if(!array_key_exists($a_name, self::$udf_ids_by_name))
		{
			self::$udf_ids_by_name[$a_name] = ilUserDefinedFields::_getInstance()->fetchFieldIdFromName($a_name);
		}

		return self::$udf_ids_by_name[$a_name];
	}

	/**
	 * @param string $segment
	 * @param string $site
	 * @return array
	 */
	protected function getOrgUnitDataBySegmentAndAptarSite($segment, $site)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		switch($segment)
		{
			case 'B+H':
				$segment = 'Beauty_+_Home';
				break;

			case 'F+B':
				$segment = 'Food_+_Beverage';
				break;

			case 'Pharma':
				break;

			case 'Corporate':
				$segment = 'Corporate_Functions';
				break;

			default:
				$this->log->warn(sprintf("Unknown segment given: %s", $segment));
				return self::$fallback_org_unit;
				break;
		}

		if(!isset(self::$org_units_by_segment_and_site[$segment]))
		{
			self::$org_units_by_segment_and_site[$segment] = array();
		}

		// Return cache hit
		if(isset(self::$org_units_by_segment_and_site[$segment][$site]))
		{
			return self::$org_units_by_segment_and_site[$segment][$site];
		}

		$like_string =  $segment . '%\\\\'. $site;
		$like = " ou_import_id LIKE '" . $like_string . "' ";
		$res  = $ilDB->query("SELECT ou_id, ou_title, ou_import_id FROM org_unit_data WHERE " . $like);

		// Pessimistic operation
		self::$org_units_by_segment_and_site[$segment][$site] = self::$fallback_org_unit;

		$num_rows = $ilDB->numRows($res);
		if($num_rows > 1)
		{
			$this->log->warn(sprintf("Multiple organisation units found for segment %s and site %s", $segment, $site));
			$this->log->warn(sprintf("Query: %s", $res->db->last_query));
		}
		else if($num_rows == 0)
		{
			$this->log->warn(sprintf("No organisation unit found for segment %s and site %s", $segment, $site));
			$this->log->warn(sprintf("Query: %s", $res->db->last_query));
		}
		else
		{
			while($row = $ilDB->fetchAssoc($res))
			{
				self::$org_units_by_segment_and_site[$segment][$site] = $row;
				break;
			}
		}

		return self::$org_units_by_segment_and_site[$segment][$site];
	}

	/**
	 * @param ilObjUser $user
	 */
	protected function removeAllAutoGeneratedOrgUnitAssignments(ilObjUser $user)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$ilDB->manipulateF(
			"DELETE FROM org_unit_assignments WHERE oa_usr_id = %s AND oa_owner_id = %s",
			array('integer', 'integer'),
			array($user->getId(), ROLE_FOLDER_ID)
		);
	}

	/**
	 * @param string $matriculation
	 * @return string
	 */
	protected function getFirstAndLastNameByMatriculationNumber($matriculation)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if(!isset(self::$public_names_by_matriculation[$matriculation]))
		{
			$res = $ilDB->queryF("
				SELECT firstname, lastname
				FROM usr_data
				WHERE matriculation = %s
				ORDER BY usr_id",
				array('text'),
				array($matriculation)
			);
			while($row = $ilDB->fetchAssoc($res))
			{
				self::$public_names_by_matriculation[$matriculation] = implode(' ', array_filter(array_map('trim', array($row['firstname'], $row['lastname']))));
				break;
			}
		}

		return self::$public_names_by_matriculation[$matriculation];
	}
}