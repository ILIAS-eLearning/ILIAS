<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/class.ilAuthDecorator.php';
include_once './Services/Authentication/classes/class.ilAuth.php';
include_once 'Auth.php';


/**
 * @classDescription CAS authentication
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesCAS
 */
class ilAuthCAS extends ilAuthDecorator
{
	/**
	 * Constructor
	 * 
	 * @param object ilAuthContainerDecorator
	 * @param array	further options Not used in the moment
	 */
	public function __construct(ilAuthContainerDecorator $container,$a_further_options = array())
	{
		parent::__construct($container);


		$this->appendOption('sessionName',"_authhttp".md5(CLIENT_ID));
		$this->initAuth();
		$this->initCallbacks();
	}
	
	public function initAuth()
	{
		global $PHPCAS_CLIENT;

		$this->setAuthObject(
			new Auth(
				$this->getContainer(),
				$this->getOptions(),
				array($this->getContainer(),'forceAuthentication'),
				true
			));
		if($PHPCAS_CLIENT->isAuthenticated())
		{
			$this->getAuthObject()->username = $PHPCAS_CLIENT->getUser();
		}
	}
}
?>