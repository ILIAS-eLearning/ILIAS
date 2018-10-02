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
				// we do not clone for legacy forms, since initEditCustomForm relies on "call by reference" behaviour
				//$this->legacy_form = clone $this->legacy_form;
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
	
	/**
	 * Add tile image
	 *
	 * @return null|ilPropertyFormGUI
	 */
	public function addTileImage()
	{
		$lng = $this->service->language();
		$tile_image_fac = $this->service->commonSettings()->tileImage();

		if (!is_null($this->legacy_form))
		{
			// we do not clone for legacy forms, since initEditCustomForm relies on "call by reference" behaviour
			//$this->legacy_form = clone $this->legacy_form;

			$tile_image = $tile_image_fac->getByObjId($this->object->getId());
			$timg = new \ilImageFileInputGUI($lng->txt('obj_tile_image'), 'tile_image');
			$timg->setInfo($lng->txt('obj_tile_image_info'));
			$timg->setSuffixes($tile_image_fac->getSupportedFileExtensions());
			$timg->setUseCache(false);
			if ($tile_image->exists()) {
				$timg->setImage($tile_image->getFullPath());
			} else {
				$timg->setImage('');
			}
			$this->legacy_form->addItem($timg);
		}

		return $this->legacy_form;
	}

	/**
	 * Save tile image
	 */
	public function saveTileImage()
	{
		$tile_image_fac = $this->service->commonSettings()->tileImage();

		if (!is_null($this->legacy_form))
		{
			$tile_image = $tile_image_fac->getByObjId($this->object->getId());

			/** @var \ilImageFileInputGUI $item */
			$item = $this->legacy_form->getItemByPostVar('tile_image');
			if ($item->getDeletionFlag()) {
				$tile_image->remove();
			}

			$file_data = (array)$this->legacy_form->getInput('tile_image');
			if ($file_data['tmp_name']) {
				$tile_image->saveFromHttpRequest();
			}
		}
	}



}