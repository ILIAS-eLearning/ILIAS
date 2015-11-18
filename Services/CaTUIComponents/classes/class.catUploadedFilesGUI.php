<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';

/**
* This class represents a gui to display uploaded files with delete button
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class catUploadedFilesGUI extends ilSubEnabledFormPropertyGUI {
	/**
	* Constructor
	*
	* @param
	*/
	function __construct($a_title = "", $a_id = "", $a_disable_escaping = false)
	{
		parent::__construct($a_title, $a_id);
		$this->setTitle($a_title);
		$this->setType("non_editable_value");
		$this->disable_escaping = (bool)$a_disable_escaping;
	}
	
	function checkInput()
	{
		return $this->checkSubItemsInput();
	}

	/**
	* Set Type.
	*
	* @param	string	$a_type	Type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get Type.
	*
	* @return	string	Type
	*/
	function getType()
	{
		return $this->type;
	}
	
	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Information Text.
	*
	* @param	string	$a_info	Information Text
	*/
	function setInfo($a_info)
	{
		$this->info = $a_info;
	}

	/**
	* Get Information Text.
	*
	* @return	string	Information Text
	*/
	function getInfo()
	{
		return $this->info;
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}

	public function setBtnDescription($description) {
		$this->btn_description = $description;
	}

	protected function getBtnDescription() {
		return $this->btn_description;
	}

	public function setBtnValue($value) {
		$this->btn_value = $value;
	}

	protected function getBtnValue() {
		return $this->btn_value;
	}

	public function showBtn($show) {
		$this->show_btn = $show;
	}

	/**
	* render
	*/
	function render()
	{
		$tpl = new ilTemplate("tpl.uploaded_file.html", true, true, "Services/CaTUIComponents");
		if ($this->getPostVar() != "")
		{
			$postvar = $this->getPostVar();
			$tpl->setCurrentBlock("file");
			$tpl->setVariable('POST_VAR', $postvar);
			$tpl->setVariable("FILE_VALUE", $this->getValue());
			if($this->show_btn) {
				$tpl->setVariable('BTN_VALUE', $this->getBtnValue());
				$tpl->setVariable('BTN_DESCRIPTION', $this->getBtnDescription());
			}
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	*/
	function insert(&$a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		if ($this->getPostVar() && isset($a_values[$this->getPostVar()]))
		{
			$this->setValue($a_values[$this->getPostVar()]);
		}
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}
}