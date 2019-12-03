<?php namespace ILIAS\BackgroundTasks\Provider;

use ilBTPopOverGUI;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class BTNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BTNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{

    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        $nr_buckets = count($this->dic->backgroundTasks()->persistence()->getBucketIdsOfUser($this->dic->user()->getId()));
        if (!$nr_buckets) {
            return [];
        }

        $this->dic->ui()->mainTemplate()->addJavaScript("./Services/BackgroundTasks/js/background_task_refresh.js");
        $this->dic->language()->loadLanguageModule('background_tasks');

        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $factory = $this->globalScreen()->notifications()->factory();

        $item_source = new ilBTPopOverGUI($this->dic);
        $group = $factory->standardGroup($id('bg_task_bucket_group'))
            ->withTitle($this->txt('background_tasks'))
            ->addNotification(
                $factory->standard($id('bg_task_bucket'))
                    ->withNotificationItem($item_source->getNotificationItem($nr_buckets))
                    ->withNewAmount(1));

        return [
            $group,
        ];
    }


    private function txt(string $id) : string
    {
        return $this->dic->language()->txt($id);
    }
}
