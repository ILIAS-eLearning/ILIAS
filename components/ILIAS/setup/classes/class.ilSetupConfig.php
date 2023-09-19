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
