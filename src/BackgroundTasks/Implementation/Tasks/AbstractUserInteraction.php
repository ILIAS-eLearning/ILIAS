<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractTask;
use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\BackgroundTasks\ValueType;

abstract class AbstractUserInteraction extends AbstractTask implements UserInteraction {
	/**
	 * @inheritdoc
	 */
	public function getExpectedTimeOfTaksInSeconds() {
		return 9999;
	}
}