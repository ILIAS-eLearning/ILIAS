<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* User selector (for course search gui) for managers,
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catSelectInputGUI.php");

class gevUserSelectorGUI {
	protected $users;
	protected $caption;
	
	public function __construct($a_target_user_id = null) {
		global $lng, $ilCtrl, $ilUser;
		
		$this->lng = &$lng;
		
		if ($a_target_user_id === null) {
			$this->target_user_id = $ilUser->getId();
		}
		else {
			$this->target_user_id = $a_target_user_id;
		}
	
		$this->select = new catSelectInputGUI("", "target_user_id");
	}
	
	// expects array containing dict entries with usr_id, lastname and firstname
	public function setUsers($a_users) {
		$this->users = $a_users;
		return $this;
	}
	
	public function setCaption($a_lng_var) {
		$this->caption = $a_lng_var;
		return $this;
	}
	
	public function setAction($a_action) {
		$this->select->setAction($a_action);
		return $this;
	}
	
	public function render($a_mode = "") {

		
		$options = array();
		foreach ($this->users as $usr) {
			$options[$usr["usr_id"]] = $usr["lastname"].", ".$usr["firstname"];
		}
		$this->select->setOptions($options);
		$this->select->setValue($this->target_user_id);
		
		$tpl = new ilTemplate("tpl.gev_user_selector.html", true, true, "Services/GEV/Desktop");
		$tpl->setVariable("CAPTION", $this->caption ? $this->lng->txt($this->caption) : "");
		$tpl->setVariable("SELECT", $this->select->render());
		
		return $tpl->get();
	}
}

?>