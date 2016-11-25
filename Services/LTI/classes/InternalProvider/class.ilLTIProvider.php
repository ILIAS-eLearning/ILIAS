<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use IMSGlobal\LTI\ToolProvider;

/**
 * LTI provider for LTI launch 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilLTIProvider extends ToolProvider\ToolProvider
{
	public function onLaunch()
	{
		return parent::onLaunch();
	}
	
	public function onError()
	{
		return parent::onError();
	}
	
}
?>