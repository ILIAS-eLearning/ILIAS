<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilSearchSettings.php';
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';
include_once './Services/Administration/interfaces/interface.ilAdministrationCommandHandling.php';

/**
* Class ilSearchBaseGUI
*
* Base class for all search gui classes. Offers functionallities like set Locator set Header ...
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @package ilias-search
*
* @ilCtrl_IsCalledBy ilSearchBaseGUI: ilSearchController
*
*
*/
class ilSearchBaseGUI implements ilDesktopItemHandling, ilAdministrationCommandHandling
{
    const SEARCH_FAST = 1;
    const SEARCH_DETAILS = 2;
    const SEARCH_AND = 'and';
    const SEARCH_OR = 'or';
    
    const SEARCH_FORM_LUCENE = 1;
    const SEARCH_FORM_STANDARD = 2;
    const SEARCH_FORM_USER = 3;
    
    /**
     * @var ilSearchSettings
     */
    protected $settings = null;

    protected $ctrl = null;
    public $ilias = null;
    public $lng = null;
    public $tpl = null;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilMainMenu = $DIC['ilMainMenu'];

        $this->ilias =&$ilias;
        $this->ctrl =&$ilCtrl;
        $this->tpl =&$tpl;
        $this->lng =&$lng;
        $this->lng->loadLanguageModule('search');

        $ilMainMenu->setActive('search');
        $this->settings = new ilSearchSettings();
    }

    public function prepareOutput()
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];
        $lng = $DIC['lng'];
        
        $this->tpl->getStandardTemplate();
        
        //		$ilLocator->addItem($this->lng->txt('search'),$this->ctrl->getLinkTarget($this));
        //		$this->tpl->setLocator();
        
        //$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_src_b.gif"),
        //	$lng->txt("search"));
        $this->tpl->setTitleIcon(
            ilObject::_getIcon("", "big", "src"),
            ""
        );
        $this->tpl->setTitle($lng->txt("search"));

        ilUtil::infoPanel();
    }
    
    /**
    * Init standard search form.
    */
    public function initStandardSearchForm($a_mode)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setOpenTag(false);
        $this->form->setCloseTag(false);

        // term combination
        $radg = new ilHiddenInputGUI('search_term_combination');
        $radg->setValue(ilSearchSettings::getInstance()->getDefaultOperator());
        $this->form->addItem($radg);
        
        if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            if ($a_mode == self::SEARCH_FORM_STANDARD) {
                // search type
                $radg = new ilRadioGroupInputGUI($lng->txt("search_type"), "type");
                $radg->setValue(
                    $this->getType() ==
                        ilSearchBaseGUI::SEARCH_FAST ?
                        ilSearchBaseGUI::SEARCH_FAST :
                        ilSearchBaseGUI::SEARCH_DETAILS
                );
                $op1 = new ilRadioOption($lng->txt("search_fast_info"), ilSearchBaseGUI::SEARCH_FAST);
                $radg->addOption($op1);
                $op2 = new ilRadioOption($lng->txt("search_details_info"), ilSearchBaseGUI::SEARCH_DETAILS);
            } else {
                $op2 = new ilCheckboxInputGUI($this->lng->txt('search_filter_by_type'), 'item_filter_enabled');
                $op2->setValue(1);
                //				$op2->setChecked($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS);
            }

            
            $cbgr = new ilCheckboxGroupInputGUI('', 'filter_type');
            $cbgr->setUseValuesAsKeys(true);
            $details = $this->getDetails();
            $det = false;
            foreach (ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions() as $type => $data) {
                $cb = new ilCheckboxOption($lng->txt($data['trans']), $type);
                if ($details[$type]) {
                    $det = true;
                }
                $cbgr->addOption($cb);
            }
            if ($a_mode == self::SEARCH_FORM_LUCENE) {
                if (ilSearchSettings::getInstance()->isLuceneMimeFilterEnabled()) {
                    $mimes = $this->getMimeDetails();
                    foreach (ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions() as $type => $data) {
                        $op3 = new ilCheckboxOption($this->lng->txt($data['trans']), $type);
                        if ($mimes[$type]) {
                            $det = true;
                        }
                        $cbgr->addOption($op3);
                    }
                }
            }
            
            $cbgr->setValue(array_merge((array) $details, (array) $mimes));
            $op2->addSubItem($cbgr);
            
            if ($a_mode != self::SEARCH_FORM_STANDARD && $det) {
                $op2->setChecked(true);
            }

            if ($a_mode == ilSearchBaseGUI::SEARCH_FORM_STANDARD) {
                $radg->addOption($op2);
                $this->form->addItem($radg);
            } else {
                $this->form->addItem($op2);
            }
        }
                
        $this->form->setFormAction($ilCtrl->getFormAction($this, 'performSearch'));
    }
    
    /**
     * Init standard search form.
     */
    public function getSearchAreaForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);

        // term combination
        $radg = new ilHiddenInputGUI('search_term_combination');
        $radg->setValue(ilSearchSettings::getInstance()->getDefaultOperator());
        $form->addItem($radg);
        
        // search area
        include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
        $ti = new ilRepositorySelectorInputGUI($lng->txt("search_area"), "area");
        $ti->setSelectText($lng->txt("search_select_search_area"));
        $form->addItem($ti);
        $ti->readFromSession();
        
        // alex, 15.8.2012: Added the following lines to get the value
        // from the main menu top right input search form
        if (isset($_POST["root_id"])) {
            $ti->setValue($_POST["root_id"]);
            $ti->writeToSession();
        }
        $form->setFormAction($ilCtrl->getFormAction($this, 'performSearch'));
        
        return $form;
    }

    
    /**
     * Handle command
     * @param string $a_cmd
     */
    public function handleCommand($a_cmd)
    {
        if (method_exists($this, $a_cmd)) {
            $this->$a_cmd();
        } else {
            $a_cmd .= 'Object';
            $this->$a_cmd();
        }
    }
    
    /**
     * Interface methods
     */
    public function addToDeskObject()
    {
        include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
        ilDesktopItemGUI::addToDesktop();
        $this->showSavedResults();
    }
     
    /**
     * Remove from dektop
     */
    public function removeFromDeskObject()
    {
        include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
        ilDesktopItemGUI::removeFromDesktop();
        $this->showSavedResults();
    }
     
    /**
     * Show deletion screen
     */
    public function delete()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->delete();
    }
     
    /**
     * Cancel delete
     */
    public function cancelDelete()
    {
        $this->showSavedResults();
    }
    
    public function cancelMoveLinkObject()
    {
        $this->showSavedResults();
    }
    
    /**
     * Delete objects
     */
    public function performDelete()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->performDelete();
    }
    
    /**
     * Interface ilAdministrationCommandHandler
     */
    public function cut()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->cut();
    }
     
    /**
     * Interface ilAdministrationCommandHandler
     */
    public function link()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->link();
    }
         
    public function paste()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->paste();
    }
    
    public function showLinkIntoMultipleObjectsTree()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->showLinkIntoMultipleObjectsTree();
    }

    public function showMoveIntoObjectTree()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->showMoveIntoObjectTree();
    }
    
    public function performPasteIntoMultipleObjects()
    {
        include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
        $admin = new ilAdministrationCommandGUI($this);
        $admin->performPasteIntoMultipleObjects();
    }

    public function clear()
    {
        unset($_SESSION['clipboard']);
        $this->ctrl->redirect($this);
    }

    public function enableAdministrationPanel()
    {
        $_SESSION["il_cont_admin_panel"] = true;
        $this->ctrl->redirect($this);
    }
    
    public function disableAdministrationPanel()
    {
        $_SESSION["il_cont_admin_panel"] = false;
        $this->ctrl->redirect($this);
    }

    /**
     * @inheritdoc
     */
    public function keepObjectsInClipboardObject()
    {
        $this->ctrl->redirect($this);
    }
    
    
    /**
     * Add Locator
     */
    public function addLocator()
    {
        $ilLocator->addItem($this->lng->txt('search'), $this->ctrl->getLinkTarget($this));
        $this->tpl->setLocator();
    }
    
    /**
     * Add Pager
     *
     * @access public
     * @param
     *
     */
    protected function addPager($result, $a_session_key)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        $_SESSION["$a_session_key"] = max($_SESSION["$a_session_key"], $this->search_cache->getResultPageNumber());
        
        if ($_SESSION["$a_session_key"] == 1 and
            (count($result->getResults()) < $result->getMaxHits())) {
            return true;
        }
        
        if ($this->search_cache->getResultPageNumber() > 1) {
            $this->ctrl->setParameter($this, 'page_number', $this->search_cache->getResultPageNumber() - 1);
            /*			$this->tpl->setCurrentBlock('prev');
                        $this->tpl->setVariable('PREV_LINK',$this->ctrl->getLinkTarget($this,'performSearch'));
                        $this->tpl->setVariable('TXT_PREV',$this->lng->txt('search_page_prev'));
                        $this->tpl->parseCurrentBlock();
            */
            $this->prev_link = $this->ctrl->getLinkTarget($this, 'performSearch');
        }
        for ($i = 1;$i <= $_SESSION["$a_session_key"];$i++) {
            if ($i == $this->search_cache->getResultPageNumber()) {
                /*				$this->tpl->setCurrentBlock('pages_link');
                                $this->tpl->setVariable('NUMBER',$i);
                                $this->tpl->parseCurrentBlock();
                */
                continue;
            }
            
            $this->ctrl->setParameter($this, 'page_number', $i);
            $link = '<a href="' . $this->ctrl->getLinkTarget($this, 'performSearch') . '" /a>' . $i . '</a> ';
            /*			$this->tpl->setCurrentBlock('pages_link');
                        $this->tpl->setVariable('NUMBER',$link);
                        $this->tpl->parseCurrentBlock();
            */
        }
        

        if (count($result->getResults()) >= $result->getMaxHits()) {
            $this->ctrl->setParameter($this, 'page_number', $this->search_cache->getResultPageNumber() + 1);
            /*			$this->tpl->setCurrentBlock('next');
                        $this->tpl->setVariable('NEXT_LINK',$this->ctrl->getLinkTarget($this,'performSearch'));
                         $this->tpl->setVariable('TXT_NEXT',$this->lng->txt('search_page_next'));
                         $this->tpl->parseCurrentBlock();
            */
            $this->next_link = $this->ctrl->getLinkTarget($this, 'performSearch');
        }

        /*		$this->tpl->setCurrentBlock('prev_next');
                 $this->tpl->setVariable('SEARCH_PAGE',$this->lng->txt('search_page'));
                 $this->tpl->parseCurrentBlock();
        */
        
        $this->ctrl->clearParameters($this);
    }
    
    /**
     * Build path for search area
     * @return
     */
    protected function buildSearchAreaPath($a_root_node)
    {
        global $DIC;

        $tree = $DIC['tree'];

        $path_arr = $tree->getPathFull($a_root_node, ROOT_FOLDER_ID);
        $counter = 0;
        foreach ($path_arr as $data) {
            if ($counter++) {
                $path .= " > ";
                $path .= $data['title'];
            } else {
                $path .= $this->lng->txt('repository');
            }
        }
        return $path;
    }
    
    /**
    * Data resource for autoComplete
    */
    public function autoComplete()
    {
        $q = $_REQUEST["term"];
        include_once("./Services/Search/classes/class.ilSearchAutoComplete.php");
        $list = ilSearchAutoComplete::getList($q);
        echo $list;
        exit;
    }
    
    // begin-patch creation_date
    protected function getCreationDateForm()
    {
        $options = $this->search_cache->getCreationFilter();
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);
        
        $enabled = new ilCheckboxInputGUI($this->lng->txt('search_filter_cd'), 'screation');
        $enabled->setValue(1);
        $enabled->setChecked((bool) $options['enabled']);
        $form->addItem($enabled);
        
        #$group = new ilRadioGroupInputGUI('', 'screation_type');
        #$group->setValue((int) $options['type']);
        #$group->addOption($opt1 = new ilRadioOption($this->lng->txt('search_filter_date'), 1));
        
        $limit_sel = new ilSelectInputGUI('', 'screation_ontype');
        $limit_sel->setValue($options['ontype']);
        $limit_sel->setOptions(
            array(
                    1 => $this->lng->txt('search_created_after'),
                    2 => $this->lng->txt('search_created_before'),
                    3 => $this->lng->txt('search_created_on')
            )
        );
        $enabled->addSubItem($limit_sel);
        
        
        if ($options['date']) {
            $now = new ilDate($options['date'], IL_CAL_UNIX);
        } else {
            $now = new ilDate(time(), IL_CAL_UNIX);
        }
        $ds = new ilDateTimeInputGUI('', 'screation_date');
        $ds->setRequired(true);
        $ds->setDate($now);
        $enabled->addSubItem($ds);
        
        #$group->addOption($opt2 = new ilRadioOption($this->lng->txt('search_filter_duration'), 2));
        
        #$duration = new ilDurationInputGUI($this->lng->txt('search_filter_duration'), 'screation_duration');
        #$duration->setMonths((int) $options['duration']['MM']);
        #$duration->setDays((int) $options['duration']['dd']);
        #$duration->setShowMonths(true);
        #$duration->setShowDays(true);
        #$duration->setShowHours(false);
        #$duration->setShowMinutes(false);
        #$duration->setTitle($this->lng->txt('search_newer_than'));
        #$opt2->addSubItem($duration);
        
        #$enabled->addSubItem($group);
                
        $form->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this, 'performSearch'));
        
        return $form;
    }
    
    /**
     * Get user search cache
     * @return ilUserSearchCache
     */
    protected function getSearchCache()
    {
        return $this->search_cache;
    }
    
    /**
     * Load creation date filter
     * @return array
     */
    protected function loadCreationFilter()
    {
        if (!$this->settings->isDateFilterEnabled()) {
            return array();
        }
        
        
        $form = $this->getCreationDateForm();
        $options = array();
        if ($form->checkInput()) {
            $options['enabled'] = $form->getInput('screation');
            $options['type'] = $form->getInput('screation_type');
            $options['ontype'] = $form->getInput('screation_ontype');
            $options['date'] = $form->getItemByPostVar('screation_date')->getDate()->get(IL_CAL_UNIX);
            $options['duration'] = $form->getInput('screation_duration');
        }
        return $options;
    }
}
