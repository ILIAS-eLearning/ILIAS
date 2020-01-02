<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/classes/public/class.ilLogLevel.php';
include_once './Services/Administration/classes/class.ilSetting.php';
include_once './Services/Logging/interfaces/interface.ilLoggingSettings.php';
    

/**
* @defgroup ServicesLogging Services/Logging
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesLogging
*/
class ilLoggingDBSettings implements ilLoggingSettings
{
    protected static $instance = null;
    
    private $enabled = false;
    
    private $storage = null;
    
    private $level = null;
    private $cache = false;
    private $cache_level = null;
    private $memory_usage = false;
    private $browser = false;
    private $browser_users = array();
    
    
    
    /**
     * Singleton contructor
     *
     * @access private
     */
    private function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        
        $this->enabled = ILIAS_LOG_ENABLED;
        $this->level = ilLogLevel::INFO;
        $this->cache_level = ilLogLevel::DEBUG;
        
        $this->storage = new ilSetting('logging');
        $this->read();
    }

    /**
     * Get instance
     * @param int $a_server_id
     * @return ilLoggingDBSettings
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }
    
    /**
     * Get level by component
     * @param type $a_component_id
     * @return type
     * @todo better performance
     */
    public function getLevelByComponent($a_component_id)
    {
        include_once './Services/Logging/classes/class.ilLogComponentLevels.php';
        $levels = ilLogComponentLevels::getInstance()->getLogComponents();
        foreach ($levels as $level) {
            if ($level->getComponentId() == $a_component_id) {
                if ($level->getLevel()) {
                    return $level->getLevel();
                }
            }
        }
        return $this->getLevel();
    }
    
    /**
     * @return ilSetting
     */
    protected function getStorage()
    {
        return $this->storage;
    }
    
    /**
     * Check if logging is enabled
     * @return type
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    public function getLogDir()
    {
        return ILIAS_LOG_DIR;
    }
    
    public function getLogFile()
    {
        return ILIAS_LOG_FILE;
    }
    
    /**
     * Get log level
     * @return type
     */
    public function getLevel()
    {
        return $this->level;
    }
    
    /**
     * Set log level
     * @param type $a_level
     */
    public function setLevel($a_level)
    {
        $this->level = $a_level;
    }
    
    /**
     * Set cache level
     * @param type $a_level
     */
    public function setCacheLevel($a_level)
    {
        $this->cache_level = $a_level;
    }
    
    /**
     * Get cache level
     * @return type
     */
    public function getCacheLevel()
    {
        return $this->cache_level;
    }
    
    /**
     * Enable caching
     * @param type $a_status
     */
    public function enableCaching($a_status)
    {
        $this->cache = $a_status;
    }
    
    public function isCacheEnabled()
    {
        return $this->cache;
    }
    
    /**
     * Enable logging of memory usage
     * @param type $a_stat
     */
    public function enableMemoryUsage($a_stat)
    {
        $this->memory_usage = $a_stat;
    }
    
    /**
     * Check if loggin of memory usage is enabled
     * @return type
     */
    public function isMemoryUsageEnabled()
    {
        return $this->memory_usage;
    }
    
    /**
     * Check if browser log is enabled
     * @return type
     */
    public function isBrowserLogEnabled()
    {
        return $this->browser;
    }
    
    
    /**
     * Check if browser log is enabled for user
     * @param type $a_login
     * @return boolean
     */
    public function isBrowserLogEnabledForUser($a_login)
    {
        if (!$this->isBrowserLogEnabled()) {
            return false;
        }
        if (in_array($a_login, $this->getBrowserLogUsers())) {
            return true;
        }
        return false;
    }
    
    /**
     * Enable browser log
     * @param type $a_stat
     */
    public function enableBrowserLog($a_stat)
    {
        $this->browser = $a_stat;
    }
    
    public function getBrowserLogUsers()
    {
        return $this->browser_users;
    }
    
    public function setBrowserUsers(array $users)
    {
        $this->browser_users = $users;
    }
    

    /**
     * Update setting
     */
    public function update()
    {
        $this->getStorage()->set('level', $this->getLevel());
        $this->getStorage()->set('cache', (int) $this->isCacheEnabled());
        $this->getStorage()->set('cache_level', $this->getCacheLevel());
        $this->getStorage()->set('memory_usage', $this->isMemoryUsageEnabled());
        $this->getStorage()->set('browser', $this->isBrowserLogEnabled());
        $this->getStorage()->set('browser_users', serialize($this->getBrowserLogUsers()));
    }

    
    /**
     * Read settings
     *
     * @access private
     */
    private function read()
    {
        $this->setLevel($this->getStorage()->get('level', $this->level));
        $this->enableCaching($this->getStorage()->get('cache', $this->cache));
        $this->setCacheLevel($this->getStorage()->get('cache_level', $this->cache_level));
        $this->enableMemoryUsage($this->getStorage()->get('memory_usage', $this->memory_usage));
        $this->enableBrowserLog($this->getStorage()->get('browser', $this->browser));
        $this->setBrowserUsers(unserialize($this->getStorage()->get('browser_users', serialize($this->browser_users))));
    }
}
