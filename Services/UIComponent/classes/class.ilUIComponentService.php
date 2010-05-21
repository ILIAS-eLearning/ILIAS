<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilService.php");

/**
 * EventHandling Service.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUIComponent
 */
class ilUIComponentService extends ilService
{

	/**
	 * Constructor: read information on component
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Core modules vs. plugged in modules
	 */
	function isCore()
	{
		return true;
	}

	/**
	 * Get version of service.
	 */
	function getVersion()
	{
		return "-";
	}

}
?>
