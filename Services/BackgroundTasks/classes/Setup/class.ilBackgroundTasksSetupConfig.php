<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilBackgroundTasksSetupConfig implements Setup\Config {
	const TYPE_SYNCHRONOUS = "sync";
	const TYPE_ASYNCHRONOUS = "async";

	/**
	 * @var mixed
	 */
	protected $type;

	/**
	 * @var int 
	 */
	protected $max_concurrent_tasks;

	public function __construct(
		string $type,
		int $max_concurrent_tasks
	) {
		$types = [
			self::TYPE_SYNCHRONOUS,
			self::TYPE_ASYNCHRONOUS
		];
		if (!in_array($type, $types)) {
			throw new \InvalidArgumentException(
				"Unknown background tasks type: '$type'"
			);
		}
		if ($max_concurrent_tasks < 1) {
			throw new \InvalidArgumentException(
				"There must be at least 1 concurrent background task."
			);
		}
		$this->type = $type;
		$this->max_concurrent_tasks = $max_concurrent_tasks;
	}

	public function getType() : string {
		return $this->type;
	}

	public function getMaxCurrentTasks() : int {
		return $this->max_concurrent_tasks;
	}
}
