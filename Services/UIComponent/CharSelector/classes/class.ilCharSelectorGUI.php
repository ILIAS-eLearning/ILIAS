<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

// Config must be included generally for availability of constants
require_once ('Services/UIComponent/CharSelector/classes/ilCharSelectorConfig.php');

/**
* This shows a character selector
*/
class ilCharSelectorGUI
{
	/**
	 * @static list of command classes for which the char selector is allowed 
	 * (can also be a parent class of the actual command class)
	 */
	private static $allowed_guis = array (
		'assQuestionGUI',
		'ilAssQuestionFeedbackEditingGUI',
		'ilAssQuestionHintGUI',
		'ilObjTestSettingsGeneralGUI',
		'ilTestScoringGUI'
	);
	
	/**
	 * @static ilCharSelectorGUI	instance used for the current selector
	 */
	private static $current_gui;
	
	/**
	 * @var ilCharSelectorConfig	configuration object
	 */
	private $config = null;
		
	/**
	 * @var boolean	selector is already added to the page 
	 */
	private $added_to_page = false;
	
	
	/**
	 * Constructor
	 * @param string	configuration context
	 */
	public function __construct($a_context = ilCharSelectorConfig::CONTEXT_NONE) 
	{		
		$this->config = new ilCharSelectorConfig($a_context);
	}
	
	/**
	 * Check if the CharSelector is allowed for the current GUI
	 * @return boolean CharSelector is allowed
	 */
	public static function _isAllowed()
	{
		global $ilCtrl;
		
		// get the command class 
		// with correct case for checking parent classes
		foreach ($ilCtrl->getCallHistory() as $call)
		{
			if ($call['mode'] == 'execComm')
			{
				$class = $call['class'];
			}			
		}

		// check the class and her parent classes
		while ($class != false)
		{
			if (in_array($class, self::$allowed_guis))
			{
				return true;
			}
			$class = get_parent_class($class);
		} 

		return false;
	}

	/**
	 * Get the GUI that is used for the currently available selector
	 * (other GUI instances may exist for configuration in property forms)
	 * 
	 * @param	object	(optional) current running test 
	 * @return	object
	 */
	public static function _getCurrentGUI(ilObjTest $a_test_obj = null) 
	{	
		if (!isset(self::$current_gui)) 
		{
			self::$current_gui = new ilCharSelectorGUI();
			self::$current_gui->setConfig(ilCharSelectorConfig::_getCurrentConfig($a_test_obj));
		}	
		return self::$current_gui;
	}
	
	/**
	 * Set the configuraton object
	 * @param ilCharSelectorConfig 
	 */
	public function setConfig(ilCharSelectorConfig $a_config)
	{
		$this->config = $a_config;
	}
	
	/**
	 * Get the configuraton object
	 * @return ilCharSelectorConfig 
	 */
	public function getConfig()
	{
		return $this->config;
	}
	
	/**
	 * add the configuration elements to a property form
	 * @param object	property form
	 */
	public function addFormProperties(ilPropertyFormGUI $a_form)
	{
		global $lng;
		$lng->loadLanguageModule('adve');

        require_once ('Services/UIComponent/CharSelector/classes/class.ilCharSelectorRadioGroupInputGUI.php');
		$availability = new ilCharSelectorRadioGroupInputGUI($lng->txt('char_selector_'.$this->config->getContext()), 'char_selector_availability');
		$inactive = new ilRadioOption($lng->txt('char_selector_inactive_'.$this->config->getContext()),ilCharSelectorConfig::INACTIVE);
		$inactive->setInfo($lng->txt('char_selector_inactive_info_'.$this->config->getContext()));
		$inherit = new ilRadioOption($lng->txt('char_selector_inherit_'.$this->config->getContext()),ilCharSelectorConfig::INHERIT);
		$inherit->setInfo($lng->txt('char_selector_inherit_info_'.$this->config->getContext()));
		$enabled = new ilRadioOption($lng->txt('char_selector_enabled_'.$this->config->getContext()), ilCharSelectorConfig::ENABLED);
		$enabled->setInfo($lng->txt('char_selector_enabled_info_'.$this->config->getContext()));
		$disabled = new ilRadioOption($lng->txt('char_selector_disabled_'.$this->config->getContext()), ilCharSelectorConfig::DISABLED);
		$disabled->setInfo($lng->txt('char_selector_disabled_info_'.$this->config->getContext()));

		$blocks = new ilSelectInputGUI($lng->txt('char_selector_blocks'), 'char_selector_blocks');
		$blocks->setInfo($lng->txt('char_selector_blocks_info'));
		$blocks->setOptions($this->config->getBlockOptions());
		$blocks->setMulti(true);
		$enabled->addSubItem($blocks);

		$custom_items = new ilTextAreaInputGUI($lng->txt('char_selector_custom_items'),'char_selector_custom_items');
		$tpl = new ilTemplate("tpl.char_selector_custom_info.html", true, true, "Services/UIComponent/CharSelector");
		$tpl->setVariable('1',$lng->txt('char_selector_custom_items_info1'));
		$tpl->setVariable('2a',$lng->txt('char_selector_custom_items_info2a'));
		$tpl->setVariable('2b',$lng->txt('char_selector_custom_items_info2b'));
		$tpl->setVariable('3a',$lng->txt('char_selector_custom_items_info3a'));
		$tpl->setVariable('3b',$lng->txt('char_selector_custom_items_info3b'));
		$tpl->setVariable('4a',$lng->txt('char_selector_custom_items_info4a'));
		$tpl->setVariable('4b',$lng->txt('char_selector_custom_items_info4b'));
		$tpl->setVariable('5a',$lng->txt('char_selector_custom_items_info5a'));
		$tpl->setVariable('5b',$lng->txt('char_selector_custom_items_info5b'));
		$tpl->setVariable('6a',$lng->txt('char_selector_custom_items_info6a'));
		$tpl->setVariable('6b',$lng->txt('char_selector_custom_items_info6b'));
		$custom_items->setInfo($tpl->get());
		$enabled->addSubItem($custom_items);
		
		switch($this->config->getContext())
		{
			case ilCharSelectorConfig::CONTEXT_ADMIN:
				$availability->addOption($inactive);
				$availability->addOption($enabled);
				$availability->addOption($disabled);
				$a_form->addItem($availability);
				break;
			
			case ilCharSelectorConfig::CONTEXT_USER:
			case ilCharSelectorConfig::CONTEXT_TEST:
				$availability->addOption($inherit);
				$availability->addOption($enabled);
				$availability->addOption($disabled);
				$a_form->addItem($availability);
				break;
		}
	}
	
	
	/**
	 * Set the values in a property form based on the configuration
	 * @param object		property form
	 * @param string		context of the form
	 */
	public function setFormValues(ilPropertyFormGUI $a_form)
	{
		$a_form->getItemByPostVar('char_selector_availability')->setValue($this->config->getAvailability());
		$a_form->getItemByPostVar('char_selector_blocks')->setValue($this->config->getAddedBlocks());
		$a_form->getItemByPostVar('char_selector_custom_items')->setValue($this->config->getCustomItems());
	}
	
	
	/**
	 * Set the configuration based on the values of a property form
	 * @param object		property form
	 * @param string		context of the form
	 */
	public function getFormValues(ilPropertyFormGUI $a_form)
	{
		$this->config->setAvailability($a_form->getInput('char_selector_availability'));
		$this->config->setAddedBlocks($a_form->getInput('char_selector_blocks'));
		$this->config->setCustomItems($a_form->getInput('char_selector_custom_items'));
	}
	
	/**
	 * Adds the the character selector to the ilias page
	 * Initializes the selector according to the state saved in the user session
	 * @see self::saveState()
	 */
	function addToPage()
	{
		global $ilCtrl, $tpl, $lng;
		
		// don't add the panel twice
		if ($this->added_to_page)
		{
			return;
		}
		
		$lng->loadLanguageModule('adve');
		
		// prepare the configuration for the js script
		$this->jsconfig = new stdClass();
		$this->jsconfig->pages = $this->config->getCharPages();
		$this->jsconfig->ajax_url = $ilCtrl->getLinkTargetByClass("ilcharselectorgui", "saveState", "", true);
		$this->jsconfig->open = (int) $_SESSION['char_selector_open'];
		$this->jsconfig->current_page = (int) $_SESSION['char_selector_current_page'];
		$this->jsconfig->current_subpage = (int) $_SESSION['char_selector_current_subpage'];
		
		// provide texts to be dynamically rendered in the js script
		$this->jstexts = new stdClass();
		$this->jstexts->page = $lng->txt('page');
		
		// add everything neded to the page
		// addLightbox() is just used to add the panel template outside the body
		// The panel template is added as <script> to be not included in the DOM by default
		// It will be included by js below the main header when the selector is switched on
		$tpl->addCss(ilUtil::getStyleSheetLocation('','char_selector_style.css','Services/UIComponent/CharSelector'));
		$tpl->addJavascript('./Services/UIComponent/CharSelector/js/ilCharSelector.js');
		$tpl->addLightbox($this->getSelectorHTML(),2);
		$tpl->addOnLoadCode('il.CharSelector.init('.json_encode($this->jsconfig).','.json_encode($this->jstexts).')');
		$this->added_to_page = true;
	}
	
	/**
	 * Get the HTML code of the selector panel
	 * @return string	panel html code
	 */
	function getSelectorHTML()
	{
		global $lng;		
		$tpl = new ilTemplate("tpl.char_selector_panel.html", true, true, "Services/UIComponent/CharSelector");
		
		if (count($this->jsconfig->pages) > 1)
		{
			$index = 0;
			foreach ($this->jsconfig->pages as $page)
			{
				$tpl->setCurrentBlock('page_option');
				$tpl->setVariable("PAGE_INDEX", $index);
				$tpl->setVariable("PAGE_NAME", $page[0]);
				$tpl->parseCurrentBlock();
				$index++;
			}

			$tpl->setVariable('TXT_PREVIOUS_PAGE', $lng->txt('previous'));
			$tpl->setVariable('TXT_NEXT_PAGE', $lng->txt('next'));
			$tpl->setVariable('TXT_PAGE', $lng->txt('page'));
		}

		$tpl->touchBlock('chars');
		return '<script type="text/html" id="ilCharSelectorTemplate">'.$tpl->get().'</script>';
	}

	
	/**
	 * Save the selector panel state in the user session
	 * (This keeps the panel state between page moves)
	 * @see self::addToPage()
	 */
	function saveState()
	{
		$_SESSION['char_selector_open'] = (int) $_GET['open'];
		$_SESSION['char_selector_current_page'] = (int) $_GET['current_page'];
		$_SESSION['char_selector_current_subpage'] = (int) $_GET['current_subpage'];
		
		// debugging output (normally ignored by the js part)
		echo json_encode(array(
			'open' => $_SESSION['char_selector_open'],
			'current_page' => $_SESSION['char_selector_current_page'],
			'current_subpage' => $_SESSION['char_selector_current_subpage'],
		));
		exit;
	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd("saveState");
		switch($cmd)
		{
			case 'saveState':
				$this->$cmd();
				break;
			default:
				return;
		}
	}
}
?>