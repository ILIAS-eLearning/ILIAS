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
class ilAuthCAS extends Auth
{
	/**
	 * Constructor
	 * 
	 * @param object $container
	 * @param array	further options Not used in the moment
	 */
	public function __construct($a_container,$a_further_options = array())
	{
		global $PHPCAS_CLIENT;

		parent::__construct(
			$a_container,
			$a_further_options,
			array($a_container,'forceAuthentication'),
			true
		);
		$this->setSessionName("_authhttp".md5(CLIENT_ID));
		$this->initAuth();
		
		if(is_object($PHPCAS_CLIENT) and $PHPCAS_CLIENT->isAuthenticated())
		{
			$this->username = $PHPCAS_CLIENT->getUser();
		}
		
	}
}
?>