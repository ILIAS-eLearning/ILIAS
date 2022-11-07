<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * List of members fo iass
 */
class ilIndividualAssessmentMembersTableGUI
{
    protected ilIndividualAssessmentMembersGUI $parent;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected IndividualAssessmentAccessHandler $iass_access;
    protected Factory $factory;
    protected Renderer $renderer;
    protected int $current_user_id;
    protected array $data = [];

    public function __construct(
        ilIndividualAssessmentMembersGUI $parent,
        ilLanguage $lng,
        ilCtrl $ctrl,
        IndividualAssessmentAccessHandler $iass_access,
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
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Renders the presentation table
     *
     * @param 	ILIAS\UI\Component\Component[] 	$view_constrols
     */
    public function render(array $view_constrols, int $offset = 0, int $limit = null): string
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

                return $row
                    ->withHeadline($headline)
                    ->withSubheadline($subheadline)
                    ->withImportantFields($important_infos)
                    ->withContent($ui_factory->listing()->descriptive($content))
                    ->withFurtherFieldsHeadline($this->txt("iass_further_field_headline"))
                    ->withFurtherFields($further_fields)
                    ->withAction($action);
            }
        );

        $data = array_slice($this->data, $offset, $limit);
        return $this->renderer->render($ptable->withData($data));
    }

    /**
     * Returns the headline for each row
     */
    protected function getHeadline(ilIndividualAssessmentMember $record): string
    {
        return $record->lastname() . ", " . $record->firstname() . " [" . $record->login() . "]";
    }

    /**
     * Returns the sub headline for each row
     */
    protected function getSubheadline(ilIndividualAssessmentMember $record): string
    {
        if (!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
            return "";
        }

        $examiner_id = $record->examinerId();
        return $this->txt("grading") . ": " . $this->getStatus($record->finalized(), $record->LPStatus(), $examiner_id);
    }

    /**
     * Returns all information needed for important row
     *
     * @return string[]
     */
    protected function importantInfos(ilIndividualAssessmentMember $record): array
    {
        $finalized = $record->finalized();

        if ((!$this->userMayViewGrades() && !$this->userMayEditGrades()) || !$finalized) {
            return [];
        }

        return array_merge(
            $this->getGradedInformation($record->eventTime()),
            $this->getGradedByInformation($record->examinerId()),
            $this->getChangedByInformation($record->changerId(), $record->changeTime())
        );
    }

    protected function getGradedByInformation(?int $graded_by_id): array
    {
        if (is_null($graded_by_id)) {
            return [];
        }

        $full_name = $this->getFullNameFor($graded_by_id);
        if (!$this->hasPublicProfile($graded_by_id)) {
            return [$this->txt('iass_graded_by') => $full_name];
        }

        return [
            $this->txt('iass_graded_by') => $this->getProfileLink($full_name, $graded_by_id)
        ];
    }

    protected function getChangedByInformation(?int $changed_by_id, ?DateTime $change_date): array
    {
        if (is_null($changed_by_id)) {
            return [];
        }

        $changed_date_str = "";
        if (!is_null($change_date)) {
            $dt = new ilDate($change_date->format("Y-m-d"), IL_CAL_DATE);
            $changed_date_str = ilDatePresentation::formatDate($dt);
        }

        $full_name = $this->getFullNameFor($changed_by_id);
        if (!$this->hasPublicProfile($changed_by_id)) {
            return [$this->txt('iass_changed_by') => $full_name . ' ' . $changed_date_str];
        }

        return [
            $this->txt('iass_changed_by') => $this->getProfileLink($full_name, $changed_by_id) . ' ' . $changed_date_str
        ];
    }

    /**
     * Return all content elements for each row
     *
     * @return string[]
     */
    protected function getContent(ilIndividualAssessmentMember $record): array
    {
        $examiner_id = $record->examinerId();
        if (
            !$this->checkEditable($record->finalized(), $record->id(), $examiner_id)
            && !$this->checkAmendable($record->finalized())
            && !$this->userMayViewGrades()
            && !$this->userMayEditGrades()
        ) {
            return [];
        }

        $usr_id = $record->id();
        $file_name = $record->fileName();

        return array_merge(
            $this->getRecordNote($record->record()),
            $this->getInternalRecordNote($record->internalNote()),
            $this->checkDownloadFile($usr_id, $file_name)
                ? $this->getFileDownloadLink($usr_id)
                : []
        );
    }

    /**
     * Returns all information needed for further information for each row
     *
     * @return string[]
     */
    protected function getFurtherFields(ilIndividualAssessmentMember $record): array
    {
        if (!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
            return [];
        }

        return array_merge(
            $this->importantInfos($record),
            $this->getLocationInfos(
                $record->finalized(),
                $record->id(),
                $record->place(),
                $record->examinerId()
            )
        );
    }

    /**
     * Return the ui control with executable actions
     */
    protected function getAction(ilIndividualAssessmentMember $record, Factory $ui_factory): Dropdown
    {
        $items = [];

        $examiner_id = $record->examinerId();
        $usr_id = $record->id();
        $finalized = $record->finalized();
        $file_name = $record->fileName();

        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', $usr_id);

        if ($this->checkEditable($finalized, $usr_id, $examiner_id)) {
            $target = $this->ctrl->getLinkTargetByClass(ilIndividualAssessmentMemberGUI::class, 'edit');
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
            $target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'downloadFile');
            $items[] = $ui_factory->button()->shy($this->txt('iass_usr_download_attachment'), $target);
        }
        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', null);

        return $ui_factory->dropdown()->standard($items)->withLabel($this->txt("actions"));
    }

    /**
     * Returns readable status
     */
    protected function getStatus(bool $finalized, int $status, int $examiner_id = null): string
    {
        if ($status == 0) {
            $status = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
        }

        if (!$finalized && !is_null($examiner_id)) {
            return $this->txt('iass_assessment_not_completed');
        }

        return $this->getEntryForStatus($status);
    }

    /**
     * Returns information about the grading
     *
     * @return string[]
     */
    protected function getGradedInformation(?DateTimeImmutable $event_time): array
    {
        $event_time_str = "";
        if (!is_null($event_time)) {
            $dt = new ilDate($event_time->format("Y-m-d"), IL_CAL_DATE);
            $event_time_str = ilDatePresentation::formatDate($dt);
        }
        return array(
            $this->txt("iass_event_time") . ": " => $event_time_str
        );
    }

    /**
     * Returns login of examiner
     */
    protected function getFullNameFor(int $user_id = null): string
    {
        if (is_null($user_id)) {
            return "";
        }

        $name_fields = ilObjUser::_lookupName($user_id);
        return $name_fields["lastname"] . ", " . $name_fields["firstname"] . " [" . $name_fields["login"] . "]";
    }

    protected function getProfileLink(string $full_name, int $user_id): string
    {
        $back_url = $this->ctrl->getLinkTarget($this->parent, "view");
        $this->ctrl->setParameterByClass('ilpublicuserprofilegui', 'user_id', $user_id);
        $this->ctrl->setParameterByClass('ilpublicuserprofilegui', "back_url", rawurlencode($back_url));
        $link = $this->ctrl->getLinkTargetByClass('ilpublicuserprofilegui', 'getHTML');
        $link = $this->factory->link()->standard($full_name, $link);

        return $this->renderer->render($link);
    }

    protected function hasPublicProfile(int $examiner_id): bool
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
        bool $finalized,
        int $usr_id,
        string $location = null,
        int $examiner_id = null
    ): array {
        if (!$this->viewLocation($finalized, $usr_id, $examiner_id)) {
            return array();
        }

        if ($location === "" || is_null($location)) {
            $location = $this->txt("none");
        }

        return array(
            $this->txt("iass_location") . ": " => $location
        );
    }

    /**
     * Returns information out of record note
     *
     * @return string[]
     */
    protected function getRecordNote(string $record_note): array
    {
        return array(
            $this->txt("iass_record") => $record_note
        );
    }

    /**
     * Returns information out of internal record note
     *
     * @return string[]
     */
    protected function getInternalRecordNote(string $internal_note = null): array
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
    protected function getFileDownloadLink(int $usr_id): array
    {
        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', $usr_id);
        $target = $this->ctrl->getLinkTargetByClass(
            'ilIndividualAssessmentMemberGUI',
            ilIndividualAssessmentMemberGUI::CMD_DOWNLOAD_FILE
        );
        $this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', null);
        $link = $this->factory->link()->standard($this->txt("iass_download"), $target);

        return array(
            $this->txt("iass_file") => $this->renderer->render($link)
        );
    }

    /**
     * Get text for lp status
     */
    protected function getEntryForStatus(int $a_status): string
    {
        switch ($a_status) {
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return $this->txt('iass_status_pending');
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return $this->txt('iass_status_completed');
            case ilIndividualAssessmentMembers::LP_FAILED:
                return $this->txt('iass_status_failed');
            default:
                throw new ilIndividualAssessmentException("Invalid status: " . $a_status);
        }
    }

    /**
     * Check user may view the location
     */
    protected function viewLocation(bool $finalized, int $usr_id, int $examiner_id = null): bool
    {
        return
            $this->checkEditable($finalized, $usr_id, $examiner_id) ||
            $this->checkAmendable($finalized) ||
            $this->userMayViewGrades()
        ;
    }

    /**
     * Check the current user has edit permission on record
     */
    protected function checkEditable(bool $finalized, int $usr_id, int $examiner_id = null): bool
    {
        if ($finalized) {
            return false;
        }

        return
            $this->userIsSystemAdmin()
            ||
            (
                $this->userMayEditGradesOf($usr_id)
                &&
                $this->wasEditedByViewer($examiner_id)
            );
    }

    /**
     * Check the current user has amended permission on record
     */
    protected function checkAmendable(bool $finalized): bool
    {
        if (
            ($this->userIsSystemAdmin() && $finalized) ||
            ($finalized && $this->userMayAmendGrades())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check the current user is allowed to remove the user
     */
    protected function checkUserRemoveable(bool $finalized): bool
    {
        if (($this->userIsSystemAdmin() && !$finalized) || (!$finalized && $this->userMayEditMembers())) {
            return true;
        }

        return false;
    }

    /**
     * Check the current user is allowed to download the record file
     */
    protected function checkDownloadFile(int $usr_id, string $file_name = null): bool
    {
        if ((!is_null($file_name) && $file_name !== '')
            && ($this->userIsSystemAdmin() || $this->userMayDownloadAttachment($usr_id))
        ) {
            return true;
        }

        return false;
    }

    protected function userMayDownloadAttachment(int $usr_id): bool
    {
        return $this->userMayViewGrades() || $this->userMayEditGrades() || $this->userMayEditGradesOf($usr_id);
    }

    protected function userMayViewGrades(): bool
    {
        return $this->iass_access->mayViewUser();
    }

    protected function userMayEditGrades(): bool
    {
        return $this->iass_access->mayGradeUser();
    }

    protected function userMayAmendGrades(): bool
    {
        return $this->iass_access->mayAmendGradeUser();
    }

    protected function userMayEditMembers(): bool
    {
        return $this->iass_access->mayEditMembers();
    }

    protected function userIsSystemAdmin(): bool
    {
        return $this->iass_access->isSystemAdmin();
    }

    protected function userMayEditGradesOf(int $usr_id): bool
    {
        return $this->iass_access->mayGradeUserById($usr_id);
    }

    protected function wasEditedByViewer(int $examiner_id = null): bool
    {
        return $examiner_id === $this->current_user_id || null === $examiner_id;
    }

    protected function txt(string $code): string
    {
        return $this->lng->txt($code);
    }
}
