<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFormGUI.php");

// please do not add any more includes here if things are not really
// highly re-used
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
include_once("./Services/Form/classes/class.ilCustomInputGUI.php");
include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
include_once("./Services/Form/classes/class.ilFileInputGUI.php");
include_once("./Services/Form/classes/class.ilImageFileInputGUI.php");
include_once('./Services/Form/classes/class.ilFlashFileInputGUI.php');
include_once("./Services/Form/classes/class.ilLocationInputGUI.php");
include_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
include_once("./Services/Form/classes/class.ilCheckboxGroupInputGUI.php");
include_once("./Services/Form/classes/class.ilFormSectionHeaderGUI.php");
include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
include_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
include_once("./Services/Form/classes/class.ilTextInputGUI.php");
include_once("./Services/Form/classes/class.ilDurationInputGUI.php");
include_once("./Services/Form/classes/class.ilFeedUrlInputGUI.php");
include_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");
include_once("./Services/Form/classes/class.ilRegExpInputGUI.php");
include_once('./Services/Form/classes/class.ilColorPickerInputGUI.php');
include_once('./Services/Form/classes/class.ilPasswordInputGUI.php');
include_once('./Services/Form/classes/class.ilUserLoginInputGUI.php');
include_once('./Services/Form/classes/class.ilEMailInputGUI.php');
include_once('./Services/Form/classes/class.ilHiddenInputGUI.php');
include_once('./Services/Form/classes/class.ilNumberInputGUI.php');
include_once('./Services/Form/classes/class.ilCSSRectInputGUI.php');
include_once('./Services/Form/classes/class.ilTextWizardInputGUI.php');
include_once './Services/Form/classes/class.ilFileWizardInputGUI.php';
include_once './Services/Form/classes/class.ilFormulaInputGUI.php';
include_once './Services/Form/classes/class.ilBirthdayInputGUI.php';

/**
* This class represents a property form user interface
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ilCtrl_Calls ilPropertyFormGUI: ilFormPropertyDispatchGUI
* @ingroup	ServicesForm
*/
class ilPropertyFormGUI extends ilFormGUI
{
	private $buttons = array();
	private $items = array();
	protected $mode = "std";
	protected $check_input_called = false;
	protected $disable_standard_message = false;
	protected $top_anchor = "il_form_top";
	protected $titleicon = false;
	protected $description = "";
	protected $tbl_width = false;
	protected $show_top_buttons = true;
	protected $reloaded_files;
	protected $hide_labels = false;
	
	/**
	* Constructor
	*
	* @param
	*/
	function ilPropertyFormGUI()
	{
		global $lng;
		
		$lng->loadLanguageModule("form");
		parent::ilFormGUI();

		// avoid double submission
		$this->setPreventDoubleSubmission(true);

		// do it as early as possible
		$this->rebuildUploadedFiles();
	}

	/**
	* Execute command.
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
			
		switch($next_class)
		{
			case 'ilformpropertydispatchgui':
				$ilCtrl->saveParameter($this, 'postvar');		
				include_once './Services/Form/classes/class.ilFormPropertyDispatchGUI.php';
				$form_prop_dispatch = new ilFormPropertyDispatchGUI();
				$item = $this->getItemByPostVar($_REQUEST["postvar"]);
				$form_prop_dispatch->setItem($item);
				return $ilCtrl->forwardCommand($form_prop_dispatch);
				break;

		}
		return false;
	}

	/**
	 * Set table width
	 *
	 * @access public
	 * @param string table width
	 * 
	 */
	final public function setTableWidth($a_width)
	{
	 	$this->tbl_width = $a_width;
	}
	
	/**
	 * get table width
	 *
	 * @access public
	 * 
	 */
	final public function getTableWidth()
	{
	 	return $this->tbl_width;
	}

	/**
	* Set Mode ('std', 'subform').
	*
	* @param	string	$a_mode	Mode ('std', 'subform')
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* Get Mode ('std', 'subform').
	*
	* @return	string	Mode ('std', 'subform')
	*/
	function getMode()
	{
		return $this->mode;
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
	* Set TitleIcon.
	*
	* @param	string	$a_titleicon	TitleIcon
	*/
	function setTitleIcon($a_titleicon)
	{
		$this->titleicon = $a_titleicon;
	}

	/**
	* Get TitleIcon.
	*
	* @return	string	TitleIcon
	*/
	function getTitleIcon()
	{
		return $this->titleicon;
	}

	/**
	* Set description
	*
	* @param	string	description
	*/
	function setDescription($a_val)
	{
		$this->description = $a_val;
	}
	
	/**
	* Get description
	*
	* @return	string	description
	*/
	function getDescription()
	{
		return $this->description;
	}
	
	/**
	* Set top anchor
	*
	* @param	string	top anchor
	*/
	function setTopAnchor($a_val)
	{
		$this->top_anchor = $a_val;
	}
	
	/**
	* Get top anchor
	*
	* @return	string	top anchor
	*/
	function getTopAnchor()
	{
		return $this->top_anchor;
	}

	/**
	 * Get show top buttons
	 */
	public function setShowTopButtons($a_val)
	{
		$this->show_top_buttons = $a_val;
	}

	/**
	 * Set show top buttons
	 */
	public function getShowTopButtons()
	{
		return $this->show_top_buttons;
	}
	
	/**
	* Add Item (Property, SectionHeader).
	*
	* @param	object	$a_property		Item object
	*/
	function addItem($a_item)
	{
		$a_item->setParentForm($this);
		return $this->items[] = $a_item;
	}

	/**
	* Remove Item.
	*
	* @param	string	$a_postvar		Post Var
	*/
	function removeItemByPostVar($a_post_var)
	{
		foreach ($this->items as $key => $item)
		{
			if (method_exists($item, "getPostVar") && $item->getPostVar() == $a_post_var)
			{
				unset($this->items[$key]);
			}
		}
	}

	/**
	* Get Item by POST variable.
	*
	* @param	string	$a_postvar		Post Var
	*/
	function getItemByPostVar($a_post_var)
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getType() != "section_header")
			{
				//if ($item->getPostVar() == $a_post_var)
				$ret = $item->getItemByPostVar($a_post_var);
				if (is_object($ret))
				{
					return $ret;
				}
			}
		}
		
		return false;
	}

	/**
	* Set Items
	*
	* @param	array	$a_items	array of item objects
	*/
	function setItems($a_items)
	{
		$this->items = $a_items;
	}

	/**
	* Get Items
	*
	* @return	array	array of item objects
	*/
	function getItems()
	{
		return $this->items;
	}
	
	/**
	 * returns a flat array of all input items including
	 * the possibly existing subitems recursively
	 * 
	 * @return array
	 */
	public function getInputItemsRecursive()
	{
		$inputItems = array();
		
		foreach($this->items as $item)
		{
			if( $item->getType() == 'section_header' )
			{
				continue;
			}
			
			$inputItems[] = $item;
			
			if( $item instanceof ilSubEnabledFormPropertyGUI )
			{				
				$inputItems = array_merge( $inputItems, $item->getSubInputItemsRecursive() );
			}
		}
		
		return $inputItems;
	}

	/**
	* Set disable standard message
	*
	* @param	boolean		disable standard message
	*/
	function setDisableStandardMessage($a_val)
	{
		$this->disable_standard_message = $a_val;
	}
	
	/**
	* Get disable standard message
	*
	* @return	boolean		disable standard message
	*/
	function getDisableStandardMessage()
	{
		return $this->disable_standard_message;
	}
	
	/**
	* Get a value indicating whether the labels should be hidden or not.
	*
	* @return	boolean		true, to hide the labels; otherwise, false.
	*/
	function getHideLabels()
	{
		return $this->hide_labels;	
	}
	
	/**
	* Set a value indicating whether the labels should be hidden or not.
	*
	* @param	boolean	$a_value	Indicates whether the labels should be hidden.
	*/
	function setHideLabels($a_value = true)
	{
		$this->hide_labels = $a_value;
	}
	
	/**
	* Set form values from an array
	*
	* @param	array	$a_values	Value array (key is post variable name, value is value)
	*/
	function setValuesByArray($a_values, $a_restrict_to_value_keys = false)
	{
		foreach($this->items as $item)
		{			
			if(!($a_restrict_to_value_keys) || 
				in_array($item->getPostVar(), array_keys($a_values)))
			{			
				$item->setValueByArray($a_values);
			}			
		}
	}

	/**
	* Set form values from POST values
	*
	*/
	function setValuesByPost()
	{
	    foreach($this->items as $item)
		{
			$item->setValueByArray($_POST);
		}
	}
	
	/**
	* Check Post Input. This method also strips slashes and html from
	* input and sets the alert texts for the items, if the input was not ok.
	*
	* @return	boolean		ok true/false
	*/
	function checkInput()
	{
		global $lng;
		
		if ($this->check_input_called)
		{
			die ("Error: ilPropertyFormGUI->checkInput() called twice.");
		}
		
		$ok = true;
		foreach($this->items as $item)
		{
			$item_ok = $item->checkInput();
			if(!$item_ok)
			{
				$ok = false;
			}
		}
		
		// check if POST is missint completely (if post_max_size exceeded)
		if (count($this->items) > 0 && !is_array($_POST))
		{
			$ok = false;
		}
		
		$this->check_input_called = true;
		
		
		
		// try to keep uploads for another try
		if(!$ok && $_POST["ilfilehash"] && sizeof($_FILES))
		{			
			$hash = $_POST["ilfilehash"];

			foreach($_FILES as $field => $data)
			{
				// we support up to 2 nesting levels (see test/assesment)				
				if(is_array($data["tmp_name"]))
				{
					foreach($data["tmp_name"] as $idx => $upload)
					{
						if(is_array($upload))
						{
							foreach($upload as $idx2 => $file)
							{
								if($file && is_uploaded_file($file))
								{
									$file_name = $data["name"][$idx][$idx2];
									$file_type = $data["type"][$idx][$idx2];
									$this->keepFileUpload($hash, $field, $file, $file_name, $file_type, $idx, $idx2);
								}
							}
						}
						else if($upload && is_uploaded_file($upload))
						{
							$file_name = $data["name"][$idx];
							$file_type = $data["type"][$idx];
							$this->keepFileUpload($hash, $field, $upload, $file_name, $file_type, $idx);
						}
					}
				}				
				else
				{
					$this->keepFileUpload($hash, $field, $data["tmp_name"], $data["name"], $data["type"]);
				}
			}
		}
		
		
		if (!$ok && !$this->getDisableStandardMessage())
		{
			ilUtil::sendFailure($lng->txt("form_input_not_valid"));
		}
		return $ok;
	}
	
	/**
	 * 
	 * Returns the value of a HTTP-POST variable, identified by the passed id 
	 * 
	 * @param	string	The key used for value determination
	 * @param	boolean	A flag whether the form input has to be validated before calling this method
	 * @return	string	The value of a HTTP-POST variable, identified by the passed id 
	 * @access	public
	 * 
	 */
	public function getInput($a_post_var, $ensureValidation = true)
	{
		// this check ensures, that checkInput has been called (incl. stripSlashes())
		if (!$this->check_input_called && $ensureValidation)
		{
			die ("Error: ilPropertyFormGUI->getInput() called without calling checkInput() first.");
		}
		
		return $_POST[$a_post_var];
	}
	
	/**
	* Add a custom property.
	*
	* @param	string		Title
	* @param	string		HTML.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addCustomProperty($a_title, $a_html, $a_info = "",
		$a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "custom",
			"title" => $a_title,
			"html" => $a_html,
			"info" => $a_info);
	}

	/**
	* Add Command button
	*
	* @param	string	Command
	* @param	string	Text
	*/
	function addCommandButton($a_cmd, $a_text)
	{
		$this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text);
	}

	/**
	* Remove all command buttons
	*/
	function clearCommandButtons()
	{
		$this->buttons = array();
	}

	/**
	* Get Content.
	*/
	function getContent()
	{
		global $lng, $tpl, $ilUser, $ilSetting;
	
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initEvent();
		ilYuiUtil::initDom();
		ilYuiUtil::initAnimation();

		$tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
		$tpl->addJavaScript("Services/Form/js/Form.js");

		$this->tpl = new ilTemplate("tpl.property_form.html", true, true, "Services/Form");

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
			if (count($this->buttons) > 0 && $this->getShowTopButtons() && count($this->items) > 2)
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

			if (is_object($ilSetting))
			{
				if ($ilSetting->get('char_selector_availability') > 0)
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
			$this->tpl->setVariable("TXT_REQUIRED", $lng->txt("required_field"));
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

	function insertItem($item, $a_sub_item = false)
	{
		global $tpl, $lng;
			
		
		$cfg = array();
		
		//if(method_exists($item, "getMulti") && $item->getMulti())
		if ($item instanceof ilMultiValuesItem && $item->getMulti())
		{
			$tpl->addJavascript("./Services/Form/js/ServiceFormMulti.js");
			
			$this->tpl->setCurrentBlock("multi_in");
			$this->tpl->setVariable("ID", $item->getFieldId());
			$this->tpl->parseCurrentBlock();

			$this->tpl->touchBlock("multi_out");

						
			// add hidden item to enable preset multi items
			// not used yet, should replace hidden field stuff
			$multi_values = $item->getMultiValues();
			if(is_array($multi_values) && sizeof($multi_values) > 1)
			{
				$multi_value = new ilHiddenInputGUI("ilMultiValues~".$item->getPostVar());
				$multi_value->setValue(implode("~", $multi_values));
				$this->addItem($multi_value);				
			}
			$cfg["multi_values"] = $multi_values;
		}		
		
		$item->insert($this->tpl);

		if ($item->getType() == "file" || $item->getType() == "image_file")
		{
			$this->setMultipart(true);
		}

		if ($item->getType() != "section_header")
		{
			$cfg["id"] = $item->getFieldId();
			
			// info text
			if ($item->getInfo() != "")
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("PROPERTY_DESCRIPTION",
					$item->getInfo());
				$this->tpl->parseCurrentBlock();
			}

			if ($this->getMode() == "subform")
			{
				// required
				if ($item->getType() != "non_editable_value")
				{
					if ($item->getRequired())
					{
						$this->tpl->touchBlock("sub_required");
						$this->required_text = true;
					}
				}
				
				// hidden title (for accessibility, e.g. file upload)
				if ($item->getHiddenTitle() != "")
				{
					$this->tpl->setCurrentBlock("sub_hid_title");
					$this->tpl->setVariable("SPHID_TITLE",
						$item->getHiddenTitle());
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("sub_prop_start");
				$this->tpl->setVariable("PROPERTY_TITLE", $item->getTitle());
				$this->tpl->setVariable("PROPERTY_CLASS", "il_".$item->getType());
				if ($item->getType() != "non_editable_value")
				{
					$this->tpl->setVariable("LAB_ID", $item->getFieldId());
				}
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				// required
				if ($item->getType() != "non_editable_value")
				{
					if ($item->getRequired())
					{
						$this->tpl->touchBlock("required");
						$this->required_text = true;
					}
				}
				
				// hidden title (for accessibility, e.g. file upload)
				if ($item->getHiddenTitle() != "")
				{
					$this->tpl->setCurrentBlock("std_hid_title");
					$this->tpl->setVariable("PHID_TITLE",
						$item->getHiddenTitle());
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("std_prop_start");
				$this->tpl->setVariable("PROPERTY_TITLE", $item->getTitle());
				if ($item->getType() != "non_editable_value")
				{
					$this->tpl->setVariable("LAB_ID", $item->getFieldId());
				}
				if ($this->getHideLabels())
				{
					$this->tpl->setVariable("HIDE_LABELS_STYLE", " ilFormOptionHidden");
				}
				$this->tpl->parseCurrentBlock();
			}
			
			// alert
			if ($item->getType() != "non_editable_value" && $item->getAlert() != "")
			{
				$this->tpl->setCurrentBlock("alert");
				$this->tpl->setVariable("IMG_ALERT",
					ilUtil::getImagePath("icon_alert.svg"));
				$this->tpl->setVariable("ALT_ALERT",
					$lng->txt("alert"));
				$this->tpl->setVariable("TXT_ALERT",
					$item->getAlert());
				$this->tpl->parseCurrentBlock();
			}
			
			// subitems
			$sf = null;
			if ($item->getType() != "non_editable_value" or 1)
			{
				$sf = $item->getSubForm();
				if ($item->hideSubForm() && is_object($sf))
				{
					$this->tpl->setCurrentBlock("sub_form_hide");
					$this->tpl->setVariable("DSFID", $item->getFieldId());
					$this->tpl->parseCurrentBlock();
				}
			}
			

			$sf_content = "";
			if (is_object($sf))
			{
				$sf_content = $sf->getContent();
				if ($sf->getMultipart())
				{
					$this->setMultipart(true);
				}
				$this->tpl->setCurrentBlock("sub_form");
				$this->tpl->setVariable("PROP_SUB_FORM", $sf_content);
				$this->tpl->setVariable("SFID", $item->getFieldId());
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("prop");
			/* not used yet
			include_once("./Services/JSON/classes/class.ilJsonUtil.php");
			$this->tpl->setVariable("ID", $item->getFieldId());
			$this->tpl->setVariable("CFG", ilJsonUtil::encode($cfg));*/
			$this->tpl->parseCurrentBlock();
		}
		
		
		$this->tpl->touchBlock("item");
	}
	
	public function getHTML() 
	{
		$html = parent::getHTML();
		
		// #13531 - get content that has to reside outside of the parent form tag, e.g. panels/layers
		foreach($this->items as $item)
		{
			// #13536 - ilFormSectionHeaderGUI does NOT extend ilFormPropertyGUI ?!
			if(method_exists($item, "getContentOutsideFormTag"))
			{
				$outside = $item->getContentOutsideFormTag();
				if($outside)
				{
					$html .= $outside;
				}
			}
		}
		
		return $html;
	}
	
	
	// 
	// UPLOAD HANDLING
	//
	
	/**
	 * Import upload into temp directory
	 * 
	 * @param string $a_hash unique form hash
	 * @param string $a_field form field
	 * @param string $a_tmp_name temp file name
	 * @param string $a_name original file name
	 * @param string $a_type file mime type
	 * @param mixed $a_index form field index (if array)
	 * @param mixed $a_sub_index form field subindex (if array)
	 * @return bool 
	 */
	protected function keepFileUpload($a_hash, $a_field, $a_tmp_name, $a_name, $a_type, $a_index = null, $a_sub_index = null)
	{
		global $ilUser;
		
		$user_id = $ilUser->getId();
		if(!$user_id || $user_id == ANONYMOUS_USER_ID)
		{
			return;
		}
		
		$a_name = ilUtil::getAsciiFileName($a_name);
		
		$tmp_file_name = implode("~~", array($user_id,
			$a_hash,
			$a_field,
			$a_index,
			$a_sub_index,
			str_replace("/", "~~", $a_type),
			str_replace("~~", "_", $a_name)));
		
		// make sure temp directory exists
		$temp_path = ilUtil::getDataDir() . "/temp";
		if (!is_dir($temp_path))
		{
			ilUtil::createDirectory($temp_path);
		}
		
		move_uploaded_file($a_tmp_name, $temp_path."/".$tmp_file_name);	
	}
	
	/**
	 * Get file upload data
	 * 
	 * @param string $a_field form field 
	 * @param mixed $a_index form field index (if array)
	 * @param mixed $a_sub_index form field subindex (if array)
	 * @return array (tmp_name, name, type, error, size, is_upload)
	 */
	function getFileUpload($a_field, $a_index = null, $a_sub_index = null)
	{
		$res = array();
		if($a_index)
		{
			if($_FILES[$a_field]["tmp_name"][$a_index][$a_sub_index])
			{
				$res = array(
					"tmp_name" => $_FILES[$a_field]["tmp_name"][$a_index][$a_sub_index],
					"name" => $_FILES[$a_field]["name"][$a_index][$a_sub_index],
					"type" => $_FILES[$a_field]["type"][$a_index][$a_sub_index],
					"error" => $_FILES[$a_field]["error"][$a_index][$a_sub_index],
					"size" => $_FILES[$a_field]["size"][$a_index][$a_sub_index],
					"is_upload" => true
				);
			}
			else if($this->reloaded_files[$a_field]["tmp_name"][$a_index][$a_sub_index])
			{
				$res = array(
					"tmp_name" => $this->reloaded_files["tmp_name"][$a_index][$a_sub_index],
					"name" => $this->reloaded_files["name"][$a_index][$a_sub_index],
					"type" => $this->reloaded_files["type"][$a_index][$a_sub_index],
					"error" => $this->reloaded_files["error"][$a_index][$a_sub_index],
					"size" => $this->reloaded_files["size"][$a_index][$a_sub_index],
					"is_upload" => false
				);
			}
		}
		else if($a_sub_index)
		{
			if($_FILES[$a_field]["tmp_name"][$a_index])
			{
				$res = array(
					"tmp_name" => $_FILES[$a_field]["tmp_name"][$a_index],
					"name" => $_FILES[$a_field]["name"][$a_index],
					"type" => $_FILES[$a_field]["type"][$a_index],
					"error" => $_FILES[$a_field]["error"][$a_index],
					"size" => $_FILES[$a_field]["size"][$a_index],
					"is_upload" => true
				);
			}
			else if($this->reloaded_files[$a_field]["tmp_name"][$a_index])
			{
				$res = array(
					"tmp_name" => $this->reloaded_files[$a_field]["tmp_name"][$a_index],
					"name" => $this->reloaded_files[$a_field]["name"][$a_index],
					"type" => $this->reloaded_files[$a_field]["type"][$a_index],
					"error" => $this->reloaded_files[$a_field]["error"][$a_index],
					"size" => $this->reloaded_files[$a_field]["size"][$a_index],
					"is_upload" => false
				);
			}
		}
		else
		{
			if($_FILES[$a_field]["tmp_name"])
			{
				$res = array(
					"tmp_name" => $_FILES[$a_field]["tmp_name"],
					"name" => $_FILES[$a_field]["name"],
					"type" => $_FILES[$a_field]["type"],
					"error" => $_FILES[$a_field]["error"],
					"size" => $_FILES[$a_field]["size"],
					"is_upload" => true
				);
			}
			else if($this->reloaded_files[$a_field]["tmp_name"])
			{
				$res = array(
					"tmp_name" => $this->reloaded_files[$a_field]["tmp_name"],
					"name" => $this->reloaded_files[$a_field]["name"],
					"type" => $this->reloaded_files[$a_field]["type"],
					"error" => $this->reloaded_files[$a_field]["error"],
					"size" => $this->reloaded_files[$a_field]["size"],
					"is_upload" => false
				);
			}
		}
		return $res;
	}
	
	/**
	 * Was any file uploaded?
	 * 
	 * @param string $a_field form field 
	 * @param mixed $a_index form field index (if array)
	 * @param mixed $a_sub_index form field subindex (if array)
	 * @return bool 
	 */
	function hasFileUpload($a_field, $a_index = null, $a_sub_index = null)
	{
		$data = $this->getFileUpload($a_field, $a_index, $a_sub_index);
		return (bool)$data["tmp_name"];
	}
	
	/**
	 * Move upload to target directory
	 * 
	 * @param string $a_target_directory target directory (without filename!)
	 * @param string $a_field form field 
	 * @param string $a_target_name target file name (if different from uploaded file)
	 * @param mixed $a_index form field index (if array)
	 * @param mixed $a_sub_index form field subindex (if array)
	 * @return string target file name incl. path
	 */
	function moveFileUpload($a_target_directory, $a_field, $a_target_name = null, $a_index = null, $a_sub_index = null)
	{
		if(!is_dir($a_target_directory))
		{
			return;
		}
		
		$data = $this->getFileUpload($a_field, $a_index, $a_sub_index);
		if($data["tmp_name"] && file_exists($data["tmp_name"]))
		{
			if($a_target_name)
			{
				$data["name"] = $a_target_name;
			}
			
			$target_file = $a_target_directory."/".$data["name"];
			$target_file = str_replace("//", "/", $target_file);
			
			if($data["is_upload"])
			{
				if (!move_uploaded_file($data["tmp_name"], $target_file))
				{
					return;
				}
			}
			else
			{
				if (!rename($data["tmp_name"], $target_file))
				{
					return;
				}
			}
			
			return $target_file;
		}
	}
	
	/**
	 * try to rebuild files		
	 */
	protected function rebuildUploadedFiles()
	{
		global $ilUser;
	
		if($_POST["ilfilehash"])
		{					
			$user_id = $ilUser->getId();
			$temp_path = ilUtil::getDataDir() . "/temp";
			if(is_dir($temp_path) && $user_id && $user_id != ANONYMOUS_USER_ID)
			{
				$reload = array();
				
				$temp_files = glob($temp_path."/".$ilUser->getId()."~~".$_POST["ilfilehash"]."~~*");
				if(is_array($temp_files))
				{
					foreach($temp_files as $full_file)
					{
						$file = explode("~~", basename($full_file));
						$field = $file[2];
						$idx = $file[3];
						$idx2 = $file[4];
						$type = $file[5]."/".$file[6];
						$name = $file[7];

						if($idx2 != "")
						{
							if(!$_FILES[$field]["tmp_name"][$idx][$idx2])
							{
								$reload[$field]["tmp_name"][$idx][$idx2] = $full_file;
								$reload[$field]["name"][$idx][$idx2] = $name;
								$reload[$field]["type"][$idx][$idx2] = $type;
								$reload[$field]["error"][$idx][$idx2] = 0;
								$reload[$field]["size"][$idx][$idx2] = filesize($full_file);								
							}
						}
						else if($idx != "")
						{
							if(!$_FILES[$field]["tmp_name"][$idx])
							{
								$reload[$field]["tmp_name"][$idx] = $full_file;
								$reload[$field]["name"][$idx] = $name;
								$reload[$field]["type"][$idx] = $type;
								$reload[$field]["error"][$idx] = 0;
								$reload[$field]["size"][$idx] = filesize($full_file);								
							}	
						}
						else
						{
							if(!$_FILES[$field]["tmp_name"])
							{
								$reload[$field]["tmp_name"] = $full_file;
								$reload[$field]["name"] = $name;
								$reload[$field]["type"] = $type;
								$reload[$field]["error"] = 0;
								$reload[$field]["size"] = filesize($full_file);								
							}
						}						
					}
				}
				
				$this->reloaded_files = $reload;
			}
		}
	}
}

?>