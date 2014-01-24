<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomGetPermissionsTask
 *
 * Returns user permissions
 *
 * @author Andreas Korodsz <akordosz@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomGetPermissionsTask extends ilChatroomTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
	}

	/**
	 * Default execute method.
	 *
	 * @param string $requestedMethod
	 */
	public function executeDefault($requestedMethod)
	{
		global $ilUser;

		switch($ilUser->getLogin())
		{
		    case 'root':
			    $kick = $ban = true;
			    break;
		    default:
			    $kick = $ban = false;
		}

		$permissions = array(
		    'kick'  => $kick,
		    'ban'   => $ban,
		);

		echo json_encode($permissions);
		exit;
	}

}

?>