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

namespace ILIAS\components\Authentication\Logout;

use ilLink;
use ilSetting;
use ilStartUpGUI;
use ILIAS\Data\URI;
use ilCtrlInterface;

class ConfigurableLogoutHandler
{
    public const LOGIN_SCREEN = 'login_screen';
    public const LOGOUT_SCREEN = 'logout_screen';
    public const INTERNAL_RESSOURCE = 'internal_ressource';
    public const EXTERNAL_RESSOURCE = 'external_ressource';
    private ilSetting $settings;
    private ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->settings = new ilSetting('auth');
    }

    public function afterLogout(): URI
    {
        switch ($this->settings->get('logout_behaviour', '')) {
            case self::LOGIN_SCREEN:
                return new URI(
                    ILIAS_HTTP_PATH .
                    '/' . $this->ctrl->getLinkTargetByClass(ilStartUpGUI::class, 'showLoginPage')
                );
            case self::INTERNAL_RESSOURCE:
                $ref_id = (int) $this->settings->get('logout_behaviour_ref_id', '0');

                return new URI(ilLink::_getStaticLink($ref_id));
            case self::EXTERNAL_RESSOURCE:
                $url = $this->settings->get('logout_behaviour_url', '');
                if ($url) {
                    return new URI($url);
                }
                // no break
            case self::LOGOUT_SCREEN:
            default:
                return new URI(
                    ILIAS_HTTP_PATH .
                    '/' . $this->ctrl->getLinkTargetByClass(ilStartUpGUI::class, 'showLogout')
                );
        }
    }
}
