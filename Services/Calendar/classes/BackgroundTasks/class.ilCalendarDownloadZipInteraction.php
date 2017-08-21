<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Bucket;

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilCalendarDownloadZipInteraction extends \ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction
{
	const OPTION_DOWNLOAD = 'download';
	const OPTION_CANCEL = 'cancel';
	
	private $logger = null;
	
	public function __construct()
	{
		$this->logger = $GLOBALS['DIC']->logger()->cal();
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function getInputTypes()
	{
		return 
		[
			new SingleType(StringValue::class),
			new SingleType(StringValue::class)
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getOutputType()
	{
		return new SingleType(StringValue::class);
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions(array $input)
	{
		return 
		[
			new UserInteractionOption('download',self::OPTION_DOWNLOAD),
			new UserInteractionOption('cancel',self::OPTION_CANCEL)
		];
	}


	/**
	 * @inheritDoc
	 */
	public function interaction(array $input, Option $user_selected_option, Bucket $bucket)
	{
		$zip_name = $input[1];
		$download_name = $input[0];

		$this->logger->debug('Download '. $input[0]->getValue().' as '. $input[1]->getValue());
		
		$this->logger->info('Download canceled');
		if($user_selected_option->getValue() != self::OPTION_DOWNLOAD)
		{
			$this->logger->info('Download canceled');
			// delete zip file
			if(file_exists($zip_name->getValue()))
			{
				unlink($zip_name->getValue());
			}
			return $zip_name->getValue();
		}
		
		ilUtil::deliverFile(
			$zip_name->getValue(),
			$download_name->getValue()
		);
		
		return $zip_name->getValue();
	}

}
?>