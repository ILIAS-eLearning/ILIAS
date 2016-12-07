<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  News settings for containers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesContainer
 */
class ilContainerNewsSettingsGUI
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
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilSetting
	 */
	protected $setting;

	/**
	 * @var ilObjectGUI
	 */
	protected $parent_gui;

	/**
	 * @var ilObject
	 */
	protected $object;

	/**
	 * Constructor
	 */
	function __construct(ilObjectGUI $a_parent_gui)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];
		$this->setting = $DIC["ilSetting"];
		$this->parent_gui = $a_parent_gui;
		$this->object = $this->parent_gui->object;
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
				if (in_array($cmd, array("show", "save")))
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
		$form = $this->initForm();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Init settings form.
	 */
	public function initForm()
	{
		include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		if($this->setting->get('block_activated_news'))
		{
			// Container tools (calendar, news, ... activation)
			$news = new ilCheckboxInputGUI($this->lng->txt('obj_tool_setting_news'), ilObjectServiceSettingsGUI::NEWS_VISIBILITY);
			$news->setValue(1);
			$news->setChecked($this->object->getNewsBlockActivated());
			$news->setInfo($this->lng->txt('obj_tool_setting_news_info'));
			$form->addItem($news);

			if (in_array(ilObject::_lookupType($this->object->getId()), array('crs', 'grp')))
			{
				$ref_id = array_pop(ilObject::_getAllReferences($this->object->getId()));
				include_once 'Services/Membership/classes/class.ilMembershipNotifications.php';
				ilMembershipNotifications::addToSettingsForm($ref_id, null, $news);
			}
		}

		// timeline
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_news_timeline"), "news_timeline");
		$cb->setInfo($this->lng->txt("cont_news_timeline_info"));
		$cb->setChecked($this->object->getNewsTimeline());
		$form->addItem($cb);

		// ...timeline: auto entries
		$cb2 = new ilCheckboxInputGUI($this->lng->txt("cont_news_timeline_auto_entries"), "news_timeline_auto_entries");
		$cb2->setInfo($this->lng->txt("cont_news_timeline_auto_entries_info"));
		$cb2->setChecked($this->object->getNewsTimelineAutoEntries());
		$cb->addSubItem($cb2);

		// ...timeline: landing page
		$cb2 = new ilCheckboxInputGUI($this->lng->txt("cont_news_timeline_landing_page"), "news_timeline_landing_page");
		$cb2->setInfo($this->lng->txt("cont_news_timeline_landing_page_info"));
		$cb2->setChecked($this->object->getNewsTimelineLandingPage());
		$cb->addSubItem($cb2);

		// save and cancel commands
		$form->addCommandButton("save", $this->lng->txt("save"));

		$form->setTitle($this->lng->txt("cont_news_settings"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save settings form
	 */
	public function save()
	{
		$form = $this->initForm();
		if ($form->checkInput())
		{
			include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
			$this->object->setNewsBlockActivated($form->getInput(ilObjectServiceSettingsGUI::NEWS_VISIBILITY));
			$this->object->setNewsTimeline($form->getInput("news_timeline"));
			$this->object->setNewsTimelineAutoEntries($form->getInput("news_timeline_auto_entries"));
			$this->object->setNewsTimelineLandingPage($form->getInput("news_timeline_landing_page"));


			if($this->setting->get('block_activated_news'))
			{
				if (in_array(ilObject::_lookupType($this->object->getId()), array('crs', 'grp')))
				{
					$ref_id = array_pop(ilObject::_getAllReferences($this->object->getId()));

					include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
					ilMembershipNotifications::importFromForm($ref_id, $form);
				}
			}

			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "");
		}
		else
		{
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHtml());
		}
	}

}

?>