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

/**
 * The addresses ILIAS gives the platforms and tools it talks to: its base URL, which is also its issuer in
 * LTI Advantage, the URLs of the entry points in resources/ and the id of the installation. They must not
 * change, since the other side keeps them.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIEndpoint
{
    public const string LAUNCH = 'lti.php';
    public const string AUTHENTICATION = 'ltiauth.php';
    public const string KEY_SET = 'lticerts.php';
    public const string CONFIGURATION = 'lticonfig.php';
    public const string REGISTRATION = 'ltiregistration.php';
    public const string RESULT = 'ltiresult.php';
    public const string SERVICES = 'ltiservices.php';
    public const string TOKEN = 'ltitoken.php';
    public const string TOOL_REGISTRATION = 'ltitoolregistration.php';

    /**
     * The base URL of the installation as the request reached it.
     */
    public static function getBaseUrl(): string
    {
        return rtrim(ILIAS_HTTP_PATH, '/');
    }

    /**
     * The URL of an entry point, with the query or path that follows it.
     */
    public static function getUrl(string $script, string $suffix = ''): string
    {
        return self::getBaseUrl() . '/' . $script . $suffix;
    }

    /**
     * The URL of a path of the services. It is built from the configured HTTP path: under ltiservices.php,
     * ILIAS_HTTP_PATH ends in the script and the path after it, and a tool reads back the URLs of its launch.
     */
    public static function getServiceUrl(string $path): string
    {
        global $DIC;

        $http_path = ilContext::modifyHttpPath($DIC->iliasIni()->readVariable('server', 'http_path'));

        return rtrim($http_path, '/') . '/' . self::SERVICES . $path;
    }

    /**
     * The id of the installation the tools know ILIAS by in every launch, LTI 1.1 or LTI Advantage: the
     * client id followed by the path and the host of ILIAS, reversed.
     */
    public static function getInstanceGuid(): string
    {
        $url = parse_url(self::getBaseUrl());
        $guid = CLIENT_ID . '.';
        if (isset($url['path'])) {
            $guid .= implode('.', array_reverse(explode('/', $url['path'])));
        }

        return $guid . $url['host'];
    }
}
