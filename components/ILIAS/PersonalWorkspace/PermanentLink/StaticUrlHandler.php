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

namespace ILIAS\PersonalWorkspace\PermanentLink;

use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;

class StaticUrlHandler extends BaseHandler implements Handler
{
    public function getNamespace(): string
    {
        return 'wsp';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $wsp_id = $request->getReferenceId()?->toInt() ?? 0;
        if ($wsp_id <= 0 || !\ilWorkspaceAccessHandler::getObjectDataFromNode($wsp_id)) {
            return $response_factory->cannot();
        }

        $ctrl = $context->ctrl();
        $ctrl->setParameterByClass(\ilSharedResourceGUI::class, 'wsp_id', $wsp_id);

        return $response_factory->can(
            $ctrl->getLinkTargetByClass(\ilSharedResourceGUI::class, 'process')
        );
    }
}
