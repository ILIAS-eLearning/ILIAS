<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history main GUI class
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $main_tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();

		$this->lhist_service = $DIC->learningHistory();
		$this->ui = $this->lhist_service->ui();
		$this->main_tpl = $this->ui->mainTemplate();
		$this->lng = $this->lhist_service->language();
		$this->access = $this->lhist_service->access();

		$this->lng->loadLanguageModule("lhist");

		$this->user_id = $this->lhist_service->user()->getId();
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;
		
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
	}
	
	/**
	 * Show
	 */
	protected function show()
	{
		$main_tpl = $this->main_tpl;
		$lng = $this->lng;
		$collector = $this->lhist_service->factory()->collector();

		$f = $this->ui->factory();
		$renderer = $this->ui->renderer();

		$to = time();
		$from = time() - (365 * 24 * 60 * 60);

		$entries = $collector->getEntries($from, $to, $this->user_id);

		$timeline = ilTimelineGUI::getInstance();
		foreach ($entries as $e)
		{
			$timeline->addItem(new ilLearningHistoryTimelineItem($e, $this->ui, $this->user_id, $this->access,
				$this->lhist_service->repositoryTree()));
		}


		$main_tpl->setTitle($lng->txt("lhist_learning_history"));

		if (count($entries) > 0)
		{
			$main_tpl->setContent($timeline->render());
		}
		else
		{
			$main_tpl->setContent(
				$renderer->render($f->messageBox()->info($lng->txt("lhist_no_entries"))
				));
		}
	}
	

}