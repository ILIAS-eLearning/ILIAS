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

/*
* Repository Explorer
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/

require_once("classes/class.ilExplorer.php");

class ilSearchResultExplorer extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $user_id;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilSearchResultExplorer($a_target,$a_user_id)
	{
		define("TABLE_SEARCH_DATA","search_data");
		define("TABLE_SEARCH_TREE","search_tree");

		parent::ilExplorer($a_target);

		$this->user_id = $a_user_id;
		
		$this->__setRootId();
		$this->tree = new ilTree($this->getUserId(),ROOT_ID);
		$this->tree->setTableNames(TABLE_SEARCH_TREE,TABLE_SEARCH_DATA);

		$this->root_id = ROOT_ID;
		$this->order_column = "title";
		$this->setSessionExpandVariable("sea_expand");

		$this->setFilterMode(IL_FM_POSITIV);
		$this->addFilter("seaf");
		#$this->addFilter("sea");
		$this->setFiltered(true);

	}

	function getUserId()
	{
		return $this->user_id;
	}

	function buildLinkTarget($a_node_id, $a_type)
	{
		switch($a_type)
		{
			case "seaf":
				return "search_administration.php?viewmode=flat&folder_id=".$a_node_id;
		}
	}

	function buildFrameTarget($a_type)
	{
		switch($a_type)
		{
			case "seaf":

				return "";
		}
	}

	function isClickable($a_type, $a_ref_id)
	{
		global $rbacsystem;

		return true;
		switch ($a_type)
		{
			// visible groups can allways be clicked; group processing decides
			// what happens next
			case "grp":
				return true;
				break;

			// all other types are only clickable, if read permission is given
			default:
				if ($rbacsystem->checkAccess("read", $a_ref_id))
				{
					return true;
				}
				else
				{
					return false;
				}
				break;
		}
	}

	function showChilds($a_ref_id)
	{
		global $rbacsystem;

		return true;

		if ($a_ref_id == 0)
		{
			return true;
		}

		if ($rbacsystem->checkAccess("read", $a_ref_id))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
		global $lng, $ilias;

		$tpl = new ilTemplate("tpl.tree.html", true, true);

		$lng->loadLanguageModule("search");

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("search_my_search_results"));
		$tpl->setVariable("LINK_TARGET", "search_administration.php");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

	function __setRootId()
	{
		$query = "SELECT * FROM search_tree ".
			"WHERE tree = '".$this->user_id."' ".
			"AND parent = '0'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			define(ROOT_ID,$row->child);
		}
		return true;
	}

} // END class ilRepositoryExplorer
?>