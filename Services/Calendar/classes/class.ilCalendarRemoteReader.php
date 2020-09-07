<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/WebServices/Curl/classes/class.ilCurlConnection.php';
include_once './Services/WebServices/Curl/classes/class.ilCurlConnectionException.php';

/**
* Reader for remote ical calendars
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*/
class ilCalendarRemoteReader
{
    const TYPE_ICAL = 1;
    
    // Fixed in the moment
    private $type = self::TYPE_ICAL;
    
    private $curl = null;
    
    private $url;
    private $user;
    private $pass;
    
    private $ical;

    /**
     * @var \ilLogger
     */
    private $logger;
    
    
    /**
     * Constructor
     * init curl
     */
    public function __construct($a_url)
    {
        global $DIC;

        $this->logger = $DIC->logger();
        $this->url = $a_url;
    }
    
    public function setUser($a_user)
    {
        $this->user = $a_user;
    }
    
    public function setPass($a_pass)
    {
        $this->pass = $a_pass;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * Read ical format
     *
     * @throws sonething
     */
    public function read()
    {
        $this->initCurl();
        
        switch ($this->getType()) {
            case self::TYPE_ICAL:
                return $this->readIcal();
        }
    }

    /**
     * Import appointments in calendar
     * @return type
     */
    public function import(ilCalendarCategory $cat)
    {
        switch ($this->getType()) {
            case self::TYPE_ICAL:
                return $this->importIcal($cat);
        }
    }
    
    /**
     * Read ical
     *
     * @throw ilCurlConnectionException
     */
    protected function readIcal()
    {
        $this->ical = $this->call();
        $this->logger->debug($this->ical);
        return true;
    }
    
    /**
     * Import ical in calendar
     * @param ilCalendarCategory $cat
     */
    protected function importIcal(ilCalendarCategory $cat)
    {
        // Delete old appointments
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        foreach (ilCalendarCategoryAssignments::_getAssignedAppointments(array($cat->getCategoryID())) as $app_id) {
            include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
            ilCalendarEntry::_delete($app_id);
        }
        ilCalendarCategoryAssignments::_deleteByCategoryId($cat->getCategoryID());
        
        // Import new appointments
        include_once './Services/Calendar/classes/iCal/class.ilICalParser.php';
        $parser = new ilICalParser($this->ical, ilICalParser::INPUT_STRING);
        $parser->setCategoryId($cat->getCategoryID());
        $parser->parse();
    }
    
    /**
     * Init curl connection
     */
    protected function initCurl()
    {
        try {
            $this->replaceWebCalProtocol();
            
            $this->curl = new ilCurlConnection($this->getUrl());
            $this->curl->init();

            if (ilProxySettings::_getInstance()->isActive()) {
                $this->curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
                $this->curl->setOpt(CURLOPT_PROXY, ilProxySettings::_getInstance()->getHost());
                $this->curl->setOpt(CURLOPT_PROXYPORT, ilProxySettings::_getInstance()->getPort());
            }

            $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
            $this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
            $this->curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
            
            $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
            $this->curl->setOpt(CURLOPT_MAXREDIRS, 3);
            
            if ($this->user) {
                $this->curl->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $this->curl->setOpt(CURLOPT_USERPWD, $this->user . ':' . $this->pass);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    protected function replaceWebCalProtocol()
    {
        if (substr($this->getUrl(), 0, 6) == 'webcal') {
            $purged = preg_replace('/webcal/', 'http', $this->getUrl(), 1);
            $this->url = $purged;
        }
    }
    
    /**
     * call peer
     *
     * @access private
     * @throws ilCurlConnectionException
     */
    private function call()
    {
        try {
            $res = $this->curl->exec();
            return $res;
        } catch (ilCurlConnectionException $exc) {
            throw($exc);
        }
    }
}
