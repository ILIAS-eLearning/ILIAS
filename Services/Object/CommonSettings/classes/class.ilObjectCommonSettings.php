<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Common settings for objects
 *
 * @author @leifos.de
 * @ingroup 
 */
class ilObjectCommonSettings
{
	/**
	 * @var ilObjectService
	 */
	protected $service;

	/**
	 * Constructor
	 */
	public function __construct(ilObjectService $service)
	{
		$this->service = $service;
	}

	/**
	 * Tile image subservice. Tile images are used in deck of cards view of repository containers.
	 *
	 * @return ilObjectTileImageFactory
	 */
	public function tileImage()
	{
		return new ilObjectTileImageFactory($this->service);
	}

	/**
	 * Get form adapter (currently only for legacy form using ilPropertyFormGUI).
	 * @todo In the future a method form() should act on new ui form containers.
	 *
	 * @return ilObjectCommonSettingFormAdapter
	 */
	public function legacyForm(ilPropertyFormGUI $form, ilObject $object)
	{
		return new ilObjectCommonSettingFormAdapter($this->service, $object, $form);
	}
}