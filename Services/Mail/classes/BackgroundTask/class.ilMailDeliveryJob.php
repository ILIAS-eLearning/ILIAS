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
 * Class ilMailDeliveryJob
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryJob extends AbstractJob
{
    public function run(array $input, Observer $observer) : Value
    {
        global $DIC;

        $arguments = array_map(static function ($value) {
            return $value->getValue();
        }, $input);

        $DIC->logger()->mail()->info('Mail delivery background task executed');

        $DIC->logger()->mail()->debug(sprintf(
            'Input: %s',
            json_encode(array_slice($arguments, 0, 5), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        ));

        if ((int) $input[0]->getValue() === ANONYMOUS_USER_ID) {
            $mail = new ilMail((int) $input[0]->getValue());
        } else {
            $mail = new ilFormatMail((int) $input[0]->getValue());
        }
        $mail->setSaveInSentbox((bool) $input[8]->getValue());
        $mail = $mail
            ->withContextId((string) $input[9]->getValue())
            ->withContextParameters((array) unserialize($input[10]->getValue(), ['allowed_classes' => false]));

        $mail->sendMail(
            (string) $input[1]->getValue(), // To
            (string) $input[2]->getValue(),  // Cc
            (string) $input[3]->getValue(),  // Bcc
            (string) $input[4]->getValue(),  // Subject
            (string) $input[5]->getValue(),  // Message
            (array) unserialize($input[6]->getValue(), ['allowed_classes' => false]),  // Attachments
            (bool) $input[7]->getValue() // Use Placeholders
        );

        $DIC->logger()->mail()->info('Mail delivery background task finished');

        $output = new BooleanValue();
        $output->setValue(true);

        return $output;
    }

    public function getInputTypes() : array
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

    public function isStateless() : bool
    {
        return true;
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }

    public function getOutputType() : Type
    {
        return new SingleType(BooleanValue::class);
    }
}
