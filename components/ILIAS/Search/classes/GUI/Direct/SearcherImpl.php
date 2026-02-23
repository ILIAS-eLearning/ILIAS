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

namespace ILIAS\Search\GUI\Direct;

use ILIAS\Search\GUI\Searcher;
use ilUserSearchCache;
use ILIAS\Search\Presentation\Result\ViewControlInfos;
use ilGlobalTemplateInterface;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Search\Presentation\Result\ResultPresenter;
use ilSearchResult;
use ILIAS\UICore\GlobalTemplate;
use ilLanguage;
use ilQueryParser;
use ilUtil;
use ilObjectSearchFactory;
use ilSearchSettings;
use ilObjectSearch;
use ilDate;
use ILIAS\Search\GUI\SearchStateHandler;

class SearcherImpl implements Searcher
{
    public function __construct(
        protected ilSearchSettings $settings,
        protected ilGlobalTemplateInterface $tpl,
        protected UIRenderer $ui_renderer,
        protected ResultPresenter $presenter,
        protected ilLanguage $lng
    ) {
    }

    public function performSearchAndRenderResults(
        int $usr_id,
        ilUserSearchCache $cache,
        ViewControlInfos $view_control_infos,
        SearchStateHandler $state_handler
    ): void {
        // Step 1: parse query string
        if (!is_object($query_parser = $this->parseQueryString($cache->getQuery()))) {
            $this->tpl->setOnScreenMessage(GlobalTemplate::MESSAGE_TYPE_INFO, $query_parser);
            return;
        }
        // Step 2: perform object search. Get an ObjectSearch object via factory. Depends on fulltext or like search type.
        $result = $this->searchObjects($query_parser, $cache);

        // Step 3: perform meta keyword search. Get an MetaDataSearch object.
        $result_meta = $this->searchLOM('keyword', $query_parser, $cache);
        $result->mergeEntries($result_meta);

        $result_meta = $this->searchLOM('contribute', $query_parser, $cache);
        $result->mergeEntries($result_meta);

        $result_meta = $this->searchLOM('title', $query_parser, $cache);
        $result->mergeEntries($result_meta);

        $result_meta = $this->searchLOM('description', $query_parser, $cache);
        $result->mergeEntries($result_meta);

        // Step 4: Perform details search in object specific tables
        $result = $this->searchDetails($query_parser, $cache, $result);


        // Step 5: merge and validate results
        $result->filter(
            $cache->getRoot(),
            ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND,
            $this->parseStartDateFromCreationFilter($cache),
            $this->parseEndDateFromCreationFilter($cache),
            $cache->getCopyrightFilter()
        );
        $result->save();

        /**
         * This should not be here. As soon as we have a unified format
         * for search results, this should be done by the GUI.
         */
        if (
            $view_control_infos->currentPage() === $view_control_infos->maxPages() &&
            $result->isLimitReached()
        ) {
            $view_control_infos = $this->presenter->getViewControlInfos(
                $view_control_infos->sortation(),
                $view_control_infos->currentPage(),
                $view_control_infos->maxPages() + 1,
                $view_control_infos->pageSize(),
                $view_control_infos->paginationAction(),
                $view_control_infos->pageParam(),
                $view_control_infos->sortationAction(),
                $view_control_infos->sortationParam()
            );
            $state_handler->updateMaxPage($view_control_infos->maxPages());
        }

        $this->renderResults($result, $cache->getQuery(), $view_control_infos);
    }

    public function readSavedResultsAndRenderResults(
        int $usr_id,
        ilUserSearchCache $cache,
        ViewControlInfos $view_control_infos
    ): void {
        $results = new ilSearchResult($usr_id);
        $results->read();
        $results->filterResults($cache->getRoot());
        $this->renderResults($results, $cache->getQuery(), $view_control_infos);
    }

    protected function renderResults(
        ilSearchResult $results,
        string $term,
        ViewControlInfos $view_control_infos
    ): void {
        if ($results->getResults()) {
            $result_panel_and_modals = $this->presenter->getDirectSearchResultAsPanel(
                $results,
                $view_control_infos
            );
            $this->tpl->setVariable(
                'SEARCH_RESULTS',
                $this->ui_renderer->render($result_panel_and_modals)
            );
        } elseif ($term !== '') {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_INFO,
                sprintf(
                    $this->lng->txt('search_no_match_hint'),
                    $term
                )
            );
        } else {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_INFO,
                $this->lng->txt('search_no_match')
            );
        }
    }

    protected function parseQueryString(string $term): ilQueryParser|string
    {
        $query_parser = new ilQueryParser(ilUtil::stripSlashes($term));
        $query_parser->setCombination(
            $this->settings->getDefaultOperator() === ilSearchSettings::OPERATOR_AND ?
                ilQueryParser::QP_COMBINATION_AND : ilQueryParser::QP_COMBINATION_OR
        );
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }

    /**
     * Search in object title, desctiption
     */
    protected function searchObjects(
        ilQueryParser $query_parser,
        ilUserSearchCache $cache
    ): ilSearchResult {
        $obj_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
        if (($type_filter = $this->parseTypeFilter($cache)) !== []) {
            $obj_search->setFilter($type_filter);
        }
        $this->parseCreationFilter($obj_search, $cache);
        return $obj_search->performSearch();
    }

    protected function searchLOM(
        string $field,
        ilQueryParser $query_parser,
        ilUserSearchCache $cache
    ): ilSearchResult {
        $meta_search = ilObjectSearchFactory::_getMetaDataSearchInstance($query_parser);
        if (($type_filter = $this->parseTypeFilter($cache)) !== []) {
            $meta_search->setFilter($type_filter);
        }
        switch ($field) {
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

    protected function searchDetails(
        ilQueryParser $query_parser,
        ilUserSearchCache $cache,
        ilSearchResult $result
    ): ilSearchResult {
        foreach ($cache->getItemFilter() as $type => $enabled) {
            if (!$enabled) {
                continue;
            }

            switch ($type) {
                case 'crs':
                    $crs_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
                    $crs_search->setFilter(['crs']);
                    $result->mergeEntries($crs_search->performSearch());
                    break;

                case 'grp':
                    $grp_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
                    $grp_search->setFilter(['grp']);
                    $result->mergeEntries($grp_search->performSearch());
                    break;

                case 'lms':
                    $content_search = ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
                    $content_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($content_search->performSearch());
                    break;

                case 'frm':
                    $forum_search = ilObjectSearchFactory::_getForumSearchInstance($query_parser);
                    $forum_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($forum_search->performSearch());
                    break;

                case 'glo':
                    // Glossary term definition pages
                    $gdf_search = ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
                    $gdf_search->setFilter(['term']);
                    $result->mergeEntries($gdf_search->performSearch());
                    // Glossary terms
                    $gdf_term_search = ilObjectSearchFactory::_getGlossaryDefinitionSearchInstance($query_parser);
                    $result->mergeEntries($gdf_term_search->performSearch());
                    break;

                case 'exc':
                    $exc_search = ilObjectSearchFactory::_getExerciseSearchInstance($query_parser);
                    $exc_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($exc_search->performSearch());
                    break;

                case 'mcst':
                    $mcst_search = ilObjectSearchFactory::_getMediacastSearchInstance($query_parser);
                    $result->mergeEntries($mcst_search->performSearch());
                    break;

                case 'tst':
                    $tst_search = ilObjectSearchFactory::_getTestSearchInstance($query_parser);
                    $tst_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($tst_search->performSearch());
                    break;

                case 'mep':
                    $mep_search = ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
                    $mep_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($mep_search->performSearch());

                    // Mob keyword search
                    $mob_search = ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
                    $mob_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($mob_search->performKeywordSearch());

                    break;

                case 'wiki':
                    $wiki_search = ilObjectSearchFactory::_getWikiContentSearchInstance($query_parser);
                    $wiki_search->setFilter($this->parseTypeFilter($cache));
                    $result->mergeEntries($wiki_search->performSearch());
                    break;
            }
        }
        return $result;
    }

    protected function parseCreationFilter(
        ilObjectSearch $search,
        ilUserSearchCache $cache
    ): void {
        $date_start = $this->parseStartDateFromCreationFilter($cache);
        $date_end = $this->parseEndDateFromCreationFilter($cache);

        if (is_null($date_start) && is_null($date_end)) {
            return;
        }

        $search->setCreationDateFilterStartDate($date_start);
        $search->setCreationDateFilterEndDate($date_end);
    }

    protected function parseStartDateFromCreationFilter(ilUserSearchCache $cache): ?ilDate
    {
        $options = $cache->getCreationFilter();
        if (!($options['date_start'] ?? false)) {
            return null;
        }
        return new ilDate($options['date_start'], IL_CAL_DATE);
    }

    protected function parseEndDateFromCreationFilter(ilUserSearchCache $cache): ?ilDate
    {
        $options = $cache->getCreationFilter();
        if (!($options['date_end'] ?? false)) {
            return null;
        }
        return new ilDate($options['date_end'], IL_CAL_DATE);
    }

    protected function parseTypeFilter(ilUserSearchCache $cache): array
    {
        $filter = [];
        foreach ($cache->getItemFilter() as $type => $enabled) {
            if (!$enabled) {
                continue;
            }

            switch ($type) {
                case 'lms':
                    $filter[] = 'lm';
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
                    $filter[] = 'wiki';
                    $filter[] = 'wpg';
                    break;

                default:
                    $filter[] = $type;
            }
        }
        return $filter;
    }
}
