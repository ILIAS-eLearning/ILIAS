<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilLuceneFileSearch
*
* class for searching files indexed by lucene rpc server 
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @package ilias-search
*
*/
class ilLuceneFileSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilLuceneFileSearch(&$qp_obj)
	{
		global $ilLog;

		
		$this->log =& $ilLog;
		$this->query_parser =& $qp_obj;
	}
	function &performSearch()
	{
		include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';
		include_once './Services/Search/classes/class.ilSearchResult.php';

		$result =& new ilSearchResult();

		$rpc_adapter =& new ilLuceneRPCAdapter();
		$rpc_adapter->setMode('query');
		$rpc_adapter->setSearchFilter(array('file'));
		$rpc_adapter->setQueryString($this->query_parser->getLuceneQueryString());

		if(($res = $rpc_adapter->send()) === false)
		{
			$this->log->write('Lucene searcher: Error performing search');
		}
		elseif(count($res))
		{
			foreach($res as $obj_id => $obj_type)
			{
				$result->addEntry($obj_id,$obj_type,array());
			}
		}
		return $result;
	}
		
}
?>
