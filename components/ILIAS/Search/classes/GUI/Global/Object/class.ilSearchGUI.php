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

use ILIAS\Search\GUI\Global\Object\Searcher;
use ILIAS\Search\GUI\Global\SearchStateHandler;
use ILIAS\Search\Presentation\Result\ResultPresenter;
use ILIAS\Search\Presentation\Result\ViewControls\PaginationInfos;
use ILIAS\Search\Presentation\Result\ViewControls\SortationInfos;
use ILIAS\Search\GUI\Global\Object\Actions;
use ILIAS\Search\Presentation\Result\Sortation;
use ILIAS\Search\GUI\Global\Param;
use ILIAS\Search\Service\Service;
use ILIAS\Search\GUI\Global\Object\FilterHandler;

/**
 * @ilCtrl_IsCalledBy ilSearchGUI: ilSearchControllerGUI
 */
class ilSearchGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilSearchSettings $settings;
    protected ResultPresenter $result_presenter;
    protected Actions $actions;
    protected Searcher $searcher;
    protected FilterHandler $filter_handler;
    protected SearchStateHandler $state_handler;

    public function __construct()
    {
        global $DIC;

        $service = new Service($DIC);

        $this->tpl = $service->dic()->ui()->mainTemplate();
        $this->user = $service->dic()->user();
        $this->lng = $service->dic()->language();
        $this->ctrl = $service->dic()->ctrl();
        $this->settings = ilSearchSettings::getInstance();
        $this->result_presenter = $service->presentation()->result();
        $this->actions = $service->gui()->objectSearchActions();
        $this->state_handler = $service->gui()->searchStateHandler();

        $this->initByMode($service);
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.search.html', 'components/ILIAS/Search');
    }

    protected function initByMode(Service $service): void
    {
        if ($this->settings->enabledLucene()) {
            $this->searcher = $service->gui()->luceneObjectSearcher();
            $this->filter_handler = $service->gui()->luceneObjectSearchFilterHandler();
        } else {
            $this->searcher = $service->gui()->directObjectSearcher();
            $this->filter_handler = $service->gui()->directObjectSearchFilterHandler();
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                if (!$this->actions->isValidCommand($cmd)) {
                    $cmd = 'showSavedResults';
                }
                $this->$cmd();
                break;
        }
    }

    protected function search(): void
    {
        $cache = $this->searcher->fetchCache($this->user->getId());
        $filter = $this->filter_handler->fetchFilter($this->actions->applyFilter());

        $term = $this->state_handler->fetchRequestedSearchTerm();
        $scope = $cache->getRoot();
        $sortation = Sortation::RELEVANCE_DESC;
        $page = 1;
        $max_page = 1;

        $this->state_handler->resetMaxPage();
        $cache->deleteCachedEntries();
        $cache->setQuery($term);
        $cache->save();

        $this->renderSearchInput($term);
        $this->renderFilter($filter, $scope);
        $pagination_infos = $this->buildPaginationInfos($sortation, $page, $max_page);
        $sortation_infos = $this->buildSortationInfos($sortation);
        $this->searcher->performSearchAndRenderResults(
            $this->user->getId(),
            $cache,
            $pagination_infos,
            $sortation_infos,
            $this->state_handler
        );
    }

    /**
     * Search from main menu.
     */
    protected function remoteSearch(): void
    {
        $cache = $this->searcher->fetchCache($this->user->getId());
        $filter = $this->filter_handler->fetchFilter($this->actions->applyFilter());

        $term = $this->searcher->decorateRemoteSearchTerm(
            $this->state_handler->fetchRequestedRemoteSearchTerm()
        );
        $scope = $this->state_handler->fetchRequestedRemoteScope();
        $sortation = Sortation::RELEVANCE_DESC;
        $page = 1;
        $max_page = 1;

        $this->state_handler->resetMaxPage();
        $cache->deleteCachedEntries();
        $cache->setQuery($term);
        $cache->setRoot($scope);
        $cache->save();

        $this->renderSearchInput($term);
        $this->renderFilter($filter, $scope);
        $pagination_infos = $this->buildPaginationInfos($sortation, $page, $max_page);
        $sortation_infos = $this->buildSortationInfos($sortation);
        $this->searcher->performSearchAndRenderResults(
            $this->user->getId(),
            $cache,
            $pagination_infos,
            $sortation_infos,
            $this->state_handler
        );
    }

    protected function showSavedResults(): void
    {
        $cache = $this->searcher->fetchCache($this->user->getId());
        $filter = $this->filter_handler->fetchFilter($this->actions->applyFilter());

        $term = $cache->getQuery();
        $scope = $cache->getRoot();
        $sortation = Sortation::RELEVANCE_DESC;
        $page = $cache->getResultPageNumber();
        $max_page = $this->state_handler->fetchMaxPage();

        $this->renderSearchInput($term);
        $this->renderFilter($filter, $scope);
        $pagination_infos = $this->buildPaginationInfos($sortation, $page, $max_page);
        $sortation_infos = $this->buildSortationInfos($sortation);
        $this->searcher->readSavedResultsAndRenderResults(
            $this->user->getId(),
            $cache,
            $pagination_infos,
            $sortation_infos
        );
    }

    protected function applyFilter(): void
    {
        $cache = $this->searcher->fetchCache($this->user->getId());
        $filter = $this->filter_handler->fetchFilter($this->actions->applyFilter());

        $this->filter_handler->loadFilterToCache($filter, $cache);

        $term = $cache->getQuery();
        $scope = $cache->getRoot();
        $sortation = Sortation::RELEVANCE_DESC;
        $page = 1;
        $max_page = 1;

        $this->state_handler->resetMaxPage();
        $cache->deleteCachedEntries();
        $cache->save();

        $this->renderSearchInput($term);
        $this->renderFilter($filter, $scope);
        $pagination_infos = $this->buildPaginationInfos($sortation, $page, $max_page);
        $sortation_infos = $this->buildSortationInfos($sortation);
        $this->searcher->performSearchAndRenderResults(
            $this->user->getId(),
            $cache,
            $pagination_infos,
            $sortation_infos,
            $this->state_handler
        );
    }

    protected function switchResultPage(): void
    {
        $cache = $this->searcher->fetchCache($this->user->getId());
        $filter = $this->filter_handler->fetchFilter($this->actions->applyFilter());

        $term = $cache->getQuery();
        $scope = $cache->getRoot();
        $sortation = $this->state_handler->fetchSortation();
        $page = $this->state_handler->fetchRequestedPage();
        $max_page = max($this->state_handler->fetchMaxPage(), $page);

        $this->state_handler->updateMaxPage($max_page);
        $cache->setResultPageNumber($page);
        $cache->save();

        $this->renderSearchInput($term);
        $this->renderFilter($filter, $scope);
        $pagination_infos = $this->buildPaginationInfos($sortation, $page, $max_page);
        $sortation_infos = $this->buildSortationInfos($sortation);
        $this->searcher->performSearchAndRenderResults(
            $this->user->getId(),
            $cache,
            $pagination_infos,
            $sortation_infos,
            $this->state_handler
        );
    }

    protected function sortResultPage(): void
    {
        $cache = $this->searcher->fetchCache($this->user->getId());
        $filter = $this->filter_handler->fetchFilter($this->actions->applyFilter());

        $term = $cache->getQuery();
        $scope = $cache->getRoot();
        $sortation = $this->state_handler->fetchSortation();
        $page = $cache->getResultPageNumber();
        $max_page = $this->state_handler->fetchMaxPage();

        $this->renderSearchInput($term);
        $this->renderFilter($filter, $scope);
        $pagination_infos = $this->buildPaginationInfos($sortation, $page, $max_page);
        $sortation_infos = $this->buildSortationInfos($sortation);
        $this->searcher->readSavedResultsAndRenderResults(
            $this->user->getId(),
            $cache,
            $pagination_infos,
            $sortation_infos
        );
    }

    protected function autoComplete(): void
    {
        $term = $this->state_handler->fetchRequestedAutoCompleteSearchTerm();
        $list = ilSearchAutoComplete::getList($term);
        echo $list;
        exit;
    }

    protected function renderSearchInput(string $term): void
    {
        $this->tpl->addJavascript("assets/js/Search.js");

        $this->tpl->setVariable("FORM_ACTION", $this->actions->search());
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($term));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search_field"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("btn_search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
    }

    protected function renderFilter(
        ilSearchFilterGUI $filter,
        int $scope
    ): void {
        $filter_html = $filter->getHTML();
        preg_match('/id="([^"]+)"/', $filter_html, $matches);
        $filter_id = $matches[1];
        $this->tpl->setVariable("SEARCH_FILTER", $filter_html);
        // scope in filter must be manipulated by JS if search is triggered in meta bar
        $this->tpl->addOnLoadCode("il.Search.syncFilterScope('" . $filter_id . "', '" . $scope . "');");
    }

    protected function buildPaginationInfos(
        Sortation $sortation,
        int $page,
        int $max_page
    ): PaginationInfos {
        return $this->result_presenter->getPaginationInfos(
            $page,
            $max_page,
            $this->settings->getMaxHits(),
            $this->actions->switchResultPage($sortation),
            Param::PAGE_NUMBER
        );
    }

    protected function buildSortationInfos(
        Sortation $sortation
    ): SortationInfos {
        return $this->result_presenter->getSortationInfos(
            $sortation,
            $this->actions->sortResultPage(),
            Param::SORTATION
        );
    }
}
