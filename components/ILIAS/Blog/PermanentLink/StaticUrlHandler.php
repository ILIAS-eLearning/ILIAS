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

namespace ILIAS\PermanentLink;

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
        return 'blog';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $access = $DIC->access();
        $uri = "";

        $id = $request->getReferenceId()?->toInt() ?? 0;
        $additional_params = $request->getAdditionalParameters() ?? [];

        $wsp = count($additional_params) > 0 &&
            $additional_params[count($additional_params) - 1] === "_wsp";
        $posting_id = 0;
        if (is_numeric($additional_params[0] ?? "")) {
            $posting_id = (int) $additional_params[0];
        }
        $edit = false;
        if ($posting_id > 0 && ($additional_params[1] ?? "" === "edit")) {
            $edit = true;
        }
        if ($posting_id > 0) {
            $ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", $posting_id);
        }

        if ($wsp) {
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "wsp_id", $id);
            if ($posting_id > 0) {
                if ($edit) {
                    $uri = $ctrl->getLinkTargetByClass([
                        \ilDashboardGUI::class,
                        \ilPersonalWorkspaceGUI::class,
                        \ilObjBlogGUI::class,
                        \ilBlogPostingGUI::class,
                    ], "edit");
                } else {
                    $uri = $ctrl->getLinkTargetByClass([
                        \ilSharedResourceGUI::class,
                        \ilObjBlogGUI::class,
                        \ilBlogPostingGUI::class,
                    ], "previewFullscreen");
                }
            } else {
                $uri = $ctrl->getLinkTargetByClass([
                    \ilSharedResourceGUI::class,
                    \ilObjBlogGUI::class
                ], "preview");
            }
        } else {
            $ctrl->setParameterByClass(\ilRepositoryGUI::class, "ref_id", $id);
            if ($posting_id > 0) {
                if ($edit && $access->checkAccess("write", "", $id)) {
                    $uri = $ctrl->getLinkTargetByClass([
                        \ilRepositoryGUI::class,
                        \ilObjBlogGUI::class,
                        \ilBlogPostingGUI::class,
                    ], "edit");
                } elseif ($access->checkAccess("read", "", $id)) {
                    $uri = $ctrl->getLinkTargetByClass([
                        \ilRepositoryGUI::class,
                        \ilObjBlogGUI::class,
                        \ilBlogPostingGUI::class,
                    ], "previewFullscreen");
                }
            } else {
                if ($access->checkAccess("read", "", $id)) {
                    $uri = $ctrl->getLinkTargetByClass([
                        \ilRepositoryGUI::class,
                        \ilObjBlogGUI::class
                    ], "preview");
                }
            }

            if ($uri === "" &&
                $access->checkAccess("visible", "", $id)) {
                $uri = $ctrl->getLinkTargetByClass([
                    \ilRepositoryGUI::class,
                    \ilObjBlogGUI::class
                ], "infoScreen");
            }
        }
        return $response_factory->can($uri);
    }

}
