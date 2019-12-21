<?php
/* Copyright (c) 2017 Denis Klöpfer <denis.kloepfer@concepts-and-training.de>  Extended GPL, see ./LICENSE */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see ./LICENSE */

declare(strict_types=1);

require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembersStorageDB.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'Services/Tracking/classes/class.ilLPStatus.php';

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * List of members fo iass
 */
class ilIndividualAssessmentMembersTableGUI
{
    const CHANGED = "changed";
    const GRADED = "graded";

    public function __construct(
        ilIndividualAssessmentMembersGUI $parent,
        ilLanguage $lng,
        ilCtrl $ctrl,
        ilIndividualAssessmentAccessHandler $iass_access,
        Factory $factory,
        Renderer $renderer,
        int $current_user_id
    ) {
        $this->parent = $parent;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->iass_access = $iass_access;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->current_user_id = $current_user_id;
    }

    /**
     * Set data to show in table
     *
     * @param mixed[] 	$data
     *
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Renders the presentation table
     *
     * @param 	ILIAS\UI\Component\Component[] 	$view_constrols
     */
    public function render(array $view_constrols, int $offset = 0, int $limit = null) : string
    {
        $ptable = $this->factory->table()->presentation(
            "",
            $view_constrols,
            function (
                PresentationRow $row,
                ilIndividualAssessmentMember $record,
                Factory $ui_factory,
                $environment
            ) {
                $headline = $this->getHeadline($record);
                $subheadline = $this->getSubheadline($record);
                $important_infos = $this->importantInfos($record);
                $further_fields = $this->getFurtherFields($record);
                $content = $this->getContent($record);
                $action = $this->getAction($record, $ui_factory);

                $row = $row
                    ->withHeadline($headline)
                    ->withSubheadline($subheadline)
                    ->withImportantFields($important_infos)
                    ->withContent($ui_factory->listing()->descriptive($content))
                    ->withFurtherFieldsHeadline($this->txt("iass_further_field_headline"))
                    ->withFurtherFields($further_fields)
                    ->withAction($action);

                return $row;
            }
        );

        $data = array_slice($this->data, $offset, $limit);
        return $this->renderer->render($ptable->withData($data));
    }

    /**
     * Returns the headline for each row
     */
    protected function getHeadline(ilIndividualAssessmentMember $record) : string
    {
        return $record->lastname() . ", " . $record->firstname();
    }

    /**
     * Returns the subheadline for each row
     */
    protected function getSubheadline(ilIndividualAssessmentMember $record) : string
    {
        if (!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
            return "";
        }

        $examiner_id = $this->getExaminerId($record);
        return $this->txt("grading") . ": " . $this->getStatus($record->finalized(), (int) $record->LPStatus(), $examiner_id);
    }

    /**
     * Returns all informations needed for important row
     *
     * @return string[]
     */
    protected function importantInfos(ilIndividualAssessmentMember $record) : array
    {
        $finalized = $record->finalized();

        if ((!$this->userMayViewGrades() && !$this->userMayEditGrades()) || !$finalized) {
            return [];
        }

        $changer = array();
        if (!is_null($record->changerId())) {
            $changer_id = $this->getChangerId($record);
            $changer = $this->getProfileLink(self::CHANGED, $this->getFullNameFor($changer_id), $changer_id);
        }

        $examiner_id = $this->getExaminerId($record);
        return array_merge(
            $this->getGradedInformations($record->eventTime()),
            $this->getProfileLink(self::GRADED, $this->getFullNameFor($examiner_id), $examiner_id),
            $changer
        );
    }

    /**
     * Return all content elements for each row
     *
     * @return string[]
     */
    protected function getContent(ilIndividualAssessmentMember $record) : array
    {
        $examiner_id = $this->getExaminerId($record);
        if (
            !$this->checkEditable($record->finalized(), $examiner_id, (int) $record->id())
            && !$this->checkAmendable($record->finalized())
            && !$this->userMayViewGrades()
            && !$this->userMayEditGrades()
        ) {
            return [];
        }

        $usr_id = (int) $record->id();
        $file_name = $record->fileName();

        return array_merge(
            $this->getRecordNote($record->record()),
            $this->getInternalRecordNote($record->internalNote()),
            $this->checkDownloadFile($usr_id, $file_name)
                ? $this->getFileDownloadLink($usr_id, $file_name)
                : []
        );
    }

    /**
     * Returns all informations needed for further informations for each row
     *
     * @return string[]
     */
    protected function getFurtherFields(ilIndividualAssessmentMember $record) : array
    {
        if (!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
            return [];
        }

        return array_merge(
            $this->importantInfos($record),
            $this->getLocationInfos(
                $record->place(),
                $record->finalized(),
                $this->getExaminerId($record),
                (int) $record->id()
            )
        );
    }

    /**
     * Return the ui control with executable actions
     */
    protected function getAction(ilIndividualAssessmentMember $record, $ui_factory) : Dropdown
    {
        $items = [];

        $examiner_id = $this->getExaminerId($record);
        $usr_id = (int) $record->id();
        $finalized = $record->finalized();
        $file_name = $record->fileName();

        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', $usr_id);

        if ($this->checkEditable($finalized, $examiner_id, $usr_id)) {
            $target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'edit');
            $items[] = $ui_factory->button()->shy($this->txt('iass_usr_edit'), $target);
        }

        if ($this->checkUserRemoveable($finalized)) {
            $this->ctrl->setParameterByClass('ilIndividualAssessmentMembersGUI', 'usr_id', $usr_id);
            $target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMembersGUI', 'removeUserConfirmation');
            $items[] = $ui_factory->button()->shy($this->txt('iass_usr_remove'), $target);
            $this->ctrl->setParameterByClass('ilIndividualAssessmentMembersGUI', 'usr_id', null);
        }

        if ($this->checkAmendable($finalized)) {
            $target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'amend');
            $items[] = $ui_factory->button()->shy($this->txt('iass_usr_amend'), $target);
        }

        if ($this->checkDownloadFile($usr_id, $file_name)) {
            $target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'downloadAttachment');
            $items[] = $ui_factory->button()->shy($this->txt('iass_usr_download_attachment'), $target);
        }
        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', null);

        $action = $ui_factory->dropdown()->standard($items)->withLabel($this->txt("actions"));
        return $action;
    }

    /**
     * Returns readable status
     */
    protected function getStatus(bool $finalized, int $status, int $examiner_id = null) : string
    {
        if ($status == 0) {
            $status = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
        }

        if (!$finalized && !is_null($examinerId)) {
            $status = ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED;
        }

        return $this->getEntryForStatus($status);
    }

    /**
     * Returns informations about the grading
     *
     * @return string[]
     */
    protected function getGradedInformations(ilDate $event_time) : array
    {
        return array(
            $this->txt("iass_event_time") . ": " => ilDatePresentation::formatDate($event_time)
        );
    }

    /**
     * Returns login of examinier
     *
     * @return string[]
     */
    protected function getFullNameFor(int $user_id = null) : string
    {
        if (is_null($user_id)) {
            return "";
        }

        $name_fields = ilObjUser::_lookupName($user_id);
        $name = $name_fields["lastname"] . ", " . $name_fields["firstname"] . " [" . $name_fields["login"] . "]";

        return $name;
    }

    protected function getProfileLink(string $case, string $examiner, int $examiner_id)
    {
        if ($examiner === "") {
            return array();
        }

        if (!$this->hasPublicProfile($examiner_id)) {
            return array(
                $this->txt("iass_" . $case . "_by") => $examiner
            );
        }

        $back_url = $this->ctrl->getLinkTarget($this->parent, "view");
        $this->ctrl->setParameterByClass('ilpublicuserprofilegui', 'user_id', $examiner_id);
        $this->ctrl->setParameterByClass('ilpublicuserprofilegui', "back_url", rawurlencode($back_url));
        $link = $this->ctrl->getLinkTargetByClass('ilpublicuserprofilegui', 'getHTML');
        $link = $this->factory->link()->standard($examiner, $link);

        return array(
            $this->txt("iass_" . $case . "_by") => $this->renderer->render($link)
        );
    }

    protected function hasPublicProfile(int $examiner_id) : bool
    {
        $user = ilObjectFactory::getInstanceByObjId($examiner_id);
        return (
            ($user->getPref('public_profile') == 'y') ||
            $user->getPref('public_profile') == 'g'
        );
    }

    /**
     * Returns the location of assessment
     *
     * @return string[]
     */
    protected function getLocationInfos(
        string $location = null,
        bool $finalized,
        int $examiner_id = null,
        int $usr_id
    ) : array {
        if (!$this->viewLocation($finalized, $examiner_id, $usr_id)) {
            return array();
        }

        if ($location == "" || is_null($location)) {
            $location = $this->txt("none");
        }

        return array(
            $this->txt("iass_location") . ": " => $location
        );
    }

    /**
     * Returns inforamtions out of record note
     *
     * @return string[]
     */
    protected function getRecordNote(string $record_note) : array
    {
        return array(
            $this->txt("iass_record") => $record_note
        );
    }

    /**
     * Returns inforamtions out of internal record note
     *
     * @return string[]
     */
    protected function getInternalRecordNote(string $internal_note = null)
    {
        if (is_null($internal_note)) {
            $internal_note = "";
        }

        return array(
            $this->txt("iass_internal_note") => $internal_note
        );
    }

    /**
     * Get the link for download of file
     */
    protected function getFileDownloadLink(int $usr_id, $file_name) : array
    {
        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', $usr_id);
        $target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'downloadAttachment');
        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', null);
        $link = $this->factory->link()->standard($this->txt("iass_download"), $target);

        return array(
            $this->txt("iass_file") => $this->renderer->render($link)
        );
    }

    /**
     * Get text for lp status
     */
    protected function getEntryForStatus(int $a_status) : string
    {
        switch ($a_status) {
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return $this->txt('iass_status_pending');
                break;
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return $this->txt('iass_status_completed');
                break;
            case ilIndividualAssessmentMembers::LP_FAILED:
                return $this->txt('iass_status_failed');
                break;
            case ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED:
                return $this->txt('iass_assessment_not_completed');
                break;
        }
    }

    /**
     * Returns the examnier id from record
     *
     * @return int | null
     */
    protected function getExaminerId(ilIndividualAssessmentMember $record)
    {
        $examiner_id = $record->examinerId();

        if (is_null($examiner_id)) {
            return null;
        }

        return (int) $examiner_id;
    }

    protected function getChangerId(ilIndividualAssessmentMember $record)
    {
        $changer_id = $record->changerId();

        if (is_null($changer_id)) {
            return null;
        }

        return (int) $changer_id;
    }

    /**
     * Check user may view the location
     */
    protected function viewLocation(bool $finalized, int $examiner_id = null, int $usr_id) : bool
    {
        return
            $this->checkEditable($finalized, $examiner_id, $usr_id)
            || $this->checkAmendable($finalized)
            || $this->userMayViewGrades()
        ;
    }

    /**
     * Check the current user has edit permission on record
     */
    protected function checkEditable(bool $finalized, int $examiner_id = null, int $usr_id) : bool
    {
        if (
            ($this->userIsSystemAdmin() && !$finalized)
            || (!$finalized && $this->userMayEditGradesOf($usr_id) && $this->wasEditedByViewer($examiner_id))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check the current user has amend permission on record
     */
    protected function checkAmendable(bool $finalized) : bool
    {
        if (
            ($this->userIsSystemAdmin() && $finalized)
            || ($finalized && $this->userMayAmendGrades())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check the current user is allowed to remove the user
     */
    protected function checkUserRemoveable(bool $finalized) : bool
    {
        if (($this->userIsSystemAdmin() && !$finalized) || (!$finalized && $this->userMayEditMembers())) {
            return true;
        }

        return false;
    }

    /**
     * Check the current user is allowed to download the record file
     */
    protected function checkDownloadFile(int $usr_id, string $file_name = null) : bool
    {
        if ((!is_null($file_name) && $file_name !== '')
            && ($this->userIsSystemAdmin() || $this->userMayDownloadAttachment($usr_id))
        ) {
            return true;
        }

        return false;
    }

    protected function userMayDownloadAttachment(int $usr_id) : bool
    {
        return $this->userMayViewGrades() || $this->userMayEditGrades() || $this->userMayEditGradesOf($usr_id);
    }

    protected function userMayViewGrades() : bool
    {
        return $this->iass_access->mayViewUser();
    }

    protected function userMayEditGrades() : bool
    {
        return $this->iass_access->mayGradeUser();
    }

    protected function userMayAmendGrades() : bool
    {
        return $this->iass_access->mayAmendGradeUser();
    }

    protected function userMayEditMembers() : bool
    {
        return $this->iass_access->mayEditMembers();
    }

    protected function userIsSystemAdmin() : bool
    {
        return $this->iass_access->isSystemAdmin();
    }

    protected function userMayEditGradesOf(int $usr_id) : bool
    {
        return $this->iass_access->mayGradeUserById($usr_id);
    }

    protected function wasEditedByViewer(int $examiner_id = null) : bool
    {
        return $examiner_id === $this->current_user_id || null === $examiner_id;
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}
