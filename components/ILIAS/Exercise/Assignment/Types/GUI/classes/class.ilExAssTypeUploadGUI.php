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

/**
 * Upload type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeUploadGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form): void
    {
    }

    /**
     * @inheritdoc
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission): void
    {
    }

    public function buildSubmissionPropertiesAndActions(\ILIAS\Exercise\Assignment\PropertyAndActionBuilderUI $builder): void
    {
        global $DIC;

        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();
        $submission = $this->getSubmission();
        $f = $DIC->ui()->factory();

        $titles = array();
        foreach ($submission->getFiles() as $file) {
            $titles[] = htmlentities($file["filetitle"]);
        }
        $files_str = implode("<br>", $titles);
        if ($files_str == "") {
            $files_str = $lng->txt("message_no_delivered_files");
        }

        // no team == no submission
        if (!$submission->hasNoTeamYet()) {
            if ($submission->canSubmit()) {
                $title = (count($titles) == 0
                    ? $lng->txt("exc_hand_in")
                    : $lng->txt("exc_edit_submission"));
                $url = $ctrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen");
                $main_button = $f->button()->primary(
                    $title,
                    $url
                );
                $builder->setMainAction($builder::SEC_SUBMISSION, $main_button);
                $builder->addView(
                    "submission",
                    $lng->txt("exc_submission"),
                    $url
                );
            } else {
                if (count($titles) > 0) {
                    $url = $ctrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen");
                    $link = $f->link()->standard(
                        $lng->txt("already_delivered_files"),
                        $url
                    );
                    $builder->addAction($builder::SEC_SUBMISSION, $link);
                    $builder->addView(
                        "submission",
                        $lng->txt("exc_submission"),
                        $url
                    );
                }
            }
        }

        $builder->addProperty(
            $builder::SEC_SUBMISSION,
            $lng->txt("exc_files_returned"),
            $files_str
        );
    }

}
