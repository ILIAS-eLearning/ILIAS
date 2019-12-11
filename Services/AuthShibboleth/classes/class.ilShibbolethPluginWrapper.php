<?php
require_once('./Services/AuthShibboleth/interfaces/interface.ilShibbolethAuthenticationPluginInt.php');

/**
 * Class ilShibbolethPluginWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilShibbolethPluginWrapper implements ilShibbolethAuthenticationPluginInt
{

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;
    /**
     * @var ilLog
     */
    protected $log;
    /**
     * @var array
     */
    protected static $active_plugins = array();
    /**
     * @var ilShibbolethPluginWrapper
     */
    protected static $cache = null;


    protected function __construct()
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        $ilLog = $DIC['ilLog'];
        $this->log = $ilLog;
        $this->plugin_admin = $ilPluginAdmin;
        if (self::$active_plugins == null) {
            self::$active_plugins = $this->plugin_admin->getActivePluginsForSlot(IL_COMP_SERVICE, 'AuthShibboleth', 'shibhk');
        }
    }


    /**
     * @return ilShibbolethPluginWrapper
     */
    public static function getInstance()
    {
        if (!self::$cache instanceof ilShibbolethPluginWrapper) {
            self::$cache = new self();
        }

        return self::$cache;
    }


    /**
     * @return ilShibbolethAuthenticationPlugin[]
     */
    protected function getPluginObjects()
    {
        $plugin_objs = array();
        foreach (self::$active_plugins as $plugin_name) {
            $plugin_obj = $this->plugin_admin->getPluginObject(IL_COMP_SERVICE, 'AuthShibboleth', 'shibhk', $plugin_name);
            if ($plugin_obj instanceof ilShibbolethAuthenticationPlugin) {
                $plugin_objs[] = $plugin_obj;
            }
        }

        return $plugin_objs;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeLogin(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeLogin($user);
        }

        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterLogin(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterLogin($user);
        }

        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeCreateUser(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeCreateUser($user);
        }

        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterCreateUser(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterCreateUser($user);
        }

        return $user;
    }


    public function beforeLogout(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeLogout($user);
        }

        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterLogout(ilObjUser $user)
    {
        $this->log->write('afterlogout');
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterLogout($user);
        }

        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeUpdateUser(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->beforeUpdateUser($user);
        }

        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterUpdateUser(ilObjUser $user)
    {
        foreach ($this->getPluginObjects() as $pl) {
            $user = $pl->afterUpdateUser($user);
        }

        return $user;
    }
}
