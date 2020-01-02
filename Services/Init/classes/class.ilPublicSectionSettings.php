<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilPublicSectionSettings
{
    /**
     * @var ilPublicSectionSettings
     */
    protected static $instance = null;
    
    
    /**
     * @var ilSetting
     */
    private $settings = null;
    
    private $enabled = false;
    private $domains = array();
    
    /**
     * read settings
     */
    private function __construct()
    {
        $this->settings = $GLOBALS['DIC']->settings();
        $this->read();
    }
    
    /**
     * Get instance
     * @return \ilPublicSectionSettings
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setDomains(array $domains)
    {
        $this->domains = $domains;
    }
    
    public function getDomains()
    {
        return (array) $this->domains;
    }
    
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * Check if public section
     * @param type $a_domain
     * @return boolean
     */
    public function isEnabledForDomain($a_domain)
    {
        if (!$this->enabled) {
            return false;
        }
        if (count($this->domains)) {
            if (in_array(trim($a_domain), $this->getDomains())) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
    
    public function setEnabled($stat)
    {
        $this->enabled = $stat;
    }
    
    public function save()
    {
        $this->settings->set('pub_section', $this->isEnabled());
        $this->settings->set('pub_section_domains', serialize($this->getDomains()));
    }
    
    /**
     * read settings
     */
    protected function read()
    {
        $this->enabled = $this->settings->get('pub_section', $this->enabled);
        
        $domains = $this->settings->get('pub_section_domains', serialize($this->domains));
        $this->domains = unserialize($domains);
    }
}
