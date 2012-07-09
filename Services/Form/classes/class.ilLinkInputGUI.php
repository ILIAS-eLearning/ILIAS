<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a external and/or internal link in a property form.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilLinkInputGUI: ilFormPropertyDispatchGUI
* @ilCtrl_Calls ilLinkInputGUI: ilInternalLinkGUI
* 
* @ingroup	ServicesForm
*/
class ilLinkInputGUI extends ilFormPropertyGUI
{
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("link");
	}
	
	/**
	* Execute current command
	*/
	function executeCommand()
	{
		global $ilCtrl, $lng;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case "ilinternallinkgui":
				$lng->loadLanguageModule("content");
				require_once("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");
				$link_gui = new ilInternalLinkGUI("RepositoryItem", 0);
				$link_gui->filterLinkType("RepositoryItem");
				$link_gui->setFilterWhiteList(true);
				$link_gui->setMode("asynch");
			
				$ret = $ilCtrl->forwardCommand($link_gui);
				break;

			default:
				var_dump($cmd);
				//exit();
		}
		
		return $ret;
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

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		switch($a_values[$this->getPostVar()."_mode"])
		{
			case "int":				
				if($a_values[$this->getPostVar()."_ajax_type"] &&
					$a_values[$this->getPostVar()."_ajax_id"])
				{
					$this->setValue($a_values[$this->getPostVar()."_ajax_type"]."|".
						$a_values[$this->getPostVar()."_ajax_id"]);
				}
				break;

			default:
				if($a_values[$this->getPostVar()])
				{
					$this->setValue($a_values[$this->getPostVar()]);
				}
				break;
		}
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		// debugging
		// return false;
		
		if($this->getRequired())
		{
			switch($_POST[$this->getPostVar()."_mode"])
			{
				case "ext":
					if(!$_POST[$this->getPostVar()])
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return false;
					}
					break;
					
				case "int":
					if(!$_POST[$this->getPostVar()."_ajax_type"] ||
						!$_POST[$this->getPostVar()."_ajax_id"])
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return false;
					}					
					break;
					
				default:
					$this->setAlert($lng->txt("msg_input_is_required"));
					return false;
			}	
		}
		
		if($_POST[$this->getPostVar()."_mode"] == "int")
		{
			// overwriting post-data so getInput() will work
			$_POST[$this->getPostVar()] = $_POST[$this->getPostVar()."_ajax_type"]."|".
				$_POST[$this->getPostVar()."_ajax_id"];
		};
	
		return true;
	}

	/**
	* Render item
	*/
	function render()
	{
		global $lng, $ilCtrl;
		
		// external
		$ext = new ilRadioOption($lng->txt("form_link_external"), "ext");
		
		$ti = new ilTextInputGUI("", $this->getPostVar());
		$ti->setMaxLength(200);
		$ti->setSize(50);
		$ext->addSubItem($ti);
		
		
		// internal
						
		$int = new ilRadioOption($lng->txt("form_link_internal"), "int");
									
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->getPostVar());
		$link = array(get_class($this->getParent()), "ilformpropertydispatchgui", get_class($this), "ilinternallinkgui");
		$link = $ilCtrl->getLinkTargetByClass($link, "", false, true, false);
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", "");
				
		$ne = new ilNonEditableValueGUI("", "");				
		$ne->setValue('<a id="'.$this->getPostVar().'_ajax" class="iosEditInternalLinkTrigger" href="'.
			$link.'">&raquo; '.$lng->txt("form_get_link").'</a>');
		$int->addSubItem($ne);
		
		// hidden field for selected value
		$hidden_type = new ilHiddenInputGUI($this->getPostVar()."_ajax_type");
		$hidden_id = new ilHiddenInputGUI($this->getPostVar()."_ajax_id");
		
		// switch
		$mode = new ilRadioGroupInputGUI("", $this->getPostVar()."_mode");
		$mode->addOption($ext);
		$mode->addOption($int);
		
		// value?
		$value = $this->getValue();
		if($value)
		{
			if(strpos($value, "|"))
			{
				$mode->setValue("int");
				
				$value = explode("|", $value);
				$hidden_type->setValue($value[0]);
				$hidden_id->setValue($value[1]);
				
				$ne->setInfo($lng->txt("obj_".$value[0]).": ".
					ilObject::_lookupTitle(ilObject::_lookupObjId($value[1])));
			}
			else
			{
				$mode->setValue("ext");
				
				$ti->setValue($value);
			}
		}
		
		include_once("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");
		
		return $mode->render().
			$hidden_type->getToolbarHTML().
			$hidden_id->getToolbarHTML().
			ilInternalLinkGUI::getInitHTML("");
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}
}
