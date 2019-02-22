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
		$mailValueObjects = $this->mailJsonService->convertFromJson((string)$input[1]->getValue());

		foreach ($mailValueObjects as $mailValueObject) {
			$mail = new ilMail((int)$input[0]->getValue());

			$mail->setSaveInSentbox((bool)$mailValueObject->shouldSaveInSentBox());
			$contextId = $input[2]->getValue();
			$mail = $mail
				->withContextId((string)$contextId)
				->withContextParameters((array)unserialize($input[3]->getValue()));

			$recipients = (string)$mailValueObject->getRecipients();
			$recipientsCC = (string)$mailValueObject->getRecipientsCC();
			$recipientsBCC = (string)$mailValueObject->getRecipientsBCC();

			$this->dic->logger()->mail()->info(
				sprintf(
					'Mail delivery to recipients: "%s" CC: "%s" BCC: "%s" From sender: "%s"',
					$recipients,
					$recipientsCC,
					$recipientsBCC,
					$mailValueObject->getFrom()
				)
			);

			$mail->sendMail(
				$recipients,
				$recipientsCC,
				$recipientsBCC,
				(string)$mailValueObject->getSubject(),
				(string)$mailValueObject->getBody(),
				(array)$mailValueObject->getAttachment(),
				(array)$mailValueObject->getTypes(),
				(bool)$mailValueObject->isUsingPlaceholders()
			);
		}

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

