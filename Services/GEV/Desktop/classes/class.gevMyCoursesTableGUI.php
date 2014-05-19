<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CatUIComponents/classes/class.catAccordionTableGUI.php");

class gevCoursesTableGUI extends catAccordionTableGUI {
	public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		
		$this->user_id = $a_user_id;
	}
	
	/*protected function compileCourseData() {
		// TODO: implement this correctly
		
		return 
		array( array("Gewerbliche Sachversicherung", )
			 , 
			);
	}*/
}

?>