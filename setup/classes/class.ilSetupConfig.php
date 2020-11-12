<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilSetupConfig implements Setup\Config
{
    /**
     * @var	client_id
     */
    protected $client_id;

    /**
     * @var \DateTimeZone
     */
    protected $server_timezone;

    /**
     * @var	bool
     */
    protected $register_nic;

    public function __construct(
        string $client_id,
        \DateTimeZone $server_timezone,
        bool $register_nic
    ) {
        if (!preg_match("/^[A-Za-z0-9]+$/", $client_id)) {
            throw new \InvalidArgumentException(
                "client_id must not be empty and may only contain alphanumeric characters"
            );
        }
        $this->client_id = $client_id;
        $this->server_timezone = $server_timezone;
        $this->register_nic = $register_nic;
    }

    public function getClientId() : string
    {
        return $this->client_id;
    }

    public function getServerTimeZone() : \DateTimeZone
    {
        return $this->server_timezone;
    }

    public function getRegisterNIC() : bool
    {
        return $this->register_nic;
    }
}
