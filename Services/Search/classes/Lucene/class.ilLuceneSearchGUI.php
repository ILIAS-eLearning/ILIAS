<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* @classDescription GUI for simple Lucene search
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ilCtrl_IsCalledBy ilLuceneSearchGUI: ilSearchControllerGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilPropertyFormGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI
*
* @ingroup ServicesSearch
*/
class ilLuceneSearchGUI extends ilSearchBaseGUI
{
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;

    protected ilLuceneAdvancedSearchFields $fields;

    protected ?int $root_node;
    protected array $admin_panel_commands = [];
    protected array $admin_view_button = [];
    protected array $creation_selector = [];

    protected string $page_form_action = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();

        parent::__construct();
        $this->fields = ilLuceneAdvancedSearchFields::getInstance();
        $this->initUserSearchCache();
    }

    /**
     * Execute Command
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        switch ($next_class) {
            case "ilpropertyformgui":
                /*$this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_LUCENE);
                $ilCtrl->setReturn($this, 'storeRoot');
                $ilCtrl->forwardCommand($this->form);*/
                $form = $this->getSearchAreaForm();
                $this->ctrl->setReturn($this, 'storeRoot');
                $this->ctrl->forwardCommand($form);
                break;

            case 'ilobjectcopygui':
                $this->ctrl->setReturn($this, '');
                $cp = new ilObjectCopyGUI($this);
                $this->ctrl->forwardCommand($cp);
                break;

            default:
                $this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_LUCENE);
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->handleCommand($cmd);
                break;
        }
    }

    /**
     * Add admin panel command
     */
    public function prepareOutput() : void
    {
        parent::prepareOutput();
        $this->getTabs();
    }

    /**
     * @todo rename
     */
    protected function getType() : int
    {
        return self::SEARCH_DETAILS;
    }

    /**
     * Needed for base class search form
     * @todo rename
     */
    protected function getDetails() : array
    {
        return $this->search_cache->getItemFilter();
    }

    /**
     * Needed for base class search form
     * @todo rename
     */
    protected function getMimeDetails() : array
    {
        return $this->search_cache->getMimeFilter();
    }

    /**
     * Search from main menu
     */
    protected function remoteSearch() : void
    {
        $queryString = '';
        if ($this->http->wrapper()->post()->has('queryString')) {
            $queryString = $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
        }
        $root_id = ROOT_FOLDER_ID;
        if ($this->http->wrapper()->post()->has('root_id')) {
            $root_id = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $qp = new ilLuceneQueryParser($queryString);
        $qp->parseAutoWildcard();

        $query = $qp->getQuery();

        $this->search_cache->setRoot($root_id);
        $this->search_cache->setQuery(ilUtil::stripSlashes($query));
        $this->search_cache->save();

        $this->search();
    }

    /**
     * Show saved results
     */
    protected function showSavedResults() : bool
    {
        if (!strlen($this->search_cache->getQuery())) {
            $this->showSearchForm();
            return false;
        }

        $qp = new ilLuceneQueryParser($this->search_cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Load saved results
        $filter = ilLuceneSearchResultFilter::getInstance($this->user->getId());
        $filter->loadFromDb();

        // Highlight
        $searcher->highlight($filter->getResultObjIds());

        $presentation = new ilSearchResultPresentation($this);
        $presentation->setResults($filter->getResultIds());
        $presentation->setSearcher($searcher);
        $this->addPager($filter, 'max_page');
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        $this->showSearchForm();

        if ($presentation->render()) {
            $this->tpl->setVariable('SEARCH_RESULTS', $presentation->getHTML());
        } elseif (strlen($this->search_cache->getQuery())) {
            $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt('search_no_match_hint'), $qp->getQuery()));
        }
        return true;
    }

    /**
     * Search (button pressed)
     * @return void
     */
    protected function search() : void
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
    protected function performSearch() : void
    {
        ilSession::clear('vis_references');
        $filter_query = '';
        if ($this->search_cache->getItemFilter() and ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            $filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
            foreach ($this->search_cache->getItemFilter() as $obj => $value) {
                if (!$filter_query) {
                    $filter_query .= '+( ';
                } else {
                    $filter_query .= 'OR';
                }
                $filter_query .= (' ' . $filter_settings[$obj]['filter'] . ' ');
            }
            $filter_query .= ') ';
        }
        // begin-patch mime_filter
        $mime_query = '';
        if ($this->search_cache->getMimeFilter() and ilSearchSettings::getInstance()->isLuceneMimeFilterEnabled()) {
            $filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions();
            foreach ($this->search_cache->getMimeFilter() as $mime => $value) {
                if (!$mime_query) {
                    $mime_query .= '+( ';
                } else {
                    $mime_query .= 'OR';
                }
                $mime_query .= (' ' . $filter_settings[$mime]['filter'] . ' ');
            }
            $mime_query .= ') ';
        }

        // begin-patch creation_date
        $cdate_query = $this->parseCreationFilter();



        $filter_query = $filter_query . ' ' . $mime_query . ' ' . $cdate_query;


        $query = $this->search_cache->getQuery();
        if ($query) {
            $query = ' +(' . $query . ')';
        }
        $qp = new ilLuceneQueryParser($filter_query . $query);
        $qp->parse();
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
        $this->showSearchForm();

        $presentation = new ilSearchResultPresentation($this);
        $presentation->setResults($filter->getResultIds());
        $presentation->setSearcher($searcher);

        // TODO: other handling required
        $this->addPager($filter, 'max_page');

        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('SEARCH_RESULTS', $presentation->getHTML());
        } else {
            $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt('search_no_match_hint'), $this->search_cache->getQuery()));
        }
    }

    /**
     * Store new root node
     */
    protected function storeRoot() : void
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
     * get tabs
     */
    protected function getTabs() : void
    {
        $this->help->setScreenIdComponent("src_luc");

        $this->tabs->addTarget('search', $this->ctrl->getLinkTarget($this));

        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tabs->addTarget('search_user', $this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
        }

        if ($this->fields->getActiveFields() && !ilSearchSettings::getInstance()->getHideAdvancedSearch()) {
            $this->tabs->addTarget('search_advanced', $this->ctrl->getLinkTargetByClass('illuceneAdvancedSearchgui'));
        }

        $this->tabs->setTabActive('search');
    }

    /**
     * Init user search cache
     *
     * @access private
     *
     */
    protected function initUserSearchCache() : void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_DEFAULT);
        $page_number = $this->initPageNumberFromQuery();

        $item_filter_enabled = false;
        if ($this->http->wrapper()->post()->has('item_filter_enabled')) {
            $item_filter_enabled = $this->http->wrapper()->post()->retrieve(
                'item_filter_enabled',
                $this->refinery->kindlyTo()->bool()
            );
        }
        $post_filter_type = (array) ($this->http->request()->getParsedBody()['filter_type'] ?? []);
        if ($page_number) {
            $this->search_cache->setResultPageNumber($page_number);
        }
        if ($this->http->wrapper()->post()->has('term')) {
            $term = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
            $this->search_cache->setQuery($term);
            if ($item_filter_enabled) {
                $filtered = array();
                foreach (ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions() as $type => $data) {
                    if ($post_filter_type[$type]) {
                        $filtered[$type] = 1;
                    }
                }
                $this->search_cache->setItemFilter($filtered);

                // Mime filter
                $mime = array();
                foreach (ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions() as $type => $data) {
                    if ($post_filter_type[$type]) {
                        $mime[$type] = 1;
                    }
                }
                $this->search_cache->setMimeFilter($mime);
            }
            $this->search_cache->setCreationFilter($this->loadCreationFilter());
            if (!$item_filter_enabled) {
                // @todo: keep item filter settings
                $this->search_cache->setItemFilter(array());
                $this->search_cache->setMimeFilter(array());
            }
            $post_screation = (array) ($this->http->request()->getParsedBody()['screation'] ?? []);
            if (!count($post_screation)) {
                $this->search_cache->setCreationFilter([]);
            }
        }
    }

    /**
    * Put admin panel into template:
    * - creation selector
    * - admin view on/off button
    */
    protected function fillAdminPanel() : void
    {
        $adm_view_cmp = $adm_cmds = $creation_selector = $adm_view = false;

        // admin panel commands
        if ((count($this->admin_panel_commands) > 0)) {
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
        if (is_array($this->admin_view_button)) {
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
        if (is_array($this->creation_selector)) {
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

    /**
    * Add a command to the admin panel
    */
    protected function addAdminPanelCommand(string $a_cmd, string $a_txt) : void
    {
        $this->admin_panel_commands[] =
            array("cmd" => $a_cmd, "txt" => $a_txt);
    }

    /**
    * Show admin view button
    */
    protected function setAdminViewButton(string $a_link, string $a_txt) : void
    {
        $this->admin_view_button =
            array("link" => $a_link, "txt" => $a_txt);
    }

    protected function setPageFormAction(string $a_action) : void
    {
        $this->page_form_action = $a_action;
    }

    /**
     * Show search form
     * @return void
     */
    protected function showSearchForm() : void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_search.html', 'Services/Search');

        ilOverlayGUI::initJavascript();
        $this->tpl->addJavascript("./Services/Search/js/Search.js");


        $this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, 'performSearch'));
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($this->search_cache->getQuery()));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
        $this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
        $this->tpl->setVariable("ARR_IMG", ilGlyphGUI::get(ilGlyphGUI::CARET));
        $this->tpl->setVariable("TXT_COMBINATION", $this->lng->txt("search_term_combination"));
        $this->tpl->setVariable('TXT_COMBINATION_DEFAULT', ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ? $this->lng->txt('search_all_words') : $this->lng->txt('search_any_word'));
        $this->tpl->setVariable("TXT_AREA", $this->lng->txt("search_area"));

        if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            $this->tpl->setCurrentBlock("type_sel");
            $this->tpl->setVariable('TXT_TYPE_DEFAULT', $this->lng->txt("search_off"));
            $this->tpl->setVariable("ARR_IMGT", ilGlyphGUI::get(ilGlyphGUI::CARET));
            $this->tpl->setVariable("TXT_FILTER_BY_TYPE", $this->lng->txt("search_filter_by_type"));
            $this->tpl->setVariable('FORM', $this->form->getHTML());
            $this->tpl->parseCurrentBlock();
        }

        // search area form
        #$this->tpl->setVariable('SEARCH_AREA_FORM', $this->getSearchAreaForm()->getHTML());
        $this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));

        if (ilSearchSettings::getInstance()->isDateFilterEnabled()) {
            // begin-patch creation_date
            $this->tpl->setVariable('TXT_FILTER_BY_CDATE', $this->lng->txt('search_filter_cd'));
            $this->tpl->setVariable('TXT_CD_OFF', $this->lng->txt('search_off'));
            $this->tpl->setVariable('FORM_CD', $this->getCreationDateForm()->getHTML());
            $this->tpl->setVariable("ARR_IMG_CD", ilGlyphGUI::get(ilGlyphGUI::CARET));
            // end-patch creation_date
        }
    }


    /**
     * Parse creation date
     */
    protected function parseCreationFilter() : string
    {
        $options = $this->search_cache->getCreationFilter();

        if (!$options['enabled']) {
            return '';
        }
        $limit = new ilDate($options['date'], IL_CAL_UNIX);

        switch ($options['ontype']) {
            case 1:
                // after
                $limit->increment(IL_CAL_DAY, 1);
                $now = new ilDate(time(), IL_CAL_UNIX);
                return '+(cdate:[' . $limit->get(IL_CAL_DATE) . ' TO ' . $now->get(IL_CAL_DATE) . '*]) ';

            case 2:
                // before
                return '+(cdate:[* TO ' . $limit->get(IL_CAL_DATE) . ']) ';

            case 3:
                // on
                return '+(cdate:' . $limit->get(IL_CAL_DATE) . '*) ';

        }
        return '';
    }
    // end-patch creation_date
}
