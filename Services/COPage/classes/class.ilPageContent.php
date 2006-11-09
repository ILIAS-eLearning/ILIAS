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
* Class ilPageContent
*
* Content object of ilPageObject (see ILIAS DTD). Every concrete object
* should be an instance of a class derived from ilPageContent (e.g. ilParagraph,
* ilMediaObject, ...)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageContent
{
	var $ilias;
	var $type;
	var $hier_id; 				// hierarchical editing id
	var $node;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageContent()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}


	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function getType()
	{
		return $this->type;
	}

	/**
	*/
	function setNode(&$a_node)
	{
		$this->node =& $a_node;
	}
	

	/**
	*/
	function &getNode()
	{
		return $this->node;
	}


	/**
	* set hierarchical id
	*/
	function setHierId($a_hier_id)
	{
		$this->hier_id = $a_hier_id;
	}

	/**
	* get hierarchical id
	*/
	function getHierId()
	{
		return $this->hier_id;
	}

	/**
	* static class method
	* increases an hierarchical editing id at lowest level (last number)
	*/
	function incEdId($ed_id)
	{
		$id = explode("_", $ed_id);
		$id[count($id) - 1]++;
		return implode($id, "_");
	}

	/**
	* static class method
	* decreases an hierarchical editing id at lowest level (last number)
	*/
	function decEdId($ed_id)
	{
		$id = explode("_", $ed_id);
		$id[count($id) - 1]--;
		return implode($id, "_");
	}

	/**
	* static class method
	* check, if two ids are in same container
	*/
	function haveSameContainer($ed_id1, $ed_id2)
	{
		$id1 = explode("_", $ed_id1);
		$id2 = explode("_", $ed_id1);
		if(count($id1) == count($id2))
		{
			array_pop($id1);
			array_pop($id2);
			foreach ($id1 as $key => $id)
			{
				if($id != $id2[$key])
				{
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * boolean accessor to enable/disable a paragraph
	 */
	 
	 function setEnabled ($value) 
	 {
			if (is_object($this->node))
			{
				$this->node->set_attribute("Enabled", $value);
			}
	 }
	 
	 
	 /**
	  * boolean is PageContent enabled? 
	  * 
	  */
	  
	function isEnabled ()
	{
	  	if (is_object($this->node) && $this->node->has_attribute("Enabled"))
	  	{
	  		$compare = $this->node->get_attribute("Enabled");	  			  		
	  	} 
	  	else $compare = "True";
	  	
		return strcasecmp($compare,"true") == 0;
	}


	  /**
	  * enable page content
	  */
	function enable() 
	{
//		echo "enable<br>";
		$this->setEnabled ("True");
	}
	  
	  /**
	  * disable page content
	  */
	function disable() 	
	{
//		echo "disable<br>";
		$this->setEnabled ("False");
	}

}
?>
