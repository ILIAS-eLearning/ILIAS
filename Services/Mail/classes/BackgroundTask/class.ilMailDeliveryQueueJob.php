<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\Data\UUID\Factory as UuidFactory;

/**
 * Class ilMailDeliveryQueueJob
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryQueueJob extends AbstractJob
{
    /**
     * @inheritdoc
     */
    public function run(array $input, Observer $observer)
    {
        global $DIC;

        $arguments = array_map(static function ($value) {
            return $value->getValue();
        }, $input);

        $DIC->logger()->mail()->info(sprintf(
            'Mail delivery background task executed for input: %s',
            json_encode($arguments, JSON_PRETTY_PRINT)
        ));

        $queuedTaskId = $input[0]->getValue();
        $queuedTaskRepo = new ilMailQueuedTaskRepository(
            $DIC->database(),
            new UuidFactory()
        );

        $output = new BooleanValue();

        try {
            $queuedTask = $queuedTaskRepo->findByUuid($queuedTaskId);

            $mail = new ilMail((int) $queuedTask->getActorUsrId());
            $mail->setSaveInSentbox($queuedTask->shouldSaveInSentBox());
            
            if ($queuedTask->getTemplateContextId()) {
                $mail = $mail
                    ->withContextId((string) $queuedTask->getTemplateContextId())
                    ->withContextParameters((array) $queuedTask->getTemplateContextParams());
            }

            $mail->sendMail(
                $queuedTask->getRecipients(),
                $queuedTask->getRecipientsCC(),
                $queuedTask->getRecipientsBCC(),
                $queuedTask->getSubject(),
                $queuedTask->getBody(),
                $queuedTask->getAttachments(),
                $queuedTask->isUsingPlaceholders()
            );

            $DIC->logger()->mail()->info(sprintf(
                'Mail delivery background task finished: %s',
                json_encode($arguments, JSON_PRETTY_PRINT)
            ));

            $queuedTaskRepo->delete($queuedTaskId);
            
            $output->setValue(true);
        } catch (ilCouldNotFindQueueTaskException $e) {
            $DIC->logger()->mail()->err($e->getMessage());
            $DIC->logger()->mail()->err($e->getTraceAsString());

            $output->setValue(false);
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function getInputTypes()
    {
        return [
            new SingleType(StringValue::class),
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
