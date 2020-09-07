<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilAdvancedSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilAdvancedSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilAdvancedSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilAdvancedSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI, ilPropertyFormGUI
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilSearchBaseGUI.php';
include_once 'Services/MetaData/classes/class.ilMDUtilSelect.php';
include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';

class ilAdvancedSearchGUI extends ilSearchBaseGUI
{
    const TYPE_LOM = 1;
    const TYPE_ADV_MD = 2;

    protected $last_section = 'adv_search';

    protected $fields = array();



    /**
    * array of all options select boxes,'and' 'or' and query strings
    * @access public
    */
    private $options = array();
    
    protected $tabs_gui;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $this->tabs_gui = $ilTabs;
        
        parent::__construct();

        $this->lng->loadLanguageModule('meta');
        $this->fields = ilLuceneAdvancedSearchFields::getInstance();

        $this->__setSearchOptions($_POST);
    }

    public function getRootNode()
    {
        return ROOT_FOLDER_ID;
    }


    /**
    * Control
    * @access public
    */
    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilpropertyformgui":
            
            
            case 'ilobjectcopygui':
                $this->prepareOutput();
                $this->ctrl->setReturn($this, '');

                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $this->ctrl->forwardCommand($cp);
                break;
            
            default:
                $this->initUserSearchCache();
                if (!$cmd) {
                    switch ($_SESSION['search_last_sub_section']) {
                        case self::TYPE_ADV_MD:
                            $cmd = "showSavedAdvMDResults";
                            break;
                        
                        default:
                            $cmd = "showSavedResults";
                            break;
                    }
                }

                $this->prepareOutput();
                $this->handleCommand($cmd);
                break;
        }
        return true;
    }
    public function reset()
    {
        $this->initSearchType(self::TYPE_LOM);
        $this->options = array();
        $this->search_cache->setQuery(array());
        $this->search_cache->save();
        $this->showSearch();
    }

    public function searchInResults()
    {
        $this->initSearchType(self::TYPE_LOM);
        $this->search_mode = 'in_results';
        $this->search_cache->setResultPageNumber(1);
        unset($_SESSION['adv_max_page']);
        $this->performSearch();

        return true;
    }

    /**
     * Search from main menu
     */
    protected function remoteSearch()
    {
        $this->search_cache->setRoot((int) $_POST['root_id']);
        $this->search_cache->setResultPageNumber(1);
        $this->search_cache->setQuery(array('lom_content' => ilUtil::stripSlashes($_POST['queryString'])));
        $this->search_cache->save();

        $this->options = $this->search_cache->getQuery();
        $this->options['type'] = 'all';

        $this->performSearch();
    }



    public function performSearch()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $this->initSearchType(self::TYPE_LOM);
        
        if (!isset($_GET['page_number']) and $this->search_mode != 'in_results') {
            unset($_SESSION['adv_max_page']);
            $this->search_cache->deleteCachedEntries();
        }
        
        if (isset($_POST['query'])) {
            $this->search_cache->setQuery($_POST['query']);
        }
        

        include_once 'Services/Search/classes/class.ilSearchResult.php';
        $res = new ilSearchResult();

        if ($res_con = &$this->__performContentSearch()) {
            $this->__storeEntries($res, $res_con);
        }
        if ($res_lan = &$this->__performLanguageSearch()) {
            $this->__storeEntries($res, $res_lan);
        }
        if ($res_gen = &$this->__performGeneralSearch()) {
            $this->__storeEntries($res, $res_gen);
        }
        if ($res_lif = &$this->__performLifecycleSearch()) {
            $this->__storeEntries($res, $res_lif);
        }
        if ($res_con = &$this->__performContributeSearch()) {
            $this->__storeEntries($res, $res_con);
        }
        if ($res_ent = &$this->__performEntitySearch()) {
            $this->__storeEntries($res, $res_ent);
        }
        if ($res_req = &$this->__performRequirementSearch()) {
            $this->__storeEntries($res, $res_req);
        }
        if ($res_for = &$this->__performFormatSearch()) {
            $this->__storeEntries($res, $res_for);
        }
        if ($res_edu = &$this->__performEducationalSearch()) {
            $this->__storeEntries($res, $res_edu);
        }
        if ($res_typ = &$this->__performTypicalAgeRangeSearch()) {
            $this->__storeEntries($res, $res_typ);
        }
        if ($res_rig = &$this->__performRightsSearch()) {
            $this->__storeEntries($res, $res_rig);
        }
        if ($res_cla = &$this->__performClassificationSearch()) {
            $this->__storeEntries($res, $res_cla);
        }
        if ($res_tax = &$this->__performTaxonSearch()) {
            $this->__storeEntries($res, $res_tax);
        }
        if ($res_key = &$this->__performKeywordSearch()) {
            $this->__storeEntries($res, $res_key);
        }
                
        $this->searchAdvancedMD($res);
                    
        if ($this->search_mode == 'in_results') {
            include_once 'Services/Search/classes/class.ilSearchResult.php';

            $old_result_obj = new ilSearchResult($ilUser->getId());
            $old_result_obj->read(ADVANCED_MD_SEARCH);

            $res->diffEntriesFromResult($old_result_obj);
        }

        $res->filter($this->getRootNode(), (ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND));
        $res->save();
        $this->showSearch();
        
        if (!count($res->getResults())) {
            ilUtil::sendInfo($this->lng->txt('search_no_match'));
        }

        if ($res->isLimitReached()) {
            #$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
            #ilUtil::sendInfo($message);
        }

        $this->addPager($res, 'adv_max_page');
        
        include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
        $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
        $presentation->setResults($res->getResultsForPresentation());
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('RESULTS', $presentation->getHTML(true));
        }
        return true;
    }
    
    /**
     *
     *
     * @access protected
     */
    protected function initAdvancedMetaDataForm()
    {
        if (is_object($this->form)) {
            return $this->form;
        }
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'performAdvMDSearch'));
        $this->form->setTitle($this->lng->txt('adv_md_search_title'));
        $this->form->addCommandButton('performAdvMDSearch', $this->lng->txt('search'));
        #$this->form->setSubformMode('right');
        
        $content = new ilTextInputGUI($this->lng->txt('meta_title') . '/' .
            $this->lng->txt('meta_keyword') . '/' .
            $this->lng->txt('meta_description'), 'title');
        $content->setValue($this->options['title']);
        $content->setSize(30);
        $content->setMaxLength(255);
        //		$content->setSubformMode('right');
        $group = new ilRadioGroupInputGUI('', 'title_ao');
        $group->setValue($this->options['title_ao']);
        $radio_option = new ilRadioOption($this->lng->txt("search_any_word"), 0);
        $group->addOption($radio_option);
        $radio_option = new ilRadioOption($this->lng->txt("search_all_words"), 1);
        $group->addOption($radio_option);
        $content->addSubItem($group);
        $this->form->addItem($content);
        
        $type = new ilSelectInputGUI($this->lng->txt('type'), 'type');
        $options['adv_all'] = $this->lng->txt('search_any');
        foreach (ilAdvancedMDRecord::_getActivatedObjTypes() as $obj_type) {
            $options[$obj_type] = $this->lng->txt('objs_' . $obj_type);
        }
        $type->setOptions($options);
        $type->setValue($this->options['type']);
        $this->form->addItem($type);
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_SEARCH);
        $record_gui->setPropertyForm($this->form);
        $record_gui->setSearchValues($this->options);
        $record_gui->parse();
    }
    
    /**
     * perform advanced meta data search
     *
     * @access protected
     */
    protected function performAdvMDSearch()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $this->initSearchType(self::TYPE_ADV_MD);
        if (!isset($_GET['page_number']) and $this->search_mode != 'in_results') {
            unset($_SESSION['adv_max_page']);
            $this->search_cache->delete();
        }

        include_once 'Services/Search/classes/class.ilSearchResult.php';
        $res = new ilSearchResult();
        
        if ($res_tit = &$this->__performTitleSearch()) {
            $this->__storeEntries($res, $res_tit);
        }
        $this->searchAdvancedMD($res);

        if ($this->search_mode == 'in_results') {
            include_once 'Services/Search/classes/class.ilSearchResult.php';

            $old_result_obj = new ilSearchResult($ilUser->getId());
            $old_result_obj->read(ADVANCED_MD_SEARCH);

            $res->diffEntriesFromResult($old_result_obj);
        }

        
        $res->filter($this->getRootNode(), true);
        $res->save();
        $this->showAdvMDSearch();
        
        if (!count($res->getResults())) {
            ilUtil::sendInfo($this->lng->txt('search_no_match'));
        }

        if ($res->isLimitReached()) {
            #$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
            #ilUtil::sendInfo($message);
        }

        $this->addPager($res, 'adv_max_page');
        
        include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
        $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
        $presentation->setResults($res->getResultsForPresentation());
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('RESULTS', $presentation->getHTML(true));
        }
        return true;
    }
    
    
    /**
     * Show advanced meta data search
     *
     * @access public
     */
    public function showAdvMDSearch()
    {
        if (isset($_SESSION['search_adv_md'])) {
            $this->options = $_SESSION['search_adv_md'];
        }
        $this->setSubTabs();
        $this->tabs_gui->setSubTabActive('search_adv_md');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.advanced_adv_search.html', 'Services/Search');

        $this->initAdvancedMetaDataForm();
        $this->tpl->setVariable('SEARCH_FORM', $this->form->getHTML());
        return true;
    }
    
    /**
     * Show search form
     */
    protected function initFormSearch()
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'performSearch'));
        $this->form->setTitle($this->lng->txt('search_advanced'));
        $this->form->addCommandButton('performSearch', $this->lng->txt('search'));
        $this->form->addCommandButton('reset', $this->lng->txt('reset'));
        
        foreach ($this->fields->getActiveSections() as $definition) {
            if ($definition['name'] != 'default') {
                $section = new ilFormSectionHeaderGUI();
                $section->setTitle($definition['name']);
                $this->form->addItem($section);
            }
            
            foreach ($definition['fields'] as $field_name) {
                if (is_object($element = $this->fields->getFormElement($this->search_cache->getQuery(), $field_name, $this->form))) {
                    $this->form->addItem($element);
                }
            }
        }
        return true;
    }
    


    public function showSearch()
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];

        $this->setSubTabs();
        $this->tabs_gui->setSubTabActive('search_lom');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.advanced_search.html', 'Services/Search');

        $this->initFormSearch();
        $this->tpl->setVariable('SEARCH_FORM', $this->form->getHTML());
        return true;
    }

    public function prepareOutput()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $ilHelp = $DIC['ilHelp'];
        
        parent::prepareOutput();
        
        $ilHelp->setScreenIdComponent("src");

        $ilTabs->addTab(
            "search",
            $this->lng->txt("search"),
            $this->ctrl->getLinkTargetByClass('ilsearchgui')
        );
        $ilTabs->addTab(
            "adv_search",
            $this->lng->txt("search_advanced"),
            $this->ctrl->getLinkTarget($this)
        );
        $ilTabs->activateTab("adv_search");
    }

    // PRIVATE
    /**
     * show advanced meta data results
     *
     * @access private
     *
     */
    private function showSavedAdvMDResults()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        // Read old result sets
        include_once 'Services/Search/classes/class.ilSearchResult.php';
    
        $this->initSearchType(self::TYPE_ADV_MD);
        $result_obj = new ilSearchResult($ilUser->getId());
        $result_obj->read(ADVANCED_MD_SEARCH);

        $this->showAdvMDSearch();

        // Show them
        if (count($result_obj->getResults())) {
            $this->addPager($result_obj, 'adv_max_page');

            include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
            $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
            $presentation->setResults($result_obj->getResultsForPresentation());
            $presentation->setPreviousNext($this->prev_link, $this->next_link);
            
            if ($presentation->render()) {
                $this->tpl->setVariable('RESULTS', $presentation->getHTML(true));
            }
        }

        return true;
    }
    
    
    public function showSavedResults()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        // Read old result sets
        include_once 'Services/Search/classes/class.ilSearchResult.php';

        $this->initSearchType(self::TYPE_LOM);
        $result_obj = new ilSearchResult($ilUser->getId());
        $result_obj->read(ADVANCED_SEARCH);

        $this->showSearch();

        // Show them
        if (count($result_obj->getResults())) {
            $this->addPager($result_obj, 'adv_max_page');

            include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
            $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
            $presentation->setResults($result_obj->getResultsForPresentation());
            $presentation->setPreviousNext($this->prev_link, $this->next_link);
            
            if ($presentation->render()) {
                $this->tpl->setVariable('RESULTS', $presentation->getHTML(true));
            }
        }

        return true;
    }

    public function &__performContentSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';
        include_once 'Services/Search/classes/class.ilSearchResult.php';

        if (!$this->options['lom_content']) {
            return false;
        }

        $res = new ilSearchResult();

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_content']));
        #$query_parser->setCombination($this->options['content_ao']);
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->parse();

        if ($this->options['type'] == 'all' or $this->options['type'] == 'lms') {
            // LM content search
            $lm_search = &ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
            $res_cont = &$lm_search->performSearch();
            $res->mergeEntries($res_cont);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'tst') {
            $tst_search = &ilObjectSearchFactory::_getTestSearchInstance($query_parser);
            $res_tes = &$tst_search->performSearch();
            $res->mergeEntries($res_tes);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'mep') {
            $med_search = &ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
            $res_med = &$med_search->performSearch();
            $res->mergeEntries($res_med);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'glo') {
            $glo_search = &ilObjectSearchFactory::_getGlossaryDefinitionSearchInstance($query_parser);
            $res_glo = &$glo_search->performSearch();
            $res->mergeEntries($res_glo);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'webr') {
            $web_search = &ilObjectSearchFactory::_getWebresourceSearchInstance($query_parser);
            $res_web = &$web_search->performSearch();
            $res->mergeEntries($res_web);
        }
        if ($tit_res = $this->__performTitleSearch()) {
            $res->mergeEntries($tit_res);
        }

        return $res;
    }


    public function &__performTitleSearch()
    {
        if (!$this->options['lom_content']) {
            return false;
        }

        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_content']));
        #$query_parser->setCombination($this->options['title_ao']);
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->parse();
        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('title_description');
        $meta_search->setOptions($this->options);
        $res_tit = &$meta_search->performSearch();
        
        $meta_search->setMode('keyword_all');
        $res_key = &$meta_search->performSearch();
        
        // merge them
        $res_tit->mergeEntries($res_key);
        
        
        return $res_tit;
    }



    public function &__performGeneralSearch()
    {
        if (!$this->options['lom_coverage'] and !$this->options['lom_structure']) {
            return false;
        }

        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        if ($this->options['lom_coverage']) {
            $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_coverage']));
            #$query_parser->setCombination($this->options['coverage_ao']);
            $query_parser->setCombination(QP_COMBINATION_OR);
            $query_parser->parse();
        } else {
            $query_parser = new ilQueryParser('');
        }
        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('general');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }

    public function &__performLifecycleSearch()
    {
        // Return if 'any'
        if (!$this->options['lom_status'] and !$this->options['lom_version']) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_version']));
        #$query_parser->setCombination($this->options['version_ao']);
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('lifecycle');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performLanguageSearch()
    {
        if (!$this->options['lom_language']) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('language');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performContributeSearch()
    {
        if (!strlen($this->options['lom_role'])) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('contribute');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performEntitySearch()
    {
        // Return if 'any'
        if (!$this->options['lom_role_entry']) {
            return false;
        }
        
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_role_entry']));
        #$query_parser->setCombination($this->options['entity_ao']);
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('entity');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }


    public function &__performRequirementSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('requirement');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performFormatSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('format');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performEducationalSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('educational');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performTypicalAgeRangeSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('typical_age_range');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    public function &__performRightsSearch()
    {
        if (!$this->options['lom_copyright'] and !$this->options['lom_costs']) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('rights');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }

    public function &__performClassificationSearch()
    {
        // Return if 'any'
        if (!$this->options['lom_purpose']) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';


        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('classification');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }

    public function &__performTaxonSearch()
    {
        // Return if 'any'
        if (!$this->options['lom_taxon']) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_taxon']));
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('taxon');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();

        return $res;
    }
    
    /**
     * Perform advanced meta data search
     *
     * @access private
     * @param obj result object
     *
     */
    private function searchAdvancedMD($res)
    {
        $this->initFormSearch();
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        foreach (array_keys($this->options) as $key) {
            if (substr($key, 0, 3) != 'adv') {
                continue;
            }
            
            // :TODO: ?
            if (!$key) {
                continue;
            }
                    
            $field_id = substr($key, 4);
            $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
        
            $field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($field->getADTDefinition(), true, false);
            $field_form->setElementId("query[" . $key . "]");
            $field_form->setForm($this->form);
        
            // reload search values
            $field_form->importFromPost($this->options);
            $field_form->validate();
                                                
            $parser_value = $field->getSearchQueryParserValue($field_form);
        
            include_once 'Services/Search/classes/class.ilQueryParser.php';
            include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
            $adv_md_search = ilObjectSearchFactory::_getAdvancedMDSearchInstance(new ilQueryParser($parser_value));
            $adv_md_search->setFilter($this->filter);
            $adv_md_search->setDefinition($field);
            $adv_md_search->setSearchElement($field_form);
            $res_field = $adv_md_search->performSearch();
            if ($res_field instanceof ilSearchResult) {
                $this->__storeEntries($res, $res_field);
            }
        }
    }

    public function &__performKeywordSearch()
    {
        // Return if 'any'
        if (!$this->options['lom_keyword']) {
            return false;
        }
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_keyword']));
        #$query_parser->setCombination($this->options['keyword_ao']);
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = &ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('keyword');
        $meta_search->setOptions($this->options);
        $res = &$meta_search->performSearch();
        
        return $res;
    }

    public function __setSearchOptions(&$post_vars)
    {
        if (isset($_POST['cmd']['performSearch'])) {
            $this->options = $_SESSION['search_adv'] = $_POST['query'];
        } elseif (isset($_POST['cmd']['performAdvMDSearch'])) {
            $this->options = $_SESSION['search_adv_md'] = $_POST;
        } else {
            $this->options = $_SESSION['search_adv'];
        }
        
        $_POST['result'] = $_POST['id'];

        $this->filter = array();

        $this->options['type'] = 'all';
        switch ($this->options['type']) {
            case 'cat':
                $this->filter[] = 'cat';
                break;
            
            case 'webr':
                $this->filter[] = 'webr';
                break;

            case 'lms':
                $this->filter[] = 'lm';
                $this->filter[] = 'dbk';
                $this->filter[] = 'pg';
                $this->filter[] = 'st';
                $this->filter[] = 'sahs';
                $this->filter[] = 'htlm';
                break;

            case 'glo':
                $this->filter[] = 'glo';
                break;

            case 'tst':
                $this->filter[] = 'tst';
                $this->filter[] = 'svy';
                $this->filter[] = 'qpl';
                $this->filter[] = 'spl';
                break;

            case 'mep':
                $this->filter[] = 'mep';
                break;
                    
            case 'crs':
                $this->filter[] = 'crs';
                break;
                
            case 'file':
                $this->filter[] = 'file';
                break;

            case 'adv_all':
                include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
                $this->filter = ilAdvancedMDRecord::_getActivatedObjTypes();
                break;

            case 'all':
            default:
                $this->filter[] = 'sess';
                $this->filter[] = 'webr';
                $this->filter[] = 'crs';
                $this->filter[] = 'mep';
                $this->filter[] = 'tst';
                $this->filter[] = 'svy';
                $this->filter[] = 'qpl';
                $this->filter[] = 'spl';
                $this->filter[] = 'glo';
                $this->filter[] = 'lm';
                $this->filter[] = 'dbk';
                $this->filter[] = 'pg';
                $this->filter[] = 'st';
                $this->filter[] = 'sahs';
                $this->filter[] = 'htlm';
                $this->filter[] = 'file';
        }
        return true;
    }

    public function __getFilterSelect()
    {
        $options = array('all' => $this->lng->txt('search_any'),
                         'crs' => $this->lng->txt('objs_crs'),
                         'lms' => $this->lng->txt('obj_lrss'),
                         'glo' => $this->lng->txt('objs_glo'),
                         'mep' => $this->lng->txt('objs_mep'),
                         'tst' => $this->lng->txt('search_tst_svy'),
                         'file' => $this->lng->txt('objs_file'),
                         'webr' => $this->lng->txt('objs_webr'),
                         'sess' => $this->lng->txt('objs_sess')
            );


        return ilUtil::formSelect($this->options['type'], 'search_adv[type]', $options, false, true);
    }


    public function __storeEntries($res, $new_res)
    {
        if ($this->stored == false) {
            $res->mergeEntries($new_res);
            $this->stored = true;

            return true;
        } else {
            $res->intersectEntries($new_res);
            return true;
        }
    }
    
    /**
     * Init user search cache
     *
     * @access private
     *
     */
    private function initUserSearchCache()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        include_once('Services/Search/classes/class.ilUserSearchCache.php');
        $this->search_cache = ilUserSearchCache::_getInstance($ilUser->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::ADVANCED_SEARCH);
        if ($_GET['page_number']) {
            $this->search_cache->setResultPageNumber((int) $_GET['page_number']);
        }
        if ($_POST['cmd']['performSearch']) {
            $this->search_cache->setQuery(ilUtil::stripSlashes($_POST['query']['lomContent']));
            $this->search_cache->save();
        }
    }
    
    /**
     * set sub tabs
     *
     * @access public
     *
     */
    public function setSubTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        if (!count(ilAdvancedMDFieldDefinition::getSearchableDefinitionIds())) {
            return true;
        }
        $ilTabs->addSubTabTarget('search_lom', $this->ctrl->getLinkTarget($this, 'showSavedResults'));
        #$ilTabs->addSubTabTarget('search_adv_md',$this->ctrl->getLinkTarget($this,'showSavedAdvMDResults'));
    }
    
    /**
     * convert input array to unix time
     *
     * @access private
     * @param
     *
     */
    private function toUnixTime($date, $time = array())
    {
        return mktime($time['h'], $time['m'], 0, $date['m'], $date['d'], $date['y']);
    }
    
    /**
     * init search type (LOM Search or Advanced meta data search)
     *
     * @access private
     * @param
     *
     */
    private function initSearchType($type)
    {
        if ($type == self::TYPE_LOM) {
            $_SESSION['search_last_sub_section'] = self::TYPE_LOM;
            $this->search_cache->switchSearchType(ilUserSearchCache::ADVANCED_SEARCH);
        } else {
            $_SESSION['search_last_sub_section'] = self::TYPE_ADV_MD;
            $this->search_cache->switchSearchType(ilUserSearchCache::ADVANCED_MD_SEARCH);
        }
    }
}
