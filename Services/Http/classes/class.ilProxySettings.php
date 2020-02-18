<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Http/exceptions/class.ilProxyException.php';

/**
 * class ilProxySettings
 *
 * @author	Michael Jansen <mjansen@databay.de>
 * @version	$Id$
 *
 */
class ilProxySettings
{
    const CONNECTION_CHECK_TIMEOUT = 10;

    /**
     *
     * Unique instance
     *
     * @access	protected
     * @var		ilProxySettings
     * @type	ilProxySettings
     *
     */
    protected static $_instance = null;
    
    /**
     *
     * Host
     *
     * @access	protected
     * @var		string
     * @type	string
     *
     */
    protected $host = '';
    
    /**
     *
     * Port
     *
     * @access	protected
     * @var		string
     * @type	string
     *
     */
    protected $post = '';
    
    /**
     *
     * Status
     *
     * @access	protected
     * @var		boolean
     * @type	boolean
     *
     */
    protected $isActive = false;
    
    /**
     *
     * Constructor
     *
     * @access	protected
     *
     */
    protected function __construct()
    {
        $this->read();
    }
    
    /**
     *
     * __clone
     *
     * @access	private
     *
     */
    private function __clone()
    {
    }
    
    /**
     *
     * Getter for unique instance
     *
     * @access	public
     * @static
     * @return	ilProxySettings
     *
     */
    public static function _getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     *
     * Fetches data from database
     *
     * @access	protected
     *
     */
    protected function read()
    {
        global $ilSetting;
        
        $this->setHost($ilSetting->get('proxy_host'));
        $this->setPort($ilSetting->get('proxy_port'));
        $this->isActive((bool) $ilSetting->get('proxy_status'));
    }
    
    /**
     *
     * Getter/Setter for status
     *
     * @access	public
     * @param	mixed	boolean or null
     * @return	mixed	ilProxySettings or boolean
     *
     */
    public function isActive($status = null)
    {
        if (null === $status) {
            return (bool) $this->isActive;
        }
        
        $this->isActive = (bool) $status;
        
        return $this;
    }
    
    /**
     *
     * Setter for host
     *
     * @access	public
     * @param	string
     * @return	ilProxySettings
     *
     */
    public function setHost($host)
    {
        $this->host = $host;
        
        return $this;
    }
    
    /**
     *
     * Getter for host
     *
     * @access	public
     * @return	string
     *
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     *
     * Setter for port
     *
     * @access	public
     * @param	string
     * @return	ilProxySettings
     *
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }
    
    /**
     *
     * Getter for port
     *
     * @access	public
     * @return	string
     *
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     *
     * Saves the current data in database
     *
     * @access	public
     * @return	ilProxySettings
     *
     */
    public function save()
    {
        global $ilSetting;
        
        $ilSetting->set('proxy_host', $this->getHost());
        $ilSetting->set('proxy_port', $this->getPort());
        $ilSetting->set('proxy_status', (int) $this->isActive());
        
        return $this;
    }
    
    /**
     *
     * Verifies the proxy server connection
     *
     * @access	public
     * @return	ilProxySettings
     * @throws	ilProxyException
     *
     */
    public function checkConnection()
    {
        global $DIC;

        $errno  = null;
        $errstr = null;

        set_error_handler(function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        });

        try {
            $host = $this->getHost();
            if (strspn($host, '.0123456789') != strlen($host) && strstr($host, '/') === false) {
                $host = gethostbyname($host);
            }
            $port =   $this->getPort() % 65536;

            if (!fsockopen($host, $port, $errno, $errstr, self::CONNECTION_CHECK_TIMEOUT)) {
                restore_error_handler();
                throw new ilProxyException(strlen($errstr) ? $errstr : $DIC->language()->txt('proxy_not_connectable'));
            }
            restore_error_handler();
        } catch (Exception $e) {
            restore_error_handler();
            throw new ilProxyException(strlen($errstr) ? $errstr : $DIC->language()->txt('proxy_not_connectable'));
        }

        return $this;
    }
}
