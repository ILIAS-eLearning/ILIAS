<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/Timeline/interfaces/interface.ilTimelineItemInt.php");

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilTimelineGUI
{
	protected $items = array();

	/**
	 * Construct
	 *
	 * @param
	 * @return
	 */
	protected function __construct()
	{

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
		$t = new ilTemplate("tpl.timeline.html", true, true, "Services/News/Timeline");
		foreach ($this->items as $i)
		{
			$t->setCurrentBlock("item");
			$t->setVariable("CONTENT", $i->render());
			$t->parseCurrentBlock();
		}
		return $t->get();
	}
	

}

?>