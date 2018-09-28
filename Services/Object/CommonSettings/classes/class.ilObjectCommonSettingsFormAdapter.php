<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Common settings form adapter. Helps to add and save common object settings for repository objects.
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
class ilObjectCommonSettingFormAdapter
{
	/**
	 * @var ilObjectService
	 */
	protected $service;

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $legacy_form;

	/**
	 * @var ilObject
	 */
	protected $object;

	/**
	 * Constructor
	 */
	public function __construct(ilObjectService $service, ilObject $object, ilPropertyFormGUI $legacy_form = null)
	{
		$this->service = $service;
		$this->legacy_form = $legacy_form;
		$this->object = $object;
	}

	/**
	 * Add icon
	 *
	 * @return null|ilPropertyFormGUI
	 */
	public function addIcon()
	{
		global $DIC;

		if ($this->service->settings()->get('custom_icons'))
		{
			if (!is_null($this->legacy_form))
			{
				$this->legacy_form = clone $this->legacy_form;
				require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfigurationGUI.php';
				$gui = new \ilObjectCustomIconConfigurationGUI($DIC, null, $this->object);
				$gui->addSettingsToForm($this->legacy_form);
			}
		}
		return $this->legacy_form;
	}
	
	/**
	 * Save icon
	 */
	public function saveIcon()
	{
		global $DIC;

		if ($this->service->settings()->get('custom_icons'))
		{
			if (!is_null($this->legacy_form))
			{
				$this->legacy_form = clone $this->legacy_form;
				require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfigurationGUI.php';
				$gui = new \ilObjectCustomIconConfigurationGUI($DIC, null, $this->object);
				$gui->saveIcon($this->legacy_form);
			}
		}

	}
	
	

}