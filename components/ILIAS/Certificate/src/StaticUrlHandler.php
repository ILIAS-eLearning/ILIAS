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

namespace ILIAS\Certificate;

use ilAchievementsGUI;
use ilDashboardGUI;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;
use ilSetting;
use ilUserCertificateGUI;

class StaticUrlHandler extends BaseHandler implements Handler
{
    public function getNamespace(): string
    {
        return 'cert';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        if (!$context->isUserLoggedIn()) {
            return $response_factory->loginFirst();
        }

        $certificate_settings = new ilSetting('certificate');
        if (!$certificate_settings->get('active', '0')) {
            return $response_factory->cannot();
        }

        $additional_params = implode('/', $request->getAdditionalParameters() ?? []);

        return match ($additional_params) {
            'list' => $response_factory->can($context->ctrl()->getLinkTargetByClass(
                [
                    ilDashboardGUI::class,
                    ilAchievementsGUI::class,
                    ilUserCertificateGUI::class,
                ],
                'listCertificates',
            )),
            default => $response_factory->cannot()
        };
    }
}
