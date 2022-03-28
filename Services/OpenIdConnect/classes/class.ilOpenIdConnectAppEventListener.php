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
class ilOpenIdConnectAppEventListener
{
    private ilLogger $logger;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
    }

    /**
     * @param int $user_id
     */
    protected function handleLogoutFor(int $user_id)
    {
        $provider = new ilAuthProviderOpenIdConnect(new ilAuthFrontendCredentials());
        $provider->handleLogout();
    }
    

    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        ilLoggerFactory::getLogger('root')->info($a_component . ' : ' . $a_event);
        if ($a_component == 'Services/Authentication') {
            ilLoggerFactory::getLogger('root')->info($a_component . ' : ' . $a_event);
            if ($a_event == 'beforeLogout') {
                ilLoggerFactory::getLogger('root')->info($a_component . ' : ' . $a_event);
                $listener = new self();
                $listener->handleLogoutFor($a_parameter['user_id']);
            }
        }
    }
}
