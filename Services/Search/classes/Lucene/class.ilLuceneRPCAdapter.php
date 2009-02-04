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
* Adapter class for communication between ilias and ilRPCServer
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
include_once 'Services/WebServices/RPC/classes/class.ilRPCServerAdapter.php';

class ilLuceneRPCAdapter extends ilRPCServerAdapter
{
	var $mode = '';
	var $files = array();
	var $query_str = '';
	var $page_number;
	var $filter = '';


	function ilLuceneRPCAdapter()
	{
		parent::ilRPCServerAdapter();
	}

	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	function getMode()
	{
		return $this->mode;
	}
	function setFiles(&$files)
	{
		$this->files =& $files;
	}
	function &getFiles()
	{
		return $this->files ? $this->files : array();
	}
	function setHTLMs(&$htlms)
	{
		$this->htlms = $htlms;
	}
	function &getHTLMs()
	{
		return $this->htlms;
	}

	function setQueryString($a_str)
	{
		$this->query_str = $a_str;
	}
	function getQueryString()
	{
		return $this->query_str;
	}
	function setPageNumber($a_number)
	{
		$this->page_number = $a_number;
	}
	function getPageNumber()
	{
		return $this->page_number;
	}
	
	function setSearchFilter($a_filter)
	{
		$this->filter = $a_filter;
	}
	function getSearchFilter()
	{
		return $this->filter ? $this->filter : array();
	}

	/**
	 * Create a unique client id. Since the lucene index can be used from multiple ILIAS-Installations it must be unique over installations
	 *
	 * @return string client_identifier
	 */
	function __getClientId()
	{
		global $ilias;

		// TODO: handle problem if nic_key isn't set
		return $ilias->getSetting('nic_key').'_'.CLIENT_ID;
	}



	function send()
	{
		$this->__initClient();
		switch($this->getMode())
		{
			case 'ping':
				$this->__preparePingParams();
				break;

			case 'file':
				$this->__prepareIndexFileParams();
				break;

			case 'query':
				$this->__prepareQueryParams();
				break;

			case 'htlm':
				$this->__prepareIndexHTLMParams();
				break;

			case 'flush':
				$this->__prepareFlushIndex();
				break;
				
			// BEGIN PATCH Lucene search
			case 'search':
				$this->__prepareSearchParams();
				break;
				
			case 'highlight':
				$this->__prepareHighlightParams();
				break;
			// END PATCH Lucene Search

			default:
				$this->log->write('ilLuceneRPCHandler(): No valid mode given');
				return false;

		}
		return parent::send();
	}
	// PRIVATE
	function __prepareQueryParams()
	{
		$this->setResponseTimeout(5);
		$filter = array();
		foreach($this->getSearchFilter() as $obj_type)
		{
			$filter[] = new XML_RPC_Value($obj_type,'string');
		}
		$this->__initMessage('Searcher.ilSearch',array(new XML_RPC_Value($this->__getClientId(),"string"),
													   new XML_RPC_Value($this->getQueryString(),"string"),
													   new XML_RPC_Value($filter,'array')));

		return true;
	}

	function __preparePingParams()
	{
		$this->setResponseTimeout(5);
		$this->__initMessage('Searcher.ilPing',array(new XML_RPC_Value($this->__getClientId(),"string")));

		return true;
	}

	function __prepareIndexFileParams()
	{
		$this->setResponseTimeout(5);
		foreach($this->getFiles() as $obj_id => $fname)
		{
			$struct[$obj_id] = new XML_RPC_Value($fname,"string");
		}
		$params = array(new XML_RPC_Value($this->__getClientId(),"string"),
						new XML_RPC_Value($struct,"struct"));

		$this->__initMessage('Indexer.ilFileIndexer',$params);

		return true;
	}

	function __prepareIndexHTLMParams()
	{
		$this->setResponseTimeout(5);
		foreach($this->getHTLMs() as $obj_id => $fname)
		{
			$struct[$obj_id] = new XML_RPC_Value($fname,"string");
		}

		$this->__initMessage('Indexer.ilHTLMIndexer',array(new XML_RPC_Value($this->__getClientId(),"string"),
														   new XML_RPC_Value($struct,"struct")));

		return true;
	}
	function __prepareFlushIndex()
	{
		$this->setResponseTimeout(5);
		$this->__initMessage('Indexer.ilClearIndex',array(new XML_RPC_Value($this->__getClientId(),"string")));

		return true;
	}
	
	// BEGIN PATCH Lucene search
	/**
	 * Prepare search parameters 
	 */
	protected function __prepareSearchParams()
	{
		$this->setResponseTimeout(5);
		$this->__initMessage('search.search',array(
			new XML_RPC_Value($this->getClientKey(),'string'),
			new XML_RPC_Value($this->getQueryString(),'string'),
			new XML_RPC_Value($this->getPageNumber(),'int')));
		
		return true;
	}
	
	/**
	 * Prepare search parameters 
	 */
	protected function __prepareHighlightParams()
	{
		$this->setResponseTimeout(5);
		
		$objIds = array();
		foreach($this->getResultIds() as $obj_id)
		{
			$objIds[] = new XML_RPC_Value($obj_id,'int');
		}
		
		$this->__initMessage('search.highlight',array(
			new XML_RPC_Value($this->getClientKey(),'string'),
			new XML_RPC_VAlue($objIds,'array'),
			new XML_RPC_Value($this->getQueryString(),'string')));
		
		return true;
	}
	
	/**
	 * set result ids 
	 * @param array array of obj ids
	 * @return
	 */
	public function setResultIds($a_ids)
	{
		$this->result_ids = $a_ids;
	}
	
	/**
	 * get result ids 
	 * @return
	 */
	public function getResultIds()
	{
		return $this->result_ids ? $this->result_ids : array();
	}
	
	
	/**
	 * Get client key 
	 * @return string client key
	 */
	protected function getClientKey()
	{
		global $ilSetting;

		return CLIENT_ID.'_'.$ilSetting->get('inst_id',0);
	}
	// END PATCH Lucene Search
}
?>