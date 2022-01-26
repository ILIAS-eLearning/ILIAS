<?php

/**
 * Logging factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
interface ilLoggerFactoryInterface
{
    /**
     * Init user specific log options
     *
     * @param type $a_login
     * @return boolean
     */
    public function initUser($a_login);
    
    /**
     * Get settigns
     *
     * @return ilLoggingSettings
     */
    public function getSettings();
    
    /**
     * Get component logger
     *
     * @param string $a_component_id
     */
    public function getComponentLogger($a_component_id): ilLoggerInterface;
}
