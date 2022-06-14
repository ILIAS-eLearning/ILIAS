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
 */

use ILIAS\Setup;

class ilPrivacySecuritySetupConfig implements Setup\Config
{
    protected bool $force_https_on_login;
    protected ?int $duration_in_ms;

    public function __construct(bool $force_https_on_login = false, ?int $duration_in_ms = null)
    {
        $this->force_https_on_login = $force_https_on_login;
        $this->duration_in_ms = $duration_in_ms;
    }

    public function getForceHttpsOnLogin() : bool
    {
        return $this->force_https_on_login;
    }

    public function getAuthDurationInMs() : ?int
    {
        return $this->duration_in_ms;
    }
}
