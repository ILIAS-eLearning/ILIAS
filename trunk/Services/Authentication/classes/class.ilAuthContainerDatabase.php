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
* Usage note:
* If you use an ilAuthContainerMDB2 object as the container for an Auth object
* you MUST call setEnableObservers(true) on the ilAuthContainerMDB2 object.
* The observers are used to perform actions depending on the success or failure
* of a login attempt.
*
 * @author Werner Randelshofer, Lucerne University of Applied Sciences and Arts, werner.randelshofer@hslu.ch
 * @version $Id$
 *
 * @deprecated Will be replace by ilAuthContainerMDB in Services/Database 
 */
class ilAuthContainerDatabase extends Auth_Container_MDB2
{
	/**
	 * If this variable is set to true, function fetchData calls
	 * function loginObserver on a successful login and function 
	 * failedLoginObserver on a failed login.
	 * 
	 * @var boolean
	 */
	private $isObserversEnabled;
        
	function ilAuthContainerDatabase($dsn)
	{
		$this->Auth_Container_MDB2($dsn);
	}

	function getUser($username)
	{
		$username = ilAuthContainerDatabase::toUsernameWithoutDomain($username);

		// Fetch the data
		return parent::getUser($username);
	}

	function fetchData($username, $password, $isChallengeResponse=false)
	{
		$username = ilAuthContainerDatabase::toUsernameWithoutDomain($username);

		$isSuccessful = parent::fetchData($username, $password, $isChallengeResponse);
		if ($this->isObserversEnabled)
		{
			if ($isSuccessful)
			{
				$this->loginObserver($username);        
			}
			else
			{
				$this->failedLoginObserver();        
			}
		}
		return $isSuccessful;
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

	/** 
	 * Enables/disables the observers of this container.
	 */
	public function setObserversEnabled($boolean) 
	{
	        $this->isObserversEnabled = $boolean;
	}
	
	/** 
	 * Returns true, if the observers of this container are enabled.
	 */
	public function isObserversEnabled() 
	{
		$this->isObserversEnabled;
	}
	
	
	/** 
	 * Called from Auth after successful login.
	 *
	 * @param string username
	 */
	public function loginObserver($a_username)
	{
		global $ilLog;
		$ilLog->write(__METHOD__.': logged in as '.$a_username.
			', remote:'.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'].
			', server:'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT']
		);
	}
	
	/** 
	 * Called from Auth after failed login
	 *
	 * @param string username
	 */
	public function failedLoginObserver()
	{
		global $ilLog;
		$ilLog->write(__METHOD__.': login failed'.
			', remote:'.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'].
			', server:'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT']
		);
	}
}

// END WebDAV: Strip Microsoft Domain Names from logins
?>
