<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/Contexts/classes/class.ilUserActionContext.php");

/**
 * Awareness context for user actions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilAwarenessUserActionContext extends ilUserActionContext
{
	/**
	 * @inheritdoc
	 */
	function getComponentId()
	{
		return "awrn";
	}

	/**
	 * @inheritdoc
	 */
	function getContextId()
	{
		return "toplist";
	}

}

?>