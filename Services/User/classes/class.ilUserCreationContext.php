<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
*/
class ilUserCreationContext
{
    const CONTEXT_REGISTRATION = 1;
    const CONTEXT_ADMINISTRATION = 2;
    const CONTEXT_SOAP = 3;
    const CONTEXT_LDAP = 4;
    const CONTEXT_RADIUS = 5;
    const CONTEXT_SHIB = 6;
    
    
    private static $instance = null;
    
    private $contexts = array();
    
    /**
     * Default constructor
     */
    protected function __construct()
    {
    }
    
    /**
     * Get instance
     * @return ilUserCreationContext
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * get contexts
     */
    public function getValidContexts()
    {
        return array(
            self::CONTEXT_REGISTRATION,
            self::CONTEXT_ADMINISTRATION,
            self::CONTEXT_SOAP,
            self::CONTEXT_LDAP,
            self::CONTEXT_RADIUS,
            self::CONTEXT_SHIB
        );
    }
    
    /**
     * Get contexts
     * @return type
     */
    public function getCurrentContexts()
    {
        return $this->contexts;
    }
    
    /**
     * Add context
     * @param type $a_context
     */
    public function addContext($a_context)
    {
        if (in_array($a_context, $this->getValidContexts())) {
            if (!in_array($a_context, $this->getCurrentContexts())) {
                $this->contexts[] = $a_context;
            }
        }
    }
}
