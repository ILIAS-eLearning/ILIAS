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

namespace ILIAS\Registration\URL;

use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;

class StaticUrlHandler extends BaseHandler implements Handler
{
    public const string NAMESPACE = 'registration';

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $registration_settings = new \ilRegistrationSettings();
        if ($registration_settings->getRegistrationType() === \ilRegistrationSettings::IL_REG_DISABLED) {
            return $response_factory->cannot();
        }

        return $response_factory->can($context->ctrl()->getLinkTargetByClass(
            [
                \ilStartUpGUI::class,
                \ilAccountRegistrationGUI::class
            ]
        ));
    }
}
