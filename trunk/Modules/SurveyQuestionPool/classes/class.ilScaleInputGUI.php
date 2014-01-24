<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilTextInputGUI.php");

/**
* This class represents a scale property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilScaleInputGUI extends ilTextInputGUI
{
	private $scale;
	private $scale_disabled;
	private $scale_stylecss;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->scale_disabled = true;
		$this->scale_stylecss = 'text-align: right;';
	}

	/**
	* Set scale inline style
	*
	* @param	string	$a_value	Value
	*/
	function setScaleInlineStyle($a_value)
	{
		$this->scale_stylecss = $a_value;
	}

	/**
	* Get scale inline style
	*
	* @param	string	Value
	*/
	function getScaleInlineStyle()
	{
		return $this->scale_stylecss;
	}
	
	/**
	* Set scale disabled
	*
	* @param	boolean	$a_value	Value
	*/
	function setScaleDisabled($a_value)
	{
		$this->scale_disabled = $a_value;
	}

	/**
	* Get scale disabled
	*
	* @return	boolean	Value
	*/
	function getScaleDisabled()
	{
		return $this->scale_disabled;
	}

	/**
	* Set scale
	*
	* @param	double	$a_value	Value
	*/
	function setScale($a_value)
	{
		$this->scale = $a_value;
	}

	/**
	* Get scale
	*
	* @return	double	scale
	*/
	function getScale()
	{
		return $this->scale;
	}


	/**
	* Render item
	*/
	protected function render($a_mode = "")
	{
		$tpl = new ilTemplate("tpl.prop.scaleinput.html", true, true, "Modules/SurveyQuestionPool");
		if (strlen($this->getValue()))
		{
			$tpl->setCurrentBlock("prop_text_propval");
			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
			$tpl->parseCurrentBlock();
		}
		if (strlen($this->getScale()))
		{
			$tpl->setCurrentBlock("prop_text_scale");
			$tpl->setVariable("SCALE_VALUE", ilUtil::prepareFormOutput($this->getScale()));
			$tpl->parseCurrentBlock();
		}
		if (strlen($this->getInlineStyle()))
		{
			$tpl->setCurrentBlock("stylecss");
			$tpl->setVariable("CSS_STYLE", ilUtil::prepareFormOutput($this->getInlineStyle()));
			$tpl->parseCurrentBlock();
		}
		if (strlen($this->getScaleInlineStyle()))
		{
			$tpl->setCurrentBlock("scale_stylecss");
			$tpl->setVariable("CSS_STYLE", ilUtil::prepareFormOutput($this->getScaleInlineStyle()));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ID", $this->getFieldId());
		$tpl->setVariable("SIZE", $this->getSize());
		$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
		if (strlen($this->getSuffix())) $tpl->setVariable("INPUT_SUFFIX", $this->getSuffix());
		
		if ($this->getDisabled())
		{
			$tpl->setVariable("DISABLED", " disabled=\"disabled\"");
		}
		if ($this->getScaleDisabled())
		{
			$tpl->setVariable("SCALE_DISABLED", " disabled=\"disabled\"");
		}
		$tpl->setVariable("POST_VAR", $this->getPostVar());

		return $tpl->get();
	}
}
?>