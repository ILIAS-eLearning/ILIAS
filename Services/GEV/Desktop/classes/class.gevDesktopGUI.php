<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Desktop for the Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevDesktopGUI {
	public function __construct() {
		global $ilLng, $ilCtrl, $tpl;
		
		$this->lng = &$ilLng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;

		$this->tpl->getStandardTemplate();
	}
	
	public function executeCommand() {
		$this->tpl->setContent("Hello World!");
		
		$this->tpl->show();
	}
}

?>