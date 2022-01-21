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
class ilRepositoryObjectDetailSearchResult
{
    private array $results = array();

    public function __construct()
    {
    }
    
    /**
     * Get results
     * Db search : array(
     * array(
     *   'obj_id' => 1,
     *   'item_id' => 123)
     * );
     * Lucene search: array(
     * array(
     *   'obj_id' => 1
     *   'item_id' => 123
     *	 'relevance' => '100%'
     *	 'content' => 'this is a <span class="ilSearchHighlight">hit</span>'
     */
    public function getResults() : array
    {
        return $this->results;
    }

    public function addResultSet(array $result_set) : void
    {
        $this->results[] = $result_set;
    }
}
