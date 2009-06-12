<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/class.ilAuthContainerDecorator.php';
include_once 'Auth/Container.php';


/**
 * @classDescription CAS authentication
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesCAS
 */
class ilAuthContainerCAS extends ilAuthContainerDecorator
{
	protected $server_version = null;
	protected $server_hostname = null;
	protected $server_port = null;
	protected $server_uri = null;


    /**
     * @see ilAuthContainerDecorator::__construct()
     */
    public function __construct()
	{
		parent::__construct();
		
		$this->initCAS();
		$this->initContainer();
    }	


    /**
     * @see ilAuthContainerDecorator::initContainer()
     */
    protected function initContainer()
    {
		$this->setContainer(
			new Auth_Container()
		);
    }
	
	/**
	 * Force CAS authentication
	 * @return 
	 * @param object $username
	 * @param object $status
	 * @param object $auth
	 */
	public function forceAuthentication($username,$status,$auth)
	{
		global $PHPCAS_CLIENT,$ilLog;
		
		if(!$PHPCAS_CLIENT->isAuthenticated())
		{
			$PHPCAS_CLIENT->forceAuthentication();
		}
	}
	
	/**
	 * 
	 * @return bool 
	 * @param string $a_username
	 * @param string $a_password
	 * @param bool $isChallengeResponse[optional]
	 */
	public function fetchData($a_username,$a_password,$isChallengeResponse = false)
	{
		global $PHPCAS_CLIENT,$ilLog;
		
		$ilLog->write(__METHOD__.': Fetch Data called');
		return $PHPCAS_CLIENT->isAuthenticated();
	}
	
	protected function initCAS()
	{
		global $ilSetting;
		
		include_once("./Services/CAS/phpcas/source/CAS/CAS.php");

		$this->server_version = CAS_VERSION_2_0;
		$this->server_hostname = $ilSetting->get('cas_server');
		$this->server_port = (int) $ilSetting->get('cas_port');
		$this->server_uri = $ilSetting->get('cas_uri');
		
		phpCAS::setDebug();
		phpCAS::client(
			$this->server_version,
			$this->server_hostname,
			$this->server_port,
			$this->server_uri
		);
	}
	
}
?>