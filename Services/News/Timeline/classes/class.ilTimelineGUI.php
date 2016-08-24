<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/Timeline/interfaces/interface.ilTimelineItemInt.php");

/**
 *  Timline (temporary implementation)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilTimelineGUI
{
	protected $items = array();

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * Construct
	 *
	 * @param
	 * @return
	 */
	protected function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];
	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function getInstance()
	{
		return new self();
	}

	/**
	 * Add item
	 *
	 * @param
	 * @return
	 */
	function addItem(ilTimelineItemInt $a_item)
	{
		$this->items[] = $a_item;
	}

	/**
	 * Render
	 *
	 * @param
	 * @return
	 */
	function render()
	{
		$this->tpl->addJavaScript("./Services/News/Timeline/js/Timeline.js");

		$t = new ilTemplate("tpl.timeline.html", true, true, "Services/News/Timeline");
		$last_date = "";
		foreach ($this->items as $i)
		{

			$dt = $i->getDateTime();
			if ($dt->get(IL_CAL_FKT_DATE, "Y-m-d") != $last_date)
			{
				$t->setCurrentBlock("badge");
				$t->setVariable("DAY", $dt->get(IL_CAL_FKT_DATE, "d"));
				$t->setVariable("MONTH", $this->lng->txt("month_" . $dt->get(IL_CAL_FKT_DATE, "m") . "_short"));
				$t->parseCurrentBlock();
			}
			$last_date = $dt->get(IL_CAL_FKT_DATE, "Y-m-d");

			$t->setCurrentBlock("item");
			$t->setVariable("CONTENT", $i->render());
			$t->parseCurrentBlock();
		}
		return $t->get();
	}
	

}

?>