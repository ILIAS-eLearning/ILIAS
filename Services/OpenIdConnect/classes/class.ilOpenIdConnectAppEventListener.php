<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * event listener
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOpenIdConnectAppEventListener implements ilAppEventListener
{
    protected function handleLogoutFor(int $user_id) : void
    {
        $provider = new ilAuthProviderOpenIdConnect(new ilAuthFrontendCredentials());
        $provider->handleLogout();
    }

    /**
     * @inheritDoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        global $DIC;
        $DIC->logger()->auth()->debug($a_component . ' : ' . $a_event);
        if (($a_component === 'Services/Authentication') && $a_event === 'beforeLogout') {
            $listener = new self();
            $listener->handleLogoutFor($a_parameter['user_id']);
        }
    }
}
