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

use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Handler\ilCtrlInterface;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\BaseHandler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilFileStaticURLHandler extends BaseHandler implements Handler
{
    public const DOWNLOAD = 'download';
    public const VERSIONS = 'versions';

    public function getNamespace(): string
    {
        return 'file';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $ref_id = $request->getReferenceId()?->toInt() ?? 0;
        $additional_params = $request->getAdditionalParameters()[0] ?? null;
        $context->ctrl()->setParameterByClass(ilObjFileGUI::class, 'ref_id', $ref_id);

        $uri = match ($additional_params) {
            self::DOWNLOAD => $context->ctrl()->getLinkTargetByClass(
                [ilRepositoryGUI::class, ilObjFileGUI::class],
                ilObjFileGUI::CMD_SEND_FILE
            ),
            self::VERSIONS => $context->ctrl()->getLinkTargetByClass(
                [ilRepositoryGUI::class, ilObjFileGUI::class, ilFileVersionsGUI::class]
            ),
            default => $context->ctrl()->getLinkTargetByClass([ilRepositoryGUI::class, ilObjFileGUI::class]),
        };

        return $response_factory->can($uri);
    }

}
