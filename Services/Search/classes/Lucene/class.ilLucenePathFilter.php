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

include_once './Services/Search/interfaces/interface.ilLuceneResultFilter.php';
/** 
* Lucene path filter
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLucenePathFilter implements ilLuceneResultFilter
{
	protected $root = ROOT_FOLDER_ID;
	protected $subnodes = array();
	
	
	/**
	 * Constructor 
	 * @param int $a_root root id
	 * @return
	 */
	public function __construct($a_root)
	{
		$this->root = $a_root;
		//$this->init();
	}
	
	/**
	 * Return whether a object reference is valid or not
	 * @param int $a_ref_id reference id of object in question
	 * @return boolean
	 */
	public function filter($a_ref_id)
	{
		global $tree;
		
		if($this->root == ROOT_FOLDER_ID)
		{
			return true;
		}
		return $tree->isGrandChild($this->root, $a_ref_id);
	}
	
	/**
	 * Read valid reference ids 
	 * @return
	 */
	protected function init()
	{
		global $tree;
		
		if($this->root == ROOT_FOLDER_ID) {
			$this->subnodes = array();
		}
		else
		{
			$node = $tree->getNodeData($this->root);
			$this->subnodes = $tree->getSubTree($node,false);
		}
	}
}
?>
