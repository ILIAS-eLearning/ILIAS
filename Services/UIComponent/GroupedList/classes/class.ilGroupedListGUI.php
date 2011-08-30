<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Grouped list GUI class 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilGroupedListGUI
{
	private $multi_column = false;
	
	/**
	 * Constructor
	 */
	function __construct()
	{
	}
	
	
	/**
	 * Add group header
	 *
	 * @param
	 * @return
	 */
	function addGroupHeader($a_content)
	{
		$this->items[] = array("type" => "group_head", "content" => $a_content);
	}
	
	/**
	 * Add separator
	 */
	function addSeparator()
	{
		$this->items[] = array("type" => "sep");
	}
	
	/**
	 * Add separator
	 */
	function nextColumn()
	{
		$this->items[] = array("type" => "next_col");
		$this->multi_column = true;
	}

	/**
	 * Add entry
	 *
	 * @param
	 * @return
	 */
	function addEntry($a_content, $a_href="", $a_target="")
	{
		$this->items[] = array("type" => "entry", "content" => $a_content,
			"href" => $a_href, "target" => $a_target);
	}
	
	
	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.grouped_list.html", true, true, "Services/UIComponent/GroupedList");
		foreach ($this->items as $i)
		{
			switch($i["type"])
			{
				case "sep":
					$tpl->touchBlock("sep");
					$tpl->touchBlock("item");
					break;
					
				case "next_col":
					$tpl->touchBlock("next_col");
					$tpl->touchBlock("item");
					break;
					
				case "group_head":
					$tpl->setCurrentBlock("group_head");
					$tpl->setVariable("GROUP_HEAD", $i["content"]);
					$tpl->parseCurrentBlock();
					$tpl->touchBlock("item");
					break;
					
				case "entry":
					if ($i["href"] != "")
					{
						$tpl->setCurrentBlock("linked_entry");
						$tpl->setVariable("HREF", $i["href"]);
						$tpl->setVariable("TXT_ENTRY", $i["content"]);
						if ($i["target"] != "")
						{
							$tpl->setVariable("TARGET", 'target="'.$i["target"].'"');
						}
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
					}
					break;
			}
		}
		
		if ($this->multi_column)
		{
			$tpl->touchBlock("multi_start");
			$tpl->touchBlock("multi_end");
		}
		
		return $tpl->get();
	}
	
}

?>
