<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCSection
*
* Section content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCSection extends ilPageContent
{
	var $dom;
	var $sec_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("sec");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->sec_node =& $a_node->first_child();		// this is the Section node
	}

	/**
	* Create section node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->sec_node =& $this->dom->create_element("Section");
		$this->sec_node =& $this->node->append_child($this->sec_node);
		$this->sec_node->set_attribute("Characteristic", "Block");
	}

	/**
	* Set Characteristic of section
	*
	* @param	string	$a_char		Characteristic
	*/
	function setCharacteristic($a_char)
	{
		if (!empty($a_char))
		{
			$this->sec_node->set_attribute("Characteristic", $a_char);
		}
		else
		{
			if ($this->sec_node->has_attribute("Characteristic"))
			{
				$this->sec_node->remove_attribute("Characteristic");
			}
		}
	}

	/**
	* Get characteristic of section.
	*
	* @return	string		characteristic
	*/
	function getCharacteristic()
	{
		if (is_object($this->sec_node))
		{
			$char =  $this->sec_node->get_attribute("Characteristic");
			if (substr($char, 0, 4) == "ilc_")
			{
				$char = substr($char, 4);
			}
			return $char;
		}
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_section");
	}

	/**
	 * After page has been updated (or created)
	 *
	 * @param object $a_page page object
	 * @param DOMDocument $a_domdoc dom document
	 * @param string $a_xml xml
	 * @param bool $a_creation true on creation, otherwise false
	 */
	static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
	{
		include_once("./Services/COPage/classes/class.ilPCSection.php");
		self::saveTimings($a_page);
	}

	/**
	 * Modify page content after xsl
	 *
	 * @param string $a_output
	 * @return string
	 */
	function modifyPageContentPostXsl($a_output, $a_mode)
	{
		$a_output = self::insertTimings($a_output);

		return $a_output;
	}

	/**
	 * Set activation from
	 *
	 * @param string $a_unix_ts unix ts activation from
	 */
	function setActiveFrom($a_unix_ts)
	{
		if ($a_unix_ts > 0)
		{
			$this->sec_node->set_attribute("ActiveFrom", $a_unix_ts);
		}
		else
		{
			if ($this->sec_node->has_attribute("ActiveFrom"))
			{
				$this->sec_node->remove_attribute("ActiveFrom");
			}
		}
	}

	/**
	 * Get activation from
	 *
	 * @return string unix ts activation from
	 */
	function getActiveFrom()
	{
		if (is_object($this->sec_node))
		{
			return $this->sec_node->get_attribute("ActiveFrom");
		}

		return "";
	}

	/**
	 * Set activation to
	 *
	 * @param string $a_unix_ts unix ts activation to
	 */
	function setActiveTo($a_unix_ts)
	{
		if ($a_unix_ts > 0)
		{
			$this->sec_node->set_attribute("ActiveTo", $a_unix_ts);
		}
		else
		{
			if ($this->sec_node->has_attribute("ActiveTo"))
			{
				$this->sec_node->remove_attribute("ActiveTo");
			}
		}
	}

	/**
	 * Get activation to
	 *
	 * @return string unix ts activation to
	 */
	function getActiveTo()
	{
		if (is_object($this->sec_node))
		{
			return $this->sec_node->get_attribute("ActiveTo");
		}

		return "";
	}

	/**
	 * Save timings
	 *
	 * @param ilPageObject $a_page  page object
	 */
	static function saveTimings($a_page)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM copg_section_timings WHERE ".
			" page_id = ".$ilDB->quote($a_page->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($a_page->getParentType(), "text")
		);

		$xml = $a_page->getXMLFromDom();

		$doc = domxml_open_mem($xml);

		// media aliases
		$xpc = xpath_new_context($doc);
		$path = "//Section";
		$res = xpath_eval($xpc, $path);
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$from = $res->nodeset[$i]->get_attribute("ActiveFrom");
			if ($from != "")
			{
				$ilDB->manipulate("INSERT INTO copg_section_timings ".
					"(page_id, parent_type, unix_ts) VALUES (".
					$ilDB->quote($a_page->getId(), "integer").",".
					$ilDB->quote($a_page->getParentType(), "text").",".
					$ilDB->quote($from, "text").
					")");
			}
			$to = $res->nodeset[$i]->get_attribute("ActiveTo");
			if ($to != "")
			{
				$ilDB->manipulate("INSERT INTO copg_section_timings ".
					"(page_id, parent_type, unix_ts) VALUES (".
					$ilDB->quote($a_page->getId(), "integer").",".
					$ilDB->quote($a_page->getParentType(), "text").",".
					$ilDB->quote($to, "text").
					")");
			}
		}
	}

	/**
	 * Get page cache update trigger string
	 *
	 * @param ilPageObject $a_page
	 * @return string trigger string
	 */
	static function getCacheTriggerString($a_page)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM copg_section_timings ".
			" WHERE page_id = ".$ilDB->quote($a_page->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($a_page->getParentType(), "text")
		);
		$str = "";
		$current_ts = new ilDateTime(time(),IL_CAL_UNIX);
		$current_ts = $current_ts->get(IL_CAL_UNIX);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$unix_ts = $rec["unix_ts"];
			if ($unix_ts < $current_ts)
			{
				$unix_ts.= "a";
			}
			$str.= "-".$unix_ts;
		}

		return $str;
	}

	/**
	 * Insert timings (in edit mode)
	 *
	 * @param string $a_html html
	 * @return string htmls
	 */
	function insertTimings($a_html)
	{
		global $ilCtrl, $lng;

		$c_pos = 0;
		$start = strpos($a_html, "{{{{{Section;ActiveFrom");
		if (is_int($start))
		{
			$end = strpos($a_html, "}}}}}", $start);
		}
		$i = 1;
		while ($end > 0)
		{
			$param = substr($a_html, $start + 13, $end - $start - 13);
			$param = explode(";", $param);
			$from = $param[1];
			$to = $param[3];
			$html = "";
			if ($from != "")
			{
				ilDatePresentation::setUseRelativeDates(false);
				$from = new ilDateTime($from, IL_CAL_UNIX);
				$html.= $lng->txt("cont_active_from").": ".ilDatePresentation::formatDate($from);
			}
			if ($to != "")
			{
				$to = new ilDateTime($to, IL_CAL_UNIX);
				$html.= " ".$lng->txt("cont_active_to").": ".ilDatePresentation::formatDate($to);
			}

			$h2 = substr($a_html, 0, $start).
				$html.
				substr($a_html, $end + 5);
			$a_html = $h2;
			$i++;

			$start = strpos($a_html, "{{{{{Section;ActiveFrom;", $start + 5);
			$end = 0;
			if (is_int($start))
			{
				$end = strpos($a_html, "}}}}}", $start);
			}
		}
		return $a_html;
	}

}

?>
