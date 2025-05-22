<?php

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

use ILIAS\Exercise\Submission\Submission;

/**
 * Delivered files table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilExcDeliveredFilesTableGUI extends ilTable2GUI
{
    protected ilExSubmission $submission;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExSubmission $a_submission
    ) {
        global $DIC;

        $this->submission = $a_submission;
        $subm = $DIC->exercise()->internal()->domain()->submission(
            $this->submission->getAssignment()->getId()
        );

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $data = [];
        foreach ($subm->getSubmissionsOfUser($a_submission->getUserId()) as $s) {
            $data[] = [
                "submission" => $s
            ];
        }
        $this->setData($data);
        $this->setTitle(
            $this->lng->txt("already_delivered_files") . " - " .
                $this->submission->getAssignment()->getTitle()
        );
        $this->setLimit(9999);

        $this->addColumn($this->lng->txt(""), "", "1", 1);
        $this->addColumn($this->lng->txt("filename"), "filetitle");

        if ($this->submission->getAssignment()->getAssignmentType()->usesTeams() &&
            $this->submission->getAssignment()->getAssignmentType()->usesFileUpload()) {
            // #11957
            $this->lng->loadLanguageModule("file");
            $this->addColumn($this->lng->txt("file_uploaded_by"));
        }

        $this->addColumn($this->lng->txt("date"), "timestamp14");

        if ($this->submission->getAssignment()->getExtendedDeadline()) {
            $this->addColumn($this->lng->txt("exc_late_submission"), "late");
        }

        $this->addColumn($this->lng->txt("action"));
        $this->setDefaultOrderField("filetitle");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.delivered_file_row.html", "components/ILIAS/Exercise");
        $this->disable("footer");
        $this->setEnableTitle(true);

        if ($this->submission->canSubmit()) {
            $this->addMultiCommand("confirmDeleteDelivered", $lng->txt("delete"));
        }
    }

    /**
     * @throws ilDateTimeException
     */
    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;

        /** @var Submission $sub */
        $sub = $a_set["submission"];

        $this->tpl->setVariable("FILE_ID", $sub->getId());
        $this->tpl->setVariable("DELIVERED_FILE", htmlentities($sub->getTitle()));

        $date = new ilDateTime($sub->getTimestamp14(), IL_CAL_TIMESTAMP);
        $this->tpl->setVariable("DELIVERED_DATE", ilDatePresentation::formatDate($date));

        if ($this->submission->getAssignment()->getAssignmentType()->usesTeams() &&
            $this->submission->getAssignment()->getAssignmentType()->usesFileUpload()) {
            $this->tpl->setVariable(
                "DELIVERED_OWNER",
                ilUserUtil::getNamePresentation($sub->getUserId())
            );
        }

        if ($this->submission->getAssignment()->getExtendedDeadline()) {
            $this->tpl->setVariable("DELIVERED_LATE", ($sub->getLate())
                ? '<span class="warning">' . $this->lng->txt("yes") . '</span>'
                : $this->lng->txt("no"));
        }

        // #16164 - download
        $ilCtrl->setParameter($this->getParentObject(), "delivered", $sub->getId());
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), "download");
        $ilCtrl->setParameter($this->getParentObject(), "delivered", "");
        $this->tpl->setVariable("ACTION_TXT", $this->lng->txt("download"));
        $this->tpl->setVariable("ACTION_URL", $url);
    }
}
