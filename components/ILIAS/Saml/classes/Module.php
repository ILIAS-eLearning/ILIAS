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

namespace ILIAS\Saml;

use SimpleSAML\Utils\Auth;
use SimpleSAML\Configuration;
use ilSamlAuthFactory;
use SimpleSAML\Module as SimpleSamlModule;
use ILIAS\Filesystem\Stream\Streams;
use ilSession;

class Module
{
    public static function run(): void
    {
        (new ilSamlAuthFactory())->auth();
        if (preg_match('@^/saml/sp/saml2-logout.php/@', $_SERVER['PATH_INFO'])) {
            global $DIC;
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
            $DIC['ilAuthSession']->logout();
            $DIC['ilAppEventHandler']->raise('Services/Authentication', 'afterLogout', [
                'username' => $DIC->user()->getLogin(),
            ]);
        }
        SimpleSamlModule::process()->send();
    }

    public static function metadata(): void
    {
        $auth = (new ilSamlAuthFactory())->auth();
        $config = Configuration::getInstance();
        if ($config->getOptionalBoolean('admin.protectmetadata', false)) {
            $admin = new Auth();
            $admin->requireAdmin();
        }

        $xml = (new Metadata(new DefaultSimpleSamlFactory()))->buildXML($auth);
        self::sendXMLString($xml);
    }

    private static function sendXMLString(string $xml_string): void
    {
        global $DIC;
        $http = $DIC->http();

        $response = $http->response()
                         ->withHeader('Content-Type', 'application/xml')
                         ->withBody(Streams::ofString($xml_string));

        $http->saveResponse($response);
        $http->sendResponse();
        $http->close();
    }
}
