<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilChatroomSetupConfig implements Setup\Config
{
    /**
     * @var string
     */
    protected $address;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $sub_directory;

    /**
     * @var string
     */
    protected $protocol;

    /**
     * @var string
     */
    protected $cert;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $dhparam;

    /**
     * @var string
     */
    protected $log;

    /**
     * @var string
     */
    protected $log_level;

    /**
     * @var string
     */
    protected $error_log;

    /**
     * @var bool
     */
    protected $ilias_proxy;

    /**
     * @var string
     */
    protected $ilias_url;

    /**
     * @var bool
     */
    protected $client_proxy;

    /**
     * @var string
     */
    protected $client_url;

    /**
     * @var bool
     */
    protected $deletion_mode;

    /**
     * @var string
     */
    protected $deletion_unit;

    /**
     * @var int
     */
    protected $deletion_value;

    /**
     * @var string
     */
    protected $deletion_time;

    public function __construct(
        string $address,
        int $port,
        string $sub_directory,
        string $protocol,
        string $cert,
        string $key,
        string $dhparam,
        string $log,
        string $log_level,
        string $error_log,
        bool $ilias_proxy,
        string $ilias_url,
        bool $client_proxy,
        string $client_url,
        bool $deletion_mode,
        string $deletion_unit,
        int $deletion_value,
        string $deletion_time
    ) {
        $this->address = $address;
        $this->port = $port;
        $this->sub_directory = $sub_directory;
        $this->protocol = $protocol;
        $this->cert = $cert;
        $this->key = $key;
        $this->dhparam = $dhparam;
        $this->log = $log;
        $this->log_level = $log_level;
        $this->error_log = $error_log;
        $this->ilias_proxy = $ilias_proxy;
        $this->ilias_url = $ilias_url;
        $this->client_proxy = $client_proxy;
        $this->client_url = $client_url;
        $this->deletion_mode = $deletion_mode;
        $this->deletion_unit = $deletion_unit;
        $this->deletion_value = $deletion_value;
        $this->deletion_time = $deletion_time;
    }

    /**
     * @return string
     */
    public function getAddress() : string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort() : int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getSubDirectory() : string
    {
        return $this->sub_directory;
    }

    /**
     * @return string
     */
    public function getProtocol() : string
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getCert() : string
    {
        return $this->cert;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getDhparam() : string
    {
        return $this->dhparam;
    }

    /**
     * @return string
     */
    public function getLog() : string
    {
        return $this->log;
    }

    /**
     * @return string
     */
    public function getLogLevel() : string
    {
        return $this->log_level;
    }

    /**
     * @return string
     */
    public function getErrorLog() : string
    {
        return $this->error_log;
    }

    /**
     * @return bool
     */
    public function hasIliasProxy() : bool
    {
        return $this->ilias_proxy;
    }

    /**
     * @return string
     */
    public function getIliasUrl() : string
    {
        return $this->ilias_url;
    }

    /**
     * @return bool
     */
    public function hasClientProxy() : bool
    {
        return $this->client_proxy;
    }

    /**
     * @return string
     */
    public function getClientUrl() : string
    {
        return $this->client_url;
    }

    /**
     * @return bool
     */
    public function hasDeletionMode() : bool
    {
        return $this->deletion_mode;
    }

    /**
     * @return string
     */
    public function getDeletionUnit() : string
    {
        return $this->deletion_unit;
    }

    /**
     * @return int
     */
    public function getDeletionValue() : int
    {
        return $this->deletion_value;
    }

    /**
     * @return string
     */
    public function getDeletionTime() : string
    {
        return $this->deletion_time;
    }
}
