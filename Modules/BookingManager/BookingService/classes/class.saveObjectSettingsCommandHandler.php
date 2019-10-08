<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\BookingManager;

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class saveObjectSettingsCommandHandler
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
	public function __construct(saveObjectSettingsCommand $cmd,
		\ilObjUseBookDBRepository $use_book_repo)
	{
		$this->cmd = $cmd;
		$this->use_book_repo = $use_book_repo;
	}

	public function handle()
	{
		$settings = $this->cmd->getSettings();
		$repo = $this->use_book_repo;

		$repo->updateUsedBookingPools($settings->getObjectId(), $settings->getUsedBookingObjectIds());
	}

}