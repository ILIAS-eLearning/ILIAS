<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;

/**
 * Class ilMailDeliveryJob
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryJob extends AbstractJob
{
    /**
     * @inheritdoc
     */
    public function run(array $input, Observer $observer)
    {
        global $DIC;

        $arguments = array_map(function ($value) {
            return $value->getValue();
        }, $input);

        $DIC->logger()->mail()->info('Mail delivery background task executed');

        $DIC->logger()->mail()->debug(sprintf(
            'Input: %s',
            json_encode(array_slice($arguments, 0, 5), JSON_PRETTY_PRINT)
        ));

        $mail = new ilMail((int) $input[0]->getValue());
        $mail->setSaveInSentbox((bool) $input[8]->getValue());
        $mail = $mail
            ->withContextId((string) $input[9]->getValue())
            ->withContextParameters((array) unserialize($input[10]->getValue()));

        $mail->sendMail(
            (string) $input[1]->getValue(), // To
            (string) $input[2]->getValue(),  // Cc
            (string) $input[3]->getValue(),  // Bcc
            (string) $input[4]->getValue(),  // Subject
            (string) $input[5]->getValue(),  // Message
            (array) unserialize($input[6]->getValue()),  // Attachments
            (bool) $input[7]->getValue() // Use Placeholders
        );

        $DIC->logger()->mail()->info('Mail delivery background task finished');

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
            new SingleType(IntegerValue::class), // 0. User Id
            new SingleType(StringValue::class), // 1. To
            new SingleType(StringValue::class), // 2. CC
            new SingleType(StringValue::class), // 3. BCC
            new SingleType(StringValue::class), // 4. Subject
            new SingleType(StringValue::class), // 5. Message
            new SingleType(StringValue::class), // 6. Attachments
            new SingleType(BooleanValue::class), // 7. Use placeholders
            new SingleType(BooleanValue::class), // 8. Save in sentbox
            new SingleType(StringValue::class), // 9. Context Id
            new SingleType(StringValue::class), // 10. Context Parameters
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
        return 30;
    }

    /**
     * @inheritdoc
     */
    public function getOutputType()
    {
        return new SingleType(BooleanValue::class);
    }
}
