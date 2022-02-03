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
    
    private static ?ilAuthModeDetermination $instance = null;
    
    private ilDBInterface $db;
    private ilLogger $logger;
    
    private ilSetting $settings;
    private ilSetting $commonSettings;
    
    private int $kind = self::TYPE_MANUAL;
    private array $position = [];
    

    /**
     * Constructor (Singleton)
     *
     * @access private
     *
     */
    private function __construct()
    {
        global $DIC;
      
        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->auth();

        $this->commonSettings = $DIC->settings();

        $this->settings = new ilSetting("auth_mode_determination");
        $this->read();
    }
    
    /**
     * Get instance
     */
    public static function _getInstance() : ilAuthModeDetermination
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthModeDetermination();
    }

    /**
     * is manual selection
     */
    public function isManualSelection() : bool
    {
        return $this->kind == self::TYPE_MANUAL;
    }

    /**
     * get kind
     */
    public function getKind() : int
    {
        return $this->kind;
    }
    
    /**
     * set kind of determination
     *
     * @param int TYPE_MANUAL or TYPE_DETERMINATION
     *
     */
    public function setKind(int $a_kind) : void
    {
        // TODO check value range
        $this->kind = $a_kind;
    }
    
    /**
     * get auth mode sequence
     */
    public function getAuthModeSequence(string $a_username = '') : array
    {
        if (!strlen($a_username)) {
            return $this->position ? $this->position : array();
        }
        $sorted = array();
        
        foreach ($this->position as $auth_key) {
            $sid = ilLDAPServer::getServerIdByAuthMode($auth_key);
            if ($sid) {
                $server = ilLDAPServer::getInstanceByServerId($sid);
                $this->logger->debug('Validating username filter for ' . $server->getName());
                if (strlen($server->getUsernameFilter())) {
                    //#17731
                    $pattern = str_replace('*', '.*?', $server->getUsernameFilter());

                    if (preg_match('/^' . $pattern . '$/', $a_username)) {
                        $this->logger->debug('Filter matches for ' . $a_username);
                        array_unshift($sorted, $auth_key);
                        continue;
                    }
                    $this->logger->debug('Filter matches not for ' . $a_username . ' <-> ' . $server->getUsernameFilter());
                }
            }
            $sorted[] = $auth_key;
        }
        
        return $sorted;
    }
    
    /**
     * get number of auth modes
     */
    public function getCountActiveAuthModes() : int
    {
        return count($this->position);
    }
    
    /**
     * set auth mode sequence
     *
     * @param array position => AUTH_MODE
     *
     */
    public function setAuthModeSequence(array $a_pos) : int
    {
        $this->position = $a_pos;
    }
    
    /**
     * Save settings
     */
    public function save() : void
    {
        $this->settings->deleteAll();
        
        $this->settings->set('kind', (string) $this->getKind());
        
        $counter = 0;
        foreach ($this->position as $auth_mode) {
            $this->settings->set((string) $counter++, $auth_mode);
        }
    }
    
    
    /**
     * Read settings
     */
    private function read()
    {
        $this->kind = (int) $this->settings->get('kind', (string) self::TYPE_MANUAL);
        
        $rad_settings = ilRadiusSettings::_getInstance();
        $rad_active = $rad_settings->isActive();

        $soap_active = (bool) $this->commonSettings->get('soap_auth_active', (string) false);

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
