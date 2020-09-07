<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomServerSettings
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomServerSettings
{
    const PREFIX = '/backend';
    private $port = '8585';
    private $protocol = 'http://';
    private $domain = '192.168.1.94';
    private $instance = '123456';
    private $smilies_enabled = false;
    private $authKey;
    private $authSecret;
    private $clientUrlEnabled;
    private $clientUrl;
    private $iliasUrlEnabled;
    private $iliasUrl;
    private $subDirectory;

    public static function loadDefault()
    {
        global $DIC;

        $query = 'SELECT * FROM chatroom_admconfig';
        $rset = $DIC->database()->query($query);
        $row = $DIC->database()->fetchAssoc($rset);

        $client_settings = json_decode($row['client_settings']);
        $server_settings = json_decode($row['server_settings']);

        $settings = new ilChatroomServerSettings();
        $settings->setPort($server_settings->port);
        $settings->setProtocol($server_settings->protocol);
        $settings->setInstance($client_settings->name);
        $settings->setDomain($server_settings->address);
        $settings->setSmiliesEnabled($client_settings->enable_smilies);
        $settings->setAuthKey($client_settings->auth->key);
        $settings->setAuthSecret($client_settings->auth->secret);
        $settings->setClientUrlEnabled($server_settings->client_proxy);
        $settings->setIliasUrlEnabled($server_settings->ilias_proxy);
        $settings->setClientUrl($server_settings->client_url);
        $settings->setIliasUrl($server_settings->ilias_url);
        $settings->setSubDirectory($server_settings->sub_directory);

        return $settings;
    }

    /**
     * Creates URL by calling $this->getBaseURL and using given $action and
     * $scope and returns it.
     * @param string      $action
     * @param string|null $scope
     * @return string
     */
    public function getURL($action, $scope = null)
    {
        $url = $this->generateIliasUrl() . self::PREFIX . '/' . $action . '/' . $this->getInstance();

        if ($scope !== null) {
            $url .= '/' . $scope;
        }

        return $url;
    }

    /**
     * @return string
     */
    public function generateIliasUrl()
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

    /**
     * @return bool
     */
    public function getIliasUrlEnabled()
    {
        return $this->iliasUrlEnabled;
    }

    /**
     * @param bool $iliasUrlEnabled
     */
    public function setIliasUrlEnabled($iliasUrlEnabled)
    {
        $this->iliasUrlEnabled = $iliasUrlEnabled;
    }

    /**
     * Returns $this->protocol.
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Sets $this->protocol using given $protocol
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        if (strpos($protocol, '://') === false) {
            $this->protocol = $protocol . '://';
        }
    }

    /**
     * @return string
     */
    public function getIliasUrl()
    {
        return $this->iliasUrl;
    }

    /**
     * @param string $iliasUrl
     */
    public function setIliasUrl($iliasUrl)
    {
        $this->iliasUrl = $iliasUrl;
    }

    /**
     * Returns base URL
     * Creates base URL by calling $this->getProtocol(), $this->getDomain() and
     * $this->getPort() and returnes it.
     * @return string
     */
    public function getBaseURL()
    {
        return $this->getProtocol() . $this->getDomain() . ':' . $this->getPort();
    }

    /**
     * Returns $this->domain.
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets $this->domain using given $domain.
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Returns $this->port.
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets $this->port using given $port
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Returns $this->instance.
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Sets $this->instance using given $instance
     * @param string $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return string
     */
    public function generateClientUrl()
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

    /**
     * @return bool
     */
    public function getClientUrlEnabled()
    {
        return $this->clientUrlEnabled;
    }

    /**
     * @param bool $clientUrlEnabled
     */
    public function setClientUrlEnabled($clientUrlEnabled)
    {
        $this->clientUrlEnabled = $clientUrlEnabled;
    }

    /**
     * @return string
     */
    public function getClientUrl()
    {
        return $this->clientUrl;
    }

    /**
     * @param string $clientUrl
     */
    public function setClientUrl($clientUrl)
    {
        $this->clientUrl = $clientUrl;
    }

    /**
     * @return bool
     */
    public function getSmiliesEnabled()
    {
        return (bool) $this->smilies_enabled;
    }

    /**
     * @param bool $a_bool
     */
    public function setSmiliesEnabled($a_bool)
    {
        $this->smilies_enabled = $a_bool;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string $authKey
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }

    /**
     * @return string
     */
    public function getAuthSecret()
    {
        return $this->authSecret;
    }

    /**
     * @param string $authSecret
     */
    public function setAuthSecret($authSecret)
    {
        $this->authSecret = $authSecret;
    }

    /**
     * @return mixed
     */
    public function getSubDirectory()
    {
        return $this->subDirectory;
    }

    /**
     * @param mixed $subDirectory
     */
    public function setSubDirectory($subDirectory)
    {
        $this->subDirectory = $subDirectory;
    }
}
