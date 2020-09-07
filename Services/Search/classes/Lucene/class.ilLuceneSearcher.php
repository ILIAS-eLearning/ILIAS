<?php
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

include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResult.php';

/**
* Reads and parses lucene search results
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup
*/
class ilLuceneSearcher
{
    const TYPE_STANDARD = 1;
    const TYPE_USER = 2;
    
    private static $instance = null;
    
    private $query_parser = null;
    private $result = null;
    private $highlighter = null;
    private $page_number = 1;
    private $type = self::TYPE_STANDARD;

    /**
     * Constructor
     * @param object ilLuceneQueryParser
     * @return ilLuceneSearcher
     */
    private function __construct($qp)
    {
        $this->result = new ilLuceneSearchResult();
        $this->result->setCallback(array($this,'nextResultPage'));
        $this->query_parser = $qp;
    }
    
    /**
     * Get singleton instance
     *
     * @param object ilLuceneQueryParser
     * @return ilLuceneSearcher
     * @static
     */
    public static function getInstance(ilLuceneQueryParser $qp)
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilLuceneSearcher($qp);
    }
    
    /**
     * Set search type
     * @param type $a_type
     */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    
    /**
     * Get type
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Search
     * @return
     */
    public function search()
    {
        $this->performSearch();
    }
    
    /**
     * Highlight/Detail query
     * @param array $a_obj_ids Arry of obj_ids
     * @return object ilLuceneHighlightResultParser
     */
    public function highlight($a_obj_ids)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilSetting = $DIC['ilSetting'];

        include_once './Services/Search/classes/Lucene/class.ilLuceneHighlighterResultParser.php';
        
        // TODO error handling
        if (!$this->query_parser->getQuery()) {
            return;
        }
        
        // Search in combined index
        $ilBench->start('Lucene', 'SearchHighlight');
        try {
            include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
            $res = ilRpcClientFactory::factory('RPCSearchHandler')->highlight(
                CLIENT_ID . '_' . $ilSetting->get('inst_id', 0),
                $a_obj_ids,
                $this->query_parser->getQuery()
            );
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('src')->error('Highlighting failed with message: ' . $e->getMessage());
            return new ilLuceneHighlighterResultParser();
        }

        include_once './Services/Search/classes/Lucene/class.ilLuceneHighlighterResultParser.php';
        $this->highlighter = new ilLuceneHighlighterResultParser();
        $this->highlighter->setResultString($res);
        $this->highlighter->parse();

        return $this->highlighter;
    }
    
    /**
     * get next result page
     * @param
     * @return
     */
    public function nextResultPage()
    {
        $this->page_number++;
        $this->performSearch();
    }
    
    /**
     * get highlighter
     * @return ilLuceneHighlightResultParser
     */
    public function getHighlighter()
    {
        return $this->highlighter;
    }
    
    /**
     * Get result
     * @param
     * @return ilLuceneSearchResult
     */
    public function getResult()
    {
        if ($this->result instanceof ilLuceneSearchResult) {
            return $this->result;
        }
        // TODO Error handling
    }
    
    /**
     * get current page number
     * @param
     * @return
     */
    public function getPageNumber()
    {
        return $this->page_number;
    }
    
    /**
     * search lucene
     * @return
     */
    protected function performSearch()
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilSetting = $DIC['ilSetting'];

        // TODO error handling
        if (!$this->query_parser->getQuery()) {
            return;
        }
        $ilBench->start('Lucene', 'SearchCombinedIndex');
        try {
            switch ($this->getType()) {
                
                case self::TYPE_USER:
                    include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
                    $res = ilRpcClientFactory::factory('RPCSearchHandler')->searchUsers(
                        CLIENT_ID . '_' . $ilSetting->get('inst_id', 0),
                        (string) $this->query_parser->getQuery()
                    );
                    break;
                
                case self::TYPE_STANDARD:
                default:
                    include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
                    $res = ilRpcClientFactory::factory('RPCSearchHandler')->search(
                        CLIENT_ID . '_' . $ilSetting->get('inst_id', 0),
                        (string) $this->query_parser->getQuery(),
                        $this->getPageNumber()
                    );
                    break;
                
            }
            ilLoggerFactory::getLogger('src')->debug('Searching for: ' . $this->query_parser->getQuery());
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('src')->error('Searching failed with message: ' . $e->getMessage());
            return;
        }
        
        // Parse results
        include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultParser.php';
        $parser = new ilLuceneSearchResultParser($res);
        $parser->parse($this->result);
        return;
    }
}
