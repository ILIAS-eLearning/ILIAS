<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSConnector
{
    public const HTTP_CODE_CREATED = 201;
    public const HTTP_CODE_OK = 200;
    public const HTTP_CODE_NOT_FOUND = 404;

    public const HEADER_MEMBERSHIPS = 'X-EcsReceiverMemberships';
    public const HEADER_COMMUNITIES = 'X-EcsReceiverCommunities';


    protected string $path_postfix = '';

    protected ?ilECSSetting $settings = null;
    protected ?ilCurlConnection $curl = null;

    protected array $header_strings = [];

    protected ilLogger $logger;

    public function __construct(ilECSSetting $settings = null)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        if ($settings) {
            $this->settings = $settings;
        } else {
            $this->logger->warning('Using deprecated call.');
            $this->logger->logStack(ilLogLevel::WARNING);
        }
    }

    // Header methods
    /**
     * Add Header
     * @param string $a_name
     * @param string $a_value
     */
    public function addHeader(string $a_name, string $a_value): void
    {
        $this->header_strings[] = ($a_name . ': ' . $a_value);
    }

    public function getHeader(): array
    {
        return $this->header_strings;
    }

    public function setHeader(array $a_header_strings): void
    {
        $this->header_strings = $a_header_strings;
    }

    /**
     * Get current server setting
     */
    public function getServer(): ilECSSetting
    {
        return $this->settings;
    }


    ///////////////////////////////////////////////////////
    // auths methods
    ///////////////////////////////////////////////////////

    /**
     * Add auth resource
     *
     * @param string post data
     * @return string the new hash for this authentication
     * @throws ilECSConnectorException
     *
     */
    public function addAuth($a_post, $a_target_mid): string
    {
        $this->logger->info(__METHOD__ . ': Add new Auth resource...');

        $this->path_postfix = '/sys/auths';

        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->addHeader(self::HEADER_MEMBERSHIPS, $a_target_mid);

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, $a_post);
            $ret = $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);

            $this->logger->info(__METHOD__ . ': Checking HTTP status...');
            if ($info !== self::HTTP_CODE_CREATED) {
                $this->logger->info(__METHOD__ . ': Cannot create auth resource, did not receive HTTP 201. ');
                $this->logger->info(__METHOD__ . ': POST was: ' . $a_post);
                $this->logger->info(__METHOD__ . ': HTTP code: ' . $info);
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->info(__METHOD__ . ': ... got HTTP 201 (created)');
            $this->logger->info(__METHOD__ . ': POST was: ' . $a_post);

            $result = new ilECSResult($ret);
            $auth = $result->getResult();

            $this->logger->info(__METHOD__ . ': ... got hash: ' . $auth->hash);

            return $auth->hash;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    /**
     * get auth resource
     *
     * @return ilECSResult|ilECSEContentDetails
     * @throws ilECSConnectorException
     */
    public function getAuth(string $a_hash, bool $a_details_only = false)
    {
        if ($a_hash === '') {
            $this->logger->error(__METHOD__ . ': No auth hash given. Aborting.');
            throw new ilECSConnectorException('No auth hash given.');
        }

        $this->path_postfix = '/sys/auths/' . $a_hash;

        if ($a_details_only) {
            $this->path_postfix .= ('/details');
        }


        try {
            $this->prepareConnection();
            $res = $this->call();
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);

            $this->logger->info(__METHOD__ . ': Checking HTTP status...');
            if ($info !== self::HTTP_CODE_OK) {
                $this->logger->info(__METHOD__ . ': Cannot get auth resource, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->info(__METHOD__ . ': ... got HTTP 200 (ok)');

            $ecs_result = new ilECSResult($res);
            // Return ECSEContentDetails for details switch
            if ($a_details_only) {
                $details = new ilECSEContentDetails();
                $details->loadFromJson($ecs_result->getResult());
                return $details;
            }
            return $ecs_result;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    #######################################################
    # event fifo methods
    #####################################################
    /**
     * Read event fifo
     *
     * @param bool $a_delete set to true for deleting the current element
     * @throws ilECSConnectorException
     */
    public function readEventFifo(bool $a_delete = false): ilECSResult
    {
        $this->path_postfix = '/sys/events/fifo';

        try {
            $this->prepareConnection();
            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');

            if ($a_delete) {
                $this->curl->setOpt(CURLOPT_POST, true);
                $this->curl->setOpt(CURLOPT_POSTFIELDS, '');
            }
            $res = $this->call();

            // Checking status code
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            if ($info !== self::HTTP_CODE_OK) {
                $this->logger->info(__METHOD__ . ': Cannot read event fifo, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            //TODO check if this return needs to be moved after the finally
            return new ilECSResult($res);
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        } finally {
            $this->curl->close();
        }
    }

    ///////////////////////////////////////////////////////
    // econtents methods
    ///////////////////////////////////////////////////////

    public function getResourceList(string $a_path): ilECSResult
    {
        $this->path_postfix = $a_path;

        try {
            $this->prepareConnection();
            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $res = $this->call();

            // Checking status code
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            $this->logger->info(__METHOD__ . ': Checking HTTP status...');
            if ($info !== self::HTTP_CODE_OK) {
                $this->logger->info(__METHOD__ . ': Cannot get ressource list, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->info(__METHOD__ . ': ... got HTTP 200 (ok)');

            return new ilECSResult($res, ilECSResult::RESULT_TYPE_URL_LIST);
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }


    /**
     * Get resources from ECS server.
     *
     * @throws ilECSConnectorException
     */
    public function getResource(string $a_path, int $a_econtent_id, $a_details_only = false): ilECSResult
    {
        // TODO make handling of a_econtent_id explict like setting it to null
        if ($a_econtent_id) {
            $this->logger->info(__METHOD__ . ': Get resource with ID: ' . $a_econtent_id);
        } else {
            $this->logger->info(__METHOD__ . ': Get all resources ...');
        }

        $this->path_postfix = $a_path;
        if ($a_econtent_id) {
            $this->path_postfix .= ('/' . $a_econtent_id);
        }
        if ($a_details_only) {
            $this->path_postfix .= ('/details');
        }

        try {
            $this->prepareConnection();
            $res = $this->call();

            // Checking status code
            $info = (int) $this->curl->getInfo(CURLINFO_HTTP_CODE);
            $this->logger->info(__METHOD__ . ': Checking HTTP status...');
            if ($info !== self::HTTP_CODE_OK) {
                $this->logger->info(__METHOD__ . ': Cannot get ressource, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->info(__METHOD__ . ': ... got HTTP 200 (ok)');

            $result = new ilECSResult($res);
            $result->setHeaders($this->curl->getResponseHeaderArray());
            $result->setHTTPCode($info);

            return $result;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    /**
     * Add resource
     *
     * @access public
     * @param string $a_path resource "path"
     * @param array|string $a_post post data
     * @return int new econtent id
     * @throws ilECSConnectorException
     *
     */
    public function addResource(string $a_path, $a_post): int
    {
        $this->logger->info(__METHOD__ . ': Add new EContent...');

        $this->path_postfix = $a_path;

        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_HEADER, true);
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, $a_post);
            $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);

            $this->logger->info(__METHOD__ . ': Checking HTTP status...');
            if ($info !== self::HTTP_CODE_CREATED) {
                $this->logger->info(__METHOD__ . ': Cannot create econtent, did not receive HTTP 201. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->info(__METHOD__ . ': ... got HTTP 201 (created)');

            return $this->_fetchEContentIdFromHeader($this->curl->getResponseHeaderArray());
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    /**
     * update resource
     *
     * @param string $a_path resource "path"
     * @param int $a_econtent_id econtent id
     * @param string $a_post_string post content
     * @throws ilECSConnectorException
     */
    public function updateResource(string $a_path, int $a_econtent_id, string $a_post_string): ilECSResult
    {
        $this->logger->info(__METHOD__ . ': Update resource with id ' . $a_econtent_id);

        $this->path_postfix = $a_path;

        if ($a_econtent_id) {
            $this->path_postfix .= ('/' . $a_econtent_id);
        } else {
            throw new ilECSConnectorException('Error calling updateResource: No content id given.');
        }
        try {
            $this->prepareConnection();
            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_HEADER, true);
            $this->curl->setOpt(CURLOPT_PUT, true);
            //TODO migrate to filesystem->tempfile
            $tempfile = ilFileUtils::ilTempnam();
            $this->logger->info(__METHOD__ . ': Created new tempfile: ' . $tempfile);

            $fp = fopen($tempfile, 'wb');
            fwrite($fp, $a_post_string);
            fclose($fp);

            $this->curl->setOpt(CURLOPT_UPLOAD, true);
            $this->curl->setOpt(CURLOPT_INFILESIZE, filesize($tempfile));
            $fp = fopen($tempfile, 'rb');
            $this->curl->setOpt(CURLOPT_INFILE, $fp);

            $res = $this->call();

            fclose($fp);
            unlink($tempfile);

            return new ilECSResult($res);
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    /**
     * Delete resource
     *
     * @param string $a_path resource "path"
     * @param int $a_econtent_id econtent id
     * @throws ilECSConnectorException
     */
    public function deleteResource(string $a_path, int $a_econtent_id): ilECSResult
    {
        $this->logger->info(__METHOD__ . ': Delete resource with id ' . $a_econtent_id);

        $this->path_postfix = $a_path;

        if ($a_econtent_id) {
            $this->path_postfix .= ('/' . $a_econtent_id);
        } else {
            throw new ilECSConnectorException('Error calling deleteResource: No content id given.');
        }

        try {
            $this->prepareConnection();
            $this->curl->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
            $res = $this->call();
            return new ilECSResult($res);
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    ///////////////////////////////////////////////////////
    // membership methods
    ///////////////////////////////////////////////////////

    /**
     * @param int $a_mid membership id
     * @throw ilECSConnectorException
     */
    public function getMemberships(int $a_mid = 0): ilECSResult
    {
        $this->logger->info(__METHOD__ . ': Get existing memberships');

        $this->path_postfix = '/sys/memberships';
        if ($a_mid) {
            $this->logger->info(__METHOD__ . ': Read membership with id: ' . $a_mid);
            $this->path_postfix .= ('/' . $a_mid);
        }
        try {
            $this->prepareConnection();
            $res = $this->call();

            $this->curl->setOpt(CURLOPT_HTTPHEADER, array(0 => 'X-EcsQueryStrings: sender=true'));

            // Checking status code
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            if ($info !== self::HTTP_CODE_OK) {
                $this->logger->info(__METHOD__ . ': Cannot get memberships, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }

            return new ilECSResult($res);
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    /**
     * prepare connection
     *
     * @throws ilCurlConnectionException
     */
    protected function prepareConnection(): void
    {
        try {
            $this->curl = new ilCurlConnection($this->settings->getServerURI() . $this->path_postfix);
            $this->curl->init(true);
            $this->curl->setOpt(CURLOPT_HTTPHEADER, array(0 => 'Accept: application/json'));
            $this->curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
            $this->curl->setOpt(CURLOPT_VERBOSE, 1);
            $this->curl->setOpt(CURLOPT_TIMEOUT_MS, 2000);

            switch ($this->getServer()->getAuthType()) {
                case ilECSSetting::AUTH_APACHE:
                    $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 1);
                    #$this->curl->setOpt(CURLOPT_SSL_VERIFYHOST,0);
                    $this->curl->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $this->curl->setOpt(
                        CURLOPT_USERPWD,
                        $this->getServer()->getAuthUser() . ':' . $this->getServer()->getAuthPass()
                    );
                    break;

                case ilECSSetting::AUTH_CERTIFICATE:
                    $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 1);
                    // use default 2 for libcurl 7.28.1 support
                    $this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
                    $this->curl->setOpt(CURLOPT_CAINFO, $this->settings->getCACertPath());
                    $this->curl->setOpt(CURLOPT_SSLCERT, $this->settings->getClientCertPath());
                    $this->curl->setOpt(CURLOPT_SSLKEY, $this->settings->getKeyPath());
                    $this->curl->setOpt(CURLOPT_SSLKEYPASSWD, $this->settings->getKeyPassword());
                    break;
            }
        } catch (ilCurlConnectionException $exc) {
            throw($exc);
        }
    }

    /**
     * call peer
     *
     * @return string|bool
     *
     * @throws ilCurlConnectionException
     */
    protected function call()
    {
        try {
            return $this->curl->exec();
        } catch (ilCurlConnectionException $exc) {
            throw($exc);
        }
    }

    /**
     * fetch new econtent id from location header
     *
     * @param array header array
     */
    private function _fetchEContentIdFromHeader(array $a_header): int
    {
        $location_parts = [];
        foreach ($a_header as $header => $value) {
            if (strcasecmp('Location', $header) === 0) {
                $location_parts = explode('/', $value);
                break;
            }
        }
        if (!$location_parts) {
            $this->logger->error(__METHOD__ . ': Cannot find location headers.');
            throw new ilECSConnectorException("Cannot find location header in response");
        }
        if (count($location_parts) === 1) {
            $this->logger->warning(__METHOD__ . ': Cannot find path seperator.');
            throw new ilECSConnectorException("Location header has wrong format: " . $location_parts[0]);
        }
        $econtent_id = end($location_parts);
        $this->logger->info(__METHOD__ . ': Received EContentId ' . $econtent_id);
        return (int) $econtent_id;
    }
}
