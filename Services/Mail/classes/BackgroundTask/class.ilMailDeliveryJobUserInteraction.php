<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;

/**
 * Class ilMailDeliveryJobUserInteraction
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryJobUserInteraction extends AbstractUserInteraction
{
	const OPTION_CANCEL = 'cancel';

	/**
	 * @inheritdoc
	 */
	public function getOptions(array $input): array
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function getRemoveOption() {
		return new UserInteractionOption('remove', self::OPTION_CANCEL);
	}

	/**
	 * @inheritdoc
	 */
	public function getInputTypes()
	{
		// TODO: Implement getInputTypes() method.
	}

	/**
	 * @inheritdoc
	 */
	public function getOutputType()
	{
		// TODO: Implement getOutputType() method.
	}

	/**
	 * @inheritdoc
	 */
	public function interaction(array $input, \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option, \ILIAS\BackgroundTasks\Bucket $bucket)
	{
		return $input;
	}
}