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

class ilForumPushNotificationJob extends AbstractJob
{
    public function run(array $input, Observer $observer): Value
    {
        global $DIC;

        $data = json_decode((string) $input[0]->getValue(), true, 512, JSON_THROW_ON_ERROR);

        $provider = new ilForumNotificationDataProvider(
            new ilForumPost((int) $data['post_id']),
            (int) $data['ref_id'],
            new ilForumNotificationCache()
        );

        $push_provider = new ilForumPushProvider();
        $mail_recipients = $push_provider->filterMailRecipients(
            $data['recipients'],
            $provider,
            (int) $data['notification_type'],
            $DIC->logger()->forComponent('forum')
        );

        $output = new BooleanValue();
        $output->setValue(true);

        if ($mail_recipients !== []) {
            $mail_notification = new ilForumMailEventNotificationSender($provider, $DIC->logger()->forComponent('forum'));
            $mail_notification->setType((int) $data['notification_type']);
            $mail_notification->setRecipients($mail_recipients);
            $mail_notification->send();
        }

        return $output;
    }

    public function getInputTypes(): array
    {
        return [
            new SingleType(StringValue::class),
        ];
    }

    public function getOutputType(): Type
    {
        return new SingleType(BooleanValue::class);
    }

    public function isStateless(): bool
    {
        return true;
    }

    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
