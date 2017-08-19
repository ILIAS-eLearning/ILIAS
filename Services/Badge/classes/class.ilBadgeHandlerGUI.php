<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeHandler
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilBadgeHandlerGUI: 
 * @package ServicesBadge
 */
class ilBadgeHandlerGUI
{
	public function executeCommand()
	{
		global $ilCtrl;
		
		if($ilCtrl->isAsynch())
		{
			$cmd = $ilCtrl->getCmd();
			echo $this->$cmd();
			exit();
		}		
	}
	
	protected function render()
	{		
		include_once "Services/Badge/classes/class.ilBadgeRenderer.php";
		$rnd = ilBadgeRenderer::initFromId(trim($_GET["id"]));
		if($rnd)
		{
			return $rnd->renderModal();			
		}				
	}
}
