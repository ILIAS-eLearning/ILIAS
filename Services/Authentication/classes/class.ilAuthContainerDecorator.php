<?php declare(strict_types=1);

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
* Abstract decorator for PEAR::Auth
* Base class for all Ilias Authentication classes
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAuthentication
*/
abstract class ilAuthContainerDecorator
{
    private $container = null;
    protected $parameter = array();
    
    /**
     * Constructor
     * @param
     */
    public function __construct()
    {
    }
    
    
    
    /**
     * Wrapper for all PEAR_Auth_Container methods
     * @param
     * @return
     */
    final public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->container,$name), $arguments);
    }
    
    /**
     * Init the PEAR container
     */
    abstract protected function initContainer();
    
    /**
     * get pear container
     */
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * set pear container
     */
    public function setContainer($a_container)
    {
        $this->container = $a_container;
    }
    
    
    public function getAuthObject()
    {
        return $this->getContainer()->_auth_obj;
    }
    
    /**
     * Add a parameter. Used for contructor in PEAR_Auth_Container
     */
    public function appendParameter($a_key, $a_value)
    {
        $this->parameter[$a_key] = $a_value;
    }
    
    public function appendParameters($a_params)
    {
        $this->parameter = array_merge($this->parameter, $a_params);
    }
    
    /**
     * get auth container parameters
     */
    public function getParameters()
    {
        return $this->parameter ? $this->parameter : array();
    }
    
    /**
     *
     * @return
     * @param object $a_username
     * @param object $a_auth
     */
    public function loginObserver($a_username, $a_auth)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(
            __METHOD__ . ': logged in as ' . $a_username .
            ', remote:' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'] .
            ', server:' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']
        );
    }
    
    /**
     * Called from base class after failed login
     *
     * @param string username
     * @param object PEAR auth object
     */
    public function failedLoginObserver($a_username, $a_auth)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(
            __METHOD__ . ': login failed for user ' . $a_username .
            ', remote:' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'] .
            ', server:' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']
        );
        return false;
    }
    
    /**
     * Called from base class after call of checkAuth
     *
     * @param string username
     * @param object PEAR auth object
     */
    public function checkAuthObserver($a_username, $a_auth)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        //$ilLog->write(__METHOD__.': checkAuth called');
    
        return true;
    }

    /**
     * Called from base class after logout
     *
     * @param string username
     * @param object PEAR auth object
     */
    public function logoutObserver($a_username, $a_auth)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(
            __METHOD__ . ': User logged out: ' . $a_username .
            ', remote:' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'] .
            ', server:' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']
        );
    }
}
