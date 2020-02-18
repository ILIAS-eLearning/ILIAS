<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilSearchBaseGUI.php';



/**
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilSearchGUI: ilPropertyFormGUI
* @ilCtrl_Calls ilSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilSearchGUI: ilObjRootFolderGUIGUI, ilObjectCopyGUI
*
* @ingroup	ServicesSearch
*/
class ilSearchGUI extends ilSearchBaseGUI
{
    protected $search_cache = null;
    
    public $root_node;
    public $combination;
    public $string;
    public $type;

    
    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("search");
        
        // put form values into "old" post variables
        $this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_STANDARD);
        $this->form->checkInput();
        
        $new_search = isset($_POST['cmd']['performSearch']) ? true : false;

        $enabled_types = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
        foreach ($enabled_types as $type => $pval) {
            if ($_POST['filter_type'][$type] == 1) {
                $_POST["search"]["details"][$type] = $_POST['filter_type'][$type];
            }
        }

        $_POST["search"]["string"] = $_POST["term"];
        $_POST["search"]["combination"] = $_POST["combination"];
        $_POST["search"]["type"] = $_POST["type"];
        $_SESSION['search_root'] = $_POST["area"];

        $this->root_node = $_SESSION['search_root'] ? $_SESSION['search_root'] : ROOT_FOLDER_ID;
        $this->setType($_POST['search']['type'] ? $_POST['search']['type'] : $_SESSION['search']['type']);
        $this->setCombination($_POST['search']['combination'] ? $_POST['search']['combination'] : $_SESSION['search']['combination']);
        $this->setString($_POST['search']['string'] ? $_POST['search']['string'] : $_SESSION['search']['string']);
        #$this->setDetails($_POST['search']['details'] ? $_POST['search']['details'] : $_SESSION['search']['details']);
        $this->setDetails($new_search ? $_POST['search']['details'] : $_SESSION['search']['details']);
        parent::__construct();
    }


    /**
    * Control
    * @access public
    */
    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilCtrl = $DIC['ilCtrl'];
        


        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilpropertyformgui":
                //$this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_STANDARD);
                $form = $this->getSearchAreaForm();
                $this->prepareOutput();
                $ilCtrl->setReturn($this, 'storeRoot');
                return $ilCtrl->forwardCommand($form);
                
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
                    $cmd = "showSavedResults";
                }
                $this->prepareOutput();
                $this->handleCommand($cmd);
                break;
        }
        return true;
    }

    /**
    * Set/get type of search (detail or 'fast' search)
    * @access public
    */
    public function setType($a_type)
    {
        $_SESSION['search']['type'] = $this->type = $a_type;
    }
    public function getType()
    {
        return $this->type ? $this->type : ilSearchBaseGUI::SEARCH_FAST;
    }
    /**
    * Set/get combination of search ('and' or 'or')
    * @access public
    */
    public function setCombination($a_combination)
    {
        $_SESSION['search']['combination'] = $this->combination = $a_combination;
    }
    public function getCombination()
    {
        return $this->combination ? $this->combination : ilSearchBaseGUI::SEARCH_OR;
    }
    /**
    * Set/get search string
    * @access public
    */
    public function setString($a_str)
    {
        $_SESSION['search']['string'] = $this->string = $a_str;
    }
    public function getString()
    {
        return $this->string;
    }
    /**
    * Set/get details (object types for details search)
    * @access public
    */
    public function setDetails($a_details)
    {
        $_SESSION['search']['details'] = $this->details = $a_details;
    }
    public function getDetails()
    {
        return $this->details ? $this->details : array();
    }

        
    public function getRootNode()
    {
        return $this->root_node ? $this->root_node : ROOT_FOLDER_ID;
    }
    public function setRootNode($a_node_id)
    {
        $_SESSION['search_root'] = $this->root_node = $a_node_id;
    }
        
    
    public function remoteSearch()
    {
        $this->setString(ilUtil::stripSlashes($_POST['queryString']));
        $this->setRootNode((int) $_POST['root_id']);
        $this->performSearch();
    }
    
    /**
     * Store new root node
     */
    protected function storeRoot()
    {
        $form = $this->getSearchAreaForm();

        $this->root_node = $form->getItemByPostVar('area')->getValue();
        $this->search_cache->setRoot($this->root_node);
        $this->search_cache->save();
        $this->search_cache->deleteCachedEntries();

        include_once './Services/Object/classes/class.ilSubItemListGUI.php';
        ilSubItemListGUI::resetDetails();

        $this->performSearch();
    }

    /**
    * Data resource for autoComplete
    */
    public function autoComplete()
    {
        if ((int) $_REQUEST['search_type'] == -1) {
            $a_fields = array('login','firstname','lastname','email');
            $result_field = 'login';

            // Starting user autocomplete search
            include_once './Services/User/classes/class.ilUserAutoComplete.php';
            $auto = new ilUserAutoComplete();


            $auto->setMoreLinkAvailable(true);
            $auto->setSearchFields($a_fields);
            $auto->setResultField($result_field);
            $auto->enableFieldSearchableCheck(true);
            $auto->setUserLimitations(true);

            $res = $auto->getList($_REQUEST['term']);
            
            $res_obj = json_decode($res);
            
            ilLoggerFactory::getLogger('sea')->debug($res);
            
            
            ilLoggerFactory::getLogger('sea')->dump($res_obj->items, ilLogLevel::DEBUG);
            if (is_array($res_obj->items)) {
                echo json_encode($res_obj->items);
                exit;
            }
        } else {
            $q = $_REQUEST["term"];
            include_once("./Services/Search/classes/class.ilSearchAutoComplete.php");
            $list = ilSearchAutoComplete::getList($q);
            ilLoggerFactory::getLogger('sea')->dump(json_decode($list));
            echo $list;
            exit;
        }
    }
    
    public function showSearch()
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        // include js needed
        include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
        ilOverlayGUI::initJavascript();
        $this->tpl->addJavascript("./Services/Search/js/Search.js");

        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.search.html', 'Services/Search');
        $this->tpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this, 'performSearch'));
        $this->tpl->setVariable("TERM", ilUtil::prepareFormOutput($this->getString()));
        include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
        $this->tpl->setVariable("TXT_OPTIONS", $lng->txt("options"));
        $this->tpl->setVariable("ARR_IMG", ilGlyphGUI::get(ilGlyphGUI::CARET));
        $this->tpl->setVariable("TXT_COMBINATION", $lng->txt("search_term_combination"));
        $this->tpl->setVariable('TXT_COMBINATION_DEFAULT', ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ? $lng->txt('search_all_words') : $lng->txt('search_any_word'));

        if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            $this->tpl->setCurrentBlock("type_sel");
            $this->tpl->setVariable('TXT_TYPE_DEFAULT', $lng->txt("search_fast_info"));
            $this->tpl->setVariable("TXT_TYPE", $lng->txt("search_type"));
            $this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_STANDARD);
            $this->tpl->setVariable("ARR_IMGT", ilGlyphGUI::get(ilGlyphGUI::CARET));
            $this->tpl->setVariable("FORM", $this->form->getHTML());
            $this->tpl->parseCurrentBlock();
        }
        
        if (ilSearchSettings::getInstance()->isDateFilterEnabled()) {
            // begin-patch creation_date
            $this->tpl->setVariable('TXT_FILTER_BY_CDATE', $this->lng->txt('search_filter_cd'));
            $this->tpl->setVariable('TXT_CD_OFF', $this->lng->txt('search_off'));
            $this->tpl->setVariable('FORM_CD', $this->getCreationDateForm()->getHTML());
            $this->tpl->setVariable("ARR_IMG_CD", ilGlyphGUI::get(ilGlyphGUI::CARET));
            // end-patch creation_date
        }
        

        $this->tpl->setVariable("TXT_AREA", $lng->txt("search_area"));

        // search area form
        $this->tpl->setVariable('SEARCH_AREA_FORM', $this->getSearchAreaForm()->getHTML());

        return true;
    }

    public function showSavedResults()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        // Read old result sets
        include_once 'Services/Search/classes/class.ilSearchResult.php';
    
        $result_obj = new ilSearchResult($ilUser->getId());
        $result_obj->read();
        $result_obj->filterResults($this->getRootNode());

        $this->showSearch();

        // Show them
        if (count($result_obj->getResults())) {
            $this->addPager($result_obj, 'max_page');

            include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
            $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
            $presentation->setResults($result_obj->getResultsForPresentation());
            $presentation->setSubitemIds($result_obj->getSubitemIds());
            $presentation->setPreviousNext($this->prev_link, $this->next_link);
            #$presentation->setSearcher($searcher);

            if ($presentation->render()) {
                //				$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
                $this->tpl->setVariable('RESULTS_TABLE', $presentation->getHTML(true));
            }
        }

        return true;
    }
        

    /**
     * Perform search
     */
    public function performSearch()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if (!isset($_GET['page_number']) and $this->search_mode != 'in_results') {
            unset($_SESSION['max_page']);
            $this->search_cache->deleteCachedEntries();
        }

        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS and !$this->getDetails()) {
            ilUtil::sendInfo($this->lng->txt('search_choose_object_type'));
            $this->showSearch();
            return false;
        }

        // Step 1: parse query string
        if (!is_object($query_parser =&$this->__parseQueryString())) {
            ilUtil::sendInfo($query_parser);
            $this->showSearch();
            
            return false;
        }
        // Step 2: perform object search. Get an ObjectSearch object via factory. Depends on fulltext or like search type.
        $result =&$this->__searchObjects($query_parser);

        // Step 3: perform meta keyword search. Get an MetaDataSearch object.
        $result_meta =&$this->__searchMeta($query_parser, 'keyword');
        $result->mergeEntries($result_meta);

        $result_meta =&$this->__searchMeta($query_parser, 'contribute');
        $result->mergeEntries($result_meta);
    
        $result_meta =&$this->__searchMeta($query_parser, 'title');
        $result->mergeEntries($result_meta);
    
        $result_meta =&$this->__searchMeta($query_parser, 'description');
        $result->mergeEntries($result_meta);
    
        // Perform details search in object specific tables
        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS) {
            $result = $this->__performDetailsSearch($query_parser, $result);
        }
        // Step 5: Search in results
        if ($this->search_mode == 'in_results') {
            include_once 'Services/Search/classes/class.ilSearchResult.php';

            $old_result_obj = new ilSearchResult($ilUser->getId());
            $old_result_obj->read();

            $result->diffEntriesFromResult($old_result_obj);
        }
            

        // Step 4: merge and validate results
        $result->filter($this->getRootNode(), $query_parser->getCombination() == 'and');
        $result->save();
        $this->showSearch();

        if (!count($result->getResults())) {
            ilUtil::sendInfo($this->lng->txt('search_no_match'));
        }

        if ($result->isLimitReached()) {
            #$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
            #ilUtil::sendInfo($message);
        }

        // Step 6: show results
        $this->addPager($result, 'max_page');
        
        include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
        $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
        $presentation->setResults($result->getResultsForPresentation());
        $presentation->setSubitemIds($result->getSubitemIds());
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            //			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
            $this->tpl->setVariable('RESULTS_TABLE', $presentation->getHTML(true));
        }

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
            $this->ctrl->getLinkTarget($this)
        );
        
        if (!$this->settings->getHideAdvancedSearch()) {
            $ilTabs->addTab(
                "adv_search",
                $this->lng->txt("search_advanced"),
                $this->ctrl->getLinkTargetByClass('iladvancedsearchgui')
            );
        }
        
        $ilTabs->activateTab("search");
    }

    // PRIVATE
    public function &__performDetailsSearch(&$query_parser, &$result)
    {
        foreach ($this->getDetails() as $type => $enabled) {
            if (!$enabled) {
                continue;
            }

            switch ($type) {
                case 'crs':
                    $crs_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
                    $crs_search->setFilter(array('crs'));
                    $result->mergeEntries($crs_search->performSearch());
                    break;
                
                case 'grp':
                    $grp_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
                    $grp_search->setFilter(array('grp'));
                    $result->mergeEntries($grp_search->performSearch());
                    break;
                
                case 'lms':
                    $content_search =&ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
                    $content_search->setFilter($this->__getFilter());
                    $result->mergeEntries($content_search->performSearch());
                    break;

                case 'frm':
                    $forum_search =&ilObjectSearchFactory::_getForumSearchInstance($query_parser);
                    $forum_search->setFilter($this->__getFilter());
                    $result->mergeEntries($forum_search->performSearch());
                    break;

                case 'glo':
                    // Glossary term definition pages
                    $gdf_search =&ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
                    $gdf_search->setFilter(array('gdf'));
                    $result->mergeEntries($gdf_search->performSearch());
                    // Glossary terms
                    $gdf_term_search =&ilObjectSearchFactory::_getGlossaryDefinitionSearchInstance($query_parser);
                    $result->mergeEntries($gdf_term_search->performSearch());
                    break;

                case 'exc':
                    $exc_search =&ilObjectSearchFactory::_getExerciseSearchInstance($query_parser);
                    $exc_search->setFilter($this->__getFilter());
                    $result->mergeEntries($exc_search->performSearch());
                    break;

                case 'mcst':
                    $mcst_search =&ilObjectSearchFactory::_getMediaCastSearchInstance($query_parser);
                    $result->mergeEntries($mcst_search->performSearch());
                    break;

                case 'tst':
                    $tst_search =&ilObjectSearchFactory::_getTestSearchInstance($query_parser);
                    $tst_search->setFilter($this->__getFilter());
                    $result->mergeEntries($tst_search->performSearch());
                    break;

                case 'mep':
                    $mep_search =&ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
                    $mep_search->setFilter($this->__getFilter());
                    $result->mergeEntries($mep_search->performSearch());
                    
                    // Mob keyword search
                    $mob_search = ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
                    $mob_search->setFilter($this->__getFilter());
                    $result->mergeEntries($mob_search->performKeywordSearch());
                    
                    break;

                case 'wiki':
                    $wiki_search =&ilObjectSearchFactory::_getWikiContentSearchInstance($query_parser);
                    $wiki_search->setFilter($this->__getFilter());
                    $result->mergeEntries($wiki_search->performSearch());

                    /*$result_meta =& $this->__searchMeta($query_parser,'title');
                    $result->mergeEntries($result_meta);
                    $result_meta =& $this->__searchMeta($query_parser,'description');
                    $result->mergeEntries($result_meta);*/
                    break;

            }
        }
        return $result;
    }

    /**
    * parse query string, using query parser instance
    * @return object of query parser or error message if an error occured
    * @access public
    */
    public function &__parseQueryString()
    {
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->getString()));
        $query_parser->setCombination($this->getCombination());
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }
    /**
    * Search in obect title,desctiption
    * @return object result object
    * @access public
    */
    public function &__searchObjects(&$query_parser)
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

        $obj_search =&ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS) {
            $obj_search->setFilter($this->__getFilter());
        }
        
        $this->parseCreationFilter($obj_search);
        return $obj_search->performSearch();
    }
    
    public function parseCreationFilter(ilObjectSearch $search)
    {
        $options = $this->getSearchCache()->getCreationFilter();
        
        if (!$options['enabled']) {
            return true;
        }
        $limit = new ilDate($options['date'], IL_CAL_UNIX);
        $search->setCreationDateFilterDate($limit);
        
        switch ($options['ontype']) {
            case 1:
                $search->setCreationDateFilterOperator(ilObjectSearch::CDATE_OPERATOR_AFTER);
                break;
                
            case 2:
                $search->setCreationDateFilterOperator(ilObjectSearch::CDATE_OPERATOR_BEFORE);
                break;
                
            case 3:
                $search->setCreationDateFilterOperator(ilObjectSearch::CDATE_OPERATOR_ON);
                break;
        }
        
        return true;
    }


    /**
    * Search in object meta data (keyword)
    * @return object result object
    * @access public
    */
    public function &__searchMeta(&$query_parser, $a_type)
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

        $meta_search =&ilObjectSearchFactory::_getMetaDataSearchInstance($query_parser);
        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS) {
            $meta_search->setFilter($this->__getFilter());
        }
        switch ($a_type) {
            case 'keyword':
                $meta_search->setMode('keyword');
                break;

            case 'contribute':
                $meta_search->setMode('contribute');
                break;

            case 'title':
                $meta_search->setMode('title');
                break;

            case 'description':
                $meta_search->setMode('description');
                break;
        }
        return $meta_search->performSearch();
    }
    /**
    * Get object type for filter (If detail search is enabled)
    * @return array object types
    * @access public
    */
    public function __getFilter()
    {
        if ($this->getType() != ilSearchBaseGUI::SEARCH_DETAILS) {
            return false;
        }
        
        foreach ($this->getDetails() as $key => $detail_type) {
            if (!$detail_type) {
                continue;
            }
            
            switch ($key) {
                case 'lms':
                    $filter[] = 'lm';
                    $filter[] = 'dbk';
                    $filter[] = 'pg';
                    $filter[] = 'st';
                    $filter[] = 'sahs';
                    $filter[] = 'htlm';
                    break;

                case 'frm':
                    $filter[] = 'frm';
                    break;

                case 'glo':
                    $filter[] = 'glo';
                    break;

                case 'exc':
                    $filter[] = 'exc';
                    break;

                case 'mcst':
                    $filter[] = 'mcst';
                    break;

                case 'tst':
                    $filter[] = 'tst';
                    $filter[] = 'svy';
                    $filter[] = 'qpl';
                    $filter[] = 'spl';
                    break;

                case 'mep':
                    $filter[] = 'mep';
                    $filter[] = 'mob';
                    break;

                case 'fil':
                    $filter[] = 'file';
                    break;
                    
                case 'wiki':
                    $filter[] = 'wpg';
                    break;
                
                default:
                    $filter[] = $key;
            }
        }
        return $filter ? $filter : array();
    }

    /**
     * Init user search cache
     *
     * @access private
     *
     */
    protected function initUserSearchCache()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        include_once('Services/Search/classes/class.ilUserSearchCache.php');
        $this->search_cache = ilUserSearchCache::_getInstance($ilUser->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::DEFAULT_SEARCH);
        
        if ($_GET['page_number']) {
            $this->search_cache->setResultPageNumber((int) $_GET['page_number']);
        }
        if (isset($_POST['cmd']['performSearch'])) {
            $this->search_cache->setQuery(ilUtil::stripSlashes($_POST['term']));
            $this->search_cache->setCreationFilter($this->loadCreationFilter());
            $this->search_cache->save();
        }
    }
}
