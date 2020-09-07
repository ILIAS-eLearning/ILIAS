<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilSearchSettings.php';
include_once './Services/Search/classes/class.ilSearchBaseGUI.php';
include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';


/**
 * @classDescription GUI for  Lucene user search
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilLuceneUserSearchGUI: ilPublicUserProfileGUI
 * @ilCtrl_IsCalledBy ilLuceneUserSearchGUI: ilSearchController
 *
 * @ingroup ServicesSearch
 */
class ilLuceneUserSearchGUI extends ilSearchBaseGUI
{
    protected $ilTabs;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $this->tabs_gui = $ilTabs;
        parent::__construct();
        $this->initUserSearchCache();
    }
    
    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $this->prepareOutput();
        switch ($next_class) {
            case "ilpublicuserprofilegui":
                include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
                $profile = new ilPublicUserProfileGUI((int) $_REQUEST['user']);
                $profile->setBackUrl($this->ctrl->getLinkTarget($this, 'showSavedResults'));
                $ret = $ilCtrl->forwardCommand($profile);
                $GLOBALS['DIC']['tpl']->setContent($ret);
                break;

            
            default:
                $this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_USER);
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->handleCommand($cmd);
                break;
        }
        return true;
    }

    /**
     * Add admin panel command
     * @todo
     */
    public function prepareOutput()
    {
        parent::prepareOutput();
        $this->getTabs();
        return true;
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
     * @return type
     */
    protected function getDetails()
    {
        return (array) $this->search_cache->getItemFilter();
    }
    
    
    /**
     * Search from main menu
     */
    protected function remoteSearch()
    {
        $_POST['query'] = $_POST['queryString'];
        $this->search_cache->setRoot((int) $_POST['root_id']);
        $this->search_cache->setQuery(ilUtil::stripSlashes($_POST['queryString']));
        $this->search_cache->save();
        
        $this->search();
    }
    
    /**
     * Show saved results
     * @return
     */
    protected function showSavedResults()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilBench = $DIC['ilBench'];
        
        if (strlen($this->search_cache->getQuery())) {
            return $this->performSearch();
        }

        return $this->showSearchForm();
    }
    
    /**
     * Search (button pressed)
     * @return
     */
    protected function search()
    {
        if (!$this->form->checkInput()) {
            $this->search_cache->deleteCachedEntries();
            // Reset details
            include_once './Services/Object/classes/class.ilSubItemListGUI.php';
            ilSubItemListGUI::resetDetails();
            $this->showSearchForm();
            return false;
        }
        
        unset($_SESSION['max_page']);
        $this->search_cache->deleteCachedEntries();
        
        // Reset details
        include_once './Services/Object/classes/class.ilSubItemListGUI.php';
        ilSubItemListGUI::resetDetails();
        
        $this->performSearch();
    }
    
    /**
     * Perform search
     */
    protected function performSearch()
    {
        include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
        include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
        $qp = new ilLuceneQueryParser($this->search_cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->setType(ilLuceneSearcher::TYPE_USER);
        $searcher->search();
        
        $this->showSearchForm();
        
        include_once './Services/Search/classes/class.ilRepositoryUserResultTableGUI.php';
        $user_table = new ilRepositoryUserResultTableGUI(
            $this,
            'performSearch',
            false,
            ilRepositoryUserResultTableGUI::TYPE_GLOBAL_SEARCH
        );
        $user_table->setLuceneResult($searcher->getResult());
        $user_table->parseUserIds($searcher->getResult()->getCandidates());

        $GLOBALS['DIC']['tpl']->setVariable('SEARCH_RESULTS', $user_table->getHTML());
        
        return true;
    }
    
    /**
     * get tabs
     */
    protected function getTabs()
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenIdComponent("src_luc");

        $this->tabs_gui->addTarget('search', $this->ctrl->getLinkTargetByClass('illucenesearchgui'));
        
        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tabs_gui->addTarget('search_user', $this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
        }
        
        $fields = ilLuceneAdvancedSearchFields::getInstance();
        
        if (
            !ilSearchSettings::getInstance()->getHideAdvancedSearch() and
            $fields->getActiveFields()) {
            $this->tabs_gui->addTarget('search_advanced', $this->ctrl->getLinkTargetByClass('illuceneadvancedsearchgui'));
        }
        
        $this->tabs_gui->setTabActive('search_user');
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
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_usr_search.html', 'Services/Search');

        // include js needed
        include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
        ilOverlayGUI::initJavascript();
        $this->tpl->addJavascript("./Services/Search/js/Search.js");

        $this->tpl->setVariable('FORM_ACTION', $GLOBALS['DIC']['ilCtrl']->getFormAction($this, 'performSearch'));
        $this->tpl->setVariable("TERM", ilUtil::prepareFormOutput($this->search_cache->getQuery()));
        include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
        
        return true;
    }
}
