<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Timeline for news
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNews
 */
class ilNewsTimelineGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * Constructor
	 *
	 * @param int $a_ref_id reference id
	 */
	protected function __construct($a_ref_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->ref_id = $a_ref_id;
	}

	/**
	 * Get instance
	 *
	 * @param int $a_ref_id reference id
	 * @return ilNewsTimelineGUI
	 */
	static function getInstance($a_ref_id)
	{
		return new self($a_ref_id);
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("show");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Show
	 *
	 * @param
	 * @return
	 */
	function show()
	{
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_item = new ilNewsItem();
		$news_item->setContextObjId($this->ctrl->getContextObjId());
		$news_item->setContextObjType($this->ctrl->getContextObjType());

		$news_data = $news_item->getNewsForRefId($this->ref_id);

		include_once("./Services/News/Timeline/classes/class.ilTimelineGUI.php");
		include_once("./Services/News/classes/class.ilNewsTimelineItemGUI.php");
		$timeline = ilTimelineGUI::getInstance();

		foreach ($news_data as $d)
		{
			$news_item = new ilNewsItem($d["id"]);
			$item = ilNewsTimelineItemGUI::getInstance($news_item);
			$timeline->addItem($item);
		}

		$this->tpl->setContent($timeline->render());

		$this->tpl->addJavaScript("./Services/News/js/News.js");
	}
	

}
?>