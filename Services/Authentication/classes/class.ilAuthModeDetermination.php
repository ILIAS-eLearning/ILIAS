<?php

declare(strict_types=1);

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

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilAuthModeDetermination
{
    public const TYPE_MANUAL = 0;
    public const TYPE_AUTOMATIC = 1;

    private static ?ilAuthModeDetermination $instance = null;

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

        $this->logger = $DIC->logger()->auth();

        $this->commonSettings = $DIC->settings();

        $this->settings = new ilSetting("auth_mode_determination");
        $this->read();
    }

    /**
     * Get instance
     */
    public static function _getInstance(): ilAuthModeDetermination
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthModeDetermination();
    }

    /**
     * is manual selection
     */
    public function isManualSelection(): bool
    {
        return $this->kind === self::TYPE_MANUAL;
    }

    /**
     * get kind
     */
    public function getKind(): int
    {
        return $this->kind;
    }

    /**
     * set kind of determination
     *
     * @param int TYPE_MANUAL or TYPE_DETERMINATION
     *
     */
    public function setKind(int $a_kind): void
    {
        // TODO check value range
        $this->kind = $a_kind;
    }

    /**
     * get auth mode sequence
     */
    public function getAuthModeSequence(string $a_username = ''): array
    {
        if ($a_username === '') {
            return $this->position ?: array();
        }
        $sorted = array();

        foreach ($this->position as $auth_key) {
            $sid = ilLDAPServer::getServerIdByAuthMode((string) $auth_key);
            if ($sid) {
                $server = ilLDAPServer::getInstanceByServerId($sid);
                $this->logger->debug('Validating username filter for ' . $server->getName());
                if ($server->getUsernameFilter() !== '') {
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
    public function getCountActiveAuthModes(): int
    {
        return count($this->position);
    }

    /**
     * set auth mode sequence
     *
     * @param array position => AUTH_MODE
     *
     */
    public function setAuthModeSequence(array $a_pos): void
    {
        $this->position = $a_pos;
    }

    /**
     * Save settings
     */
    public function save(): void
    {
        $this->settings->deleteAll();

        $this->settings->set('kind', (string) $this->getKind());

        $counter = 0;
        foreach ($this->position as $auth_mode) {
            $this->settings->set((string) $counter++, (string) $auth_mode);
        }
    }


    /**
     * Read settings
     */
    private function read(): void
    {
        $this->kind = (int) $this->settings->get('kind', (string) self::TYPE_MANUAL);

        $soap_active = (bool) $this->commonSettings->get('soap_auth_active', "");

        // apache settings
        $apache_settings = new ilSetting('apache_auth');
        $apache_active = $apache_settings->get('apache_enable_auth');

        // Check if active
        $i = 0;
        while (true) {
            $auth_mode = $this->settings->get((string) $i++, null);
            if ($auth_mode === null) {
                break;
            }
            if ($auth_mode) {
                //TODO fix casting strings like 2_1 (auth_key for first ldap server) to int to get it to 2
                switch ((int) $auth_mode) {
                    case ilAuthUtils::AUTH_LOCAL:
                        $this->position[] = (int) $auth_mode;
                        break;
                    case ilAuthUtils::AUTH_LDAP:
                        $auth_id = ilLDAPServer::getServerIdByAuthMode($auth_mode);
                        $server = ilLDAPServer::getInstanceByServerId($auth_id);

                        if ($server->isActive()) {
                            $this->position[] = $auth_mode;
                        }
                        break;

                    case ilAuthUtils::AUTH_SOAP:
                        if ($soap_active) {
                            $this->position[] = (int) $auth_mode;
                        }
                        break;

                    case ilAuthUtils::AUTH_APACHE:
                        if ($apache_active) {
                            $this->position[] = (int) $auth_mode;
                        }
                        break;

                    default:
                        foreach (ilAuthUtils::getAuthPlugins() as $pl) {
                            if ($pl->isAuthActive($auth_mode)) {
                                $this->position[] = $auth_mode;
                            }
                        }
                        break;
                }
            }
        }

        // Append missing active auth modes
        if (!in_array(ilAuthUtils::AUTH_LOCAL, $this->position, true)) {
            $this->position[] = ilAuthUtils::AUTH_LOCAL;
        }
        // begin-patch ldap_multiple
        foreach (ilLDAPServer::_getActiveServerList() as $sid) {
            $server = ilLDAPServer::getInstanceByServerId($sid);
            if ($server->isActive() && !in_array(ilAuthUtils::AUTH_LDAP . '_' . $sid, $this->position, true)) {
                $this->position[] = (ilAuthUtils::AUTH_LDAP . '_' . $sid);
            }
        }
        // end-patch ldap_multiple
        if ($soap_active && !in_array(ilAuthUtils::AUTH_SOAP, $this->position, true)) {
            $this->position[] = ilAuthUtils::AUTH_SOAP;
        }
        if ($apache_active && !in_array(ilAuthUtils::AUTH_APACHE, $this->position, true)) {
            $this->position[] = ilAuthUtils::AUTH_APACHE;
        }
        // begin-patch auth_plugin
        foreach (ilAuthUtils::getAuthPlugins() as $pl) {
            foreach ($pl->getAuthIds() as $auth_id) {
                if ($pl->isAuthActive($auth_id) && !in_array($auth_id, $this->position, true)) {
                    $this->position[] = $auth_id;
                }
            }
        }
        // end-patch auth_plugin
    }
}
