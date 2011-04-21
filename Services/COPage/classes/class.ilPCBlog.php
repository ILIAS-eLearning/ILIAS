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

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCBlog
*
* Blog content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ServicesCOPage
*/
class ilPCBlog extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("blog");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->blog_node =& $a_node->first_child();		// this is the blog node
	}

	/**
	* Create blog node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->blog_node = $this->dom->create_element("Blog");
		$this->blog_node = $this->node->append_child($this->blog_node);
	}

	/**
	 * Set blog settings
	 *
	 * @param int $a_blog_id
	 * @param array $a_posting_ids
	 */
	function setData($a_blog_id, array $a_posting_ids = null)
	{
		global $ilUser;
		
		$this->blog_node->set_attribute("Id", $a_blog_id);
		$this->blog_node->set_attribute("User", $ilUser->getId());

		// remove all children first
		$children = $this->blog_node->child_nodes();
		if($children)
		{
			foreach($children as $child)
			{
				$this->blog_node->remove_child($child);
			}
		}

		if(sizeof($a_posting_ids))
		{
			foreach($a_posting_ids as $posting_id)
			{
				$post_node = $this->dom->create_element("BlogPosting");
				$post_node = $this->blog_node->append_child($post_node);
				$post_node->set_attribute("Id", $posting_id);
			}
		}
	}

	/**
	 * Get blog mode
	 *
	 * @return string
	 */
	function getBlogId()
	{
		if (is_object($this->blog_node))
		{
			return $this->blog_node->get_attribute("Id");
		}
	}

	/**
	* Get blog postings
	*
	* @return array
	*/
	function getPostings()
	{
		$res = array();
		if (is_object($this->blog_node))
		{
			$children = $this->blog_node->child_nodes();
			if($children)
			{
				foreach($children as $child)
				{
					$res[] = $child->get_attribute("Id");
				}
			}
		}
		return $res;
	}
}
?>
