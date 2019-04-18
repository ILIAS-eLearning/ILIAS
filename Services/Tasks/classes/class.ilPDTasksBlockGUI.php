<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * BlockGUI class for Tasks on PD
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_IsCalledBy ilPDTasksBlockGUI: ilColumnGUI
 */
class ilPDTasksBlockGUI extends ilBlockGUI
{
	static $block_type = "pdtasks";

	protected $tasks = [];

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$lng = $DIC->language();

		parent::__construct();

		$this->setLimit(5);
		$lng->loadLanguageModule("task");
		$this->setTitle($lng->txt("task_derived_tasks"));
		$this->setAvailableDetailLevels(2);
	}

	/**
	 * @inheritdoc
	 */
	public function getBlockType(): string
	{
		return self::$block_type;
	}

	/**
	 * @inheritdoc
	 */
	protected function isRepositoryObject(): bool
	{
		return false;
	}

	/**
	 * Get Screen Mode for current command.
	 */
	static function getScreenMode()
	{
		switch($_GET["cmd"])
		{
			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	 * execute command
	 */
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;

		$cmd = $ilCtrl->getCmd("getHTML");

		return $this->$cmd();
	}

	function getHTML()
	{
		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}
		else
		{
			return parent::getHTML();
		}
	}

	/**
	 * Fill data section
	 */
	function fillDataSection()
	{
		global $DIC;

		$collector = $DIC->task()->derived()->factory()->collector();

		$this->tasks = $collector->getEntries($this->user->getId());

		if ($this->getCurrentDetailLevel() > 1 && count($this->tasks) > 0)
		{
			$this->setRowTemplate("tpl.pd_tasks.html", "Services/Tasks");
			$this->getListRowData();
			//$this->setColSpan(2);
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			if (count($this->tasks) == 0)
			{
				$this->setEnableDetailRow(false);
			}
			$this->setDataSection($this->getOverview());
		}
	}


	/**
	 * Get list data.
	 */
	function getListRowData()
	{
		$data = [];

		/** @var ilDerivedTask $task */
		foreach($this->tasks as $task)
		{
			$data[] = array(
				"title" => $task->getTitle(),
				"ref_id" => $task->getRefId(),
				"deadline" => $task->getDeadline(),
				"starting_time" => $task->getStartingTime()
			);
		}

		$this->setData($data);
	}

	/**
	 * get flat bookmark list for personal desktop
	 */
	function fillRow($a_set)
	{
		global $DIC;

		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
		$lng = $this->lng;

		$info_screen = new ilInfoScreenGUI($this);
		$info_screen->setFormAction("#");
		$info_screen->addSection($lng->txt(""));
		//$toolbar = new ilToolbarGUI();

		$info_screen->addProperty($lng->txt("task_task"),
			$a_set["title"]);

		if ($a_set["ref_id"] > 0)
		{
			$obj_id = ilObject::_lookupObjId($a_set["ref_id"]);
			$obj_type = ilObject::_lookupType($obj_id);
			$link = $factory->button()->shy(ilObject::_lookupTitle($obj_id), ilLink::_getStaticLink($a_set["ref_id"]));
			$info_screen->addProperty($lng->txt("obj_".$obj_type),
				$renderer->render($link));
		}

		if ($a_set["starting_time"] > 0)
		{
			$start = new ilDateTime($a_set["starting_time"], IL_CAL_UNIX);
			$info_screen->addProperty($lng->txt("task_start"),
				ilDatePresentation::formatDate($start));
		}

		if ($a_set["deadline"] > 0)
		{
			$end = new ilDateTime($a_set["deadline"], IL_CAL_UNIX);
			$info_screen->addProperty($lng->txt("task_deadline"),
				ilDatePresentation::formatDate($end));
		}

		$modal = $factory->modal()->roundtrip($lng->txt("task_details"),
			$factory->legacy($info_screen->getHTML()))
			->withCancelButtonLabel("close");
		$button1 = $factory->button()->shy($a_set["title"], '#')
			->withOnClick($modal->getShowSignal());

		$this->tpl->setVariable("TITLE", $renderer->render([$button1, $modal]));
	}

	/**
	 * Get overview.
	 */
	function getOverview()
	{
		$lng = $this->lng;

		return '<div class="small">'.((int) count($this->tasks))." ".$lng->txt("task_derived_tasks")."</div>";
	}
}

?>
