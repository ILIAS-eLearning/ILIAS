<?php

declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilPrivacySecuritySetupConfig implements Setup\Config
{
    /**
     * @var bool
     */
    protected bool $force_https_on_login;

    public function __construct(bool $force_https_on_login = false)
    {
        $this->force_https_on_login = $force_https_on_login;
    }

    public function getForceHttpsOnLogin(): bool
    {
        return $this->force_https_on_login;
    }
}
