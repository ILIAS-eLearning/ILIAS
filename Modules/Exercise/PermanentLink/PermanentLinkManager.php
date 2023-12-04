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

declare(strict_types=1);

namespace ILIAS\Exercise\PermanentLink;

use ILIAS\Exercise\InternalGUIService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\UICore\PageContentProvider;
use ILIAS\StaticURL\Services as StaticUrl;
use ILIAS\Data\ReferenceId;

/**
 * Link to exercise: goto.php?target=exc_<exc_ref_id>
 * Link to assignment: goto.php?target=exc_<exc_ref_id>_<ass_id>
 * Link to grades screen of assignment: goto.php?target=exc_<exc_ref_id>_<ass_id>_grades
 * Link to download screen of member in assignment: goto.php?target=exc_<exc_ref_id>_<ass_id>_<member_id>_setdownload
 */
class PermanentLinkManager
{
    protected StaticUrl $static_url;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui
    ) {
        global $DIC;
        /** @var StaticUrl $static_url */
        $this->static_url = $DIC['static_url'];

        $this->domain = $domain;
        $this->gui = $gui;
    }

    /*
    public function goto(
        string $target,
        string $raw
    ): void {

        $main_tpl = $this->gui->ui()->mainTemplate();
        $request = $this->gui->request();
        $ass_id = $request->getAssId();

        $lng = $this->domain->lng();
        $ilAccess = $this->domain->access();
        $ilCtrl = $this->gui->ctrl();

        //we don't have baseClass here...
        $ilCtrl->setTargetScript("ilias.php");

        //ilExerciseMailNotification has links to:
        // "Assignments", "Submission and Grades" and Downnoad the NEW files if the assignment type is "File Upload".
        $parts = explode("_", $raw);
        $action = null;
        $member = null;
        if (!$ass_id) {
            $ass_id = (int) ($parts[1] ?? 0);

            switch (end($parts)) {
                case "download":
                case "setdownload":
                    $action = $parts[3] ?? "";
                    $member = $parts[2];
                    break;

                case "given":
                    $action = $parts[3] ?? "";
                    $peer_id = (int) ($parts[2] ?? 0);
                    break;

                case "grades":
                case "received":
                    $action = $parts[2] ?? "";
                    break;
            }
        }

        $ilCtrl->setParameterByClass(\ilExerciseHandlerGUI::class, "ref_id", $target);

        if ($ilAccess->checkAccess("read", "", (int) $target)) {
            $ilCtrl->setParameterByClass(\ilExerciseHandlerGUI::class, "target", $raw);

            if ($ass_id > 0) {
                $ilCtrl->setParameterByClass(\ilExerciseManagementGUI::class, "ass_id", $ass_id);
            }

            switch ($action) {
                case "grades":
                    $ilCtrl->redirectByClass(
                        [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilExerciseManagementGUI::class],
                        "members"
                    );
                    break;

                case "setdownload":
                    $ilCtrl->setParameterByClass(\ilExerciseHandlerGUI::class, "member_id", $member);
                    $ilCtrl->redirectByClass(
                        array(\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilExerciseManagementGUI::class),
                        "waitingDownload"
                    );
                    break;

                case "given":
                    $ilCtrl->setParameterByClass(\ilObjExerciseGUI::class, "ass_id", $ass_id);
                    $ilCtrl->setParameterByClass(\ilObjExerciseGUI::class, "peer_id", $peer_id);
                    $ilCtrl->redirectByClass(
                        array(\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class,
                              \ilExSubmissionGUI::class, \ilExPeerReviewGUI::class),
                        "editPeerReviewItem"
                    );
                    break;

                case "received":
                    $ilCtrl->setParameterByClass(\ilObjExerciseGUI::class, "ass_id", $ass_id);
                    $ilCtrl->redirectByClass(
                        array(\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class,
                              \ilExSubmissionGUI::class, \ilExPeerReviewGUI::class),
                        "showReceivedPeerReview"
                    );
                    break;

                default:
                    if ($ass_id > 0) {
                        $ilCtrl->setParameterByClass(\ilObjExerciseGUI::class, "ass_id", $ass_id);
                        $ilCtrl->redirectByClass(
                            [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class],
                            ""
                        );
                    }
                    $ilCtrl->redirectByClass(
                        [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class],
                        "showOverview"
                    );
                    break;

            }
        } elseif ($ilAccess->checkAccess("visible", "", (int) $target)) {
            $ilCtrl->redirectByClass(
                [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class],
                "infoScreen"
            );
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                \ilObject::_lookupTitle(\ilObject::_lookupObjId((int) $target))
            ), true);
            \ilObjectGUI::_gotoRepositoryRoot();
        }
    }*/

    protected function _setPermanentLink(array $append): void
    {
        $request = $this->gui->request();
        $ref_id = $request->getRefId();
        $uri = $this->static_url->builder()->build(
            'exc', // namespace
            $ref_id > 0 ? new ReferenceId($ref_id) : null, // ref_id
            $append // additional parameters
        );
        PageContentProvider::setPermaLink((string) $uri);
    }

    public function setPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getDefaultAppend(
                $request->getAssId()
            )
        );
    }

    public function getDefaultAppend(int $ass_id): array
    {
        $append = [];
        if ($ass_id > 0) {
            $append[] = $ass_id;
        }
        return $append;
    }

    public function getPermanentLink(int $ref_id, int $ass_id): string
    {
        $append = $this->getDefaultAppend($ass_id);
        $uri = $this->static_url->builder()->build(
            'exc', // namespace
            $ref_id > 0 ? new ReferenceId($ref_id) : null, // ref_id
            $append // additional parameters
        );

        return (string) $uri;
    }



    public function getDownloadSubmissionAppend(int $ass_id, int $user_id): array
    {
        return [$ass_id, $user_id, "setdownload"];
    }

    public function setGradesPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getGradesAppend(
                $request->getAssId()
            )
        );
    }

    public function getGradesAppend(int $ass_id): array
    {
        return [$ass_id, "grades"];
    }

    public function setGivenFeedbackPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getGivenFeedbackAppend(
                $request->getAssId(),
                $request->getPeerId()
            )
        );
    }

    public function getGivenFeedbackAppend(int $ass_id, int $peer_id): array
    {
        return [$ass_id, $peer_id, "given"];
    }

    public function setReceivedFeedbackPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getReceivedFeedbackAppend(
                $request->getAssId()
            )
        );
    }

    public function getReceivedFeedbackAppend(int $ass_id): array
    {
        return [$ass_id, "received"];
    }

}
