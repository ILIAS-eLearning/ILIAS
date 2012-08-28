<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/Rest/lib/Slim/Slim.php';
include_once './Services/WebServices/Rest/classes/class.ilRestFileStorage.php';

/**
 * Slim rest server
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilRestServer extends Slim
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Init server / add handlers
	 */
	public function init()
	{
		$callback_obj = new ilRestFileStorage();
		
		$this->get('/fileStorage/:name',array($callback_obj,'getFile'));
		$this->post('/fileStorage',array($callback_obj,'createFile'));


		$callback_obj->deleteDeprecated();
	}

}
?>
