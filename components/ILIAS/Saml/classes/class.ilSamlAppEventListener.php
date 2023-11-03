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

class ilSamlAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;

        if ($a_component === 'components/ILIAS/Authentication' && $a_event === 'afterLogout' &&
            isset($a_parameter['is_explicit_logout']) && $a_parameter['is_explicit_logout'] === true &&
            isset($a_parameter['used_external_auth_mode']) && $a_parameter['used_external_auth_mode']) {
            if ((int) $a_parameter['used_external_auth_mode'] === ilAuthUtils::AUTH_SAML) {
                $DIC->ctrl()->redirectToURL('saml.php?action=logout&logout_url=' . urlencode(ILIAS_HTTP_PATH . '/login.php'));
            }
        }
    }
}
