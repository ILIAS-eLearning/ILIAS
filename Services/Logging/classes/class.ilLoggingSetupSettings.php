<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/interfaces/interface.ilLoggingSettings.php';
/**
* Logger settings for setup
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesLogging
*/
class ilLoggingSetupSettings implements ilLoggingSettings
{
    private $enabled = false;
    private $log_dir = '';
    private $log_file = '';
    
    
    public function __construct()
    {
    }
    
    public function init()
    {
        $ilIliasIniFile = new ilIniFile("./ilias.ini.php");
        $ilIliasIniFile->read();


        $enabled =  $ilIliasIniFile->readVariable('log', 'enabled');
        $this->enabled = (($enabled == '1') ? true : false);
        
        
        
        $this->log_dir = (string) $ilIliasIniFile->readVariable('log', 'path');
        $this->log_file = (string) $ilIliasIniFile->readVariable('log', 'file');
    }
    
    /**
     * Logging enabled
     * @return type
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    public function getLogDir()
    {
        return $this->log_dir;
    }
    
    public function getLogFile()
    {
        return $this->log_file;
    }
    
    /**
     * Get log Level
     * @return type
     */
    public function getLevel()
    {
        include_once './Services/Logging/classes/public/class.ilLogLevel.php';
        return ilLogLevel::INFO;
    }
    
    public function getLevelByComponent($a_component_id)
    {
        return $this->getLevel();
    }
    
    /**
     * Get log Level
     * @return type
     */
    public function getCacheLevel()
    {
        include_once './Services/Logging/classes/public/class.ilLogLevel.php';
        return ilLogLevel::INFO;
    }
    
    public function isCacheEnabled()
    {
        return false;
    }
    
    public function isMemoryUsageEnabled()
    {
        return false;
    }
    
    public function isBrowserLogEnabled()
    {
        return false;
    }
    
    public function isBrowserLogEnabledForUser($a_login)
    {
        return false;
    }
    
    public function getBrowserLogUsers()
    {
        return array();
    }
}
