<?php
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
 * Class ilShibbolethPluginWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilShibbolethPluginWrapper implements ilShibbolethAuthenticationPluginInt
{
    protected ilComponentFactory $component_factory;
    protected ilComponentLogger $log;
    protected static ?ilShibbolethPluginWrapper $cache = null;

    protected function __construct()
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $this->log = $ilLog;
        $this->component_factory = $DIC["component.factory"];
    }

    public static function getInstance(): self
    {
        if (!self::$cache instanceof self) {
            self::$cache = new self();
        }

        return self::$cache;
    }

    /**
     * @return ilShibbolethAuthenticationPlugin[]
     */
    protected function getPluginObjects(): Iterator
    {
        return $this->component_factory->getActivePluginsInSlot('shibhk');
    }


    public function beforeLogin(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeLogin($user);
        }

        return $user;
    }


    public function afterLogin(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterLogin($user);
        }

        return $user;
    }


    public function beforeCreateUser(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeCreateUser($user);
        }

        return $user;
    }


    public function afterCreateUser(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterCreateUser($user);
        }

        return $user;
    }


    public function beforeLogout(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeLogout($user);
        }

        return $user;
    }


    public function afterLogout(ilObjUser $user): ilObjUser
    {
        $this->log->write('afterlogout');
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterLogout($user);
        }

        return $user;
    }


    public function beforeUpdateUser(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeUpdateUser($user);
        }

        return $user;
    }


    public function afterUpdateUser(ilObjUser $user): ilObjUser
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterUpdateUser($user);
        }

        return $user;
    }
}
