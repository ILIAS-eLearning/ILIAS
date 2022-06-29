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
    protected ?int $authentication_duration_in_ms;
    protected ?int $account_assistance_duration_in_ms;

    public function __construct(
        bool $force_https_on_login = false,
        ?int $authentication_duration_in_ms = null,
        ?int $account_assistance_duration_in_ms = null,
    ) {
        $this->force_https_on_login = $force_https_on_login;
        $this->authentication_duration_in_ms = $authentication_duration_in_ms;
        $this->account_assistance_duration_in_ms = $account_assistance_duration_in_ms;
    }

    public function getForceHttpsOnLogin() : bool
    {
        return $this->force_https_on_login;
    }

    public function getAuthDurationInMs() : ?int
    {
        return $this->authentication_duration_in_ms;
    }

    public function getAccountAssistanceDurationInMs() : ?int
    {
        return $this->account_assistance_duration_in_ms;
    }
}
