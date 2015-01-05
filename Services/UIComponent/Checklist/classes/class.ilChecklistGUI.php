<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup 
 */
class ilChecklistGUI
{
	protected $items = array();

	const STATUS_OK = "ok";
	const STATUS_NOT_OK = "nok";
	const STATUS_NO_STATUS = "no_status";

	function __construct()
	{

	}

	/**
	 * Add item
	 *
	 * @param string $a_txt text
	 */
	function addEntry($a_txt, $a_href, $a_status = self::STATUS_NO_STATUS, $a_highlighted = false, $a_info_texts = array())
	{
		$this->items[] = array(
			"txt" => $a_txt,
			"href" => $a_href,
			"status" => $a_status,
			"highlighted" => $a_highlighted,
			"info" => $a_info_texts
		);
	}
	
	/**
	 * Set heading
	 *
	 * @param string $a_val heading	
	 */
	function setHeading($a_val)
	{
		$this->heading = $a_val;
	}
	
	/**
	 * Get heading
	 *
	 * @return string heading
	 */
	function getHeading()
	{
		return $this->heading;
	}

	/**
	 * Get HTML
	 */
	function getHTML()
	{
		global $lng;

		include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");
		$panel = ilPanelGUI::getInstance();
		$panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
		$panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_BLOCK);
		$panel->setHeading($this->getHeading());
		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
		$list = new ilGroupedListGUI();
		foreach ($this->items as $i)
		{
			$item_tpl = new ilTemplate("tpl.checklist_item.html", true, true, "Services/UIComponent/Checklist");

			if (!is_array($i["info"]) && $i["info"] != "")
			{
				$i["info"] = array($i["info"]);
			}
			if (is_array($i["info"]) && count($i["info"]) > 0)
			{
				foreach ($i["info"] as $info)
				{
					$item_tpl->setCurrentBlock("info");
					$item_tpl->setVariable("INFO_MSG", $info);
					$item_tpl->parseCurrentBlock();
				}
			}

			$item_tpl->setVariable("TXT_STEP", $i["txt"]);
			switch ($i["status"])
			{
				case self::STATUS_OK:
					$item_tpl->setVariable("STATUS_IMG", ilUtil::getImagePath("icon_ok.svg"));
					$item_tpl->setVariable("STATUS_ALT", $lng->txt("uic_checklist_ok"));
					break;

				case self::STATUS_NOT_OK:
					$item_tpl->setVariable("STATUS_IMG", ilUtil::getImagePath("icon_not_ok.svg"));
					$item_tpl->setVariable("STATUS_ALT", $lng->txt("uic_checklist_not_ok"));
					break;
			}
			$list->addEntry($item_tpl->get(), $i["href"], "", "", $i["highlighted"] ? "ilHighlighted" : "");
		}
		$ch_tpl = new ilTemplate("tpl.checklist.html", true, true, "Services/UIComponent/Checklist");
		$ch_tpl->setVariable("LIST", $list->getHTML());
		$panel->setBody($ch_tpl->get());
		return $panel->getHTML();
	}

}

?>