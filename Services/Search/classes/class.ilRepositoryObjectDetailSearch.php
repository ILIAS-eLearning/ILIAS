<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilRepositoryObjectSearchGUI
 * Repository object detail search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectDetailSearch
{
    protected ilSearchSettings $settings;
    
    protected int $obj_id;
    protected string $type;
    protected string $query_string;
    

    public function __construct(int $a_obj_id)
    {
        $this->obj_id = $a_obj_id;
        $this->type = ilObject::_lookupType($this->getObjId());
        
        $this->settings = ilSearchSettings::getInstance();
    }

    public function getSettings() : ilSearchSettings
    {
        return $this->settings;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }
    
    public function getType() : string
    {
        return $this->type;
    }
    
    
    public function setQueryString(string $a_query) : void
    {
        $this->query_string = $a_query;
    }
    

    public function getQueryString() : string
    {
        return $this->query_string;
    }

    public function performSearch() : ilRepositoryObjectDetailSearchResult
    {
        if ($this->getSettings()->enabledLucene()) {
            return $this->performLuceneSearch();
        } else {
            return $this->performDBSearch();
        }
    }
    
    /**
     * @throws ilLuceneQueryParserException
     */
    protected function performLuceneSearch() : ilRepositoryObjectDetailSearchResult
    {
        try {
            $qp = new ilLuceneQueryParser($this->getQueryString());
            $qp->parse();
        } catch (ilLuceneQueryParserException $e) {
            ilLoggerFactory::getLogger('src')->warning('Invalid query: ' . $e->getMessage());
            throw $e;
        }

        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->highlight(array($this->getObjId()));

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
     * @throws Exception
     */
    protected function performDBSearch() : ilRepositoryObjectDetailSearchResult
    {
        $query_parser = new ilQueryParser($this->getQueryString());
        
        $query_parser->setCombination(
            ($this->getSettings()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND) ?
                ilQueryParser::QP_COMBINATION_AND :
                ilQueryParser::QP_COMBINATION_OR
        );
        $query_parser->parse();
        
        if (!$query_parser->validate()) {
            throw new Exception($query_parser->getMessage());
        }
        $search_result = new ilSearchResult();

        $search = ilObjectSearchFactory::getByTypeSearchInstance($this->getType(), $query_parser);
        
        switch ($this->getType()) {
            case 'wiki':
                $search->setFilter(array('wpg'));
                break;
        }
        
        $search->setIdFilter(array($this->getObjId()));
        
        $search_result->mergeEntries($search->performSearch());

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
