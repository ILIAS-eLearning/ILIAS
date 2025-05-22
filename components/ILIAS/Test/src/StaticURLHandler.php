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

namespace ILIAS\Test;

use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\BaseHandler;

class StaticURLHandler extends BaseHandler implements Handler
{
    public const NAMESPACE = 'tst';
    public const QUESTION_OPERATIONS = 'qst';

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function handle(
        Request $request,
        Context $context,
        Factory $response_factory
    ): Response {
        $ref_id = $request->getReferenceId()?->toInt() ?? 0;
        $additional_params = $request->getAdditionalParameters();
        $context->ctrl()->setParameterByClass(\ilObjTestGUI::class, 'ref_id', $ref_id);

        if (!$context->checkPermission('read', $ref_id) && !$context->isUserLoggedIn()) {
            return $response_factory->can("login.php?target=tst_{$ref_id}&cmd=force_login");
        }

        $uri = match ($additional_params[0] ?? 'default') {
            self::QUESTION_OPERATIONS => $this->buildQuestionURL($additional_params[1], $context->ctrl()),
            default => $context->ctrl()->getLinkTargetByClass([\ilRepositoryGUI::class, \ilObjTestGUI::class]),
        };

        return $response_factory->can($uri);
    }

    private function buildQuestionURL(string $q_id, \ilCtrl $ctrl): string
    {
        $ctrl->setParameterByClass(\ilAssQuestionPreviewGUI::class, 'q_id', $q_id);
        $link = $ctrl->getLinkTargetByClass([\ilRepositoryGUI::class, \ilObjTestGUI::class, \ilAssQuestionPreviewGUI::class], \ilAssQuestionPreviewGUI::CMD_SHOW);
        $ctrl->clearParameterByClass(\ilAssQuestionPreviewGUI::class, 'q_id');
        return $link;
    }
}
