<?php
/*
  +----------------------------------------------------------------------------+
  | ILIAS open source                                                          |
  +----------------------------------------------------------------------------+
  | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
  |                                                                            |
  | This program is free software; you can redistribute it and/or              |
  | modify it under the terms of the GNU General Public License                |
  | as published by the Free Software Foundation; either version 2             |
  | of the License, or (at your option) any later version.                     |
  |                                                                            |
  | This program is distributed in the hope that it will be useful,            |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
  | GNU General Public License for more details.                               |
  |                                                                            |
  | You should have received a copy of the GNU General Public License          |
  | along with this program; if not, write to the Free Software                |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
  +----------------------------------------------------------------------------+
*/


use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;

/**
 * Class ilCertificateMigrationInteraction
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationInteraction extends AbstractUserInteraction
{
	const OPTION_GOTO_LIST = 'listCertificates';
	const OPTION_CANCEL = 'cancel';

	/**
	 * @return array|\ILIAS\BackgroundTasks\Types\Type[]
	 */
	public function getInputTypes(): array
	{
		return [
			new SingleType(IntegerValue::class),
			new SingleType(IntegerValue::class),
		];
	}

	/**
	 * @return SingleType|\ILIAS\BackgroundTasks\Types\Type
	 */
	public function getOutputType(): SingleType
	{
		return new SingleType(StringValue::class);
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoveOption() {
		return new UserInteractionOption('remove', self::OPTION_CANCEL);
	}

	/**
	 * @param array $input
	 * @return array|\ILIAS\BackgroundTasks\Task\UserInteraction\Option[]
	 */
	public function getOptions(array $input): array
	{
		return [
			new UserInteractionOption('my_certificates', self::OPTION_GOTO_LIST),
		];
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
		if ($user_selected_option->getValue() != self::OPTION_GOTO_LIST) {
			$logger->info('User interaction certificate migration canceled for user with id: ' . $user_id);
			return $input;
		}

		$logger->info('User interaction certificate migration redirect to certificate list for user with id: ' . $user_id);
		// @TODO: Change when integrating into trunk
		$DIC->ctrl()->redirectByClass(['ilPersonalDesktopGUI', 'ilBadgeProfileGUI'], 'listCertificates');

		return $input;
	}

}