<?php



/**
 * EcsServer
 */
class EcsServer
{
    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var bool|null
     */
    private $active = '0';

    /**
     * @var bool|null
     */
    private $protocol = '1';

    /**
     * @var string|null
     */
    private $server;

    /**
     * @var int|null
     */
    private $port = '1';

    /**
     * @var bool|null
     */
    private $authType = '1';

    /**
     * @var string|null
     */
    private $clientCertPath;

    /**
     * @var string|null
     */
    private $caCertPath;

    /**
     * @var string|null
     */
    private $keyPath;

    /**
     * @var string|null
     */
    private $keyPassword;

    /**
     * @var string|null
     */
    private $certSerial;

    /**
     * @var int|null
     */
    private $pollingTime = '0';

    /**
     * @var int|null
     */
    private $importId = '0';

    /**
     * @var int|null
     */
    private $globalRole = '0';

    /**
     * @var string|null
     */
    private $econtentRcp;

    /**
     * @var string|null
     */
    private $userRcp;

    /**
     * @var string|null
     */
    private $approvalRcp;

    /**
     * @var int|null
     */
    private $duration = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $authUser;

    /**
     * @var string|null
     */
    private $authPass;


    /**
     * Get serverId.
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return EcsServer
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set protocol.
     *
     * @param bool|null $protocol
     *
     * @return EcsServer
     */
    public function setProtocol($protocol = null)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol.
     *
     * @return bool|null
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Set server.
     *
     * @param string|null $server
     *
     * @return EcsServer
     */
    public function setServer($server = null)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server.
     *
     * @return string|null
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set port.
     *
     * @param int|null $port
     *
     * @return EcsServer
     */
    public function setPort($port = null)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set authType.
     *
     * @param bool|null $authType
     *
     * @return EcsServer
     */
    public function setAuthType($authType = null)
    {
        $this->authType = $authType;

        return $this;
    }

    /**
     * Get authType.
     *
     * @return bool|null
     */
    public function getAuthType()
    {
        return $this->authType;
    }

    /**
     * Set clientCertPath.
     *
     * @param string|null $clientCertPath
     *
     * @return EcsServer
     */
    public function setClientCertPath($clientCertPath = null)
    {
        $this->clientCertPath = $clientCertPath;

        return $this;
    }

    /**
     * Get clientCertPath.
     *
     * @return string|null
     */
    public function getClientCertPath()
    {
        return $this->clientCertPath;
    }

    /**
     * Set caCertPath.
     *
     * @param string|null $caCertPath
     *
     * @return EcsServer
     */
    public function setCaCertPath($caCertPath = null)
    {
        $this->caCertPath = $caCertPath;

        return $this;
    }

    /**
     * Get caCertPath.
     *
     * @return string|null
     */
    public function getCaCertPath()
    {
        return $this->caCertPath;
    }

    /**
     * Set keyPath.
     *
     * @param string|null $keyPath
     *
     * @return EcsServer
     */
    public function setKeyPath($keyPath = null)
    {
        $this->keyPath = $keyPath;

        return $this;
    }

    /**
     * Get keyPath.
     *
     * @return string|null
     */
    public function getKeyPath()
    {
        return $this->keyPath;
    }

    /**
     * Set keyPassword.
     *
     * @param string|null $keyPassword
     *
     * @return EcsServer
     */
    public function setKeyPassword($keyPassword = null)
    {
        $this->keyPassword = $keyPassword;

        return $this;
    }

    /**
     * Get keyPassword.
     *
     * @return string|null
     */
    public function getKeyPassword()
    {
        return $this->keyPassword;
    }

    /**
     * Set certSerial.
     *
     * @param string|null $certSerial
     *
     * @return EcsServer
     */
    public function setCertSerial($certSerial = null)
    {
        $this->certSerial = $certSerial;

        return $this;
    }

    /**
     * Get certSerial.
     *
     * @return string|null
     */
    public function getCertSerial()
    {
        return $this->certSerial;
    }

    /**
     * Set pollingTime.
     *
     * @param int|null $pollingTime
     *
     * @return EcsServer
     */
    public function setPollingTime($pollingTime = null)
    {
        $this->pollingTime = $pollingTime;

        return $this;
    }

    /**
     * Get pollingTime.
     *
     * @return int|null
     */
    public function getPollingTime()
    {
        return $this->pollingTime;
    }

    /**
     * Set importId.
     *
     * @param int|null $importId
     *
     * @return EcsServer
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return int|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set globalRole.
     *
     * @param int|null $globalRole
     *
     * @return EcsServer
     */
    public function setGlobalRole($globalRole = null)
    {
        $this->globalRole = $globalRole;

        return $this;
    }

    /**
     * Get globalRole.
     *
     * @return int|null
     */
    public function getGlobalRole()
    {
        return $this->globalRole;
    }

    /**
     * Set econtentRcp.
     *
     * @param string|null $econtentRcp
     *
     * @return EcsServer
     */
    public function setEcontentRcp($econtentRcp = null)
    {
        $this->econtentRcp = $econtentRcp;

        return $this;
    }

    /**
     * Get econtentRcp.
     *
     * @return string|null
     */
    public function getEcontentRcp()
    {
        return $this->econtentRcp;
    }

    /**
     * Set userRcp.
     *
     * @param string|null $userRcp
     *
     * @return EcsServer
     */
    public function setUserRcp($userRcp = null)
    {
        $this->userRcp = $userRcp;

        return $this;
    }

    /**
     * Get userRcp.
     *
     * @return string|null
     */
    public function getUserRcp()
    {
        return $this->userRcp;
    }

    /**
     * Set approvalRcp.
     *
     * @param string|null $approvalRcp
     *
     * @return EcsServer
     */
    public function setApprovalRcp($approvalRcp = null)
    {
        $this->approvalRcp = $approvalRcp;

        return $this;
    }

    /**
     * Get approvalRcp.
     *
     * @return string|null
     */
    public function getApprovalRcp()
    {
        return $this->approvalRcp;
    }

    /**
     * Set duration.
     *
     * @param int|null $duration
     *
     * @return EcsServer
     */
    public function setDuration($duration = null)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration.
     *
     * @return int|null
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return EcsServer
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set authUser.
     *
     * @param string|null $authUser
     *
     * @return EcsServer
     */
    public function setAuthUser($authUser = null)
    {
        $this->authUser = $authUser;

        return $this;
    }

    /**
     * Get authUser.
     *
     * @return string|null
     */
    public function getAuthUser()
    {
        return $this->authUser;
    }

    /**
     * Set authPass.
     *
     * @param string|null $authPass
     *
     * @return EcsServer
     */
    public function setAuthPass($authPass = null)
    {
        $this->authPass = $authPass;

        return $this;
    }

    /**
     * Get authPass.
     *
     * @return string|null
     */
    public function getAuthPass()
    {
        return $this->authPass;
    }
}
