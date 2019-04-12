<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived tasks list
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
class ilDerivedTasksGUI
{
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $main_tpl;

	/**
	 * @var ilTaskService
	 */
	protected $task;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 * @param \ILIAS\DI\Container|null $dic
	 */
	public function __construct(\ILIAS\DI\Container $di_container = null)
	{
		global $DIC;

		if (is_null($di_container))
		{
			$di_container = $DIC;
		}
		$this->dic = $di_container;
		$this->ctrl = $DIC->ctrl();
		$this->main_tpl = $DIC->ui()->mainTemplate();
		$this->task = $DIC->task();
		$this->user = $DIC->user();
		$this->ui = $DIC->ui();
		$this->lng = $DIC->language();

		$this->lng->loadLanguageModule("task");
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;
		$main_tpl = $this->main_tpl;
		$main_tpl->loadStandardTemplate();

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show")))
				{
					$this->$cmd();
				}
		}
		$main_tpl->printToStdout();
	}

	/**
	 * Show list of tasks
	 */
	protected function show()
	{
		$ui = $this->ui;
		$lng = $this->lng;
		$main_tpl = $this->main_tpl;

		$main_tpl->setTitle($lng->txt("task_derived_tasks"));

		$f = $ui->factory();
		$renderer = $ui->renderer();

		$collector = $this->task->derived()->factory()->collector();

		$entries = $collector->getEntries($this->user->getId());

		$list_items_with_deadline = [];
		$list_items_without_deadline = [];
		$item_groups = [];

		// item groups from tasks
		foreach ($entries as $i)
		{
			$props = [];
			if ($i->getRefId() > 0)
			{
				$obj_id = ilObject::_lookupObjId($i->getRefId());
				$obj_type = ilObject::_lookupType($obj_id);
				$title = $f->button()->shy($i->getTitle(), ilLink::_getStaticLink($i->getRefId()));
				$props[$lng->txt("obj_".$obj_type )] = ilObject::_lookupTitle($obj_id);
			}
			else
			{
				$title = $i->getTitle();
			}

			if ($i->getStartingTime() > 0)
			{
				$start = new ilDateTime($i->getStartingTime(), IL_CAL_UNIX);
				$props[$lng->txt("task_start")] = ilDatePresentation::formatDate($start);
			}
			if ($i->getDeadline() > 0)
			{
				$end = new ilDateTime($i->getDeadline(), IL_CAL_UNIX);
				$props[$lng->txt("task_deadline")] = ilDatePresentation::formatDate($end);
			}
			$item = $f->item()->standard($title)->withProperties($props);
			if ($i->getDeadline() > 0)
			{
				$list_items_with_deadline[] = $item;
			}
			else
			{
				$list_items_without_deadline[] = $item;
			}
		}
		if (count($list_items_with_deadline) > 0)
		{
			$item_groups[] = $f->item()->group($lng->txt("task_tasks_with_deadline"), $list_items_with_deadline);
		}
		if (count($list_items_without_deadline) > 0)
		{
			$item_groups[] = $f->item()->group($lng->txt("task_tasks_without_deadline"), $list_items_without_deadline);
		}

		// output list panel or info message
		if (count($item_groups) > 0)
		{
			$std_list = $f->panel()->listing()->standard("", $item_groups);
			$main_tpl->setContent($renderer->render($std_list));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("task_no_tasks"));
		}
	}

	
}