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
