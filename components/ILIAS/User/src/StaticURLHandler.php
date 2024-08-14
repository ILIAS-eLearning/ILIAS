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

namespace ILIAS\User;

use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\Data\URI;

class StaticURLHandler extends BaseHandler implements Handler
{
    public const NAMESPACE = 'user';
    public const CHANGE_EMAIL_OPERATIONS = 'email';

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function handle(
        Request $request,
        Context $context,
        Factory $response_factory
    ): Response {
        $additional_params = $request->getAdditionalParameters();

        $uri = match ($additional_params[0] ?? 'default') {
            self::CHANGE_EMAIL_OPERATIONS => $context->isUserLoggedIn()
                    ? $this->buildChangeEmailUrl($additional_params[1], $context->ctrl())
                    : $this->getLoginUrl($request, $context),
            default => $context->ctrl()->getLinkTargetByClass([\ilDashboardGUI::class], 'jumpToProfile'),
        };

        return $response_factory->can($uri);
    }

    private function buildChangeEmailUrl(string $token, \ilCtrl $ctrl): string
    {
        $ctrl->setParameterByClass(\ilPersonalProfileGUI::class, 'token', $token);
        $link = $ctrl->getLinkTargetByClass([\ilDashboardGUI::class, \ilPersonalProfileGUI::class], \ilPersonalProfileGUI::CHANGE_EMAIL_CMD);
        $ctrl->clearParameterByClass(\ilPersonalProfileGUI::class, 'token');
        return $link;
    }

    private function getLoginUrl(
        Request $request,
        Context $context
    ): string {
        $target = (new StandardURIBuilder(ILIAS_HTTP_PATH, false))->buildTarget(
            $request->getNamespace(),
            $request->getReferenceId(),
            $request->getAdditionalParameters()
        );

        return '/login.php?target='
            . str_replace('/', '_', rtrim($target, '/'))
            . '&cmd=force_login&lang=' . $context->getUserLanguage();

    }
}
