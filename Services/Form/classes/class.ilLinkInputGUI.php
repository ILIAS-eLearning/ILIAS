<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	const BOTH = "both";
	const INT = "int";
	const EXT = "ext";
	protected $allowed_link_types = self::BOTH;
	protected $int_link_default_type = "RepositoryItem";
	protected $int_link_default_obj = 0;
	protected $int_link_filter_types = array("RepositoryItem");
	
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
	 * Set allowed link types (BOTH, INT, EXT)
	 *
	 * @param string $a_val self::BOTH|self::INT|self::EXT	
	 */
	function setAllowedLinkTypes($a_val)
	{
		$this->allowed_link_types = $a_val;
	}
	
	/**
	 * Get allowed link types (BOTH, INT, EXT)
	 *
	 * @return string self::BOTH|self::INT|self::EXT
	 */
	function getAllowedLinkTypes()
	{
		return $this->allowed_link_types;
	}
	
	/**
	 * Set internal link default
	 *
	 * @param string $a_type link type
	 * @param int $a_obj object id
	 */
	function setInternalLinkDefault($a_type, $a_obj = 0)
	{
		$this->int_link_default_type = $a_type;
		$this->int_link_default_obj = $a_obj;
	}
	
	/**
	 * Set internal link filter types
	 *
	 * @param array $a_val filter types	
	 */
	function setInternalLinkFilterTypes(array $a_val)
	{
		$this->int_link_filter_types = $a_val;
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
				$link_gui = new ilInternalLinkGUI($this->int_link_default_type,
					$this->int_link_default_obj);
				foreach ($this->int_link_filter_types as $t)
				{
					$link_gui->filterLinkType($t);
				}
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
					$val = $a_values[$this->getPostVar()."_ajax_type"]."|".
						$a_values[$this->getPostVar()."_ajax_id"];
					if ($a_values[$this->getPostVar()."_ajax_target"] != "")
					{
						$val.= "|".$a_values[$this->getPostVar()."_ajax_target"];
					}
					$this->setValue($val);
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
			$val = $_POST[$this->getPostVar()."_ajax_type"]."|".
				$_POST[$this->getPostVar()."_ajax_id"];
			if ($_POST[$this->getPostVar()."_ajax_target"] != "")
			{
				$val.= "|".$_POST[$this->getPostVar()."_ajax_target"];
			}

			$_POST[$this->getPostVar()] = $val;
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
		$ti = new ilTextInputGUI("", $this->getPostVar());
		$ti->setMaxLength(200);
		$ti->setSize(50);
		
		if ($this->getAllowedLinkTypes() == self::BOTH)
		{
			$ext = new ilRadioOption($lng->txt("form_link_external"), "ext");
			$ext->addSubItem($ti);
		}
		
		
		// internal				
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->getPostVar());
		$link = array(get_class($this->getParent()), "ilformpropertydispatchgui", get_class($this), "ilinternallinkgui");
		$link = $ilCtrl->getLinkTargetByClass($link, "", false, true, false);
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", "");
				
		$ne = new ilNonEditableValueGUI("", $this->getPostVar()."_val", true);
		$no_disp_class = (strpos($this->getValue(), "|"))
			? ""
			: " ilNoDisplay";
		$ne->setValue('<a id="'.$this->getPostVar().'_ajax" class="iosEditInternalLinkTrigger" href="'.
			$link.'">&raquo; '.$lng->txt("form_get_link").'</a>
			<div class="'.$no_disp_class.'" id="'.$this->getPostVar().'_rem">'.
			'<a class="ilLinkInputRemove" href="#">&raquo; '.$lng->txt("remove").'</a></div>');
		$ne->setInfo("&nbsp;");

		if ($this->getAllowedLinkTypes() == self::BOTH)
		{
			$int = new ilRadioOption($lng->txt("form_link_internal"), "int");
			$int->addSubItem($ne);
		}
		
		// hidden field for selected value
		$hidden_type = new ilHiddenInputGUI($this->getPostVar()."_ajax_type");
		$hidden_id = new ilHiddenInputGUI($this->getPostVar()."_ajax_id");
		$hidden_target = new ilHiddenInputGUI($this->getPostVar()."_ajax_target");
		
		// switch
		if ($this->getAllowedLinkTypes() == self::BOTH)
		{
			$mode = new ilRadioGroupInputGUI("", $this->getPostVar()."_mode");
			$mode->addOption($ext);
			$mode->addOption($int);
		}
		else
		{
			$mode = new ilHiddenInputGUI($this->getPostVar()."_mode");
			if ($this->getAllowedLinkTypes() == self::INT)
			{
				$mode->setValue("int");
			}
			else
			{
				$mode->setValue("ext");
			}
		}

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
				$hidden_target->setValue($value[2]);

				switch($value[0])
				{
					case "media":
						$ne->setInfo($lng->txt("obj_mob").": ".
							ilObject::_lookupTitle($value[1]));
						break;
					
					case "page":
						include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
						$ne->setInfo($lng->txt("obj_pg").": ".
							ilLMPageObject::_lookupTitle($value[1]));
						break;
					
					case "term":
						include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
						$ne->setInfo($lng->txt("term").": ".
							ilGlossaryTerm::_lookGlossaryTerm($value[1]));
						break;
					
					default:
						$ne->setInfo($lng->txt("obj_".$value[0]).": ".
							ilObject::_lookupTitle(ilObject::_lookupObjId($value[1])));
						break;
				}
			}
			else
			{
				$mode->setValue("ext");
				
				$ti->setValue($value);
			}
		}
		
		include_once("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");

		if ($this->getAllowedLinkTypes() == self::BOTH)
		{
			$html = $mode->render();
		}
		else
		{
			$html = $mode->getToolbarHTML();
		}

		if ($this->getAllowedLinkTypes() == self::EXT)
		{
			$html.= $ti->getToolbarHTML();
		}
		else
		{
			if ($this->getAllowedLinkTypes() == self::INT)
			{
				$html.= $ne->render().'<div class="ilFormInfo">'.$ne->getInfo().'</div>';
			}
			$html.= $hidden_type->getToolbarHTML().
				$hidden_id->getToolbarHTML().
				$hidden_target->getToolbarHTML().
				ilInternalLinkGUI::getInitHTML("");
		}
		return $html;
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
