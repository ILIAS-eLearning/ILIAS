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
class ilRepositoryObjectDetailSearchResult
{
    private $results = array();
    
    /**
     * constructor
     */
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
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }
    
    /**
     * Add result set row
     */
    public function addResultSet(array $result_set)
    {
        $this->results[] = $result_set;
    }
}
