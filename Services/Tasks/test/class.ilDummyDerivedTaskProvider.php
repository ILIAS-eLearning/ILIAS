<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dummy derived task provider
 *
 * @author killing@leifos.de
 */
class ilDummyDerivedTaskProvider implements ilDerivedTaskProvider
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
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getTasks(int $user_id): array
	{
		$tasks = [];

		$tasks[] = $this->task_service->derived()->factory()->task("title", 123,
			1234, 1000);

		return $tasks;
	}
}