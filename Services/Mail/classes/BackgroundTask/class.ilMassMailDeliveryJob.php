<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    private ILIAS\DI\Container $dic;
    private ilMailValueObjectJsonService $mailJsonService;

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
    public function run(array $input, Observer $observer) : BooleanValue
    {
        $mailValueObjects = $this->mailJsonService->convertFromJson((string) $input[1]->getValue());

        foreach ($mailValueObjects as $mailValueObject) {
            $mail = new ilMail((int) $input[0]->getValue());

            $mail->setSaveInSentbox($mailValueObject->shouldSaveInSentBox());
            $contextId = $input[2]->getValue();
            $mail = $mail
                ->withContextId((string) $contextId)
                ->withContextParameters((array) unserialize($input[3]->getValue()));

            $recipients = $mailValueObject->getRecipients();
            $recipientsCC = $mailValueObject->getRecipientsCC();
            $recipientsBCC = $mailValueObject->getRecipientsBCC();

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
                $mailValueObject->getSubject(),
                $mailValueObject->getBody(),
                $mailValueObject->getAttachments(),
                $mailValueObject->isUsingPlaceholders()
            );
        }

        $output = new BooleanValue();
        $output->setValue(true);

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function getInputTypes() : array
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
    public function isStateless() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 42; // The answer to life, universe and the rest
    }

    /**
     * @inheritdoc
     */
    public function getOutputType() : SingleType
    {
        return new SingleType(BooleanValue::class);
    }
}
