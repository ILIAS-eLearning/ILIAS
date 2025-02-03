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

declare(strict_types=1);

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ilCtrl_Calls ilSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilSearchGUI: ilObjStudyProgrammeGUI
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
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

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

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->initFilter(self::SEARCH_FORM_STANDARD);


        $requested_search = (array) ($this->http->request()->getParsedBody()['search'] ?? []);

        if ($this->http->wrapper()->post()->has('cmd')) {
            $requested_cmd = (array) $this->http->wrapper()->post()->retrieve('cmd', $this->getStringArrayTransformation());
        } elseif ($this->http->wrapper()->query()->has('cmd')) {
            $requested_cmd = (array) $this->http->wrapper()->query()->retrieve(
                'cmd',
                $this->refinery->kindlyTo()->string()
            );
            $requested_cmd = [$requested_cmd[0] => "Search"];
        } else {
            $requested_cmd = [];
        }
        $new_search = (bool) ($requested_cmd["performSearch"] ?? false);
        $new_filter = (bool) ($requested_cmd["performSearchFilter"] ?? false);
        $new_search_or_filter = $new_search || $new_filter;

        $requested_filter_type = (array) ($this->search_filter_data["search_type"] ?? []);
        $requested_filter_type = array_flip($requested_filter_type);
        $requested_filter_type = array_fill_keys(array_keys($requested_filter_type), "1");
        $enabled_types = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
        foreach ($enabled_types as $type => $pval) {
            if (isset($requested_filter_type[$type])) {
                $requested_search["details"][$type] = $requested_filter_type[$type];
            }
        }

        // Search term input field and filter are handled separately, see README (Filtering behaviour)
        $post_term = $this->http->wrapper()->post()->retrieve(
            'term',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
        $filter_type_active = (is_null($this->search_filter_data["search_type"] ?? null))
            ? self::SEARCH_FAST
            : self::SEARCH_DETAILS;
        $filter_scope = $this->search_filter_data["search_scope"] ?? ROOT_FOLDER_ID;

        if ($new_filter) {
            ilSession::set('search_root', $filter_scope);
        }

        $requested_search["string"] = $post_term;
        $requested_search["type"] = $filter_type_active;

        $this->root_node = (int) (ilSession::get('search_root') ?? ROOT_FOLDER_ID);

        $session_search = ilSession::get('search') ?? [];
        $this->setType((int) ($requested_search['type'] ?? ($session_search['type'] ?? self::SEARCH_FAST)));

        $this->setCombination(
            ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ?
                self::SEARCH_AND :
                self::SEARCH_OR
        );
        $this->setString((string) ($requested_search['string'] ?? ($session_search['string'] ?? '')));
        $this->setDetails(
            $new_search_or_filter ?
                ($requested_search['details'] ?? []) :
                ($session_search['details'] ?? [])
        );

        if ($new_search_or_filter) {
            $this->getSearchCache()->setQuery(ilUtil::stripSlashes(
                $requested_search['string'] ?? ($session_search['string'] ?? '')
            ));
            $this->getSearchCache()->setCreationFilter($this->loadCreationFilter());
            $this->getSearchCache()->save();
        }
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
        $session_search = ilSession::get('search');
        $session_search['type'] = $this->type = $a_type;
        ilSession::set('search', $session_search);
    }

    public function getType(): int
    {
        return $this->type ?? self::SEARCH_FAST;
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
        return $this->combination ?: self::SEARCH_OR;
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
        } elseif ($this->http->wrapper()->query()->has('term')) {
            $query = $this->http->wrapper()->query()->retrieve(
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
        } elseif ($this->http->wrapper()->query()->has('search_type')) {
            $search_type = $this->http->wrapper()->query()->retrieve(
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
            if (is_array($res_obj->items)) {
                echo json_encode($res_obj->items);
                exit;
            }
        } else {
            $list = ilSearchAutoComplete::getList($query);
            echo $list;
            exit;
        }
    }

    public function showSearch(): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.search.html', 'components/ILIAS/Search');
        $this->renderSearch($this->getString(), $this->getRootNode());
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
                $this->tpl->setVariable('SEARCH_RESULTS', $presentation->getHTML());
            }
        }
    }

    public function performSearchFilter(): void
    {
        $this->performSearch();
    }

    /**
     * Perform search
     */
    public function performSearch(): void
    {
        $page_number = $this->initPageNumberFromQuery();
        if (!$page_number and $this->search_mode != 'in_results') {
            ilSession::clear('max_page');
            $this->search_cache->deleteCachedEntries();
        }
        $this->search_cache->setResultPageNumber($page_number);
        if ($this->getType() == ilSearchBaseGUI::SEARCH_DETAILS and !$this->getDetails()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_choose_object_type'));
            $this->showSearch();
            return;
        }

        // Step 1: parse query string
        if (!is_object($query_parser = $this->__parseQueryString())) {
            $this->tpl->setOnScreenMessage('info', $query_parser);
            $this->showSearch();

            return;
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
        if ($this->getType() == self::SEARCH_DETAILS) {
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
            ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND,
            $this->parseStartDateFromCreationFilter(),
            $this->parseEndDateFromCreationFilter()
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
            $this->tpl->setVariable('SEARCH_RESULTS', $presentation->getHTML());
        }
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
                    $gdf_search->setFilter(array('term'));
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
        if ($this->getType() == self::SEARCH_DETAILS) {
            $obj_search->setFilter($this->__getFilter());
        }
        $this->parseCreationFilter($obj_search);
        return $obj_search->performSearch();
    }

    public function parseCreationFilter(ilObjectSearch $search): bool
    {
        $date_start = $this->parseStartDateFromCreationFilter();
        $date_end = $this->parseEndDateFromCreationFilter();

        if (is_null($date_start) && is_null($date_end)) {
            return true;
        }

        $search->setCreationDateFilterStartDate($date_start);
        $search->setCreationDateFilterEndDate($date_end);
        return true;
    }

    protected function parseStartDateFromCreationFilter(): ?ilDate
    {
        $options = $this->getSearchCache()->getCreationFilter();
        if (!($options['date_start'] ?? false)) {
            return null;
        }
        return new ilDate($options['date_start'] ?? "", IL_CAL_DATE);
    }

    protected function parseEndDateFromCreationFilter(): ?ilDate
    {
        $options = $this->getSearchCache()->getCreationFilter();
        if (!($options['date_end'] ?? false)) {
            return null;
        }
        return new ilDate($options['date_end'] ?? "", IL_CAL_DATE);
    }

    /**
    * Search in object meta data (keyword)
    * @return ilSearchResult result object
    * @access public
    */
    public function __searchMeta(ilQueryParser $query_parser, string $a_type): ilSearchResult
    {
        $meta_search = ilObjectSearchFactory::_getMetaDataSearchInstance($query_parser);
        if ($this->getType() == self::SEARCH_DETAILS) {
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
        if ($this->getType() != self::SEARCH_DETAILS) {
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
