<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * TODO Centralize all this Download Zip interactions in the same place.
 *
 * @author Jesús López <lopez@leifos.com>
 *
 */
class ilExDownloadSubmissionsZipInteraction extends AbstractUserInteraction {

	const OPTION_DOWNLOAD = 'download';
	const OPTION_CANCEL = 'cancel';

	/**
	 * @var \Monolog\Logger
	 */
	private $logger = null;


	public function __construct() {
		$this->logger = $GLOBALS['DIC']->logger()->exc();
	}


	/**
	 * @inheritdoc
	 */
	public function getInputTypes() {
		return [
			new SingleType(StringValue::class),
			new SingleType(StringValue::class),
		];
	}


	/**
	 * @inheritDoc
	 */
	public function getRemoveOption() {
		return new UserInteractionOption('remove', self::OPTION_CANCEL);
	}


	/**
	 * @inheritDoc
	 */
	public function getOutputType() {
		return new SingleType(StringValue::class);
	}


	/**
	 * @inheritDoc
	 */
	public function getOptions(array $input) {
		return [
			new UserInteractionOption('download', self::OPTION_DOWNLOAD),
		];
	}


	/**
	 * @inheritDoc
	 */
	public function interaction(array $input, Option $user_selected_option, Bucket $bucket) {
		global $DIC;
		$download_name = $input[0]; //directory name.
		$zip_name = $input[1]; // zip job

		$this->logger->info("Interaction -> option[0] download name MUST BE FULL PATH=> ".$download_name->getValue());
		$this->logger->info("Interaction -> option[1] zip name get value() MUST BE THE NAME WITHOUT EXTENSION. => ".$zip_name->getValue());

		$this->logger->debug('User interaction download zip ==> ' . $input[0]->getValue() . ' as ==>'
			. $input[1]->getValue());

		if ($user_selected_option->getValue() != self::OPTION_DOWNLOAD) {
			$this->logger->info('Download canceled');
			// delete zip file
			$filesystem = $DIC->filesystem()->temp();
			try {
				$path = LegacyPathHelper::createRelativePath($zip_name->getValue());
			} catch (InvalidArgumentException $e) {
				$path = null;
			}
			if (!is_null($path) && $filesystem->has($path)) {
				$filesystem->deleteDir(dirname($path));
			}

			return $input;
		}

		$this->logger->info("Delivering File.");


		$zip_name = $zip_name->getValue();

		$ending = substr($zip_name, -4);
		if($ending != ".zip"){
			$zip_name .= ".zip";
			$this->logger->info("Add .zip extension");
		}

		//Download_name->getValue should return the complete path to the file
		//Zip name is just an string
		ilFileDelivery::deliverFileAttached($download_name->getValue(), $zip_name);

		return $input;
	}
}
