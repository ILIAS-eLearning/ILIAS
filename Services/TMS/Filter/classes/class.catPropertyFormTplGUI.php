<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Property with switchable template and replaceable variables
*
* $items and $buttons in the parent class need to be made protected in order
* to let this class work correctly.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/TMS/Filter/classes/class.catPropertyFormGUI.php");

class catPropertyFormTplGUI extends catPropertyFormGUI {
	protected $tpl_filename = "tpl.property_form.html";
	protected $tpl_location = "Services/Form";
	protected $items = [];
	protected $buttons = [];

	public function __construct() {
		global $lng, $tpl, $ilSetting;
		$this->gTpl = $tpl;
		$this->glng = $lng;
		$this->gSettings = $ilSetting;
		parent::__construct();
	}

	public function setTemplate($a_name, $a_location) {
		$this->tpl_filename = $a_name;
		$this->tpl_location = $a_location;

		$this->tpl = new ilTemplate($this->getTemplateFilename(), true, true, $this->getTemplateLocation());
	}

	public function getTemplate() {
		return $this->tpl;
	}
	
	/**
	* Get Content.
	*
	* "Reimplementation" from base, just to slip in another template to 
	* be used.
	*/
	function getContent()
	{
		
	
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initEvent();
		ilYuiUtil::initDom();
		ilYuiUtil::initAnimation();

		$this->gTpl->addJavaScript("./Services/JavaScript/js/Basic.js");
		$this->gTpl->addJavaScript("Services/Form/js/Form.js");

		// check if form has not title and first item is a section header
		// -> use section header for title and remove section header
		// -> command buttons are presented on top
		$fi = $this->items[0];
		if ($this->getMode() == "std" &&
			$this->getTitle() == "" &&
			is_object($fi) && $fi->getType() == "section_header"
			)
		{
			$this->setTitle($fi->getTitle());
			unset($this->items[0]);
		}
		
		
		// title icon
		if ($this->getTitleIcon() != "" && @is_file($this->getTitleIcon()))
		{
			$this->tpl->setCurrentBlock("title_icon");
			$this->tpl->setVariable("IMG_ICON", $this->getTitleIcon());
			$this->tpl->parseCurrentBlock();
		}

		// title
		if ($this->getTitle() != "")
		{
			// commands on top
			if (count($this->buttons) > 0 && $this->getShowTopButtons())
			{
				// command buttons
				foreach($this->buttons as $button)
				{
					$this->tpl->setCurrentBlock("cmd2");
					$this->tpl->setVariable("CMD", $button["cmd"]);
					$this->tpl->setVariable("CMD_TXT", $button["text"]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("commands2");
				$this->tpl->parseCurrentBlock();
			}

			if (is_object($this->gSettings))
			{
				if ($this->gSettings->get('char_selector_availability') > 0)
				{
					require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
					if (ilCharSelectorGUI::_isAllowed())
					{
						$char_selector = ilCharSelectorGUI::_getCurrentGUI();
						if ($char_selector->getConfig()->getAvailability() == ilCharSelectorConfig::ENABLED)
						{
							$char_selector->addToPage();
							$this->tpl->TouchBlock('char_selector');
						}
					}
				}
			}
			
			$this->tpl->setCurrentBlock("header");
			$this->tpl->setVariable("TXT_TITLE", $this->getTitle());
			$this->tpl->setVariable("LABEL", $this->getTopAnchor());
			$this->tpl->setVariable("TXT_DESCRIPTION", $this->getDescription());
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->touchBlock("item");
		
		// properties
		$this->required_text = false;
		foreach($this->items as $item)
		{
			if ($item->getType() != "hidden")
			{
				$this->insertItem($item);
			}
		}				

		// required
		if ($this->required_text && $this->getMode() == "std")
		{
			$this->tpl->setCurrentBlock("required_text");
			$this->tpl->setVariable("TXT_REQUIRED", $this->glng->txt("required_field"));
			$this->tpl->parseCurrentBlock();			
		}
		
		// command buttons
		foreach($this->buttons as $button)
		{
			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("CMD", $button["cmd"]);
			$this->tpl->setVariable("CMD_TXT", $button["text"]);
			$this->tpl->parseCurrentBlock();
		}
		
		// try to keep uploads even if checking input fails
		if($this->getMultipart())
		{
			$hash = $_POST["ilfilehash"];
			if(!$hash)
			{
				$hash = md5(uniqid(mt_rand(), true));
			}		
			$fhash = new ilHiddenInputGUI("ilfilehash");
			$fhash->setValue($hash);
			$this->addItem($fhash);
		}
		
		// hidden properties
		$hidden_fields = false;
		foreach($this->items as $item)
		{
			if ($item->getType() == "hidden")
			{
				$item->insert($this->tpl);
				$hidden_fields = true;
			}
		}
		
		if ($this->required_text || count($this->buttons) > 0 || $hidden_fields)
		{
			$this->tpl->setCurrentBlock("commands");
			$this->tpl->parseCurrentBlock();
		}

		
		if ($this->getMode() == "subform")
		{
			$this->tpl->touchBlock("sub_table");
		}
		else
		{
			$this->tpl->touchBlock("std_table");
			$this->tpl->setVariable('STD_TABLE_WIDTH',$this->getTableWidth());
		}
		
		return $this->tpl->get();
	}
}