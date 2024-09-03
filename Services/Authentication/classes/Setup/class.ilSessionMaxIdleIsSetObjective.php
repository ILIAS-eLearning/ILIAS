<?php

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

declare(strict_types=1);

use ILIAS\Setup;

class ilSessionMaxIdleIsSetObjective implements Setup\Objective
{
    public function __construct(
        protected ilAuthenticationSetupConfig $config
    ) {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Ensures 'session_max_idle' is set properly";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilIniFilesPopulatedObjective(),
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $session_max_idle = $this->config->getSessionMaxIdle();

        $apache_php_ini_path = $this->getPHPIniPathForApache("localhost/info.php");
        $apache_php_ini = parse_ini_file($apache_php_ini_path);

        $cookie_lifetime = $apache_php_ini["session.cookie_lifetime"];
        $gc_maxlifetime = $apache_php_ini["session.gc_maxlifetime"];

        if ($cookie_lifetime != 0) {
            $message =
                "The value 'session.cookie_lifetime' in your php.ini does not correspond" . PHP_EOL .
                "to the value '0' recommended by ILIAS. Do you want to continue anyway?";

            if (!$admin_interaction->confirmOrDeny($message)) {
                throw new Setup\NoConfirmationException($message);
            }
        }

        if ($gc_maxlifetime <= $session_max_idle) {
            $message =
                "The value 'session.gc_maxlifetime' in your php.ini is smaller or equal than" . PHP_EOL .
                "'session_max_idle' in your ILIAS-Config. ILIAS recommends a bigger value." . PHP_EOL .
                "Do you want to continue anyway?";

            if (!$admin_interaction->confirmOrDeny($message)) {
                throw new Setup\NoConfirmationException($message);
            }
        }

        $client_ini->setVariable("session", "expire", (string) $session_max_idle);

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    protected function getPHPIniPathForApache(string $url): string
    {
        return exec("wget -q -O - ${url} | grep 'Loaded Configuration File' | cut -d '<' -f5 | cut -d '>' -f2");
    }
}
