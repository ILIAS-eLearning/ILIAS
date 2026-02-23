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

namespace ILIAS\Search\GUI\Lucene;

use ILIAS\Search\GUI\Searcher;
use ilUserSearchCache;
use ILIAS\Search\Presentation\Result\ViewControlInfos;
use ilGlobalTemplateInterface;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Search\Presentation\Result\ResultPresenter;
use ILIAS\UICore\GlobalTemplate;
use ilLanguage;
use ilSearchSettings;
use ilLuceneSearchResultFilter;
use ilLuceneHighlighterResultParser;
use ilLuceneQueryParser;
use ilLuceneSearcher;
use ilLucenePathFilter;
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
        $filter_query = '';
        if ($cache->getItemFilter() and ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            $filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
            foreach ($cache->getItemFilter() as $obj => $value) {
                if (!$filter_query) {
                    $filter_query .= '+( ';
                } else {
                    $filter_query .= 'OR';
                }
                $filter_query .= (' ' . $filter_settings[$obj]['filter'] . ' ');
            }
            $filter_query .= ') ';
        }

        $mime_query = '';
        if ($cache->getMimeFilter() and ilSearchSettings::getInstance()->isLuceneMimeFilterEnabled()) {
            $filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions();
            foreach ($cache->getMimeFilter() as $mime => $value) {
                if (!$mime_query) {
                    $mime_query .= '+( ';
                } else {
                    $mime_query .= 'OR';
                }
                $mime_query .= (' ' . $filter_settings[$mime]['filter'] . ' ');
            }
            $mime_query .= ') ';
        }

        $cdate_query = $this->parseCreationFilter($cache);
        $copyright_query = $this->parseCopyrightFilter($cache);

        $filter_query = $filter_query . ' ' . $mime_query . ' ' . $cdate_query . ' ' . $copyright_query;

        $query = $cache->getQuery();
        if ($query) {
            $query = ' +(' . $query . ')';
        }
        $qp = new ilLuceneQueryParser($filter_query . $query);
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Filter results
        $filter = ilLuceneSearchResultFilter::getInstance($usr_id);
        $filter->addFilter(new ilLucenePathFilter($cache->getRoot()));
        $filter->setCandidates($searcher->getResult());
        $filter->filter();

        if ($filter->getResultObjIds()) {
            $searcher->highlight($filter->getResultObjIds());
        }

        /**
         * This should not be here. As soon as we have a unified format
         * for search results, this should be done by the GUI.
         */
        if (
            $view_control_infos->currentPage() === $view_control_infos->maxPages() &&
            $filter->isLimitReached()
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

        $this->renderResults($filter, $searcher->getHighlighter(), $cache->getQuery(), $view_control_infos);
    }

    public function readSavedResultsAndRenderResults(
        int $usr_id,
        ilUserSearchCache $cache,
        ViewControlInfos $view_control_infos
    ): void {
        if (!strlen($cache->getQuery())) {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_INFO,
                $this->lng->txt('search_no_match')
            );
            return;
        }

        $qp = new ilLuceneQueryParser($cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Load saved results
        $filter = ilLuceneSearchResultFilter::getInstance($usr_id);
        $filter->loadFromDb();

        // Highlight
        $searcher->highlight($filter->getResultObjIds());

        $this->renderResults($filter, $searcher->getHighlighter(), $cache->getQuery(), $view_control_infos);
    }

    protected function renderResults(
        ilLuceneSearchResultFilter $filter,
        ?ilLuceneHighlighterResultParser $highlighter,
        string $term,
        ViewControlInfos $view_control_infos
    ): void {
        if ($filter->getResults() && $highlighter !== null) {
            $result_panel_and_modals = $this->presenter->getLuceneSearchResultAsPanel(
                $filter,
                $highlighter,
                $view_control_infos
            );
            $this->tpl->setVariable(
                'SEARCH_RESULTS',
                $this->presenter->replacePlaceholders($this->ui_renderer->render($result_panel_and_modals))
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

    protected function parseCreationFilter(ilUserSearchCache $cache): string
    {
        $options = $cache->getCreationFilter();

        if (!($options['date_start'] ?? false) && !($options['date_end'] ?? false)) {
            return '';
        }

        $start = null;
        $end = null;
        if (($options['date_start'] ?? false)) {
            $start = new ilDate($options['date_start'] ?? "", IL_CAL_DATE);
        }
        if (($options['date_end'] ?? false)) {
            $end = new ilDate($options['date_end'] ?? "", IL_CAL_DATE);
        }

        if ($start && is_null($end)) {
            $now = new ilDate(time(), IL_CAL_UNIX);
            return '+(cdate:[' . $start->get(IL_CAL_DATE) . ' TO ' . $now->get(IL_CAL_DATE) . '*]) ';
        } elseif ($end && is_null($start)) {
            return '+(cdate:[* TO ' . $end->get(IL_CAL_DATE) . ']) ';
        } else {
            return '+(cdate:[' . $start->get(IL_CAL_DATE) . ' TO ' . $end->get(IL_CAL_DATE) . '*]) ';
        }

        return '';
    }

    protected function parseCopyrightFilter(ilUserSearchCache $cache): string
    {
        $identifiers = $cache->getCopyrightFilter();
        if ($identifiers === []) {
            return '';
        }

        $conditions = [];
        foreach ($identifiers as $identifier) {
            $conditions[] = 'lomCopyright:"' . $identifier . '"';
        }
        return '+(' . implode(' OR ', $conditions) . ')';
    }
}
