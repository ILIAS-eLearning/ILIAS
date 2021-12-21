<?php declare(strict_types=1);
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
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $this->prepareOutput();
        switch ($next_class) {
            case "ilpublicuserprofilegui":
                include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
                $profile = new ilPublicUserProfileGUI((int) $_REQUEST['user']);
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
        return;
    }

    public function prepareOutput() : void
    {
        parent::prepareOutput();
        $this->getTabs();
    }
    
    
    
    /**
     * Get type of search (details | fast)
     * @todo rename
     * Needed for base class search form
     */
    protected function getType()
    {
        if (count($this->search_cache)) {
            return ilSearchBaseGUI::SEARCH_DETAILS;
        }
        return ilSearchBaseGUI::SEARCH_FAST;
    }
    
    /**
     * Needed for base class search form
     * @todo rename
     */
    protected function getDetails()
    {
        return $this->search_cache->getItemFilter();
    }
    
    
    /**
     * Search from main menu
     */
    protected function remoteSearch() : void
    {
        $_POST['query'] = $_POST['queryString'];
        $this->search_cache->setRoot((int) $_POST['root_id']);
        $this->search_cache->setQuery(ilUtil::stripSlashes($_POST['queryString']));
        $this->search_cache->save();
        
        $this->search();
    }
    
    /**
     * Show saved results
     * @return void
     */
    protected function showSavedResults() : void
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
    protected function search() : void
    {
        if (!$this->form->checkInput()) {
            $this->search_cache->deleteCachedEntries();
            // Reset details
            ilSubItemListGUI::resetDetails();
            $this->showSearchForm();
            return;
        }
        
        unset($_SESSION['max_page']);
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
    protected function getTabs() : void
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
    protected function initUserSearchCache() : void
    {
        
        include_once('Services/Search/classes/class.ilUserSearchCache.php');
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_USER_SEARCH);
        if ((int) $_GET['page_number']) {
            $this->search_cache->setResultPageNumber((int) $_GET['page_number']);
        }
        if (isset($_POST['term'])) {
            $this->search_cache->setQuery(ilUtil::stripSlashes($_POST['term']));
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

        // include js needed
        include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
        ilOverlayGUI::initJavascript();
        $this->tpl->addJavascript("./Services/Search/js/Search.js");

        $this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performSearch'));
        $this->tpl->setVariable("TERM", ilUtil::prepareFormOutput($this->search_cache->getQuery()));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
        
        return true;
    }
}
