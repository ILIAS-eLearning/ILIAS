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

namespace ILIAS\Authentication;

use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;
use ilCtrlInterface;
use ilLanguage;

class StaticUrlHandler extends BaseHandler implements Handler
{
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $language;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->language = $DIC->language();
    }

    public function getNamespace(): string
    {
        return 'auth';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $additional_params = implode('/', $request->getAdditionalParameters() ?? []);

        return match ($additional_params) {
            'login' => $response_factory->can('login.php?' . http_build_query([
                'cmd' => 'force_login',
                'lang' => $this->language->getLangKey(),
            ])),
        };
    }
}
