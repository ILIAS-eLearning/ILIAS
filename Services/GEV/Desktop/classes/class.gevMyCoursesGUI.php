<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* My Courses GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyCoursesQuicklinksGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyCoursesTableGUI.php");

class gevMyCoursesGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->user = &$ilUser;
	}
	
	public function executeCommand() {
		$qls = new gevMyCoursesQuicklinksGUI();
		$qls_out = $qls->render();
		
		$spacer = new catHSpacerGUI();
		$spacer_out = $spacer->render();
		
		$crss = new gevCoursesTableGUI($this->user->getId(), $this);
		$crss_out = $crss->getHTML();
		
		$this->tpl->setContent( $qls_out
							  . $spacer_out
							  . $crss_out
							  );
	}
}

?>