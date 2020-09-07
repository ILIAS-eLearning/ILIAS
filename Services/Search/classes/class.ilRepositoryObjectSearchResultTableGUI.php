<?php

include_once './Services/Table/classes/class.ilTable2GUI.php';

abstract class ilRepositoryObjectSearchResultTableGUI extends ilTable2GUI
{
    private $settings = null;
    protected $ref_id = 0;
    private $search_term = '';
    
    private $results = null;
    
    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
    {
        $this->settings = ilSearchSettings::getInstance();
        $this->ref_id = $a_ref_id;
        $this->setId('repository_object_search_result_' . $this->ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }
    
    /**
     * Set search term
     * @param type $a_term
     */
    public function setSearchTerm($a_term)
    {
        $this->search_term = $a_term;
    }
    
    /**
     * Get search term
     * @return type
     */
    public function getSearchTerm()
    {
        return $this->search_term;
    }
    
    /**
     * Get search settings
     * @return ilSearchSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Set result object
     * @param ilRepositoryObjectDetailSearchResult $a_result
     */
    public function setResults(ilRepositoryObjectDetailSearchResult $a_result)
    {
        $this->results = $a_result;
    }
    
    public function getResults()
    {
        return $this->results;
    }

    /**
     * init table
     */
    public function init()
    {
        $this->initColumns();
        $this->initRowTemplate();
        
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setLimit(0);
        
        $this->setTitle($lng->txt('search_results') . ' "' . str_replace(array('"'), '', $this->getSearchTerm()) . '"');
    }

    /**
     * Init columns
     */
    protected function initColumns()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        
        if ($this->getSettings()->enabledLucene()) {
            $lng->loadLanguageModule('search');
            #$this->addColumn($lng->txt("title"), "title", "80%");
            #$this->addColumn($lng->txt("lucene_relevance_short"), "relevance", "20%");
            $this->addColumn($lng->txt("title"), "", "80%");
            $this->addColumn($lng->txt("lucene_relevance_short"), "", "20%");
        } else {
            $this->addColumn($lng->txt("title"), "", "100%");
        }
    }

    /**
     * init row template
     */
    protected function initRowTemplate()
    {
        $this->setRowTemplate('tpl.repository_object_search_result_row.html', 'Services/Search');
    }
    
    
    /**
     * Parse search result set and call set data
     */
    abstract public function parse();
    
    
    /**
     * Get relevance html
     */
    public function getRelevanceHTML($a_rel)
    {
        $tpl = new ilTemplate('tpl.lucene_relevance.html', true, true, 'Services/Search');
        
        include_once "Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php";
        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($a_rel);
        
        $tpl->setCurrentBlock('relevance');
        $tpl->setVariable('REL_PBAR', $pbar->render());
        $tpl->parseCurrentBlock();
        
        return $tpl->get();
    }
}
