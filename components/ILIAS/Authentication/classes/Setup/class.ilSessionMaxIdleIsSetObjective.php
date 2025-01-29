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
        protected Setup\Config $config
    ) {
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return "Ensures 'session_max_idle' is set properly";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        $http_config = $environment->getConfigFor('http');

        return [
            new ilIniFilesPopulatedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilSettingsFactoryExistsObjective(),
            new ilHttpConfigStoredObjective($http_config)
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        /** @var ilIniFile $client_ini */
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        /** @var ilIniFile $ini */
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        /** @var Setup\CLI\IOWrapper $io */
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        /** @var ilSetting $settings */
        $settings = $factory->settingsFor('common');

        $session_max_idle = $this->config->getSessionMaxIdle();

        $url = $ini->readVariable('server', 'http_path');
        $filename = uniqid((string) mt_rand(), true) . '.php';
        $url .= '/' . $filename;
        $token = bin2hex(random_bytes(32));
        $this->generateServerInfoFile($filename, $token);

        try {
            $curl = null;
            if (ilCurlConnection::_isCurlExtensionLoaded()) {
                $curl = $this->getCurlConnection($settings, $url, $token);
                $result = $curl->exec();
            } else {
                $result = $this->getPHPIniValuesByFileGetContents($url, $token);
            }
        } catch (Throwable $e) {
            $io->inform(
                "An error occurred while trying to determine the values for 'session.cookie_lifetime' and" . PHP_EOL .
                "'session.gc_maxlifetime' in your php.ini: {$e->getMessage()}" . PHP_EOL .
                'You can IGNORE the the error if you are sure these settings comply with our expection to' . PHP_EOL .
                'ensure a proper session handling.'
            );

            $client_ini->setVariable('session', 'expire', (string) $session_max_idle);

            return $environment;
        } finally {
            if (!is_null($curl)) {
                $curl->close();
            }
            unlink("public/$filename");
        }

        if ($result === '') {
            $message =
                "ILIAS could not determine the value for 'session.cookie_lifetime' and 'session.gc_maxlifetime'" . PHP_EOL .
                'in your php.ini to check whether it complies with our expection to ensure a proper session handling.' . PHP_EOL .
                'Do you like to continue, anyway?';

            if (!$io->confirmOrDeny($message)) {
                throw new Setup\NoConfirmationException($message);
            }
        }

        [$cookie_lifetime, $gc_maxlifetime] = explode('&', $result);

        if ($cookie_lifetime != 0) {
            $message =
                "The value 'session.cookie_lifetime' in your php.ini does not correspond" . PHP_EOL .
                "to the value '0' recommended by ILIAS. Do you want to continue anyway?";

            if (!$io->confirmOrDeny($message)) {
                throw new Setup\NoConfirmationException($message);
            }
        }

        if ($gc_maxlifetime <= $session_max_idle) {
            $message =
                "The value 'session.gc_maxlifetime' in your php.ini is smaller or equal than" . PHP_EOL .
                "'session_max_idle' in your ILIAS-Config. ILIAS recommends a bigger value." . PHP_EOL .
                'Do you want to continue anyway?';

            if (!$io->confirmOrDeny($message)) {
                throw new Setup\NoConfirmationException($message);
            }
        }

        $client_ini->setVariable('session', 'expire', (string) $session_max_idle);

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        /** @var ilSetting $settings */
        $settings = $factory->settingsFor('common');
        /** @var ilIniFile $ini */
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        /** @var Setup\CLI\IOWrapper $io */
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $url = $ini->readVariable('server', 'http_path');

        if (ilCurlConnection::_isCurlExtensionLoaded()) {
            try {
                $curl = $this->getCurlConnection($settings, $url);
                $curl->exec();
                $result = $curl->getInfo(CURLINFO_HTTP_CODE);
                if ($result !== 200) {
                    throw new \Exception();
                }
            } catch (\Exception $e) {
                $this->infoNoConnection($io);
                return false;
            } finally {
                $curl->close();
            }
        } else {
            try {
                $this->getPHPIniValuesByFileGetContents($url);
            } catch (Exception $e) {
                $this->infoNoConnection($io);
                return false;
            }
        }

        return true;
    }

    private function generateServerInfoFile(string $filename, string $token): void
    {
        $content = <<<TEXT
<?php
if (!isset(\$_GET['token'])) {
    return "";
}

if (\$_GET['token'] !== "$token") {
    return "";
}

\$scl = ini_get('session.cookie_lifetime');
\$smlt = ini_get('session.gc_maxlifetime');

echo \$scl . "&" . \$smlt;
TEXT;

        file_put_contents("public/$filename", $content);
    }

    /**
     * @throws ilCurlConnectionException
     */
    private function getCurlConnection(ilSetting $settings, string $url, ?string $token = null): ilCurlConnection
    {
        if (!is_null($token)) {
            $url = $url . "?token=" . $token;
        }

        $curl = new ilCurlConnection(
            $url,
            new ilProxySettings($settings)
        );
        $curl->init();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
        $curl->setOpt(CURLOPT_MAXREDIRS, 1);

        return $curl;
    }

    /**
     * @throws ErrorException
     */
    private function getPHPIniValuesByFileGetContents(string $url, ?string $token = null): string
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        });

        if (!is_null($token)) {
            $url = $url . "?token=" . $token;
        }

        try {
            return file_get_contents($url);
        } catch (ErrorException $e) {
            restore_error_handler();
            throw $e;
        }
    }

    private function infoNoConnection(Setup\CLI\IOWrapper $io): void
    {
        $message =
            "ilSessionMaxIdleIsSetObjective:\n" .
            "Cannot establish proper connection to webserver.\n" .
            "In the event of an installation the value for session expire\n" .
            "will be the default value.\n" .
            "In the event of an update, the current value for session expire\n" .
            "is retained."
        ;

        $io->inform($message);
    }
}
