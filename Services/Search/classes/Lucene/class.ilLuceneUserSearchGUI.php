<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
 * @classDescription GUI for  Lucene user search
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilLuceneUserSearchGUI: ilPublicUserProfileGUI
 * @ilCtrl_IsCalledBy ilLuceneUserSearchGUI: ilSearchControllerGUI
 *
 * @ingroup ServicesSearch
 */
class ilLuceneUserSearchGUI extends ilSearchBaseGUI
{
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();
        parent::__construct();
        $this->initUserSearchCache();
    }

    /**
     * Execute Command
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        switch ($next_class) {
            case "ilpublicuserprofilegui":

                $user_id = 0;
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $user_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $profile = new ilPublicUserProfileGUI($user_id);
                $profile->setBackUrl($this->ctrl->getLinkTarget($this, 'showSavedResults'));
                $ret = $this->ctrl->forwardCommand($profile);
                $this->tpl->setContent($ret);
                break;


            default:
                $this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_USER);
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->handleCommand($cmd);
                break;
        }
    }

    public function prepareOutput(): void
    {
        parent::prepareOutput();
        $this->getTabs();
    }



    /**
     * Get type of search (details | fast)
     * @todo rename
     * Needed for base class search form
     */
    protected function getType(): int
    {
        return self::SEARCH_DETAILS;
    }

    /**
     * Needed for base class search form
     * @todo rename
     */
    protected function getDetails(): array
    {
        return $this->search_cache->getItemFilter();
    }


    /**
     * Search from main menu
     */
    protected function remoteSearch(): void
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
        $this->search_cache->setRoot($root_id);
        $this->search_cache->setQuery($queryString);
        $this->search_cache->save();
        $this->search();
    }

    /**
     * Show saved results
     * @return void
     */
    protected function showSavedResults(): void
    {
        if (strlen($this->search_cache->getQuery())) {
            $this->performSearch();
            return;
        }

        $this->showSearchForm();
    }

    /**
     * Search (button pressed)
     * @return void
     */
    protected function search(): void
    {
        if (!$this->form->checkInput()) {
            $this->search_cache->deleteCachedEntries();
            // Reset details
            ilSubItemListGUI::resetDetails();
            $this->showSearchForm();
            return;
        }
        ilSession::clear('max_page');
        $this->search_cache->deleteCachedEntries();

        // Reset details
        ilSubItemListGUI::resetDetails();
        $this->performSearch();
    }

    /**
     * Perform search
     */
    protected function performSearch(): void
    {
        $qp = new ilLuceneQueryParser($this->search_cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->setType(ilLuceneSearcher::TYPE_USER);
        $searcher->search();

        $this->showSearchForm();

        $user_table = new ilRepositoryUserResultTableGUI(
            $this,
            'performSearch',
            false,
            ilRepositoryUserResultTableGUI::TYPE_GLOBAL_SEARCH
        );
        $user_table->setLuceneResult($searcher->getResult());
        $user_table->parseUserIds($searcher->getResult()->getCandidates());

        $this->tpl->setVariable('SEARCH_RESULTS', $user_table->getHTML());
    }

    /**
     * get tabs
     */
    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent("src_luc");

        $this->tabs->addTarget('search', $this->ctrl->getLinkTargetByClass('illucenesearchgui'));

        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tabs->addTarget('search_user', $this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
        }

        $fields = ilLuceneAdvancedSearchFields::getInstance();

        if (
            !ilSearchSettings::getInstance()->getHideAdvancedSearch() and
            $fields->getActiveFields()) {
            $this->tabs->addTarget('search_advanced', $this->ctrl->getLinkTargetByClass('illuceneadvancedsearchgui'));
        }

        $this->tabs->setTabActive('search_user');
    }

    /**
     * Init user search cache
     */
    protected function initUserSearchCache(): void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_USER_SEARCH);
        $page_number = $this->initPageNumberFromQuery();
        if ($page_number) {
            $this->search_cache->setResultPageNumber($page_number);
        }

        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
            $this->search_cache->setQuery($query);
            $this->search_cache->setItemFilter(array());
            $this->search_cache->setMimeFilter(array());
            $this->search_cache->save();
        }
    }



    /**
     * Show search form
     * @return boolean
     */
    protected function showSearchForm()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_usr_search.html', 'Services/Search');

        ilOverlayGUI::initJavascript();
        $this->tpl->addJavascript("./Services/Search/js/Search.js");

        $this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performSearch'));
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($this->search_cache->getQuery()));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());

        return true;
    }
}
