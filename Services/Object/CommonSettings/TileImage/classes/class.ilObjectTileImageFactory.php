<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * tile image factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
class ilObjectTileImageFactory
{
	/**
	 * @var ilObjectService
	 */
	protected $service;

	/**
	 * Constructor
	 * @param ilObjectService $service
	 */
	public function __construct(ilObjectService $service)
	{
		$this->service = $service;
	}

	/**
	 * Get supported file extensions
	 *
	 * @return string[]
	 */
	public function getSupportedFileExtensions()
	{
		return ["png", "jpg", "jpeg"];
	}

	/**
	 * @param int $objId
	 * @return ilObjectTileImage
	 */
	public function getByObjId(int $obj_id)
	{
		return new \ilObjectTileImage($this->service, $obj_id);
	}
}