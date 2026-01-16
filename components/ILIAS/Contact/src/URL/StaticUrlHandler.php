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

namespace ILIAS\Contact\URL;

use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;
use ILIAS\Data\ReferenceId;
use ILIAS\User\Profile\PublicProfileGUI;

class StaticUrlHandler extends BaseHandler implements Handler
{
    public const string NAMESPACE = 'contact';

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        if (!$context->isUserLoggedIn()) {
            return $response_factory->loginFirst();
        }

        if (!\ilBuddySystem::getInstance()->isEnabled()) {
            return $response_factory->cannot();
        }

        $params = implode('/', $request->getAdditionalParameters() ?? []);
        $path = parse_url($params, PHP_URL_PATH);

        return match ($path) {
            'approve' => $response_factory->can($this->buildProfileUrl(
                $context->ctrl(),
                $request->getReferenceId(),
                'approveContactRequest'
            )),
            'ignore' => $response_factory->can($this->buildProfileUrl(
                $context->ctrl(),
                $request->getReferenceId(),
                'ignoreContactRequest'
            )),
            default => $response_factory->cannot(),
        };
    }

    private function buildProfileUrl(
        \ilCtrl $ctrl,
        ?ReferenceId $target_user_id,
        string $cmd
    ): string {
        if ($target_user_id === null) {
            return $ctrl->getLinkTargetByClass(
                [\ilDashboardGUI::class],
                'jumpToProfile'
            );
        }

        $ctrl->setParameterByClass(PublicProfileGUI::class, 'user_id', $target_user_id->toInt());

        return $ctrl->getLinkTargetByClass([\ilPublicProfileBaseClassGUI::class, PublicProfileGUI::class], $cmd);
    }
}
