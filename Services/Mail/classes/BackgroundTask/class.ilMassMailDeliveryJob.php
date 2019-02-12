<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMassMailDeliveryJob extends AbstractJob
{
	/**
	 * @var \ILIAS\DI\Container
	 */
	private $dic;

	/**
	 * @var ilMailValueObjectJsonService|null
	 */
	private $mailJsonService;

	/**
	 * ilMassMailDeliveryJob constructor.
	 * @param \ILIAS\DI\Container|null $dic
	 * @param ilMailValueObjectJsonService|null $mailJsonService
	 */
	public function __construct()
	{
		global $DIC;
		$this->dic = $DIC;

		$this->mailJsonService = new ilMailValueObjectJsonService();
	}

	/**
	 * @inheritdoc
	 * @throws \ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException
	 */
	public function run(array $input, Observer $observer)
	{
		$arguments = array_map(function($value) {
			return $value->getValue();
		}, $input);

		$this->dic->logger()->mail()->info(sprintf(
			'Mail delivery background task executed for input: %s',
			json_encode($arguments, JSON_PRETTY_PRINT)
		));

		$mailValueObjects = $this->mailJsonService->convertFromJson((string)$input[1]->getValue());

		foreach ($mailValueObjects as $mailValueObject) {
			$mail = new ilMail((int)$input[0]->getValue());

			$mail->setSaveInSentbox((bool)$mailValueObject->shouldSaveInSentBox());
			$contextId = $input[2]->getValue();
			$mail = $mail
				->withContextId((string)$contextId)
				->withContextParameters((array)unserialize($input[3]->getValue()));

			$mail->sendMail(
				(string)$mailValueObject->getRecipients(),    // To
				(string)$mailValueObject->getRecipientsCC(),  // Cc
				(string)$mailValueObject->getRecipientsCC(),  // Bcc
				(string)$mailValueObject->getSubject(),       // Subject
				(string)$mailValueObject->getBody(),          // Message
				(array)$mailValueObject->getAttachment(),     // Attachments
				(array)$mailValueObject->getTypes(),          // Type
				(bool)$mailValueObject->isUsingPlaceholders() // Use Placeholders
			);
		}

		$this->dic->logger()->mail()->info(sprintf(
			'Mail delivery background task finished',
			json_encode($arguments, JSON_PRETTY_PRINT)
		));

		$output = new BooleanValue();
		$output->setValue(true);

		return $output;
	}

	/**
	 * @inheritdoc
	 */
	public function getInputTypes()
	{
		return [
			new SingleType(IntegerValue::class), // User Id
			new SingleType(StringValue::class),  // JSON encoded array of ilMailValueObject
			new SingleType(StringValue::class),  // Context Id
			new SingleType(StringValue::class),  // Context Parameters
		];
	}

	/**
	 * @inheritdoc
	 */
	public function isStateless()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getExpectedTimeOfTaskInSeconds()
	{
		return 42; // The answer to life, universe and the rest
	}

	/**
	 * @inheritdoc
	 */
	public function getOutputType()
	{
		return new SingleType(BooleanValue::class);
	}
}

