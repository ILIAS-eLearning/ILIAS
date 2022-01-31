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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAuthentication
*/

class ilAuthModeDetermination
{
    const TYPE_MANUAL = 0;
    const TYPE_AUTOMATIC = 1;
    
    protected static $instance = null;
    
    protected $db = null;
    protected $settings = null;
    
    protected $kind = 0;
    protected $position = array();
    

    /**
     * Constructor (Singleton)
     *
     * @access private
     *
     */
    private function __construct()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilDB = $DIC['ilDB'];
        
        $this->db = $ilDB;

        include_once "./Services/Administration/classes/class.ilSetting.php";
        $this->settings = new ilSetting("auth_mode_determination");
        $this->read();
    }
    
    /**
     * Get instance
     *
     * @access public
     * @static
     *
     * @return ilAuthModeDetermination
     */
    public static function _getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthModeDetermination();
    }

    /**
     * is manual selection
     *
     * @access public

     *
     * @param
     */
    public function isManualSelection()
    {
        return $this->kind == self::TYPE_MANUAL;
    }

    /**
     * get kind
     *
     * @access public
     *
     */
    public function getKind()
    {
        return $this->kind;
    }
    
    /**
     * set kind of determination
     *
     * @access public
     * @param int TYPE_MANUAL or TYPE_DETERMINATION
     *
     */
    public function setKind($a_kind)
    {
        $this->kind = $a_kind;
    }
    
    /**
     * get auth mode sequence
     *
     * @access public
     *
     */
    public function getAuthModeSequence($a_username = '')
    {
        if (!strlen($a_username)) {
            return $this->position ? $this->position : array();
        }
        $sorted = array();
        
        foreach ($this->position as $auth_key) {
            include_once './Services/LDAP/classes/class.ilLDAPServer.php';
            $sid = ilLDAPServer::getServerIdByAuthMode($auth_key);
            if ($sid) {
                $server = ilLDAPServer::getInstanceByServerId($sid);
                ilLoggerFactory::getLogger('auth')->debug('Validating username filter for ' . $server->getName());
                if (strlen($server->getUsernameFilter())) {
                    //#17731
                    $pattern = str_replace('*', '.*?', $server->getUsernameFilter());

                    if (preg_match('/^' . $pattern . '$/', $a_username)) {
                        ilLoggerFactory::getLogger('auth')->debug('Filter matches for ' . $a_username);
                        array_unshift($sorted, $auth_key);
                        continue;
                    }
                    ilLoggerFactory::getLogger('auth')->debug('Filter matches not for ' . $a_username . ' <-> ' . $server->getUsernameFilter());
                }
            }
            $sorted[] = $auth_key;
        }
        
        return (array) $sorted;
    }
    
    /**
     * get number of auth modes
     *
     * @access public
     *
     */
    public function getCountActiveAuthModes()
    {
        return count($this->position);
    }
    
    /**
     * set auth mode sequence
     *
     * @access public
     * @param array position => AUTH_MODE
     *
     */
    public function setAuthModeSequence($a_pos)
    {
        $this->position = $a_pos;
    }
    
    /**
     * Save settings
     *
     * @access public
     * @param
     *
     */
    public function save()
    {
        $this->settings->deleteAll();
        
        $this->settings->set('kind', $this->getKind());
        
        $counter = 0;
        foreach ($this->position as $auth_mode) {
            $this->settings->set((string) $counter++, $auth_mode);
        }
    }
    
    
    /**
     * Read settings
     *
     * @access private
     * @param
     *
     */
    private function read()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->kind = (int) $this->settings->get('kind', (string) self::TYPE_MANUAL);
        
        $rad_settings = ilRadiusSettings::_getInstance();
        $rad_active = $rad_settings->isActive();

        $soap_active = (bool) $ilSetting->get('soap_auth_active', (string) false);

        // apache settings
        $apache_settings = new ilSetting('apache_auth');
        $apache_active = $apache_settings->get('apache_enable_auth');

        // Check if active
        // begin-patch ldap_multiple
        $i = 0;
        while (true) {
            $auth_mode = $this->settings->get((string) $i++, null);
            if ($auth_mode === null) {
                break;
            }
            if ($auth_mode) {
                // begin-patch ldap_multiple
                switch ((int) $auth_mode) {
                    case AUTH_LOCAL:
                        $this->position[] = $auth_mode;
                        break;
                    
                    case AUTH_LDAP:
                        $auth_id = ilLDAPServer::getServerIdByAuthMode($auth_mode);
                        $server = ilLDAPServer::getInstanceByServerId($auth_id);
                        
                        if ($server->isActive()) {
                            $this->position[] = $auth_mode;
                        }
                        break;
                        
                    case ilAuthUtils::AUTH_RADIUS:
                        if ($rad_active) {
                            $this->position[] = $auth_mode;
                        }
                        break;
                    
                    case AUTH_SOAP:
                        if ($soap_active) {
                            $this->position[] = $auth_mode;
                        }
                        break;

                    case AUTH_APACHE:
                        if ($apache_active) {
                            $this->position[] = $auth_mode;
                        }
                        break;
                        
                    // begin-patch auth_plugin
                    default:
                        foreach (ilAuthUtils::getAuthPlugins() as $pl) {
                            if ($pl->isAuthActive($auth_mode)) {
                                $this->position[] = $auth_mode;
                            }
                        }
                        break;
                    // end-patch auth_plugin
                        
                }
            }
        }
        // end-patch ldap_multiple

        // Append missing active auth modes
        if (!in_array(AUTH_LOCAL, $this->position)) {
            $this->position[] = AUTH_LOCAL;
        }
        // begin-patch ldap_multiple
        foreach (ilLDAPServer::_getActiveServerList() as $sid) {
            $server = ilLDAPServer::getInstanceByServerId($sid);
            if ($server->isActive()) {
                if (!in_array(AUTH_LDAP . '_' . $sid, $this->position)) {
                    $this->position[] = (AUTH_LDAP . '_' . $sid);
                }
            }
        }
        // end-patch ldap_multiple
        if ($rad_active) {
            if (!in_array(ilAuthUtils::AUTH_RADIUS, $this->position)) {
                $this->position[] = ilAuthUtils::AUTH_RADIUS;
            }
        }
        if ($soap_active) {
            if (!in_array(AUTH_SOAP, $this->position)) {
                $this->position[] = AUTH_SOAP;
            }
        }
        if ($apache_active) {
            if (!in_array(AUTH_APACHE, $this->position)) {
                $this->position[] = AUTH_APACHE;
            }
        }
        // begin-patch auth_plugin
        foreach (ilAuthUtils::getAuthPlugins() as $pl) {
            foreach ($pl->getAuthIds() as $auth_id) {
                if ($pl->isAuthActive($auth_id)) {
                    if (!in_array($auth_id, $this->position)) {
                        $this->position[] = $auth_id;
                    }
                }
            }
        }
        // end-patch auth_plugin
    }
}
