<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

class ilMassMailDeliveryJob extends AbstractJob
{
    private readonly ILIAS\DI\Container $dic;
    private readonly ilMailValueObjectJsonService $mail_json_service;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;

        $this->mail_json_service = new ilMailValueObjectJsonService();
    }

    public function run(array $input, Observer $observer): Value
    {
        $value_objects = $this->mail_json_service->convertFromJson((string) $input[1]->getValue());

        foreach ($value_objects as $value_object) {
            $mail = new ilMail((int) $input[0]->getValue());

            $mail->setSaveInSentbox($value_object->shouldSaveInSentBox());
            $context_id = $input[2]->getValue();
            $mail = $mail
                ->withContextId((string) $context_id)
                ->withContextParameters((array) unserialize($input[3]->getValue(), ['allowed_classes' => false]));

            $recipients = $value_object->getRecipients();
            $recipients_cc = $value_object->getRecipientsCC();
            $recipients_bcc = $value_object->getRecipientsBCC();

            $this->dic->logger()->mail()->info(
                sprintf(
                    'Mail delivery to recipients: "%s" CC: "%s" BCC: "%s" From sender: "%s"',
                    $recipients,
                    $recipients_cc,
                    $recipients_bcc,
                    $value_object->getFrom()
                )
            );

            $mail_data = new MailDeliveryData(
                $recipients,
                $recipients_cc,
                $recipients_bcc,
                $value_object->getSubject(),
                $value_object->getBody(),
                $value_object->getAttachments(),
                $value_object->isUsingPlaceholders()
            );
            $mail->sendMail($mail_data);
        }

        $output = new BooleanValue();
        $output->setValue(true);

        return $output;
    }

    public function getInputTypes(): array
    {
        return [
            new SingleType(IntegerValue::class), // User Id
            new SingleType(StringValue::class),  // JSON encoded array of ilMailValueObject
            new SingleType(StringValue::class),  // Context Id
            new SingleType(StringValue::class),  // Context Parameters
        ];
    }

    public function isStateless(): bool
    {
        return true;
    }

    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 42; // The answer to life, universe and the rest
    }

    public function getOutputType(): Type
    {
        return new SingleType(BooleanValue::class);
    }
}
