<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Auth/Container.php';

/**
 * @classDescription Calendar token based authentication
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 *
 * @ingroup ServicesCalendar
 */
class ilAuthContainerCalendarToken extends Auth_Container
{
	protected $current_user_id = 0;

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	function fetchData($username,$password)
	{
		$GLOBALS['ilLog']->write('Fetch data');
		include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';
		$this->current_user_id = ilCalendarAuthenticationToken::lookupUser($_GET['token']);
		
		return $this->current_user_id > 0;
	}
	
	/** 
	 * Called from fetchData after successful login.
	 *
	 * @param string username
	 */
	public function loginObserver($a_username,$a_auth)
	{
		$GLOBALS['ilLog']->write('Called login observer');
		
		$name = ilObjUser::_lookupName($this->current_user_id);
		$a_auth->setAuth($name['login']);
		return true;
	}
}
