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
 * The session cookie of ILIAS in the LTI flows. ILIAS sends it as SameSite=Lax, which keeps it from requests
 * another site starts: the pages ILIAS shows inside the iframe of a platform, and the POST with which a tool
 * asks for the id_token of a launch. For these the cookie is sent again as SameSite=None.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTISessionCookie
{
    /**
     * Sends the session cookie again as SameSite=None, with the path and the domain of the one PHP sends so
     * that it replaces that one instead of adding a second cookie of the same name.
     *
     * The header is set directly, as some LTI responses are echoed by celtic/lti and never pass the HTTP
     * service. A SameSite=None cookie must be secure; it is marked so even when ILIAS does not detect HTTPS
     * behind a proxy, and a browser on plain HTTP just ignores it.
     */
    public static function allowCrossSite(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE || headers_sent()) {
            return;
        }

        $parameters = session_get_cookie_params();

        setcookie(session_name(), session_id(), [
            'expires' => $parameters['lifetime'] > 0 ? time() + $parameters['lifetime'] : 0,
            'path' => $parameters['path'],
            'domain' => $parameters['domain'],
            'secure' => true,
            'httponly' => $parameters['httponly'],
            'samesite' => 'None',
        ]);
    }

    /**
     * Removes the session cookie and the client cookie from the browser at the end of an LTI session.
     *
     * The headers are set directly as well: the end of an LTI session usually redirects to the platform with
     * ilCtrl::redirectToURL(), which sends a new response and drops the cookies of the HTTP service.
     */
    public static function remove(): void
    {
        if (headers_sent()) {
            return;
        }

        $parameters = session_get_cookie_params();
        foreach ([session_name(), 'ilClientId'] as $name) {
            setcookie($name, '', [
                'expires' => time() - 3600,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => (bool) $parameters['secure'],
                'httponly' => (bool) $parameters['httponly'],
            ]);
        }
    }
}
