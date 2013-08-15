<?php

require_once("./Services/Form/classes/class.ilMultiUserSelectInputGUI.php");
require_once("./Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php");


class ilMultiUserToolbarInputGUI extends ilMultiUserSelectInputGUI implements ilToolbarItem {


	public function __construct($postvar){
		parent::__construct($postvar, $postvar);
	}
	/** @var  string */
	protected $submitLink;

	/**
	 *
	 * Get input item HTML to be inserted into ilToolbarGUI
	 *
	 * @access    public
	 * @return    string
	 *
	 */
	public function getToolbarHTML()
	{
		//TODO refactor into template.
		$html = "<form method='post' class='ilOrguUserPicker' action='".$this->getSubmitLink()."'>";
		$html .= $this->render();
		$html .= $this->getSubmitButtonHTML();
		$html .= "</form>";
		return $html;
	}

	protected function getSubmitButtonHTML(){
		global $lng;
		//TODO refactor into template
		return "<input type='submit' class='submit' value=".$lng->txt("add").">";
	}

	/**
	 * @param string $submitLink
	 */
	public function setSubmitLink($submitLink)
	{
		$this->submitLink = $submitLink;
	}

	/**
	 * @return string
	 */
	public function getSubmitLink()
	{
		return $this->submitLink;
	}
}