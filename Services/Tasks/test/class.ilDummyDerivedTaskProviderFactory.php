<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dummy derived task provider factory
 *
 * @author killing@leifos.de
 */
class ilDummyDerivedTaskProviderFactory implements ilDerivedTaskProviderFactory
{
	/**
	 * @var ilTaskService
	 */
	protected $task_service;

	/**
	 * Constructor
	 */
	public function __construct(ilTaskService $task_service)
	{
		$this->task_service = $task_service;
	}

	/**
	 * @inheritdoc
	 */
	public function getProviders(): array
	{
		return [
			new ilDummyDerivedTaskProvider($this->task_service)
		];
	}
}