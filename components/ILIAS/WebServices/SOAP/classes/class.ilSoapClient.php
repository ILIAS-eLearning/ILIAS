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
/**
 * Wrapper class for soap_client
 * Extends built-in soap client and offers time (connect, response) settings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @package ilias
 */
class ilSoapClient
{
    public const DEFAULT_CONNECT_TIMEOUT = 10;
    public const DEFAULT_RESPONSE_TIMEOUT = 5;

    private ilLogger $log;
    private ilSetting $settings;
    private ?SoapClient $client = null;
    private string $uri;
    private bool $use_wsdl = true;
    private int $connect_timeout = self::DEFAULT_CONNECT_TIMEOUT;
    private int $response_timeout = self::DEFAULT_RESPONSE_TIMEOUT;
    private ?int $stored_socket_timeout = null;

    public function __construct(string $a_uri = '')
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->log = $DIC->logger()->wsrv();
        $this->uri = $a_uri;
        $this->use_wsdl = true;
        $timeout = (int) $this->settings->get('soap_connect_timeout', (string) self::DEFAULT_CONNECT_TIMEOUT);
        if ($timeout) {
            $this->connect_timeout = $timeout;
        }
        $this->connect_timeout = $timeout;

        $this->response_timeout = (int) $this->settings->get('soap_response_timeout', (string) self::DEFAULT_RESPONSE_TIMEOUT);
    }

    public function getServer(): string
    {
        return $this->uri;
    }

    public function setTimeout(int $a_timeout): void
    {
        $this->connect_timeout = $a_timeout;
    }

    public function getTimeout(): int
    {
        return $this->connect_timeout;
    }

    public function setResponseTimeout(int $a_timeout): void
    {
        $this->response_timeout = $a_timeout;
    }

    public function getResponseTimeout(): int
    {
        return $this->response_timeout;
    }

    public function enableWSDL(bool $a_stat): void
    {
        $this->use_wsdl = $a_stat;
    }

    public function enabledWSDL(): bool
    {
        return $this->use_wsdl;
    }

    public function init(): bool
    {
        $internal_path = $this->settings->get('soap_internal_wsdl_path');
        if (trim($this->getServer()) === '') {
            if ($internal_path) {
                $this->uri = $internal_path;
            } elseif (trim($this->settings->get('soap_wsdl_path', '')) !== '') {
                $this->uri = $this->settings->get('soap_wsdl_path', '');
            } else {
                $this->uri = ilUtil::_getHttpPath() . '/public/soap/server.php?wsdl';
            }
        }
        try {
            $this->log->debug('Using wsdl: ' . $this->getServer());
            $this->log->debug('Using connection timeout: ' . $this->getTimeout());
            $this->log->debug('Using response timeout: ' . $this->getResponseTimeout());

            $this->setSocketTimeout(true);
            $this->client = new SoapClient(
                $this->uri,
                array(
                    'exceptions' => true,
                    'trace' => 1,
                    'connection_timeout' => $this->getTimeout(),
                    'stream_context' => $this->uri === $internal_path ? stream_context_create([
                        'ssl' => [
                            'verify_peer' => (bool) $this->settings->get('soap_internal_wsdl_verify_peer', '1'),
                            'verify_peer_name' => (bool) $this->settings->get('soap_internal_wsdl_verify_peer_name', '1'),
                            'allow_self_signed' => (bool) $this->settings->get('soap_internal_wsdl_allow_self_signed', ''),
                        ]
                    ]) : null
                )
            );
            return true;
        } catch (SoapFault $ex) {
            $this->log->warning('Soap init failed with message: ' . $ex->getMessage());
            $this->resetSocketTimeout();
            return false;
        } finally {
            $this->resetSocketTimeout();
        }
    }

    protected function setSocketTimeout(bool $a_wsdl_mode): bool
    {
        $this->stored_socket_timeout = (int) ini_get('default_socket_timeout');
        $this->log->debug('Default socket timeout is: ' . $this->stored_socket_timeout);

        if ($a_wsdl_mode) {
            $this->log->debug('WSDL mode, using socket timeout: ' . $this->getTimeout());
            ini_set('default_socket_timeout', (string) $this->getTimeout());
        } else {
            $this->log->debug('Non WSDL mode, using socket timeout: ' . $this->getResponseTimeout());
            ini_set('default_socket_timeout', (string) $this->getResponseTimeout());
        }

        return true;
    }

    /**
     * Reset socket default timeout to defaults
     */
    protected function resetSocketTimeout(): bool
    {
        ini_set('default_socket_timeout', (string) $this->stored_socket_timeout);
        $this->log->debug('Restoring default socket timeout to: ' . $this->stored_socket_timeout);
        return true;
    }

    /**
     * @param string $a_operation
     * @param array $a_params
     * @return false|mixed
     */
    public function call(string $a_operation, array $a_params)
    {
        $this->log->debug('Calling webservice: ' . $a_operation);

        $this->setSocketTimeout(false);
        try {
            return $this->client->__call($a_operation, $a_params);
        } catch (SoapFault $exception) {
            $this->log->error('Calling webservice failed with message: ' . $exception->getMessage());
            $this->log->debug((string) $this->client->__getLastResponseHeaders());
            $this->log->debug((string) $this->client->__getLastResponse());
            return false;
        } catch (Exception $exception) {
            $this->log->error('Caught unknown exception with message: ' . $exception->getMessage());
            $this->log->debug((string) $this->client->__getLastResponseHeaders());
            $this->log->debug((string) $this->client->__getLastResponse());
        } finally {
            $this->resetSocketTimeout();
        }

        return false;
    }
}
