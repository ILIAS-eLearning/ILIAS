<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilSetupConfig implements Setup\Config
{
    /**
     * @var	\ILIAS\Data\ClientId
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
        \ILIAS\Data\ClientId $client_id,
        \DateTimeZone $server_timezone,
        bool $register_nic
    ) {
        $this->client_id = $client_id;
        $this->server_timezone = $server_timezone;
        $this->register_nic = $register_nic;
    }

    public function getClientId() : string
    {
        return $this->client_id->toString();
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
