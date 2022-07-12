<?php declare(strict_types=1);


abstract class ilRepositoryObjectSearchResultTableGUI extends ilTable2GUI
{
    private ilSearchSettings $settings;
    protected int $ref_id;
    private string $search_term;
    
    private ?ilRepositoryObjectDetailSearchResult $results = null;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_ref_id)
    {
        $this->settings = ilSearchSettings::getInstance();
        $this->ref_id = $a_ref_id;
        $this->setId('rep_obj_search_res_' . $this->ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function setSearchTerm(string $a_term) : void
    {
        $this->search_term = $a_term;
    }

    public function getSearchTerm() : string
    {
        return $this->search_term;
    }

    public function getSettings() : ilSearchSettings
    {
        return $this->settings;
    }

    public function setResults(ilRepositoryObjectDetailSearchResult $a_result) : void
    {
        $this->results = $a_result;
    }
    
    public function getResults() : ilRepositoryObjectDetailSearchResult
    {
        return $this->results;
    }

    public function init() : void
    {
        $this->initColumns();
        $this->initRowTemplate();
        
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setLimit(0);
        
        $this->setTitle(
            $this->lng->txt('search_results') . ' "' . str_replace(array('"'), '', $this->getSearchTerm()) . '"'
        );
    }

    protected function initColumns() : void
    {
        if ($this->getSettings()->enabledLucene()) {
            $this->lng->loadLanguageModule('search');
            $this->addColumn($this->lng->txt("title"), "", "80%");
            $this->addColumn($this->lng->txt("lucene_relevance_short"), "", "20%");
        } else {
            $this->addColumn($this->lng->txt("title"), "", "100%");
        }
    }

    protected function initRowTemplate() : void
    {
        $this->setRowTemplate('tpl.repository_object_search_result_row.html', 'Services/Search');
    }
    

    abstract public function parse();
    

    public function getRelevanceHTML(float $a_rel) : string
    {
        $tpl = new ilTemplate('tpl.lucene_relevance.html', true, true, 'Services/Search');

        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($a_rel);
        
        $tpl->setCurrentBlock('relevance');
        $tpl->setVariable('REL_PBAR', $pbar->render());
        $tpl->parseCurrentBlock();
        
        return $tpl->get();
    }
}
