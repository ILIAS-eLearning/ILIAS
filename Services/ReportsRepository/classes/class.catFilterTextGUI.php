<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilTextInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

class catFilterTextGUI {
	protected $parent;
	protected $filter;
	protected $path;
	protected $post_values;

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

		$input = new ilTextInputGUI($this->filter->label(), $this->path);
		$input->setInfo($this->filter->description());
		$form->addItem($input);

		$post_values = new ilHiddenInputGUI("post_values");
		$post_values->setValue(serialize($this->post_values));
		$form->addItem($post_values);

		return $form->getHTML();
	}
}