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
use ilAccess;
use ilObject;
use ilSetting;
use ilStartUpGUI;
use ILIAS\Data\URI;
use ilCtrlInterface;
use InvalidArgumentException;

class ConfigurableLogoutService
{
    public const LOGIN_SCREEN = 'login_screen';
    public const LOGOUT_SCREEN = 'logout_screen';
    public const INTERNAL_RESSOURCE = 'internal_ressource';
    public const EXTERNAL_RESSOURCE = 'external_ressource';

    public function __construct(
        private readonly ilCtrlInterface $ctrl,
        private readonly ilSetting $settings,
        private readonly ilAccess $access,
        private readonly string $http_path = ILIAS_HTTP_PATH,
    ) {
    }

    public function afterLogout(): URI
    {
        switch ($this->settings->get('logout_behaviour', '')) {
            case self::LOGIN_SCREEN:
                return new URI(
                    $this->http_path .
                    '/' . $this->ctrl->getLinkTargetByClass(ilStartUpGUI::class, 'showLoginPage')
                );

            case self::INTERNAL_RESSOURCE:
                $ref_id = (int) $this->settings->get('logout_behaviour_ref_id', '0');
                if ($this->isValidInternalResource($ref_id)) {
                    return new URI(ilLink::_getStaticLink($ref_id));
                }
                // no break
            case self::EXTERNAL_RESSOURCE:
                $url = $this->settings->get('logout_behaviour_url', '');
                if ($url && $this->isValidExternalResource($url)) {
                    return new URI($url);
                }
                // no break
            case self::LOGOUT_SCREEN:
            default:
                return new URI(
                    $this->http_path .
                    '/' . $this->ctrl->getLinkTargetByClass(ilStartUpGUI::class, 'showLogout')
                );
        }
    }

    public function isValidInternalResource(int $ref_id): bool
    {
        return $this->isInRepository($ref_id) && $this->isAnonymousAccessible($ref_id);
    }

    public function isInRepository($ref_id): bool
    {
        return ilObject::_exists($ref_id, true) && !ilObject::_isInTrash($ref_id);
    }

    public function isAnonymousAccessible(int $ref_id): bool
    {
        return $this->access->checkAccessOfUser(ANONYMOUS_USER_ID, 'read', '', $ref_id);
    }

    public function isValidExternalResource(string $url): bool
    {
        try {
            $uri = new URI($url);
        } catch (InvalidArgumentException) {
            return false;
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
