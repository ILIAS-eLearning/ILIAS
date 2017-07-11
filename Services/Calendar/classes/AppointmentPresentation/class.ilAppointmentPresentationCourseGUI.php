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

	public function getHTML()
	{
		global $lng, $ilCtrl,$DIC;

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		include_once "./Modules/Course/classes/class.ilObjCourse.php";
		include_once "./Modules/Course/classes/class.ilCourseFile.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$crs = new ilObjCourse($cat_info['obj_id'], false);
		$files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

		// get course ref id (this is possible, since courses only have one ref id)
		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		include_once('./Services/Link/classes/class.ilLink.php');
		$crs_ref_id = current($refs);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		$a_infoscreen->addSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$a_infoscreen->addProperty($lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}
		$a_infoscreen->addProperty($lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		$a_infoscreen->addSection($lng->txt((ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		if($crs->getImportantInformation())
		{
			$a_infoscreen->addProperty($lng->txt("crs_important_info"), $crs->getImportantInformation());
		}

		if($crs->getSyllabus())
		{
			$a_infoscreen->addProperty($lng->txt("crs_syllabus"), $crs->getSyllabus());
		}

		if (count($files)) {
			$tpl = new ilTemplate('tpl.event_info_file.html', true, true, 'Modules/Course');
			foreach ($files as $file) {
				$tpl->setCurrentBlock("files");
				$ilCtrl->setParameter($this, 'file_id', $file->getFileId());
				$ilCtrl->setParameterByClass('ilobjcoursegui','file_id', $file->getFileId());
				$ilCtrl->setParameterByClass('ilobjcoursegui','ref_id', $crs_ref_id);
				$tpl->setVariable("DOWN_LINK",$ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI","ilobjcoursegui"),'sendfile'));
				$tpl->setVariable("DOWN_NAME", $file->getFileName());
				$tpl->setVariable("DOWN_INFO_TXT", $lng->txt('crs_file_size_info'));
				$tpl->setVariable("DOWN_SIZE", $file->getFileSize());
				$tpl->setVariable("TXT_BYTES", $lng->txt('bytes'));
				$tpl->parseCurrentBlock();
				$ilCtrl->setParameterByClass('ilobjcoursegui','ref_id', $_GET["ref_id"]);
			}
			$a_infoscreen->addProperty($lng->txt("files"), $tpl->get());
		}

		// tutorial support members
		$parts = ilParticipants::getInstanceByObjId($cat_info['obj_id']);
		//contacts is an array of user ids.
		$contacts = $parts->getContacts();
		$num_contacts = count($contacts);
		if($num_contacts > 0) {
			$str = "";
			foreach ($contacts as $contact) {
				// link to user profile: todo: check if profile is really public
				include_once('./Services/Link/classes/class.ilLink.php');
				$href = ilLink::_getStaticLink($contact, "usr");
				if($num_contacts > 1 && $contacts[0] != $contact)
				{
					$str .= ", ";
				}
				$str .= $r->render($f->button()->shy(ilObjUser::_lookupFullname($contact), $href));
			}
			$a_infoscreen->addProperty($lng->txt("crs_mem_contacts"),$str);
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
			$str .=$lng->txt("crs_contact_phone").": ".$crs->getContactPhone()."<br>";
		}
		if($crs->getContactResponsibility()) {
			$str .=$crs->getContactResponsibility()."<br>";
		}
		if($crs->getContactConsultation()) {
			$str .=$crs->getContactConsultation()."<br>";
		}

		if($str != ""){
			$a_infoscreen->addProperty($lng->txt("crs_contact"), $str);
		}

		// fill toolbar (example)
		$toolbar = $this->getToolbar();
		$toolbar->addSeparator();
		$toolbar->addButton("Download all Files", "www.ilias.de");
		$toolbar->addButton("Open Course", "www.ilias.de");


	}
}