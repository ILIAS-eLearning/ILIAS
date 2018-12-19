<?php

declare(strict_types=1);

/**
 * Static definition of available PostCondition for the LearningSequence
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LSPostConditionTypesDB
{
	const TYPE_ALWAYS = 0;
	const TYPE_FINISHED = 1; //completed or failed
	const TYPE_COMPLETED = 2; //aka passed
	const TYPE_FAILED = 3;

	public static function getAvailableTypes(): array
	{
		return [
			new LSPostConditionType(self::TYPE_ALWAYS, 'condition_always'),
			new LSPostConditionType(self::TYPE_FINISHED, 'condition_finished'),
			new LSPostConditionType(self::TYPE_COMPLETED, 'condition_completed'),
			new LSPostConditionType(self::TYPE_FAILED, 'condition_failed'),
		];
	}
}
