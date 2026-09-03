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

namespace ILIAS\Init\URL;

use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;

class StaticUrlHandler extends BaseHandler implements Handler
{
    public const string NAMESPACE = 'assistant';

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $settings = new \ilSetting();
        if (!$settings->get('password_assistance', '0')) {
            return $response_factory->cannot();
        }

        $path = implode('/', $request->getAdditionalParameters() ?? []);

        return match ($path) {
            'password' => $response_factory->can(
                $context->ctrl()->getLinkTargetByClass(
                    [
                        \ilStartUpGUI::class,
                        \ilPasswordAssistanceGUI::class
                    ]
                )
            ),
            'username' => $response_factory->can(
                $context->ctrl()->getLinkTargetByClass(
                    [
                        \ilStartUpGUI::class,
                        \ilPasswordAssistanceGUI::class
                    ],
                    'showUsernameAssistanceForm'
                )
            ),
            default => $response_factory->cannot(),
        };
    }
}
