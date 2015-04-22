<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Command class for registration of an agent.
*
* @author	Stefan Hecken <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevExpRegistrationGUI{

	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilLog;

		error_reporting(E_ERROR);

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->crs_id = null;
		$this->user_id = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		global $ilAuth;

		// The user should not be logged in...
		if ($ilAuth->checkAuth()) {
			ilUtil::redirect("login.php");
		}
		
		if(!isset($_POST["type"])){
			$cmd = "startExpRegistration";
		}else{
			$cmd = $_POST["type"];
		}

		switch ($cmd) {
			case "startExpRegistration":
			case "registerExp":
			case "registerExpUser":
				$cont = $this->$cmd();
				break;
			case "redirectNewLogin":
				ilUtil::redirect("gev_registration.php");
			case "redirectLogin":
			default:
				ilUtil::redirect("login.php?target=login&client_id=Generali");
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}

	protected function registerExpUser(){

		$res = $this->checkForm();
		
		if ($res[1]) {
			return $this->startExpRegistration($res[0]);
		}

		$form = $res[0];

		$user = new ilObjUser();

		$user->setLogin("expr_".$form->getInput("firstname").$form->getInput("lastname"));
		$user->setEmail($form->getInput("email"));
		$user->setLastname($form->getInput("lastname"));
		$user->setFirstname($form->getInput("firstname"));
		$user->setInstitution($form->getInput("institution"));
		$user->setPhoneOffice($form->getINput("phone"));

		// is not active, owner is root
		$user->setActive(0, 6);
		$user->setTimeLimitUnlimited(true);
		// user already agreed at registration
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		$user->setAgreeDate($now->get(IL_CAL_DATETIME));
		$user->setIsSelfRegistered(true);
		
		$user->create();
		$user->saveAsNew();

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstanceByObj($user);

		$user_utils->setPrivateEmail($form->getInput("email"));
		$user_utils->setCompanyName($form->getInput("institution"));
		$user_utils->setJobNumber($form->getInput("vnumber"));

		$data = $this->getStellennummerData($user_utils->getJobNumber());
		$user_utils->setADPNumberGEV($data["adp"]);
		$user_utils->setAgentKey($data["vms"]);

		$this->user_id = $user->getId();
		
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role_utils = gevRoleUtils::getInstance();
		$role_utils->assignUserToGlobalRole($this->user_id, "ExpressUser");

		$user->setActive(true, 6);
		$user->update();

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$this->crs_id = $_POST["crs_id"];
		$crsUtil = gevCourseUtils::getInstance($this->crs_id);
		$crsUtil->bookUser($this->user_id);

		$isSelfLearning = $crsUtil->getType() == "Selbstlernkurs";

		$status = $crsUtil->getBookingStatusOf($this->user_id);

		if ($status != ilCourseBooking::STATUS_BOOKED && $status != ilCourseBooking::STATUS_WAITING) {
			$this->failAtFinalize("Status was neither booked nor waiting.");
		}
		$this->finalizedBookingRedirect($status,$isSelfLearning);
	}

	protected function failAtFinalize($msg) {
		$this->log->write("gevBookingGUI::finalizeBooking: ".$msg);
		ilUtil::sendFailure($this->lng->txt("gev_finalize_booking_error"), true);
		$this->toMaklerOffer();
		exit();
	}

	protected function finalizedBookingRedirect($a_status,$a_isSelfLerning) {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$booked = $a_status == ilCourseBooking::STATUS_BOOKED;
		$automails = new gevCrsAutoMails($this->crs_id);
		
		if (!$a_isSelfLearning) {
			if ($booked) {
				$automails->send("self_booking_to_booked", array($this->user_id));
				$automails->send("invitation", array($this->user_id));
			}
			else {
				$automails->send("self_booking_to_waiting", array($this->user_id));
			}
		}

		ilUtil::sendSuccess( sprintf( $booked ? $this->lng->txt("gev_was_booked_self")
											  : $this->lng->txt("gev_was_booked_waiting_self")
									, $this->crs_utils->getTitle()
									)
							, true
							);
		
		ilUtil::redirect("makler.php");
	}

	protected function toMaklerOffer() {
		ilUtil::redirect("makler.php");
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
	
	protected function isValidStellennummer($a_stellennummer) {
		$this->loadStellennummerData($a_stellennummer);
		return $this->stellennummer_data !== false && $this->stellennummer_data["stellennummer"] == $a_stellennummer;
	}

	protected function checkForm(){
		$form = $this->buildRegistrationForm();
		$err = false;

		if (!$form->checkInput()) {
			$err = true;
		}

		if ($_POST["tou"] != 1) {
			$err = true;
			$chb = $form->getItemByPostVar("tou");
			$chb->setAlert($this->lng->txt("evg_mandatory"));
		}

		$stellennummer = $form->getInput("vnumber");
		if(!($this->isValidStellennummer($stellennummer) && $this->isAgent($stellennummer))) {
			$err = true;
			$form->getItemByPostVar("vnumber")->setAlert($this->lng->txt("gev_evg_registration_not_found"));
		}

		return array($form, $err);
	}

	protected function startExpRegistration($a_form = NULL) {
		$tpl = new ilTemplate("tpl.gev_express_registration.html", false, false, "Services/GEV/Registration");
		
		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		}
		else {
			$form = $this->buildRegistrationForm();
		}
		$tpl->setVariable("FORM", $form->getHTML());

		return  $tpl->get();
	}

	protected function buildRegistrationForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilNumberInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton('next',$this->lng->txt('next'));

		$regType = new ilRadioGroupInputGUI($this->lng->txt('gev_login_express'),"type");
		$regType->setValue("registerExpUser");
		
		$optOld = new ilRadioOption($this->lng->txt('gev_login_express_existend_account'));
		$optOld->setValue("redirectLogin");
		$regType->addOption($optOld);
		
		$optNew = new ilRadioOption($this->lng->txt('gev_login_express_new_account'));
		$optNew->setValue("redirectNewLogin");
		$regType->addOption($optNew);
		
		$optExp = new ilRadioOption($this->lng->txt('gev_login_express_no_login'));
		$optExp->setValue("registerExpUser");
		$regType->addOption($optExp);
			$inputName = new ilTextInputGUI($this->lng->txt('firstname'),"firstname");
			$inputName->setRequired(true);
			$optExp->addSubItem($inputName);

			$inputSurName = new ilTextInputGUI($this->lng->txt('lastname'),"lastname");
			$inputSurName->setRequired(true);
			$optExp->addSubItem($inputSurName);

			$inputInstitution = new ilTextInputGUI($this->lng->txt('gev_login_express_companyname'),"institution");
			$inputInstitution->setRequired(true);
			$optExp->addSubItem($inputInstitution);

			$inputVNumber = new ilTextInputGUI($this->lng->txt('gev_login_express_vnumber'),"vnumber");
			$inputVNumber->setRequired(true);
			$optExp->addSubItem($inputVNumber);

			$inputEMail = new ilEMailInputGUI($this->lng->txt('email'),"email");	
			$inputEMail->setRequired(true);		
			$optExp->addSubItem($inputEMail);			
		
			$inputPhone = new ilNumberInputGUI($this->lng->txt('phone_office'),"phone");
			$inputPhone->setRequired(true);
			$optExp->addSubItem($inputPhone);

			$checkToU = new ilCheckboxInputGUI('',"tou");
			$checkToU->setOptionTitle($this->lng->txt('gev_login_express_agreement'));
			$checkToU->setValue(1);
			$checkToU->setChecked(false);
			$optExp->addSubItem($checkToU);

		$form->addItem($regType);

		$crsId = new ilHiddenInputGUI("crs_id");
		$crsId->setValue($_GET["crs_id"]);
		$form->addItem($crsId);

		return $form;
	}
}