<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCAMDPageList
*
* Advanced MD page list content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ModulesWiki
*/
class ilPCAMDPageList extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("amdpl");
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_amd_page_list", "pc_amdpl");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->amdpl_node =& $a_node->first_child();		// this is the courses node
	}

	/**
	* Create list node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->amdpl_node = $this->dom->create_element("AMDPageList");
		$this->amdpl_node = $this->node->append_child($this->amdpl_node);
	}

	/**
	 * Set list settings
	 */
	function setData(array $a_fields_data)
	{		
		global $ilDB;
		
		$data_id = $this->amdpl_node->get_attribute("Id");		
		if($data_id)
		{
			$ilDB->manipulate("DELETE FROM pg_amd_page_list".
				" WHERE id = ".$ilDB->quote($data_id, "integer"));
		}
		else
		{
			$data_id = $ilDB->nextId("pg_amd_page_list");
			$this->amdpl_node->set_attribute("Id", $data_id);
		};
		
		foreach($a_fields_data as $field_id => $field_data)
		{
			$fields = array(
				"id" => array("integer", $data_id)
				,"field_id" => array("integer", $field_id)
				,"data" => array("text", serialize($field_data))
			);		
			$ilDB->insert("pg_amd_page_list", $fields);	
		}
	}
	
	/**
	 * Get consultation hours group ids
	 *
	 * @return string
	 */
	function getFieldValues()
	{
		global $ilDB;
		
		$res = array();
		if (is_object($this->amdpl_node))
		{
			$data_id = $this->amdpl_node->get_attribute("Id");		
			if($data_id)
			{
				$set = $ilDB->query("SELECT * FROM pg_amd_page_list".
					" WHERE id = ".$ilDB->quote($data_id, "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{
					$res[$row["field_id"]] = unserialize($row["data"]);
				}
			}			
		}
		return $res;
	}
	
	function modifyPageContentPostXsl($a_html, $a_mode)
	{
		$c_pos = 0;
		$start = strpos($a_html, "[[[[[AMDPageList;");
		if (is_int($start))
		{
			$end = strpos($a_html, "]]]]]", $start);
		}
		$i = 1;
		while ($end > 0)
		{
			$param = substr($a_html, $start + 9, $end - $start - 9);
			
			
			
			
			$start = strpos($a_html, "[[[[[AMDPageList;", $start + 5);
			$end = 0;
			if (is_int($start))
			{
				$end = strpos($a_html, "]]]]]", $start);
			}
		}
				
		return $a_html;
	}
}

?>