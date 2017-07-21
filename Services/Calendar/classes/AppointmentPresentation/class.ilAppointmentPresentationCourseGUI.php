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

	public function getHTML()
	{
		global $DIC;

		include_once('./Services/Link/classes/class.ilLink.php');

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$this->ctrl = $DIC->ctrl();

		include_once "./Modules/Course/classes/class.ilObjCourse.php";
		include_once "./Modules/Course/classes/class.ilCourseFile.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$crs = new ilObjCourse($cat_info['obj_id'], false);
		$files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

		// get course ref id (this is possible, since courses only have one ref id)
		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		$crs_ref_id = current($refs);

		if(ilObject::_lookupDescription($cat_info['obj_id']) != "") {
			$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		}
		else {
			$description_text = $cat_info['title'];
		}

		$a_infoscreen->addSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$a_infoscreen->addProperty($this->lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}
		$a_infoscreen->addProperty($this->lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		$a_infoscreen->addSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		if($crs->getImportantInformation())
		{
			$a_infoscreen->addProperty($this->lng->txt("crs_important_info"), $crs->getImportantInformation());
		}

		if($crs->getSyllabus())
		{
			$a_infoscreen->addProperty($this->lng->txt("crs_syllabus"), $crs->getSyllabus());
		}

		if (count($files)) {
			$tpl = new ilTemplate('tpl.event_info_file.html', true, true, 'Modules/Course');
			foreach ($files as $file) {
				$tpl->setCurrentBlock("files");
				$this->ctrl->setParameter($this, 'file_id', $file->getFileId());
				$this->ctrl->setParameterByClass('ilobjcoursegui','file_id', $file->getFileId());
				$this->ctrl->setParameterByClass('ilobjcoursegui','ref_id', $crs_ref_id);
				$tpl->setVariable("DOWN_LINK",$this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI","ilobjcoursegui"),'sendfile'));
				$tpl->setVariable("DOWN_NAME", $file->getFileName());
				$tpl->setVariable("DOWN_INFO_TXT", $this->lng->txt('crs_file_size_info'));
				$tpl->setVariable("DOWN_SIZE", $file->getFileSize());
				$tpl->setVariable("TXT_BYTES", $this->lng->txt('bytes'));
				$tpl->parseCurrentBlock();
				$this->ctrl->setParameterByClass('ilobjcoursegui','ref_id', $_GET["ref_id"]);
			}
			$a_infoscreen->addProperty($this->lng->txt("files"), $tpl->get());
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
					$href = ilLink::_getStaticLink($contact, "usr");
					$str .= $r->render($f->button()->shy(ilObjUser::_lookupFullname($contact), $href));
				}
				else
				{
					$str .= ilObjUser::_lookupFullname($contact);
				}
			}
			$a_infoscreen->addProperty($this->lng->txt("crs_mem_contacts"),$str);
		}

		//course contact
		$contact_fields = false;
		$str = "";
		if($crs->getContactName()) {
			$str .=$crs->getContactName()."<br>";
		}

		if($crs->getContactEmail())
		{
			include_once './Modules/Course/classes/class.ilCourseMailTemplateMemberContext.php';
			require_once 'Services/Mail/classes/class.ilMailFormCall.php';

			$courseGUI = new ilObjCourseGUI();

			$emails = explode(",",$crs->getContactEmail());
			foreach ($emails as $email) {
				$email = trim($email);
				$etpl = new ilTemplate("tpl.crs_contact_email.html", true, true , 'Modules/Course');
				$etpl->setVariable(
					"EMAIL_LINK",
					ilMailFormCall::getLinkTarget(
						$a_infoscreen, 'showSummary', array(),
						array(
							'type'   => 'new',
							'rcp_to' => $email,
							'sig' => $courseGUI->createMailSignature()
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
			$a_infoscreen->addProperty($this->lng->txt("crs_contact"), $str);
		}

		//TODO: Remove the hack in ilADTActiveRecordByType.php.
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO,'crs',$cat_info['obj_id']);
		$record_gui->setInfoObject($a_infoscreen);
		$record_gui->parse();
		// meta data
		$a_infoscreen->addMetaDataSections($cat_info['obj_id'],0, "crs");


		// fill toolbar
		$toolbar = $this->getToolbar();

		//example download all files
		$btn_download = ilLinkButton::getInstance();
		$btn_download->setCaption($this->lng->txt("cal_download_all_files"));
		$btn_download->setUrl("www.ilias.de");
		$toolbar->addButtonInstance($btn_download);

		$btn_open = ilLinkButton::getInstance();
		$btn_open->setCaption($this->lng->txt("cal_crs_open"));
		$btn_open->setUrl(ilLink::_getStaticLink($crs_ref_id, "crs"));
		$toolbar->addButtonInstance($btn_open);

	}
}