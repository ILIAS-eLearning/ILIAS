<?php
// BEGIN WebDAV: Strip Microsoft Domain Names from logins
require_once 'Auth/Container.php';
require_once 'Auth/Container/MDB2.php';

/**
 * Storage driver for fetching login data from a database. This driver
 * strips leading Microsoft Windows domain names from the user name.
 * 
 * For example: hsw\wrandels hsw/wrandels and wrandels refer all to the
 * login name wrandels.
 *
 * This storage driver can use all databases which are supported
 * by the PEAR DB abstraction layer to fetch login data.
 *
 * @author Werner Randelshofer, Lucerne University of Applied Sciences and Arts, werner.randelshofer@hslu.ch
 * @version $Id$
 */
class ilAuthContainerMDB2 extends Auth_Container_MDB2
{
    function ilAuthContainerMDB2($dsn)
    {
    	$this->Auth_Container_MDB2($dsn);
    }
    
    function getUser($username)
    {
		$username = ilAuthContainerMDB2::toUsernameWithoutDomain($username);

		// Fetch the data
		return parent::getUser($username);
    }

    function fetchData($username, $password)
    {
		$username = ilAuthContainerMDB2::toUsernameWithoutDomain($username);

		// Fetch the data
		return parent::fetchData($username, $password);
    }

	/**
	 * Static function removes Microsoft domain name from username
	 */
	static function toUsernameWithoutDomain($username)
	{
		// Remove all characters including the last slash or the last backslash
		// in the username
		$pos = strrpos($username, '/');
		$pos2 = strrpos($username, '\\');
		if ($pos === false || $pos < $pos2) 
		{
			$pos = $pos2;
		}
		if ($pos !== false)
		{
			$username = substr($username, $pos + 1);
		}
		return $username;
	}
}

// END WebDAV: Strip Microsoft Domain Names from logins
?>
