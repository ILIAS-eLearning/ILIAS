<?php declare(strict_types=1);

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
 * Class ilChatroomServerSettings
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomServerSettings
{
    private const DEFAULT_PORT = 8585;
    private const DEFAULT_PROCOTOL = 'http://';
    private const DEFAULT_HOST = '192.168.1.94';
    
    public const PREFIX = '/backend';
    private int $port = self::DEFAULT_PORT;
    private string $protocol = self::DEFAULT_PROCOTOL;
    private string $domain = self::DEFAULT_HOST;
    private string $instance = '123456';
    private bool $smilies_enabled = false;
    private string $authKey = '';
    private string $authSecret = '';
    private bool $clientUrlEnabled = false;
    private string $clientUrl = '';
    private bool $iliasUrlEnabled = false;
    private string $iliasUrl = '';
    private string $subDirectory = '';

    public static function loadDefault() : self
    {
        global $DIC;

        $query = 'SELECT * FROM chatroom_admconfig';
        $rset = $DIC->database()->query($query);
        $row = $DIC->database()->fetchAssoc($rset);

        $client_settings = json_decode($row['client_settings'], false, 512, JSON_THROW_ON_ERROR);
        $server_settings = json_decode($row['server_settings'], false, 512, JSON_THROW_ON_ERROR);

        $settings = new self();
        if ($server_settings instanceof stdClass) {
            $settings->setPort((int) ($server_settings->port ?? self::DEFAULT_PORT));
            $settings->setProtocol((string) ($server_settings->protocol ?? self::DEFAULT_PROCOTOL));
            $settings->setDomain((string) ($server_settings->address ?? self::DEFAULT_HOST));
            $settings->setSmiliesEnabled((bool) ($client_settings->enable_smilies ?? false));
            $settings->setClientUrlEnabled((bool) ($server_settings->client_proxy ?? false));
            $settings->setIliasUrlEnabled((bool) ($server_settings->ilias_proxy ?? false));
            $settings->setClientUrl((string) ($server_settings->client_url ?? ''));
            $settings->setIliasUrl((string) ($server_settings->ilias_url ?? ''));
            $settings->setSubDirectory((string) ($server_settings->sub_directory ?? ''));
        }

        if ($client_settings instanceof stdClass) {
            $settings->setInstance((string) ($client_settings->name ?? ''));
            $settings->setAuthKey((string) ($client_settings->auth->key ?? ''));
            $settings->setAuthSecret((string) ($client_settings->auth->secret ?? ''));
        }

        return $settings;
    }

    /**
     * Creates URL by calling $this->getBaseURL and using given $action and
     * $scope and returns it.
     * @param string $action
     * @param string|int|null $scope
     * @return string
     */
    public function getURL(string $action, $scope = null) : string
    {
        $url = $this->generateIliasUrl() . self::PREFIX . '/' . $action . '/' . $this->getInstance();

        if ($scope !== null) {
            $url .= '/' . $scope;
        }

        return $url;
    }

    public function generateIliasUrl() : string
    {
        if ($this->getIliasUrlEnabled()) {
            $url = $this->getIliasUrl();

            if (strpos($url, '://') === false) {
                $url = $this->getProtocol() . $url;
            }

            return $url;
        }

        return $this->getBaseURL();
    }

    public function getIliasUrlEnabled() : bool
    {
        return $this->iliasUrlEnabled;
    }

    public function setIliasUrlEnabled(bool $iliasUrlEnabled) : void
    {
        $this->iliasUrlEnabled = $iliasUrlEnabled;
    }

    public function getProtocol() : string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol) : void
    {
        if (strpos($protocol, '://') === false) {
            $this->protocol = $protocol . '://';
        }
    }

    public function getIliasUrl() : string
    {
        return $this->iliasUrl;
    }

    public function setIliasUrl(string $iliasUrl) : void
    {
        $this->iliasUrl = $iliasUrl;
    }

    /**
     * Creates base URL by calling $this->getProtocol(), $this->getDomain() and
     * $this->getPort() and returnes it.
     * @return string
     */
    public function getBaseURL() : string
    {
        return $this->getProtocol() . $this->getDomain() . ':' . $this->getPort();
    }

    public function getDomain() : string
    {
        return $this->domain;
    }

    public function setDomain(string $domain) : void
    {
        $this->domain = $domain;
    }

    public function getPort() : int
    {
        return $this->port;
    }

    public function setPort(int $port) : void
    {
        $this->port = $port;
    }

    public function getInstance() : string
    {
        return $this->instance;
    }

    public function setInstance(string $instance) : void
    {
        $this->instance = $instance;
    }

    public function generateClientUrl() : string
    {
        if ($this->getClientUrlEnabled()) {
            $url = $this->getClientUrl();

            if (strpos($url, '://') === false) {
                $url = $this->getProtocol() . $url;
            }

            return $url;
        }
        return $this->getBaseURL();
    }

    public function getClientUrlEnabled() : bool
    {
        return $this->clientUrlEnabled;
    }

    public function setClientUrlEnabled(bool $clientUrlEnabled) : void
    {
        $this->clientUrlEnabled = $clientUrlEnabled;
    }

    public function getClientUrl() : string
    {
        return $this->clientUrl;
    }

    public function setClientUrl(string $clientUrl) : void
    {
        $this->clientUrl = $clientUrl;
    }

    public function getSmiliesEnabled() : bool
    {
        return $this->smilies_enabled;
    }

    public function setSmiliesEnabled(bool $a_bool) : void
    {
        $this->smilies_enabled = $a_bool;
    }

    public function getAuthKey() : string
    {
        return $this->authKey;
    }

    public function setAuthKey(string $authKey) : void
    {
        $this->authKey = $authKey;
    }

    public function getAuthSecret() : string
    {
        return $this->authSecret;
    }

    public function setAuthSecret(string $authSecret) : void
    {
        $this->authSecret = $authSecret;
    }

    public function getSubDirectory() : string
    {
        return $this->subDirectory;
    }

    public function setSubDirectory(string $subDirectory) : void
    {
        $this->subDirectory = $subDirectory;
    }
}
