<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter administration for containers
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterAdminGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $main_tpl;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilContainerFilterService
	 */
	protected $container_filter_service;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * Constructor
	 */
	public function __construct(ilContainerGUI $container_gui)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->main_tpl = $DIC->ui()->mainTemplate();
		$this->container_gui = $container_gui;
		$this->ref_id = $this->container_gui->object->getRefId();
		$this->toolbar = $DIC["ilToolbar"];
		$this->ui = $DIC->ui();
		$this->request = $DIC->http()->request();
		// not sure if this should go to dic someday, currently this is not an internal API
		$this->container_filter_service = new ilContainerFilterService();
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
				if (in_array($cmd, array("show", "selectFields", "saveFields")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Show table
	 */
	protected function show()
	{
		$main_tpl = $this->main_tpl;
		$ui = $this->ui;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$f = $ui->factory();

		$button = $f->button()->standard($lng->txt("cont_select_fields"),
			$ctrl->getLinkTarget($this, "selectFields"));

		$this->toolbar->addComponent($button);

		$table = new ilContainerFilterTableGUI($this, "show", $this->container_filter_service,
			$this->container_gui->object);
		$main_tpl->setContent($table->getHTML());
	}

	/**
	 * Select fields
	 */
	protected function selectFields()
	{
		$main_tpl = $this->main_tpl;
		$ui = $this->ui;
		$r = $ui->renderer();
		$form = $this->getFieldSelectionForm();
		$main_tpl->setContent($r->render($form));
	}

	/**
	 * Get field selection form
	 *
	 * @return \ILIAS\UI\Component\Input\Container\Form\Standard
	 */
	protected function getFieldSelectionForm()
	{
		$ui = $this->ui;
		$f = $ui->factory();
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$adv = $this->container_filter_service->advancedMetadata();
		$service = $this->container_filter_service;


		$fields[] = array();


		// current filter set
		$current_filters = $service->data()->getFilterSetForRefId($this->ref_id);

		// standar set
		$selected = [];
		foreach ($service->standardSet()->getFields() as $field)
		{
			$options[$field->getFieldId()] = $service->util()->getContainerFieldTitle($field->getRecordSetId(), $field->getFieldId());
			if ($current_filters->has(0, $field->getFieldId()))
			{
				$selected[] = $field->getFieldId();
			}
		}

		$fields[0] = $f->input()->field()->multiselect($lng->txt("cont_std_record_title"), $options)
			->withRequired(false)
			->withValue($selected);

		// ADV MD record sets
		foreach ($adv->getAvailableRecordSets() as $rs)
		{
			$options = [];
			$selected = [];
			foreach ($adv->getFields($rs->getRecordId()) as $fl)
			{
				$options[$fl->getFieldId()] = $fl->getTitle();
				if ($current_filters->has($rs->getRecordId(), $fl->getFieldId()))
				{
					$selected[] = $fl->getFieldId();
				}
			}
			$fields[$rs->getRecordId()] = $f->input()->field()->multiselect($rs->getTitle(), $options, $rs->getDescription())
				->withRequired(false)
				->withValue($selected);
		}

		// Standard filter fields
		$section1 = $f->input()->field()->section($fields,$lng->txt("cont_filter_fields"), "");

		$form_action = $ctrl->getLinkTarget($this, "saveFields");
		return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
	}

	/**
	 * Save field selection
	 */
	protected function saveFields()
	{
		$request = $this->request;
		$service = $this->container_filter_service;
		$form = $this->getFieldSelectionForm();
		$lng = $this->lng;
		$ctrl = $this->ctrl;

		$fields = [];
		if ($request->getMethod() == "POST")
		{
			$form = $form->withRequest($request);
			$data = $form->getData();

			// ADV MD record sets
			if (is_array($data["sec"]))
			{
				foreach ($data["sec"] as $rec_id => $ids)
				{
					if (is_array($ids))
					{
						foreach ($ids as $field_id)
						{
							$fields[] = $service->field($rec_id, $field_id);
						}
					}
				}
			}
			ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
			$service->data()->saveFilterSetForRefId($this->ref_id, $service->set($fields));
		}
		$ctrl->redirect($this, "");
	}



}