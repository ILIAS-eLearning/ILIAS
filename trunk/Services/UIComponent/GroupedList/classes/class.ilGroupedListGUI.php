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
	private $items = array();
	
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
	function addGroupHeader($a_content, $a_add_class = "")
	{
		$this->items[] = array("type" => "group_head", "content" => $a_content,
			"add_class" => $a_add_class);
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
	function addEntry($a_content, $a_href="", $a_target="", $a_onclick="", $a_add_class = "",
		$a_id = "", $a_ttip = "", $a_tt_my = "right center", $a_tt_at = "left center",
		$a_tt_use_htmlspecialchars = true)
	{
		$this->items[] = array("type" => "entry", "content" => $a_content,
			"href" => $a_href, "target" => $a_target, "onclick" => $a_onclick,
			"add_class" => $a_add_class, "id" => $a_id, "ttip" => $a_ttip,
			"tt_my" => $a_tt_my, "tt_at" => $a_tt_at,
			"tt_use_htmlspecialchars" => $a_tt_use_htmlspecialchars);
	}
	
	
	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		global $ilCtrl;
		
		$tpl = new ilTemplate("tpl.grouped_list.html", true, true, "Services/UIComponent/GroupedList");
		$tt_calls = "";
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
					if ($i["add_class"] != "")
					{
						$tpl->setVariable("ADD_CLASS", $i["add_class"]);
					}
					$tpl->setVariable("GROUP_HEAD", $i["content"]);
					$tpl->parseCurrentBlock();
					$tpl->touchBlock("item");
					break;
					
				case "entry":
					if ($i["href"] != "")
					{
						$tpl->setCurrentBlock("linked_entry");
						if ($i["add_class"] != "")
						{
							$tpl->setVariable("ADD_CLASS", $i["add_class"]);
						}
						$tpl->setVariable("HREF", $i["href"]);
						$tpl->setVariable("TXT_ENTRY", $i["content"]);
						if ($i["target"] != "")
						{
							$tpl->setVariable("TARGET", 'target="'.$i["target"].'"');
						}
						else
						{
							$tpl->setVariable("TARGET", 'target="_top"');
						}
						if ($i["onclick"] != "")
						{
							$tpl->setVariable("ONCLICK", 'onclick="'.$i["onclick"].'"');
						}
						if ($i["id"] != "")
						{
							$tpl->setVariable("ID", 'id="'.$i["id"].'"');
						}
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						if ($i["ttip"] != "" && $i["id"] != "")
						{
							include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
							if ($ilCtrl->isAsynch())
							{
								$tt_calls.= " ".ilTooltipGUI::getTooltip($i["id"], $i["ttip"],
									"", $i["tt_my"], $i["tt_at"], $i["tt_use_htmlspecialchars"]);
							}
							else
							{
								ilTooltipGUI::addTooltip($i["id"], $i["ttip"],
									"", $i["tt_my"], $i["tt_at"], $i["tt_use_htmlspecialchars"]);
							}
						}

					}
					break;
			}
		}
		
		if ($this->multi_column)
		{
			$tpl->touchBlock("multi_start");
			$tpl->touchBlock("multi_end");
		}
		
		if ($tt_calls != "")
		{
			$tpl->setCurrentBlock("script");
			$tpl->setVariable("TT_CALLS", $tt_calls);
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}
	
}

?>
