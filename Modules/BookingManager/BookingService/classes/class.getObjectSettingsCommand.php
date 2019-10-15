<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\BookingManager;

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class getObjectSettingsCommand
{
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * Constructor
	 * @param int $obj_id
	 */
	public function __construct(int $obj_id)
	{
		$this->obj_id = $obj_id;
	}

	/**
	 * @return int
	 */
	public function getObjectId(): int
	{
		return $this->obj_id;
	}
}