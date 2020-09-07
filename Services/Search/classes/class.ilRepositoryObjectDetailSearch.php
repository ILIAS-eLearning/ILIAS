<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilSearchSettings.php';

/**
 * Class ilRepositoryObjectSearchGUI
 * Repository object detail search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectDetailSearch
{
    protected $settings = null;
    
    protected $obj_id;
    protected $type;
    protected $query_string;
    
    /**
     * constructor
     */
    public function __construct($a_obj_id)
    {
        $this->obj_id = $a_obj_id;
        $this->type = ilObject::_lookupType($this->getObjId());
        
        $this->settings = ilSearchSettings::getInstance();
    }
    
    /**
     * Get settings
     * @return ilSearchSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * get obj id
     * @return type
     */
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    
    public function setQueryString($a_query)
    {
        $this->query_string = $a_query;
    }
    

    /**
     * get query string
     */
    public function getQueryString()
    {
        return $this->query_string;
    }
    
    /**
     * Perform search
     * @return ilRepositoryObjectDetailSearchResult
     *
     * @throws ilLuceneQueryParserException
     */
    public function performSearch()
    {
        if ($this->getSettings()->enabledLucene()) {
            return $this->performLuceneSearch();
        } else {
            return $this->performDBSearch();
        }
    }
    
    /**
     * Perform lucene search
     * @throws ilLuceneQueryParserException
     */
    protected function performLuceneSearch()
    {
        include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
        try {
            $qp = new ilLuceneQueryParser($this->getQueryString());
            $qp->parse();
        } catch (ilLuceneQueryParserException $e) {
            ilLoggerFactory::getLogger('src')->warning('Invalid query: ' . $e->getMessage());
            throw $e;
        }
        
        include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->highlight(array($this->getObjId()));
        
        include_once './Services/Search/classes/class.ilRepositoryObjectDetailSearchResult.php';
        $detail_search_result = new ilRepositoryObjectDetailSearchResult();
        
        if ($searcher->getHighlighter() instanceof ilLuceneHighlighterResultParser) {
            foreach ($searcher->getHighlighter()->getSubItemIds($this->getObjId()) as $sub_id) {
                $detail_search_result->addResultSet(
                    array(
                        'obj_id' => $this->getObjId(),
                        'item_id' => $sub_id,
                        'relevance' => $searcher->getHighlighter()->getRelevance($this->getObjId(), $sub_id),
                        'content' => $searcher->getHighlighter()->getContent($this->getObjId(), $sub_id)
                    )
                );
            }
        }
        return $detail_search_result;
    }
    
    
    /**
     * Perform DB  search
     * @return ilRepositoryObjectDetailSearchResult
     */
    protected function performDBSearch()
    {
        // query parser
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser($this->getQueryString());
        
        $query_parser->setCombination(
            ($this->getSettings()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND) ?
                QP_COMBINATION_AND :
                QP_COMBINATION_OR
        );
        $query_parser->parse();
        
        if (!$query_parser->validate()) {
            throw new Exception($query_parser->getMessage());
        }
        include_once 'Services/Search/classes/class.ilSearchResult.php';
        $search_result = new ilSearchResult();
        
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        $search = ilObjectSearchFactory::getByTypeSearchInstance($this->getType(), $query_parser);
        
        switch ($this->getType()) {
            case 'wiki':
                $search->setFilter(array('wpg'));
                break;
        }
        
        $search->setIdFilter(array($this->getObjId()));
        
        $search_result->mergeEntries($search->performSearch());
        
        include_once './Services/Search/classes/class.ilRepositoryObjectDetailSearchResult.php';
        $detail_search_result = new ilRepositoryObjectDetailSearchResult();
        
        foreach ($search_result->getEntries() as $entry) {
            foreach ((array) $entry['child'] as $child) {
                $detail_search_result->addResultSet(
                    array(
                            'obj_id' => $entry['obj_id'],
                            'item_id' => $child
                        )
                );
            }
        }
        return $detail_search_result;
    }
}
