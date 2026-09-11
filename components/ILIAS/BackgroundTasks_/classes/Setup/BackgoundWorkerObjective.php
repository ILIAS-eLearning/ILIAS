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

namespace ILIAS\BackgroundTasks\Setup;

use ilUtil;
use ilSetting;
use Throwable;
use SoapClient;
use ilProxySettings;
use ilCurlConnection;
use RuntimeException;
use ILIAS\DI\Container;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ilCurlConnectionException;
use ILIAS\Setup\CLI\IOWrapper;
use ilIniFilesPopulatedObjective;
use ilDatabaseInitializedObjective;
use ilSettingsFactoryExistsObjective;
use ILIAS\Setup\Objective\ClientIdReadObjective;
use ILIAS\Setup\Objective\AdminConfirmedObjective;
use ilBackgroundTasksSetupConfig;

class BackgoundWorkerObjective extends AdminConfirmedObjective
{
    private ?IOWrapper $io = null;
    private ?Container $old_DIC = null;
    private ?ilSetting $old_settings = null;

    public function __construct()
    {
        parent::__construct(
            "This will start a Background-Worker\n" .
            'Continue?'
        );
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return 'Start a Background-Worker';
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return Objective[]
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new ClientIdReadObjective(),
            new ilIniFilesPopulatedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilSettingsFactoryExistsObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        if (!$ini->groupExists('background_tasks')) {
            return false;
        }

        return $ini->readVariable('background_tasks', 'concurrency') === ilBackgroundTasksSetupConfig::TYPE_ASYNCHRONOUS;
    }

    public function achieve(Environment $environment): Environment
    {
        $environment = parent::achieve($environment);
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }

        $settings = $settings_factory->settingsFor('common');

        try {
            $internal_path = $settings->get('soap_internal_wsdl_path');
            if ($internal_path) {
                $uri = (new \ILIAS\Data\URI($internal_path));
                parse_str($uri->getQuery() ?? '', $query);
                $uri = (string) (isset($query['wsdl']) ?
                    $uri :
                    $uri->withQuery(http_build_query(array_merge($query, ['wsdl' => '']))));
            } elseif (trim($settings->get('soap_wsdl_path', '')) !== '') {
                $uri = $settings->get('soap_wsdl_path', '');
            } else {
                $uri = ilUtil::_getHttpPath() . '/public/soap/server.php?wsdl';
            }
            $this->inform('Trying to call webserver by using WSDL: ' . $uri);
            $soap_client = new SoapClient($uri, ['exceptions' => true, 'trace' => true, 'connection_timeout' => 10]);
            $this->inform('SOAP client initialized');

            $client_id = defined('CLIENT_ID') ? CLIENT_ID : $environment->getResource(Environment::RESOURCE_CLIENT_ID);
            if ($client_id === null) {
                throw new RuntimeException('CLIENT_ID is not defined.');
            }

            $url = $ini->readVariable('server', 'http_path');
            $session_url = rtrim($url, '/') . '/goto.php';
            $curl = $this->getCurlConnection($settings, $session_url);
            $result = $curl->exec();
            $header_array = $curl->getResponseHeaderArray();
            $session_id = null;
            if (isset($header_array['set-cookie'])) {
                $set_cookie = $header_array['set-cookie'];
                $cookies = is_array($set_cookie) ? $set_cookie : [$set_cookie];
                foreach ($cookies as $cookie) {
                    if (preg_match('/PHPSESSID=([^;]+)/i', $cookie, $matches)) {
                        $session_id = $matches[1];
                        break;
                    }
                }
            }

            if ($session_id === null) {
                throw new RuntimeException('Could not extract session ID from server response.');
            }

            if (!$result) {
                throw new RuntimeException('Could not retrieve session ID from server.');
            }

            $this->inform('Obtained session ID: ' . substr($session_id, 0, 40));
            $session_string = $session_id ? ($session_id . '::' . $client_id) : ('::' . $client_id);
            $this->inform('Calling startBackgroundTaskWorker with session: ' . ($session_id ? substr($session_id, 0, 40) : 'empty') . ', client: ' . $client_id);

            $result = $soap_client->__call('startBackgroundTaskWorker', [
                $session_string,
            ]);
            if ($result) {
                $this->inform('Calling webserver successful.');
            } else {
                throw new RuntimeException('Calling webserver failed.');
            }
        } catch (Throwable $t) {
            $this->error($t->getMessage());
            $this->error('Calling webserver failed');
            $this->error('Stack trace: ' . $t->getTraceAsString());
        }

        return $environment;
    }

    private function inform(string $text, bool $force = false): void
    {
        if ($this->io === null || (!$force && !$this->io->isVerbose())) {
            return;
        }

        $this->io->inform($text);
    }

    private function error(string $text): void
    {
        if ($this->io === null) {
            return;
        }

        $this->io->error($text);
    }

    /**
     * @throws ilCurlConnectionException
     */
    private function getCurlConnection(ilSetting $settings, string $url): ilCurlConnection
    {
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
}
