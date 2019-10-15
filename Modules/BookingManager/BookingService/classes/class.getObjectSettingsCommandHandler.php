<?php

namespace ILIAS\BookingManager;

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class getObjectSettingsCommandHandler
{
	/**
	 * @var saveObjectSettingsCommand
	 */
	protected $cmd;

	/**
	 * @var \ilObjUseBookDBRepository
	 */
	protected $use_book_repo;

	/**
	 * Constructor
	 */
	public function __construct(getObjectSettingsCommand $cmd,
		\ilObjUseBookDBRepository $use_book_repo)
	{
		$this->cmd = $cmd;
		$this->use_book_repo = $use_book_repo;
	}

	public function handle()
	{
		$obj_id = $this->cmd->getObjectId();
		$repo = $this->use_book_repo;

		$used_book_ids = $repo->getUsedBookingPools($obj_id);

		return new getObjectSettingsResponse(new \ilObjBookingServiceSettings($obj_id, $used_book_ids));
	}

}