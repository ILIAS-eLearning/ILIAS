<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 * @ingroup
 */
class ilLMContentRendererGUI
{
	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var int
	 */
	protected $current_page;

	/**
	 * @var ilObjLearningModule
	 */
	protected $lm;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var bool
	 */
	protected $offline;

	/**
	 * @var ilLMTracker
	 */
	protected $tracker;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLMTree
	 */
	protected $lm_tree;

	/**
	 * @var ilLMPresentationGUI
	 */
	protected $parent_gui;

	/**
	 * @var ilSetting
	 */
	protected $lm_set;

	/**
	 * @var string
	 */
	protected $lang;

	/**
	 * Constructor
	 */
	public function __construct(int $current_page, ilObjLearningModule $lm, bool $offline,
		bool $chapter_has_no_active_page,
		bool $deactivated_page,
		int $focus_id,
		string $lang,
		ilSetting $lm_set,
		ilLMTree $lm_tree,
		ilLMPresentationGUI $parent_gui,
		ilLMTracker $tracker,
		ilLanguage $lng,
		ilCtrl $ctrl,
		ilAccessHandler $access,
		ilObjUser $user,
		ilHelpGUI $help)
	{
		global $DIC;

		$this->access = $access;
		$this->user = $user;
		$this->help = $help;
		$this->ctrl = $ctrl;
		$this->lm_tree = $lm_tree;
		$this->lang = $lang;
		$this->current_page = $current_page;
		$this->lm = $lm;
		$this->lm_set = $lm_set;
		$this->lng = $lng;
		$this->offline = $offline;
		$this->tracker = $tracker;
		$this->parent_gui = $parent_gui;
		$this->chapter_has_no_active_page = $chapter_has_no_active_page;
		$this->deactivated_page = $deactivated_page;
		$this->focus_id = $focus_id;
	}

    
}