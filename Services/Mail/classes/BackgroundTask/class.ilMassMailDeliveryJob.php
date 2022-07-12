<?php declare(strict_types=1);

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

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

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

    public function run(array $input, Observer $observer) : Value
    {
        $mailValueObjects = $this->mailJsonService->convertFromJson((string) $input[1]->getValue());

        foreach ($mailValueObjects as $mailValueObject) {
            $mail = new ilMail((int) $input[0]->getValue());

            $mail->setSaveInSentbox($mailValueObject->shouldSaveInSentBox());
            $contextId = $input[2]->getValue();
            $mail = $mail
                ->withContextId((string) $contextId)
                ->withContextParameters((array) unserialize($input[3]->getValue(), ['allowed_classes' => false]));

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

    public function getInputTypes() : array
    {
        return [
            new SingleType(IntegerValue::class), // User Id
            new SingleType(StringValue::class),  // JSON encoded array of ilMailValueObject
            new SingleType(StringValue::class),  // Context Id
            new SingleType(StringValue::class),  // Context Parameters
        ];
    }

    public function isStateless() : bool
    {
        return true;
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 42; // The answer to life, universe and the rest
    }

    public function getOutputType() : Type
    {
        return new SingleType(BooleanValue::class);
    }
}
