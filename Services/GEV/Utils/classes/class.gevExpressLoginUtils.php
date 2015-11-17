<?php
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevExpressLoginUtils {
	static protected $instance = null;
	
	protected function __construct(){
		global $ilDB;
		$this->db = $ilDB;
	}

	static public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevExpressLoginUtils();
		}
		
		return self::$instance;
	}

	//register a new express user
	//param $a_form Form for user Data
	public function registerExpressUser($a_form){
		$this->user = new ilObjUser();
		$this->user->setLogin("expr_".$a_form->getInput("firstname").$a_form->getInput("lastname"));
		$this->user->setGender($a_form->getInput("gender"));
		$this->user->setEmail($a_form->getInput("email"));
		$this->user->setLastname($a_form->getInput("lastname"));
		$this->user->setFirstname($a_form->getInput("firstname"));
		$this->user->setInstitution($a_form->getInput("institution"));
		$this->user->setPhoneOffice($a_form->getINput("phone"));

		// is not active, owner is root
		$this->user->setActive(0, 6);
		$this->user->setTimeLimitUnlimited(true);
		// user already agreed at registration
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		$this->user->setAgreeDate($now->get(IL_CAL_DATETIME));
		$this->user->setIsSelfRegistered(true);
		
		$this->user->create();
		$this->user->saveAsNew();

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstanceByObj($this->user);

		$user_utils->setCompanyName($a_form->getInput("institution"));
		$user_utils->setJobNumber($a_form->getInput("vnumber"));

		$data = $this->getStellennummerData($user_utils->getJobNumber());
		$user_utils->setADPNumberGEV($data["adp"]);
		$user_utils->setAgentKey($data["vms"]);
		
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role_utils = gevRoleUtils::getInstance();
		$role_utils->assignUserToGlobalRole($this->user->getId(), "ExpressUser");

		$this->user->setActive(true, 6);
		$this->user->update();

		return $this->user->getID();
	}

	protected function getImport() {
		if ($this->import === null) {
			require_once("gev_utils.php");
			$this->import = get_gev_import();
		}
		
		return $this->import;
	}

	protected function loadStellennummerData($a_stellennummer) {
		if ($this->stellennummer_data === null) {
			$import = $this->getImport();
			$this->stellennummer_data = $import->get_stelle($a_stellennummer);
		}
	}
	
	protected function getStellennummerData($a_stellennummer) {
		$this->loadStellennummerData($a_stellennummer);

		if ($this->stellennummer_data["stellennummer"] != $a_stellennummer) {
			throw new Exception("gevRegistrationGUI::getStellennummerData: stellennummer does not match.");
		}

		return $this->stellennummer_data;
	}
	
	public function isValidStellennummer($a_stellennummer) {
		$this->loadStellennummerData($a_stellennummer);
		return $this->stellennummer_data !== false 
			&& $this->stellennummer_data["stellennummer"] == $a_stellennummer;
	}
	
	public function isAgent($a_stellennummer) {
		$data = $this->getStellennummerData($a_stellennummer);
		return $data["agent"] == 1;
	}

	public function setExpressUserExperienceDate($a_usr_id){
		if($this->isExpressUser($a_usr_id)){
			require_once("Services/GEV/Utils/classes/class.gevSettings.php");

			$gev_settings = gevSettings::getInstance();
			$exit_udf_field_id = $gev_settings->getUDFFieldId(gevSettings::USR_UDF_EXIT_DATE);
			$sql = "UPDATE udf_text "
				  ."   SET value = CURDATE()"
				  ." WHERE usr_id = ".$this->db->quote($a_usr_id, "integer")
				  ."   AND field_id = ".$this->db->quote($exit_udf_field_id, "integer");
			$res = $this->db->query($sql);
		}
	}

	public function isExpressUser($a_usr_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$usrUtil = gevUserUtils::getInstance($a_usr_id);
		$globalRoles = $usrUtil->getGlobalRoles();

		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$roleUtils = gevRoleUtils::getInstance();
		$roleTitles = $roleUtils->getGlobalRolesTitles($globalRoles);
		
		$isExpressUser = false;

		foreach ($roleTitles as $key => $title) {
			if($title == "ExpressUser"){
				$isExpressUser = true;
				break;
			}
		}

		return $isExpressUser;
	}
}
?>