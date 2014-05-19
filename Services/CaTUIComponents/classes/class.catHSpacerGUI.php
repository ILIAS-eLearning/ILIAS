<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Horizontal Spacer for the CaT-GUI.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/UICore/classes/class.ilTemplate.php");

class catHSpacerGUI {
	public function render() {
		$tpl = new ilTemplate("tpl.cat_hspacer.html", true, true, "Services/CaTUIComponents");
		$tpl->touchBlock("spacer");
		return $tpl->get();
	}
}

?>