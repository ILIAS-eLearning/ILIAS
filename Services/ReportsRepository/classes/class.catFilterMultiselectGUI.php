<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

class catFilterMultiselectGUI {
	protected $parent;
	protected $filter;
	protected $path;

	public function __construct($parent, $filter, $path, array $post_values) {
		$this->parent = $parent;
		$this->filter = $filter;
		$this->path = $path;
		$this->post_values = $post_values;
	}

	public function executeCommand() {

	}

	public function getHTML() {
		$form = new ilPropertyFormGUI();
		$form->setTitle($parent->getTitle());
		$form->addCommandButton("saveFilter", $this->lng->txt("continue"));
		$form->setFormAction($this->ctrl->getFormAction($this->parent));

		$multi_select = new ilMultiSelectInputGUI($this->filter->label(), $this->path);
		$multi_select->setInfo($this->filter->description());
		$multi_select->setOptions($this->filter->options());
		$form->addItem($multi_select);

		$post_values = new ilHiddenInputGUI("post_values");
		$post_values->setValue(serialize($this->post_values));
		$form->addItem($post_values);

		return $form->getHTML();
	}
}