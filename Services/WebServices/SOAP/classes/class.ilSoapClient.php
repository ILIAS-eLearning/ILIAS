<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Wrapper class for soap_client
 * Extends built-in soap client and offers time (connect, response) settings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ilias
 */
class ilSoapClient
{
    const DEFAULT_CONNECT_TIMEOUT = 10;
    const DEFAULT_RESPONSE_TIMEOUT = 5;

    /**
     * @var ilLogger
     */
    private $log = null;
    
    /**
     * @var SoapClient
     */
    private $client = null;
    
    private $uri;
    
    private $connect_timeout = 10;
    private $response_timeout = 10;
    
    private $stored_socket_timeout = null;
    
    
    /**
     * @param string $a_uri
     */
    public function __construct($a_uri = '')
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->log = ilLoggerFactory::getLogger('wsrv');

        $this->uri = $a_uri;

        $this->use_wsdl = true;
        $timeout = $ilSetting->get('soap_connect_timeout', self::DEFAULT_CONNECT_TIMEOUT);
        if (!$timeout) {
            $timeout = self::DEFAULT_CONNECT_TIMEOUT;
        }
        $this->connect_timeout = $timeout;
        
        $this->response_timeout = (int) $ilSetting->get('soap_response_timeout', self::DEFAULT_RESPONSE_TIMEOUT);
    }
    
    /**
     * Get server uri
     * @return string
     */
    public function getServer()
    {
        return $this->uri;
    }
    
    /**
     * Set connect timeout
     * @param int $a_timeout
     */
    public function setTimeout($a_timeout)
    {
        $this->connect_timeout = $a_timeout;
    }
    
    /**
     * Get connect timeout
     * @return int
     */
    public function getTimeout()
    {
        return $this->connect_timeout;
    }
    
    /**
     * @param int $a_timeout Response Timeout
     */
    public function setResponseTimeout($a_timeout)
    {
        $this->response_timeout = (int) $a_timeout;
    }
    
    /**
     * @return int Response Timeout
     */
    public function getResponseTimeout()
    {
        return $this->response_timeout;
    }
    
    /**
     * enable wsdl mode
     * @param type $a_stat
     */
    public function enableWSDL($a_stat)
    {
        $this->use_wsdl = $a_stat;
    }
    
    /**
     * Check if wsdl is enabled
     * @return type
     */
    public function enabledWSDL()
    {
        return $this->use_wsdl;
    }
    

    /**
     * Init soap client
     */
    public function init()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        if (!strlen(trim($this->getServer()))) {
            if (strlen(trim($ilSetting->get('soap_wsdl_path', '')))) {
                $this->uri = $ilSetting->get('soap_wsdl_path', '');
            } else {
                $this->uri = ilUtil::_getHttpPath() . '/webservice/soap/server.php?wsdl';
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
                    'connection_timeout' => (int) $this->getTimeout()
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
    
    /**
     * Set socket timeout
     * @return boolean
     */
    protected function setSocketTimeout($a_wsdl_mode)
    {
        $this->stored_socket_timeout = ini_get('default_socket_timeout');
        $this->log->debug('Default socket timeout is: ' . $this->stored_socket_timeout);
        
        if ($a_wsdl_mode) {
            $this->log->debug('WSDL mode, using socket timeout: ' . $this->getTimeout());
            ini_set('default_socket_timeout', $this->getTimeout());
        } else {
            $this->log->debug('Non WSDL mode, using socket timeout: ' . $this->getResponseTimeout());
            ini_set('default_socket_timeout', $this->getResponseTimeout());
        }
        return true;
    }
    
    /**
     * Reset socket default timeout to defaults
     * @return boolean
     */
    protected function resetSocketTimeout()
    {
        ini_set('default_socket_timeout', $this->stored_socket_timeout);
        $this->log->debug('Restoring default socket timeout to: ' . $this->stored_socket_timeout);
        return true;
    }
    
    /**
     * Call webservice method
     * @param string $a_operation
     * @param array $a_params
     */
    public function call($a_operation, $a_params)
    {
        $this->log->debug('Calling webservice: ' . $a_operation);

        $this->setSocketTimeout(false);
        try {
            return $this->client->__call($a_operation, $a_params);
        } catch (SoapFault $exception) {
            $this->log->error('Calling webservice failed with message: ' . $exception->getMessage());
            $this->log->debug($this->client->__getLastResponseHeaders());
            $this->log->debug($this->client->__getLastResponse());
            return false;
        } catch (Exception $exception) {
            $this->log->error('Caught unknown exception with message: ' . $exception->getMessage());
            $this->log->debug($this->client->__getLastResponseHeaders());
            $this->log->debug($this->client->__getLastResponse());
        } finally {
            $this->resetSocketTimeout();
        }
    }
}
