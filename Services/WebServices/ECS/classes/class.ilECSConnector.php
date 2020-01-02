<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/

include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
include_once('Services/WebServices/ECS/classes/class.ilECSResult.php');
include_once('Services/WebServices/Curl/classes/class.ilCurlConnection.php');

class ilECSConnector
{
    const HTTP_CODE_CREATED = 201;
    const HTTP_CODE_OK = 200;
    const HTTP_CODE_NOT_FOUND = 404;
    
    const HEADER_MEMBERSHIPS = 'X-EcsReceiverMemberships';
    const HEADER_COMMUNITIES = 'X-EcsReceiverCommunities';


    protected $path_postfix = '';
    
    protected $settings;

    protected $header_strings = array();
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct(ilECSSetting $settings = null)
    {
        if ($settings) {
            $this->settings = $settings;
        } else {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Using deprecated call');
            $GLOBALS['DIC']['ilLog']->logStack();
        }
    }

    // Header methods
    /**
     * Add Header
     * @param string $a_name
     * @param string $a_value
     */
    public function addHeader($a_name, $a_value)
    {
        $this->header_strings[] = ($a_name . ': ' . $a_value);
    }

    public function getHeader()
    {
        return (array) $this->header_strings;
    }

    public function setHeader($a_header_strings)
    {
        $this->header_strings = $a_header_strings;
    }

    /**
     * Get current server setting
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->settings;
    }

    
    ///////////////////////////////////////////////////////
    // auths methods
    ///////////////////////////////////////////////////////
    
    /**
     * Add auth resource
     *
     * @access public
     * @param string post data
     * @return int new econtent id
     * @throws ilECSConnectorException
     *
     */
    public function addAuth($a_post, $a_target_mid)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Add new Auth resource...');

        $this->path_postfix = '/sys/auths';
        
        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_target_mid);
            #$this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, 1);

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, $a_post);
            $ret = $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_CREATED) {
                $ilLog->write(__METHOD__ . ': Cannot create auth resource, did not receive HTTP 201. ');
                $ilLog->write(__METHOD__ . ': POST was: ' . $a_post);
                $ilLog->write(__METHOD__ . ': HTTP code: ' . $info);
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 201 (created)');
            $ilLog->write(__METHOD__ . ': POST was: ' . $a_post);

            $result = new ilECSResult($ret);
            $auth = $result->getResult();

            $ilLog->write(__METHOD__ . ': ... got hash: ' . $auth->hash);

            return $auth->hash;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
    
    /**
     * get auth resource
     *
     * @access public
     * @param auth hash (transfered via GET)
     * @throws ilECSConnectorException
     */
    public function getAuth($a_hash, $a_details_only = false)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        if (!strlen($a_hash)) {
            $ilLog->write(__METHOD__ . ': No auth hash given. Aborting.');
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
            
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_OK) {
                $ilLog->write(__METHOD__ . ': Cannot get auth resource, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 200 (ok)');
            
            $ecs_result = new ilECSResult($res);
            // Return ECSEContentDetails for details switch
            if ($a_details_only) {
                include_once './Services/WebServices/ECS/classes/class.ilECSEContentDetails.php';
                $details = new ilECSEContentDetails();
                $details->loadFromJson($ecs_result->getResult());
                return $details;
            }
            return $ecs_result;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
    
    ///////////////////////////////////////////////////////
    // eventqueues methods
    ///////////////////////////////////////////////////////
    
    /**
     * get event queue
     *
     * @access public
     * @throw ilECSConnectorException
     * @deprecated
     */
    public function getEventQueues()
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $this->path_postfix = '/eventqueues';

        try {
            $this->prepareConnection();
            
            $res = $this->call();
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_OK) {
                $ilLog->write(__METHOD__ . ': Cannot get event queue, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 200 (ok)');
            return new ilECSResult($res);
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
     * @param bool set to true for deleting the current element
     * @throws ilECSConnectorException
     */
    public function readEventFifo($a_delete = false)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

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
            #$ilLog->write(__METHOD__.': Checking HTTP status...');
            if ($info != self::HTTP_CODE_OK) {
                $ilLog->write(__METHOD__ . ': Cannot read event fifo, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            #$ilLog->write(__METHOD__.': ... got HTTP 200 (ok)');

            $result = new ilECSResult($res);
            
            #$GLOBALS['DIC']['ilLog']->write(__METHOD__.':------------------------------------- FIFO content'. print_r($result,true));
            
            return $result;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
    
    ///////////////////////////////////////////////////////
    // econtents methods
    ///////////////////////////////////////////////////////

    public function getResourceList($a_path)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        $this->path_postfix = $a_path;

        try {
            $this->prepareConnection();
            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $res = $this->call();

            // Checking status code
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_OK) {
                $ilLog->write(__METHOD__ . ': Cannot get ressource list, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 200 (ok)');

            return new ilECSResult($res, false, ilECSResult::RESULT_TYPE_URL_LIST);
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    
    /**
     * Get resources from ECS server.
     *
     *
     *
     * @access public
     * @param string resource "path"
     * @param int e-content id
     * @return object ECSResult
     * @throws ilECSConnectorException
     */
    public function getResource($a_path, $a_econtent_id, $a_details_only = false)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        if ($a_econtent_id) {
            $ilLog->write(__METHOD__ . ': Get resource with ID: ' . $a_econtent_id);
        } else {
            $ilLog->write(__METHOD__ . ': Get all resources ...');
        }
        
        $this->path_postfix = $a_path;
        if ($a_econtent_id) {
            $this->path_postfix .= ('/' . (int) $a_econtent_id);
        }
        if ($a_details_only) {
            $this->path_postfix .= ('/details');
        }
        
        try {
            $this->prepareConnection();
            $res = $this->call();

            // Checking status code
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_OK) {
                $ilLog->write(__METHOD__ . ': Cannot get ressource, did not receive HTTP 200. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 200 (ok)');
            
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
     * @param string resource "path"
     * @param string post data
     * @return int new econtent id
     * @throws ilECSConnectorException
     *
     */
    public function addResource($a_path, $a_post)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Add new EContent...');

        $this->path_postfix = $a_path;
        
        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_HEADER, true);
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, $a_post);
            $res = $this->call();
            
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_CREATED) {
                $ilLog->write(__METHOD__ . ': Cannot create econtent, did not receive HTTP 201. ');
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 201 (created)');

            $eid =  self::_fetchEContentIdFromHeader($this->curl->getResponseHeaderArray());
            return $eid;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
    
    /**
     * update resource
     *
     * @access public
     * @param string resource "path"
     * @param int econtent id
     * @param string post content
     * @throws ilECSConnectorException
     */
    public function updateResource($a_path, $a_econtent_id, $a_post_string)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Update resource with id ' . $a_econtent_id);
        
        $this->path_postfix = $a_path;
        
        if ($a_econtent_id) {
            $this->path_postfix .= ('/' . (int) $a_econtent_id);
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

            $tempfile = ilUtil::ilTempnam();
            $ilLog->write(__METHOD__ . ': Created new tempfile: ' . $tempfile);

            $fp = fopen($tempfile, 'w');
            fwrite($fp, $a_post_string);
            fclose($fp);
            
            $this->curl->setOpt(CURLOPT_UPLOAD, true);
            $this->curl->setOpt(CURLOPT_INFILESIZE, filesize($tempfile));
            $fp = fopen($tempfile, 'r');
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
     * @access public
     * @param string resource "path"
     * @param string econtent id
     * @throws ilECSConnectorException
     */
    public function deleteResource($a_path, $a_econtent_id)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Delete resource with id ' . $a_econtent_id);

        $this->path_postfix = $a_path;
        
        if ($a_econtent_id) {
            $this->path_postfix .= ('/' . (int) $a_econtent_id);
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
     *
     *
     * @access public
     * @param int membership id
     * @throw ilECSConnectorException
     */
    public function getMemberships($a_mid = 0)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Get existing memberships');

        $this->path_postfix = '/sys/memberships';
        if ($a_mid) {
            $ilLog->write(__METHOD__ . ': Read membership with id: ' . $a_mid);
            $this->path_postfix .= ('/' . (int) $a_mid);
        }
        try {
            $this->prepareConnection();
            $res = $this->call();

            $this->curl->setOpt(CURLOPT_HTTPHEADER, array(0 => 'X-EcsQueryStrings: sender=true'));
            
            // Checking status code
            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            if ($info != self::HTTP_CODE_OK) {
                $ilLog->write(__METHOD__ . ': Cannot get memberships, did not receive HTTP 200. ');
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
     * @access private
     * @throws ilCurlConnectionException
     */
    protected function prepareConnection()
    {
        try {
            $this->curl = new ilCurlConnection($this->settings->getServerURI() . $this->path_postfix);
            $this->curl->init();
            $this->curl->setOpt(CURLOPT_HTTPHEADER, array(0 => 'Accept: application/json'));
            $this->curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
            $this->curl->setOpt(CURLOPT_VERBOSE, 1);
            $this->curl->setOpt(CURLOPT_TIMEOUT_MS, 2000);

            switch ($this->getServer()->getAuthType()) {
                case ilECSSetting::AUTH_APACHE:
                    $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
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
     * @access private
     * @throws ilCurlConnectionException
     */
    protected function call()
    {
        try {
            $res = $this->curl->exec();
            return $res;
        } catch (ilCurlConnectionException $exc) {
            throw($exc);
        }
    }
    
    
    /**
     * fetch new econtent id from location header
     *
     * @access public
     * @static
     *
     * @param array header array
     */
    protected static function _fetchEContentIdFromHeader($a_header)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        if (!isset($a_header['Location'])) {
            return false;
        }
        $end_path = strrpos($a_header['Location'], "/");
        
        if ($end_path === false) {
            $ilLog->write(__METHOD__ . ': Cannot find path seperator.');
            return false;
        }
        $econtent_id = substr($a_header['Location'], $end_path + 1);
        $ilLog->write(__METHOD__ . ': Received EContentId ' . $econtent_id);
        return (int) $econtent_id;
    }
}
