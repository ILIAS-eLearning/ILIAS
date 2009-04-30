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
	private static $instance = null;
	
	private $query_parser = null;
	private $result = null;
	private $highlighter = null;
	private $page_number = 1;

	/**
	 * Constructor 
	 * @param object ilLuceneQueryParser
	 * @return
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
	 * @return
	 * @static
	 */
	public static function getInstance(ilLuceneQueryParser $qp)
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilLuceneSearcher($qp);
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
		global $ilBench;

		// Search in combined index
		$ilBench->start('Lucene','SearchHighlight');
		include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';
		$adapter = new ilLuceneRPCAdapter();
		$adapter->setQueryString($this->query_parser->getQuery());
		$adapter->setMode('highlight');
		$adapter->setResultIds($a_obj_ids);
		$res = $adapter->send();
		$ilBench->stop('Lucene','SearchHighlight');
		
		$ilBench->start('Lucene','SearchHighlightParser');
		include_once './Services/Search/classes/Lucene/class.ilLuceneHighlighterResultParser.php';
		$this->highlighter = new ilLuceneHighlighterResultParser();
		$this->highlighter->setResultString($res);
		$this->highlighter->parse();
		$ilBench->stop('Lucene','SearchHighlightParser');

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
	 * @return
	 */
	public function getHighlighter()
	{
		return $this->highlighter;	 
	}
	
	/**
	 * Get result
	 * @param
	 * @return
	 */
	public function getResult()
	{
		if($this->result instanceof ilLuceneSearchResult)
		{
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
		global $ilBench;

		// TODO error handling
		if(!$this->query_parser->getQuery())
		{
			return;
		}

		// Search in combined index
		$ilBench->start('Lucene','SearchCombinedIndex');
		include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';
		$adapter = new ilLuceneRPCAdapter();
		$adapter->setQueryString($this->query_parser->getQuery());
		$adapter->setPageNumber($this->getPageNumber());
		$adapter->setMode('search');
		$res = $adapter->send();
		$ilBench->stop('Lucene','SearchCombinedIndex');
		
		// Parse results
		$ilBench->start('Lucene','ParseSearchResult');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultParser.php';
		$parser = new ilLuceneSearchResultParser($res);
		$parser->parse($this->result);
		$ilBench->stop('Lucene','ParseSearchResult');
		return;
	}
}
?>
