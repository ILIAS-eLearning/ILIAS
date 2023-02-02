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
* Meta Data search GUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ilCtrl_IsCalledBy ilLuceneAdvancedSearchGUI: ilSearchControllerGUI
* @ilCtrl_Calls ilLuceneAdvancedSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilLuceneAdvancedSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilLuceneAdvancedSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchGUI extends ilSearchBaseGUI
{
    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help;

    protected ilLuceneAdvancedSearchFields $fields;

    protected ?array $admin_panel_commands;
    protected ?array $admin_view_button;
    protected ?array $creation_selector;
    protected ?string $page_form_action;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tabs_gui = $DIC->tabs();
        $this->help = $DIC['ilHelp'];
        parent::__construct();

        $this->fields = ilLuceneAdvancedSearchFields::getInstance();
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
            case 'ilobjectcopygui':
                $this->ctrl->setReturn($this);
                $cp = new ilObjectCopyGUI($this);
                $this->ctrl->forwardCommand($cp);
                break;


            default:
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->handleCommand($cmd);
                break;
        }
    }


    /**
     * Show saved results
     */
    public function showSavedResults(): void
    {
        $qp = new ilLuceneAdvancedQueryParser($this->search_cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Load saved results
        $filter = ilLuceneSearchResultFilter::getInstance($this->user->getId());
        $filter->loadFromDb();

        // Highlight
        if ($filter->getResultObjIds()) {
            $searcher->highlight($filter->getResultObjIds());
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_adv_search.html', 'Services/Search');
        $presentation = new ilSearchResultPresentation($this);
        $presentation->setResults($filter->getResultIds());
        $presentation->setSearcher($searcher);


        // TODO: other handling required
        $this->addPager($filter, 'max_page');
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('SEARCH_RESULTS', $presentation->getHTML());
        } elseif (strlen(trim($qp->getQuery()))) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_no_match'));
        }

        // and finally add search form
        $this->initFormSearch();
        $this->tpl->setVariable('SEARCH_TABLE', $this->form->getHTML());

        if ($filter->getResultIds()) {
            $this->fillAdminPanel();
        }
    }

    /**
     * Show search form
     */
    protected function initFormSearch(): void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'search'));
        $this->form->setTitle($this->lng->txt('search_advanced'));
        $this->form->addCommandButton('search', $this->lng->txt('search'));
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
        $this->search_cache->setQuery(['lom_content' => $queryString]);
        $this->search_cache->save();
        $this->search();
    }

    protected function search(): void
    {
        if (!is_array($this->search_cache->getQuery())) {
            // TOD: handle empty advances search
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_no_search_string'));
            $this->showSavedResults();
            return;
        }
        ilSession::clear('max_page');
        $this->search_cache->deleteCachedEntries();

        // Reset details
        ilSubItemListGUI::resetDetails();

        $this->performSearch();
    }

    /**
     * Reset search form
     */
    protected function reset(): void
    {
        $this->search_cache->setQuery(array());
        $this->search_cache->save();
        $this->showSavedResults();
    }

    /**
     * Perform search
     */
    protected function performSearch(): void
    {
        ilSession::clear('vis_references');
        $qp = new ilLuceneAdvancedQueryParser($this->search_cache->getQuery());
        $qp->parse();
        if (!strlen(trim($qp->getQuery()))) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_no_search_string'));
            $this->showSavedResults();
            return;
        }

        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Filter results
        $filter = ilLuceneSearchResultFilter::getInstance($this->user->getId());
        $filter->addFilter(new ilLucenePathFilter($this->search_cache->getRoot()));
        $filter->setCandidates($searcher->getResult());
        $filter->filter();

        if ($filter->getResultObjIds()) {
            $searcher->highlight($filter->getResultObjIds());
        }

        // Show results
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_adv_search.html', 'Services/Search');
        $presentation = new ilSearchResultPresentation($this);
        $presentation->setResults($filter->getResultIds());
        $presentation->setSearcher($searcher);

        // TODO: other handling required
        $this->addPager($filter, 'max_page');
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('SEARCH_RESULTS', $presentation->getHTML());
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_no_match'));
        }

        // and finally add search form
        $this->initFormSearch();
        $this->tpl->setVariable('SEARCH_TABLE', $this->form->getHTML());

        if ($filter->getResultIds()) {
            $this->fillAdminPanel();
        }
    }

    /**
     * Add admin panel command
     */
    public function prepareOutput(): void
    {
        parent::prepareOutput();
        $this->getTabs();
    }

    /**
     * get tabs
     */
    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent("src_luc");

        $this->tabs_gui->addTarget('search', $this->ctrl->getLinkTargetByClass('illucenesearchgui'));

        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tabs_gui->addTarget('search_user', $this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
        }

        if (
            !ilSearchSettings::getInstance()->getHideAdvancedSearch() and
            $this->fields->getActiveFields()) {
            $this->tabs_gui->addTarget('search_advanced', $this->ctrl->getLinkTarget($this));
        }

        $this->tabs_gui->setTabActive('search_advanced');
    }

    /**
     * Init user search cache
     *
     * @access private
     *
     */
    protected function initUserSearchCache(): void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_ADVANCED);
        $page_number = $this->initPageNumberFromQuery();
        if ($page_number) {
            $this->search_cache->setResultPageNumber($page_number);
        }
        if ($this->http->wrapper()->post()->has('query')) {
            $this->search_cache->setQuery($this->http->request()->getParsedBody()['query'] ?? []);
        }
    }

    /**
     * @todo Couldn't find any of these template variables anywhere. Is this still currently used?
     */
    protected function fillAdminPanel(): void
    {
        $adm_view_cmp = $adm_cmds = $creation_selector = $adm_view = false;

        // admin panel commands
        if (isset($this->admin_panel_commands) && (count((array) $this->admin_panel_commands) > 0)) {
            foreach ($this->admin_panel_commands as $cmd) {
                $this->tpl->setCurrentBlock("lucene_admin_panel_cmd");
                $this->tpl->setVariable("LUCENE_PANEL_CMD", $cmd["cmd"]);
                $this->tpl->setVariable("LUCENE_TXT_PANEL_CMD", $cmd["txt"]);
                $this->tpl->parseCurrentBlock();
            }

            $adm_cmds = true;
        }
        if ($adm_cmds) {
            $this->tpl->setCurrentBlock("lucene_adm_view_components");
            $this->tpl->setVariable("LUCENE_ADM_IMG_ARROW", ilUtil::getImagePath("arrow_upright.svg"));
            $this->tpl->setVariable("LUCENE_ADM_ALT_ARROW", $this->lng->txt("actions"));
            $this->tpl->parseCurrentBlock();
            $adm_view_cmp = true;
        }

        // admin view button
        if (isset($this->admin_view_button) && is_array($this->admin_view_button)) {
            if (is_array($this->admin_view_button)) {
                $this->tpl->setCurrentBlock("lucene_admin_button");
                $this->tpl->setVariable(
                    "LUCENE_ADMIN_MODE_LINK",
                    $this->admin_view_button["link"]
                );
                $this->tpl->setVariable(
                    "LUCENE_TXT_ADMIN_MODE",
                    $this->admin_view_button["txt"]
                );
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("lucene_admin_view");
            $this->tpl->parseCurrentBlock();
            $adm_view = true;
        }

        // creation selector
        if (isset($this->creation_selector) && is_array($this->creation_selector)) {
            $this->tpl->setCurrentBlock("lucene_add_commands");
            if ($adm_cmds) {
                $this->tpl->setVariable("LUCENE_ADD_COM_WIDTH", 'width="1"');
            }
            $this->tpl->setVariable(
                "LUCENE_SELECT_OBJTYPE_REPOS",
                $this->creation_selector["options"]
            );
            $this->tpl->setVariable(
                "LUCENE_BTN_NAME_REPOS",
                $this->creation_selector["command"]
            );
            $this->tpl->setVariable(
                "LUCENE_TXT_ADD_REPOS",
                $this->creation_selector["txt"]
            );
            $this->tpl->parseCurrentBlock();
            $creation_selector = true;
        }
        if ($adm_view || $creation_selector) {
            $this->tpl->setCurrentBlock("lucene_adm_panel");
            if ($adm_view_cmp) {
                $this->tpl->setVariable("LUCENE_ADM_TBL_WIDTH", 'width:"100%";');
            }
            $this->tpl->parseCurrentBlock();
        }
    }
}
