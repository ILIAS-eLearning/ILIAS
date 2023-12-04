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

use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;

class StaticURLHandler extends BaseHandler implements Handler
{
    public function getNamespace(): string
    {
        return 'exc';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        global $DIC;

        $main_tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC->language();

        $ref_id = $request->getReferenceId()?->toInt() ?? 0;
        $additional_params = $request->getAdditionalParameters() ?? [];
        $last = "";
        if (count($additional_params) > 0) {
            $last = (string) $additional_params[count($additional_params) - 1];
        }
        $ass_id = (int) ($additional_params[0] ?? 0);
        $action = null;
        $member = null;

        switch ($last) {
            case "download":
            case "setdownload":
                $action = $additional_params[2] ?? "";
                $member = $additional_params[1];
                break;

            case "given":
                $action = $additional_params[2] ?? "";
                $peer_id = (int) ($additional_params[1] ?? 0);
                break;

            case "grades":
            case "received":
                $action = $additional_params[1] ?? "";
                break;
        }

        $ctrl = $context->ctrl();

        $ctrl->setParameterByClass(\ilExerciseHandlerGUI::class, "ref_id", $ref_id);

        if ($context->checkPermission("read", $ref_id)) {

            if ($ass_id > 0) {
                $ctrl->setParameterByClass(\ilExerciseManagementGUI::class, "ass_id", $ass_id);
            }

            switch ($action) {
                case "grades":
                    $uri = $ctrl->getLinkTargetByClass(
                        [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilExerciseManagementGUI::class],
                        "members"
                    );
                    break;

                case "setdownload":
                    $ctrl->setParameterByClass(\ilExerciseHandlerGUI::class, "member_id", $member);
                    $uri = $ctrl->getLinkTargetByClass(
                        array(\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilExerciseManagementGUI::class),
                        "waitingDownload"
                    );
                    break;

                case "given":
                    $ctrl->setParameterByClass(\ilObjExerciseGUI::class, "ass_id", $ass_id);
                    $ctrl->setParameterByClass(\ilObjExerciseGUI::class, "peer_id", $peer_id);
                    $uri = $ctrl->getLinkTargetByClass(
                        array(\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class,
                              \ilExSubmissionGUI::class, \ilExPeerReviewGUI::class),
                        "editPeerReviewItem"
                    );
                    break;

                case "received":
                    $ctrl->setParameterByClass(\ilObjExerciseGUI::class, "ass_id", $ass_id);
                    $uri = $ctrl->getLinkTargetByClass(
                        array(\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class,
                              \ilExSubmissionGUI::class, \ilExPeerReviewGUI::class),
                        "showReceivedPeerReview"
                    );
                    break;

                default:
                    if ($ass_id > 0) {
                        $ctrl->setParameterByClass(\ilObjExerciseGUI::class, "ass_id", $ass_id);
                        $uri = $ctrl->getLinkTargetByClass(
                            [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class],
                            ""
                        );
                    } else {
                        $uri = $ctrl->getLinkTargetByClass(
                            [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class],
                            "showOverview"
                        );
                    }
                    break;

            }
        } elseif ($context->checkPermission("visible", $ref_id)) {
            $uri = $ctrl->getLinkTargetByClass(
                [\ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class],
                "infoScreen"
            );
        } elseif ($context->checkPermission("read", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                \ilObject::_lookupTitle(\ilObject::_lookupObjId($ref_id))
            ), true);
            \ilObjectGUI::_gotoRepositoryRoot();
        }

        return $response_factory->can($uri);
    }

}
