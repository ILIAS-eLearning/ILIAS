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
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package core
*/

require_once("classes/class.ilExplorer.php");

class ilRepositoryExplorer extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilRepositoryExplorer($a_target)
	{
		global $tree;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";
		$this->setSessionExpandVariable("repexpand");

		$this->addFilter("root");
		$this->addFilter("cat");
		$this->addFilter("grp");
		$this->addFilter("lm");
		$this->addFilter("frm");
		$this->addFilter("dbk");
		$this->setFiltered(true);

	}

	function buildLinkTarget($a_node_id, $a_type)
	{
		switch($a_type)
		{
			case "cat":
				return "repository.php?ref_id=".$a_node_id."&set_mode=flat";

			case "lm":
			case "dbk":
				return "content/lm_presentation.php?ref_id=".$a_node_id;

			case "grp":
				return "group.php?ref_id=".$a_node_id."&cmd=view";

			case "frm":
				return "forums_threads_liste.php?ref_id=".$a_node_id."&backurl=repository";
		}
	}

	function buildFrameTarget($a_type)
	{
		switch($a_type)
		{
			case "cat":
				return "";

			case "lm":
			case "dbk":
				return "_top";

			case "grp":
				return "";

			case "frm":
				return "";
		}
	}

	function isClickable($a_type, $a_ref_id)
	{
		global $rbacsystem;

		switch ($a_type)
		{
			// visible groups can allways be clicked; group processing decides
			// what happens next
			case "grp":
				return true;
				break;

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

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("repository"));
		$tpl->setVariable("LINK_TARGET", "repository.php?set_mode=flat");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

} // END class ilRepositoryExplorer
?>
