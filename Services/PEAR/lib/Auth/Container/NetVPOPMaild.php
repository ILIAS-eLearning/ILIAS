<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * Storage driver for use with a Vpopmaild server
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Authentication
 * @package    Auth
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @author     Stefan Ekman <stekman@sedata.org>
 * @author     Martin Jansen <mj@php.net>
 * @author     Mika Tuupola <tuupola@appelsiini.net>
 * @author     Adam Ashley <aashley@php.net>
 * @copyright  2001-2006 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link       http://pear.php.net/package/Auth
 * @since      File available since Release 1.2.0
 */

/**
 * Include Auth_Container base class
 */
require_once 'Auth/Container.php';
/**
 * Include PEAR package for error handling
 */
require_once 'PEAR.php';
/**
 * Include PEAR Net_Vpopmaild package
 */
require_once 'Net/Vpopmaild.php';

/**
 * Storage driver for Authentication on a Vpopmaild server.
 *
 * @category   Authentication
 * @package    Auth
 * @author     Martin Jansen <mj@php.net>
 * @author     Mika Tuupola <tuupola@appelsiini.net>
 * @author     Adam Ashley <aashley@php.net>
 * @copyright  2001-2006 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 1.5.4  File: $Revision: 1.1 $
 * @link       http://pear.php.net/package/Auth
 * @since      Class available since Release 1.6.0
 */
class Auth_Container_Vpopmaild extends Auth_Container
{

    /**
     * Vpopmaild Server
     * @var string
     */
    var $server = 'localhost';

    /**
     * Vpopmaild Server port
     * @var string
     */
    var $port = 89;

    /**
     * Constructor of the container class
     *
     * @param  $server string server or server:port combination
     * @return object Returns an error object if something went wrong
     */
    function Auth_Container_Vpopmaild($server=null)
    {
        if (isset($server) && !is_null($server)) {
            if (is_array($server)) {
                if (isset($server['host'])) {
                    $this->server = $server['host'];
                }
                if (isset($server['port'])) {
                    $this->port = $server['port'];
                }
            } else {
                if (strstr($server, ':')) {
                    $serverparts = explode(':', trim($server));
                    $this->server = $serverparts[0];
                    $this->port   = $serverparts[1];
                } else {
                    $this->server = $server;
                }
            }
        }
    }

    /**
     * fetchData()
     *
     * Try to login to the Vpopmaild server
     *
     * @param  string username
     * @param  string password
     *
     * @return boolean
     */
    function fetchData($username, $password)
    {
        $this->log('Auth_Container_Vpopmaild::fetchData() called.', AUTH_LOG_DEBUG);
        $vpopmaild =& new Net_Vpopmaild();
        // Connect
        try {
            $res = $vpopmaild->connect($this->server, $this->port, $this->method);
        } catch (Net_Vpopmaild_FatalException $e) {
            $this->log('Connection to Vpopmaild server failed.', AUTH_LOG_DEBUG);
            return PEAR::raiseError($e->getMessage(), $e->getCode());
        }
        // Authenticate
        try {
            $result = $vpopmaild->clogin($username, $password);
            $vpopmaild->quit();
        } catch (Net_Vpopmaild_Exception $e) {
            return PEAR::raiseError($e->getMessage(), $e->getCode());
        }
        return $result;
    }
}
?>
