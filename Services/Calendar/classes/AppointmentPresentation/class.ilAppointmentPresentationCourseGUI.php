<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationCourseGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationCourseGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	protected $ctrl;


	public function collectPropertiesAndActions()
	{
		global $DIC;

		include_once('./Services/Link/classes/class.ilLink.php');

		$this->lng->loadLanguageModule("crs");

		$a_app = $this->appointment;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$this->ctrl = $DIC->ctrl();

		include_once "./Modules/Course/classes/class.ilObjCourse.php";
		include_once "./Modules/Course/classes/class.ilCourseFile.php";

		$cat_info = $this->getCatInfo();

		$crs = new ilObjCourse($cat_info['obj_id'], false);
		$files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

		// get course ref id (this is possible, since courses only have one ref id)
		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		$crs_ref_id = current($refs);

		// add common section (title, description, object/calendar, location)
		$this->addCommonSection($a_app, $cat_info['obj_id']);

		$this->addInfoSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		if($crs->getImportantInformation())
		{
			$this->addInfoProperty($this->lng->txt("crs_important_info"), $crs->getImportantInformation());
		}

		if($crs->getSyllabus())
		{
			$this->addInfoProperty($this->lng->txt("crs_syllabus"), $crs->getSyllabus());
		}

		if (count($files)) {
			$this->has_files = true;
			$links = array();
			foreach ($files as $file) {
				$this->ctrl->setParameter($this, 'file_id', $file->getFileId());
				$this->ctrl->setParameterByClass('ilobjcoursegui','file_id', $file->getFileId());
				$this->ctrl->setParameterByClass('ilobjcoursegui','ref_id', $crs_ref_id);

				$links[] = $this->ui->renderer()->render(($this->ui->factory()->button()->shy($file->getFileName(),
					$this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI","ilobjcoursegui"),'sendfile'))));

				$this->ctrl->setParameterByClass('ilobjcoursegui','ref_id', $_GET["ref_id"]);
			}
			$this->addInfoProperty($this->lng->txt("files"), implode("<br>", $links));
			$this->addListItemProperty($this->lng->txt("files"), implode(", ", $links));
		}

		// tutorial support members
		$parts = ilParticipants::getInstanceByObjId($cat_info['obj_id']);
		//contacts is an array of user ids.
		$contacts = $parts->getContacts();
		$num_contacts = count($contacts);
		if($num_contacts > 0) {
			$str = "";
			foreach ($contacts as $contact) {
				$usr = new ilObjUser($contact);
				if($num_contacts > 1 && $contacts[0] != $contact)
				{
					$str .= ", ";
				}
				if($usr->hasPublicProfile())
				{
					include_once('./Services/Link/classes/class.ilLink.php');
					$str .= $this->getUserName($contact);
				}
				else
				{
					$str .= ilObjUser::_lookupFullname($contact);
				}
			}
			$this->addInfoProperty($this->lng->txt("crs_mem_contacts"),$str);
			$this->addListItemProperty($this->lng->txt("crs_mem_contacts"),$str);
		}

		//course contact
		$contact_fields = false;
		$str = "";
		if($crs->getContactName()) {
			$str .=$crs->getContactName()."<br>";
		}

		if($crs->getContactEmail())
		{
			//include_once './Modules/Course/classes/class.ilCourseMailTemplateMemberContext.php';
			//require_once 'Services/Mail/classes/class.ilMailFormCall.php';

			//TODO: optimize this
			//$courseGUI = new ilObjCourseGUI("", $crs_ref_id);
			$emails = explode(",",$crs->getContactEmail());
			foreach ($emails as $email) {
				$email = trim($email);
				$etpl = new ilTemplate("tpl.crs_contact_email.html", true, true , 'Modules/Course');
				$etpl->setVariable(
					"EMAIL_LINK",
					ilMailFormCall::getLinkTarget(
						$this->getInfoScreen(), 'showSummary', array(),
						array(
							'type'   => 'new',
							'rcp_to' => $email,
							//'sig' => $courseGUI->createMailSignature()
						),
						array(
							ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateMemberContext::ID,
							'ref_id' => $crs->getRefId(),
							'ts'     => time()
						)
					)
				);
				$etpl->setVariable("CONTACT_EMAIL", $email);
				$str .= $etpl->get()."<br />";
			}
		}

		if($crs->getContactPhone()) {
			$str .=$this->lng->txt("crs_contact_phone").": ".$crs->getContactPhone()."<br>";
		}
		if($crs->getContactResponsibility()) {
			$str .=$crs->getContactResponsibility()."<br>";
		}
		if($crs->getContactConsultation()) {
			$str .=$crs->getContactConsultation()."<br>";
		}

		if($str != ""){
			$this->addInfoProperty($this->lng->txt("crs_contact"), $str);
			$this->addListItemProperty($this->lng->txt("crs_contact"), str_replace("<br>", ", ", $str));
		}

		$this->addAction($this->lng->txt("crs_open"), ilLink::_getStaticLink($crs_ref_id, "crs"));

		$this->addMetaData('crs', $cat_info['obj_id']);
	}
}