<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateMigrationReducedInteraction
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilCertificateMigrationReducedInteraction extends ilCertificateMigrationInteraction
{
	/**
	 * @param array $input
	 * @return array|\ILIAS\BackgroundTasks\Task\UserInteraction\Option[]
	 */
	public function getOptions(array $input): array
	{
		return [];
	}

	/**
	 * @param array $input
	 * @param \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option
	 * @param \ILIAS\BackgroundTasks\Bucket $bucket
	 * @return array|\ILIAS\BackgroundTasks\Value
	 */
	public function interaction(array $input, \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option, \ILIAS\BackgroundTasks\Bucket $bucket)
	{
		global $DIC;

		$progress = $input[0]->getValue();
		$user_id = $input[1]->getValue();
		$logger = $DIC->logger()->cert();

		$logger->debug('User interaction certificate migration for user with id: ' . $user_id);
		$logger->debug('User interaction certificate migration State: '. $bucket->getState());
		$logger->info('User interaction certificate migration canceled for user with id: ' . $user_id);

		return $input;
	}

}