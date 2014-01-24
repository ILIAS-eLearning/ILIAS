<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * Storage driver for using multiple storage drivers in a fall through fashion
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Authentication
 * @package    Auth
 * @author     Adam Ashley <aashley@php.net>
 * @copyright  2001-2006 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id: Multiple.php,v 1.4 2007/06/12 03:11:26 aashley Exp $
 * @since      File available since Release 1.5.0
 */

/**
 * Include Auth_Container base class
 */
require_once "Auth/Container.php";
/**
 * Include PEAR package for error handling
 */
require_once "PEAR.php";

/**
 * Storage driver for using multiple storage drivers in a fall through fashion
 *
 * This storage driver provides a mechanism for working through multiple
 * storage drivers until either one allows successful login or the list is
 * exhausted.
 *
 * This container takes an array of options of the following form:
 *
 * array(
 *   array(
 *     'type'    => <standard container type name>,
 *     'options' => <normal array of options for container>,
 *   ),
 * );
 *
 * Full example:
 *
 * $options = array(
 *   array(
 *     'type'    => 'DB',
 *     'options' => array(
 *       'dsn' => "mysql://user:password@localhost/database",
 *     ),
 *   ),
 *   array(
 *     'type'    => 'Array',
 *     'options' => array(
 *       'cryptType' => 'md5',
 *       'users'     => array(
 *         'admin' => md5('password'),
 *       ),
 *     ),
 *   ),
 * );
 *
 * $auth = new Auth('Multiple', $options);
 *
 * @category   Authentication
 * @package    Auth
 * @author     Adam Ashley <aashley@php.net>
 * @copyright  2001-2006 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 1.6.1  File: $Revision: 1.4 $
 * @since      File available since Release 1.5.0
 */

class Auth_Container_Multiple extends Auth_Container {

    // {{{ properties

    /**
     * The options for each container
     *
     * @var array $options
     */
    var $options = array();

    /**
     * The instanciated containers
     *
     * @var array $containers
     */
    var $containers = array();

    // }}}
    // {{{ Auth_Container_Multiple()

    /**
     * Constructor for Array Container
     *
     * @param array $data Options for the container
     * @return void
     */
    function Auth_Container_Multiple($options)
    {
        if (!is_array($options)) {
            PEAR::raiseError('The options for Auth_Container_Multiple must be an array');
        }
        if (count($options) < 1) {
            PEAR::raiseError('You must define at least one sub container to use in Auth_Container_Multiple');
        }
        foreach ($options as $option) {
            if (!isset($option['type'])) {
                PEAR::raiseError('No type defined for sub container');
            }
        }
        $this->options = $options;
    }

    // }}}
    // {{{ fetchData()

    /**
     * Get user information from array
     *
     * This function uses the given username to fetch the corresponding
     * login data from the array. If an account that matches the passed
     * username and password is found, the function returns true.
     * Otherwise it returns false.
     *
     * @param  string Username
     * @param  string Password
     * @return boolean|PEAR_Error Error object or boolean
     */
    function fetchData($user, $pass)
    {
        $this->log('Auth_Container_Multiple::fetchData() called.', AUTH_LOG_DEBUG);
		
        foreach ($this->options as $key => $options) {

            $this->log('Using Container '.$key.' of type '.$options['type'].'.', AUTH_LOG_DEBUG);

            if (isset($this->containers[$key]) && is_a($this->containers[$key], 'Auth_Container')) {

				$container = &$this->containers[$key];

            } else {

				$this->containers[$key] = &$this->_auth_obj->_factory($options['type'], $options['options']);
                $this->containers[$key]->_auth_obj = &$this->_auth_obj;
                $container = &$this->containers[$key];

            }

            $result = $container->fetchData($user, $pass);

            if (PEAR::isError($result)) {

                $this->log('Container '.$key.': '.$result->getMessage(), AUTH_LOG_ERR);
                return $result;

            } elseif ($result == true) {

                $this->log('Container '.$key.': Authentication successful.', AUTH_LOG_DEBUG);
                return true;

            } else {

                $this->log('Container '.$key.': Authentication failed.', AUTH_LOG_DEBUG);

            }

        }

        $this->log('Auth_Container_Multiple: All containers rejected user credentials.', AUTH_LOG_DEBUG);

        return false;

    }

    // }}}

}

?>
