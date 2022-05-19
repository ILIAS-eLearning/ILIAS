<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * This shows a character selector
 * @deprecated needs to be moved to KS
 */
class ilCharSelectorGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected stdClass $jsconfig;
    protected stdClass $jstexts;

    /**
     * @static list of command classes for which the char selector is allowed
     * (can also be a parent class of the actual command class)
     */
    private static array $allowed_guis = array(
        'assQuestionGUI',
        'ilAssQuestionFeedbackEditingGUI',
        'ilAssQuestionHintGUI',
        'ilObjTestSettingsGeneralGUI',
        'ilTestScoringGUI'
    );
    
    // instance used for the current selector
    private static ilCharSelectorGUI $current_gui;
    private ?ilCharSelectorConfig $config = null;
        
    // selector is already added to the page
    private bool $added_to_page = false;

    protected \ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    
    /**
     * @param string $a_context configuration context
     */
    public function __construct(
        string $a_context = ilCharSelectorConfig::CONTEXT_NONE
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->config = new ilCharSelectorConfig($a_context);
        $this->refinery = $DIC->refinery();
        $this->wrapper = $DIC->http()->wrapper();
    }

    /**
     * Check if the CharSelector is allowed for the current GUI
     */
    public static function _isAllowed() : bool
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        
        // get the command class
        // with correct case for checking parent classes
        $class = false;
        foreach ($ilCtrl->getCallHistory() as $call) {
            if (($call['mode'] ?? "") === 'execComm') {
                $class = $call['class'];
            }
        }

        // check the class and her parent classes
        while ($class != false) {
            if (in_array($class, self::$allowed_guis)) {
                return true;
            }
            $class = get_parent_class($class);
        }

        return false;
    }

    /**
     * Get the GUI that is used for the currently available selector
     * (other GUI instances may exist for configuration in property forms)
     */
    public static function _getCurrentGUI(ilObjTest $a_test_obj = null) : self
    {
        if (!isset(self::$current_gui)) {
            self::$current_gui = new ilCharSelectorGUI();
            self::$current_gui->setConfig(ilCharSelectorConfig::_getCurrentConfig($a_test_obj));
        }
        return self::$current_gui;
    }
    
    public function setConfig(ilCharSelectorConfig $a_config) : void
    {
        $this->config = $a_config;
    }
    
    public function getConfig() : ilCharSelectorConfig
    {
        return $this->config;
    }
    
    /**
     * add the configuration elements to a property form
     */
    public function addFormProperties(ilPropertyFormGUI $a_form) : void
    {
        $lng = $this->lng;
        $lng->loadLanguageModule('adve');

        $availability = new ilCharSelectorRadioGroupInputGUI($lng->txt('char_selector_' . $this->config->getContext()), 'char_selector_availability');
        $inactive = new ilRadioOption($lng->txt('char_selector_inactive_' . $this->config->getContext()), ilCharSelectorConfig::INACTIVE);
        $inactive->setInfo($lng->txt('char_selector_inactive_info_' . $this->config->getContext()));
        $inherit = new ilRadioOption($lng->txt('char_selector_inherit_' . $this->config->getContext()), ilCharSelectorConfig::INHERIT);
        $inherit->setInfo($lng->txt('char_selector_inherit_info_' . $this->config->getContext()));
        $enabled = new ilRadioOption($lng->txt('char_selector_enabled_' . $this->config->getContext()), ilCharSelectorConfig::ENABLED);
        $enabled->setInfo($lng->txt('char_selector_enabled_info_' . $this->config->getContext()));
        $disabled = new ilRadioOption($lng->txt('char_selector_disabled_' . $this->config->getContext()), ilCharSelectorConfig::DISABLED);
        $disabled->setInfo($lng->txt('char_selector_disabled_info_' . $this->config->getContext()));

        $blocks = new ilSelectInputGUI($lng->txt('char_selector_blocks'), 'char_selector_blocks');
        $blocks->setInfo($lng->txt('char_selector_blocks_info'));
        $blocks->setOptions($this->config->getBlockOptions());
        $blocks->setMulti(true);
        $enabled->addSubItem($blocks);

        $custom_items = new ilTextAreaInputGUI($lng->txt('char_selector_custom_items'), 'char_selector_custom_items');
        $tpl = new ilTemplate("tpl.char_selector_custom_info.html", true, true, "Services/UIComponent/CharSelector");
        $tpl->setVariable('1', $lng->txt('char_selector_custom_items_info1'));
        $tpl->setVariable('2a', $lng->txt('char_selector_custom_items_info2a'));
        $tpl->setVariable('2b', $lng->txt('char_selector_custom_items_info2b'));
        $tpl->setVariable('3a', $lng->txt('char_selector_custom_items_info3a'));
        $tpl->setVariable('3b', $lng->txt('char_selector_custom_items_info3b'));
        $tpl->setVariable('4a', $lng->txt('char_selector_custom_items_info4a'));
        $tpl->setVariable('4b', $lng->txt('char_selector_custom_items_info4b'));
        $tpl->setVariable('5a', $lng->txt('char_selector_custom_items_info5a'));
        $tpl->setVariable('5b', $lng->txt('char_selector_custom_items_info5b'));
        $tpl->setVariable('6a', $lng->txt('char_selector_custom_items_info6a'));
        $tpl->setVariable('6b', $lng->txt('char_selector_custom_items_info6b'));
        $custom_items->setInfo($tpl->get());
        $enabled->addSubItem($custom_items);
        
        switch ($this->config->getContext()) {
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
     */
    public function setFormValues(ilPropertyFormGUI $a_form) : void
    {
        $a_form->getItemByPostVar('char_selector_availability')->setValue($this->config->getAvailability());
        $a_form->getItemByPostVar('char_selector_blocks')->setValue($this->config->getAddedBlocks());
        $a_form->getItemByPostVar('char_selector_custom_items')->setValue($this->config->getCustomItems());
    }
    
    
    /**
     * Set the configuration based on the values of a property form
     */
    public function getFormValues(ilPropertyFormGUI $a_form) : void
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
    public function addToPage() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        // don't add the panel twice
        if ($this->added_to_page) {
            return;
        }
        
        $lng->loadLanguageModule('adve');
        
        // prepare the configuration for the js script
        $this->jsconfig = new stdClass();
        $this->jsconfig->pages = $this->config->getCharPages();
        $this->jsconfig->ajax_url = $ilCtrl->getLinkTargetByClass("ilcharselectorgui", "saveState", "", true);
        $this->jsconfig->open = (int) ilSession::get('char_selector_open');
        $this->jsconfig->current_page = (int) ilSession::get('char_selector_current_page');
        $this->jsconfig->current_subpage = (int) ilSession::get('char_selector_current_subpage');
        
        // provide texts to be dynamically rendered in the js script
        $this->jstexts = new stdClass();
        $this->jstexts->page = $lng->txt('page');
        // fau: testNav - add texts for open/close char selector actions in the question menu
        $this->jstexts->open = $lng->txt('char_selector_menu_open');
        $this->jstexts->close = $lng->txt('char_selector_menu_close');
        // fau.

        // add everything neded to the page
        // addLightbox() is just used to add the panel template outside the body
        // The panel template is added as <script> to be not included in the DOM by default
        // It will be included by js below the main header when the selector is switched on
        $tpl->addCss(ilUtil::getStyleSheetLocation('', 'char_selector_style.css', 'Services/UIComponent/CharSelector'));
        $tpl->addJavaScript('./Services/UIComponent/CharSelector/js/ilCharSelector.js');
        $tpl->addLightbox($this->getSelectorHTML(), 2);
        $tpl->addOnLoadCode(
            'il.CharSelector.init(' .
            json_encode($this->jsconfig, JSON_THROW_ON_ERROR) . ',' .
            json_encode($this->jstexts, JSON_THROW_ON_ERROR) . ')'
        );
        $this->added_to_page = true;
    }
    
    /**
     * Get the HTML code of the selector panel
     */
    public function getSelectorHTML() : string
    {
        $lng = $this->lng;
        $tpl = new ilTemplate("tpl.char_selector_panel.html", true, true, "Services/UIComponent/CharSelector");
        
        if (count($this->jsconfig->pages) > 1) {
            $index = 0;
            foreach ($this->jsconfig->pages as $page) {
                $tpl->setCurrentBlock('page_option');
                $tpl->setVariable("PAGE_INDEX", $index);
                $tpl->setVariable("PAGE_NAME", $page[0]);
                $tpl->parseCurrentBlock();
                $index++;
            }
        }

        $tpl->setVariable('TXT_PREVIOUS_PAGE', $lng->txt('previous'));
        $tpl->setVariable('TXT_NEXT_PAGE', $lng->txt('next'));
        $tpl->setVariable('TXT_PAGE', $lng->txt('page'));

        $tpl->touchBlock('chars');
        return '<script type="text/html" id="ilCharSelectorTemplate">' . $tpl->get() . '</script>';
    }

    
    /**
     * Save the selector panel state in the user session
     * (This keeps the panel state between page moves)
     * @see self::addToPage()
     */
    public function saveState() : void
    {
        $int = $this->refinery->kindlyTo()->int();
        ilSession::set(
            'char_selector_open',
            $this->wrapper->query()->retrieve("open", $int)
        );
        ilSession::set(
            'char_selector_current_page',
            $this->wrapper->query()->retrieve("current_page", $int)
        );
        ilSession::set(
            'char_selector_current_subpage',
            $this->wrapper->query()->retrieve("current_subpage", $int)
        );

        // debugging output (normally ignored by the js part)
        echo json_encode(array(
            'open' => ilSession::get('char_selector_open'),
            'current_page' => ilSession::get('char_selector_current_page'),
            'current_subpage' => ilSession::get('char_selector_current_subpage'),
        ), JSON_THROW_ON_ERROR);
        exit;
    }
    
    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        $cmd = $ilCtrl->getCmd("saveState");
        switch ($cmd) {
            case 'saveState':
                $this->$cmd();
                break;
            default:
                break;
        }
    }
}
