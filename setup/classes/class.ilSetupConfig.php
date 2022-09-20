<?php

declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilSetupConfig implements Setup\Config
{
    protected \ILIAS\Data\ClientId $client_id;
    protected \DateTimeZone $server_timezone;
    protected bool $register_nic;

    public function __construct(
        \ILIAS\Data\ClientId $client_id,
        \DateTimeZone $server_timezone,
        bool $register_nic
    ) {
        $this->client_id = $client_id;
        $this->server_timezone = $server_timezone;
        $this->register_nic = $register_nic;
    }

    public function getClientId(): \ILIAS\Data\ClientId
    {
        return $this->client_id;
    }

    public function getServerTimeZone(): \DateTimeZone
    {
        return $this->server_timezone;
    }

    public function getRegisterNIC(): bool
    {
        return $this->register_nic;
    }
}
