<?php

declare(strict_types=1);

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
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ilCtrl_Calls ilSearchGUI: ilPropertyFormGUI
* @ilCtrl_Calls ilSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI
*
* @ingroup	ServicesSearch
*/
class ilSearchGUI extends ilSearchBaseGUI
{
    private array $details;
    public int $root_node;
    public string $combination;
    public string $string;
    public int $type;

    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help_gui;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->tabs_gui = $DIC->tabs();
        $this->help_gui = $DIC->help();
        $this->lng->loadLanguageModule("search");

        $post_search = (array) ($this->http->request()->getParsedBody()['search'] ?? []);
        $post_filter_type = (array) ($this->http->request()->getParsedBody()['filter_type'] ?? []);
        $post_cmd = (array) ($this->http->request()->getParsedBody()['cmd'] ?? []);

        // put form values into "old" post variables
        $this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_STANDARD);
        $this->form->checkInput();

        $new_search = (bool) ($post_cmd['performSearch'] ?? false);
        $enabled_types = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
        foreach ($enabled_types as $type => $pval) {
            if (isset($post_filter_type[$type]) && $post_filter_type[$type] == 1) {
                $post_search["details"][$type] = $post_filter_type[$type];
            }
        }
        $post_term = '';
        if ($this->http->wrapper()->post()->has('term')) {
            $post_term = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        $post_combination = '';
        if ($this->http->wrapper()->post()->has('combination')) {
            $post_combination = $this->http->wrapper()->post()->retrieve(
                'combination',
                $this->refinery->kindlyTo()->string()
            );
        }
        $post_type = 0;
        if ($this->http->wrapper()->post()->has('type')) {
            $post_type = $this->http->wrapper()->post()->retrieve(
                'type',
                $this->refinery->kindlyTo()->int()
            );
        }
        $post_area = 0;
        if ($this->http->wrapper()->post()->has('area')) {
            $post_area = $this->http->wrapper()->post()->retrieve(
                'area',
                $this->refinery->kindlyTo()->int()
            );
        }
        $post_search["string"] = $post_term;
        $post_search["combination"] = $post_combination;
        $post_search["type"] = $post_type;
        ilSession::set('search_root', (string) $post_area);

        $this->root_node = (int) (ilSession::get('search_root') ?? ROOT_FOLDER_ID);

        $session_search = ilSession::get('search') ?? [];
        $this->setType((int) ($post_search['type'] ?? ($session_search['type'] ?? ilSearchBaseGUI::SEARCH_FAST)));

        $this->setCombination(
            ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ?
                self::SEARCH_AND :
                self::SEARCH_OR
        );
        $this->setString((string) ($post_search['string'] ?? ($session_search['string'] ?? '')));
        $this->setDetails($new_search ? $post_search['details'] : ($session_search['details'] ?? []));
    }


    /**
    * Control
    * @access public
    */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilpropertyformgui":
                //$this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_STANDARD);
                $form = $this->getSearchAreaForm();
                $this->prepareOutput();
                $this->ctrl->setReturn($this, 'storeRoot');
                $this->ctrl->forwardCommand($form);
                return;

            case 'ilobjectcopygui':
                $this->prepareOutput();
                $this->ctrl->setReturn($this, '');
                $cp = new ilObjectCopyGUI($this);
                $this->ctrl->forwardCommand($cp);
                break;

            default:
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->prepareOutput();
                $this->handleCommand($cmd);
                break;
        }
    }

    /**
    * Set/get type of search (detail or 'fast' search)
    * @access public
    */
    public function setType(int $a_type): void
    {
        $session_search = ilSession::get('saerch');
        $session_search['type'] = $this->type = $a_type;
        ilSession::set('search', $session_search);
    }

    public function getType(): int
    {
        return $this->type ?? ilSearchBaseGUI::SEARCH_FAST;
    }
    /**
    * Set/get combination of search ('and' or 'or')
    * @access public
    */
    public function setCombination(string $a_combination): void
    {
        $session_search = ilSession::get('search') ?? [];
        $session_search['combination'] = $this->combination = $a_combination;
        ilSession::set('search', $session_search);
    }
    public function getCombination(): string
    {
        return $this->combination ?: ilSearchBaseGUI::SEARCH_OR;
    }
    /**
    * Set/get search string
    * @access public
    */
    public function setString(string $a_str): void
    {
        $session_search = ilSession::get('search') ?? [];
        $session_search['string'] = $this->string = $a_str;
        ilSession::set('search', $session_search);
    }
    public function getString(): string
    {
        return $this->string;
    }
    /**
    * Set/get details (object types for details search)
    * @access public
    */
    public function setDetails(array $a_details): void
    {
        $session_search = ilSession::get('search') ?? [];
        $session_search['details'] = $this->details = $a_details;
        ilSession::set('search', $session_search);
    }
    public function getDetails(): array
    {
        return $this->details ?? [];
    }


    public function getRootNode(): int
    {
        return $this->root_node ?: ROOT_FOLDER_ID;
    }

    public function setRootNode(int $a_node_id): void
    {
        ilSession::set('search_root', $this->root_node = $a_node_id);
    }


    public function remoteSearch(): void
    {
        $root_id = 0;
        if ($this->http->wrapper()->post()->has('root_id')) {
            $root_id = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $queryString = '';
        if ($this->http->wrapper()->post()->has('queryString')) {
            $queryString = $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->setString($queryString);
        $this->setRootNode($root_id);
        $this->performSearch();
    }

    /**
     * Store new root node
     */
    protected function storeRoot(): void
    {
        $form = $this->getSearchAreaForm();

        $this->root_node = $form->getItemByPostVar('area')->getValue();
        $this->search_cache->setRoot($this->root_node);
        $this->search_cache->save();
        $this->search_cache->deleteCachedEntries();
        ilSubItemListGUI::resetDetails();
        $this->performSearch();
    }

    /**
    * Data resource for autoComplete
    */
    public function autoComplete(): void
    {
        $query = '';
        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        $search_type = 0;
        if ($this->http->wrapper()->post()->has('search_type')) {
            $search_type = $this->http->wrapper()->post()->retrieve(
                'search_type',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ((int) $search_type === -1) {
            $a_fields = array('login','firstname','lastname','email');
            $result_field = 'login';

            // Starting user autocomplete search
            $auto = new ilUserAutoComplete();


            $auto->setMoreLinkAvailable(true);
            $auto->setSearchFields($a_fields);
            $auto->setResultField($result_field);
            $auto->enableFieldSearchableCheck(true);
            $auto->setUserLimitations(true);

            $res = $auto->getList($query);
            $res_obj = json_decode($res);

            ilLoggerFactory::getLogger('sea')->debug($res);


            ilLoggerFactory::getLogger('sea')->dump($res_obj->items, ilLogLevel::DEBUG);
            if (is_array($res_obj->items)) {
                echo json_encode($res_obj->items);
                exit;
            }
        } else {
            $list = ilSearchAutoComplete::getList($query);
            ilLoggerFactory::getLogger('sea')->dump(json_decode($list));
            echo $list;
            exit;
        }
    }

    public function showSearch(): void
    {
        ilOverlayGUI::initJavascript();
        $this->tpl->addJavascript("./Services/Search/js/Search.js");


        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.search.html', 'Services/Search');
        $this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, 'performSearch'));
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($this->getString()));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
        $this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
        $this->tpl->setVariable("ARR_IMG", ilGlyphGUI::get(ilGlyphGUI::CARET));
        $this->tpl->setVariable("TXT_COMBINATION", $this->lng->txt("search_term_combination"));
        $this->tpl->setVariable('TXT_COMBINATION_DEFAULT', ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ? $this->lng->txt('search_all_words') : $this->lng->txt('search_any_word'));

        if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            $this->tpl->setCurrentBlock("type_sel");
            $this->tpl->setVariable('TXT_TYPE_DEFAULT', $this->lng->txt("search_fast_info"));
            $this->tpl->setVariable("TXT_TYPE", $this->lng->txt("search_type"));
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


        $this->tpl->setVariable("TXT_AREA", $this->lng->txt("search_area"));

        // search area form
        $this->tpl->setVariable('SEARCH_AREA_FORM', $this->getSearchAreaForm()->getHTML());
    }

    public function showSavedResults(): void
    {

        // Read old result sets

        $result_obj = new ilSearchResult($this->user->getId());
        $result_obj->read();
        $result_obj->filterResults($this->getRootNode());

        $this->showSearch();

        // Show them
        if (count($result_obj->getResults())) {
            $this->addPager($result_obj, 'max_page');

            $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
            $presentation->setResults($result_obj->getResultsForPresentation());
            $presentation->setSubitemIds($result_obj->getSubitemIds());
            $presentation->setPreviousNext($this->prev_link, $this->next_link);
            #$presentation->setSearcher($searcher);

            if ($presentation->render()) {
                //				$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
                $this->tpl->setVariable('RESULTS_TABLE', $presentation->getHTML());
            }
        }
    }

    /**
     * Perform search
     */
    public function performSearch(): bool
    {
        $page_number = $this->initPageNumberFromQuery();
        if (!$page_number and $this->search_mode != 'in_results') {
            ilSession::clear('max_page');
            $this->search_cache->deleteCachedEntries();
        }

        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS and !$this->getDetails()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_choose_object_type'));
            $this->showSearch();
            return false;
        }

        // Step 1: parse query string
        if (!is_object($query_parser = $this->__parseQueryString())) {
            $this->tpl->setOnScreenMessage('info', $query_parser);
            $this->showSearch();

            return false;
        }
        // Step 2: perform object search. Get an ObjectSearch object via factory. Depends on fulltext or like search type.
        $result = $this->__searchObjects($query_parser);

        // Step 3: perform meta keyword search. Get an MetaDataSearch object.
        $result_meta = $this->__searchMeta($query_parser, 'keyword');
        $result->mergeEntries($result_meta);

        $result_meta = $this->__searchMeta($query_parser, 'contribute');
        $result->mergeEntries($result_meta);

        $result_meta = $this->__searchMeta($query_parser, 'title');
        $result->mergeEntries($result_meta);

        $result_meta = $this->__searchMeta($query_parser, 'description');
        $result->mergeEntries($result_meta);

        // Perform details search in object specific tables
        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS) {
            $result = $this->__performDetailsSearch($query_parser, $result);
        }
        // Step 5: Search in results
        if ($this->search_mode == 'in_results') {
            $old_result_obj = new ilSearchResult($this->user->getId());
            $old_result_obj->read();

            $result->diffEntriesFromResult();
        }


        // Step 4: merge and validate results
        $result->filter(
            $this->getRootNode(),
            ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND
        );
        $result->save();
        $this->showSearch();

        if (!count($result->getResults())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_no_match'));
        }

        if ($result->isLimitReached()) {
            #$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
            #ilUtil::sendInfo($message);
        }

        // Step 6: show results
        $this->addPager($result, 'max_page');

        $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
        $presentation->setResults($result->getResultsForPresentation());
        $presentation->setSubitemIds($result->getSubitemIds());
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('RESULTS_TABLE', $presentation->getHTML());
        }
        return true;
    }



    public function prepareOutput(): void
    {
        parent::prepareOutput();

        $this->help_gui->setScreenIdComponent("src");

        $this->tabs_gui->addTab(
            "search",
            $this->lng->txt("search"),
            $this->ctrl->getLinkTarget($this)
        );

        if (!$this->settings->getHideAdvancedSearch()) {
            $this->tabs_gui->addTab(
                "adv_search",
                $this->lng->txt("search_advanced"),
                $this->ctrl->getLinkTargetByClass('iladvancedsearchgui')
            );
        }

        $this->tabs_gui->activateTab("search");
    }

    protected function __performDetailsSearch(ilQueryParser $query_parser, ilSearchResult $result): ilSearchResult
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
                    $content_search = ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
                    $content_search->setFilter($this->__getFilter());
                    $result->mergeEntries($content_search->performSearch());
                    break;

                case 'frm':
                    $forum_search = ilObjectSearchFactory::_getForumSearchInstance($query_parser);
                    $forum_search->setFilter($this->__getFilter());
                    $result->mergeEntries($forum_search->performSearch());
                    break;

                case 'glo':
                    // Glossary term definition pages
                    $gdf_search = ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
                    $gdf_search->setFilter(array('gdf'));
                    $result->mergeEntries($gdf_search->performSearch());
                    // Glossary terms
                    $gdf_term_search = ilObjectSearchFactory::_getGlossaryDefinitionSearchInstance($query_parser);
                    $result->mergeEntries($gdf_term_search->performSearch());
                    break;

                case 'exc':
                    $exc_search = ilObjectSearchFactory::_getExerciseSearchInstance($query_parser);
                    $exc_search->setFilter($this->__getFilter());
                    $result->mergeEntries($exc_search->performSearch());
                    break;

                case 'mcst':
                    $mcst_search = ilObjectSearchFactory::_getMediacastSearchInstance($query_parser);
                    $result->mergeEntries($mcst_search->performSearch());
                    break;

                case 'tst':
                    $tst_search = ilObjectSearchFactory::_getTestSearchInstance($query_parser);
                    $tst_search->setFilter($this->__getFilter());
                    $result->mergeEntries($tst_search->performSearch());
                    break;

                case 'mep':
                    $mep_search = ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
                    $mep_search->setFilter($this->__getFilter());
                    $result->mergeEntries($mep_search->performSearch());

                    // Mob keyword search
                    $mob_search = ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
                    $mob_search->setFilter($this->__getFilter());
                    $result->mergeEntries($mob_search->performKeywordSearch());

                    break;

                case 'wiki':
                    $wiki_search = ilObjectSearchFactory::_getWikiContentSearchInstance($query_parser);
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
     * @return ilQueryParser | string
     */
    public function __parseQueryString()
    {
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
    */
    public function __searchObjects(ilQueryParser $query_parser): ilSearchResult
    {
        $obj_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS) {
            $obj_search->setFilter($this->__getFilter());
        }
        $this->parseCreationFilter($obj_search);
        return $obj_search->performSearch();
    }

    public function parseCreationFilter(ilObjectSearch $search): bool
    {
        $options = $this->getSearchCache()->getCreationFilter();
        if (!($options['enabled'] ?? false)) {
            return true;
        }
        $limit = new ilDate($options['date'] ?? 0, IL_CAL_UNIX);
        $search->setCreationDateFilterDate($limit);

        switch ($options['ontype'] ?? 0) {
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
    * @return ilSearchResult result object
    * @access public
    */
    public function __searchMeta(ilQueryParser $query_parser, string $a_type): ilSearchResult
    {
        $meta_search = ilObjectSearchFactory::_getMetaDataSearchInstance($query_parser);
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
     * @return string[]
    */
    public function __getFilter(): array
    {
        if ($this->getType() != ilSearchBaseGUI::SEARCH_DETAILS) {
            return [];
        }

        $filter = [];
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
        return $filter;
    }
}
