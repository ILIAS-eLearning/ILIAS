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
 * Adapts the request URI of an incoming LTI 1.1 launch so that celtic/lti checks its OAuth1 signature
 * against the URL the platform signed.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTI1p1ProviderLaunchRequestUri
{
    /**
     * Removes the client_id query parameter from REQUEST_URI before the OAuth1 signature of a basic launch is checked.
     */
    public static function stripClientId(): void
    {
        // celtic/lti builds the signed URL from $_SERVER['REQUEST_URI'] in OAuth\OAuthRequest::from_request(),
        // so there is no API to override it: this is the only superglobal access of the component.
        $_SERVER['REQUEST_URI'] = rtrim(preg_replace('/([?&])client_id=[^&]+(&|$)/', '$1', $_SERVER['REQUEST_URI']), '?&');
    }
}
