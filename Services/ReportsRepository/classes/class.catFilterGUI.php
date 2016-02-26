<?php
require_once("Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

class catFilterGUI {
	public function addHiddenInputs(ilPropertyFormGUI $form, array $post_values) {
		foreach ($post_values as $key => $value) {
				$hidden = new ilHiddenInputGUI("filter[$key]");
				if(is_array($value)) {
					$hidden->setValue(serialize($value));
				} else {
					$hidden->setValue($value);
				}
				
				$form->addItem($hidden);
			}

		return $form;
	}
}