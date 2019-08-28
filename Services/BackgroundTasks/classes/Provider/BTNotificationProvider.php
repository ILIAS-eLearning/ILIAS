<?php namespace ILIAS\BackgroundTasks\Provider;

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
        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $factory = $this->globalScreen()->notifications()->factory();

        $group = $factory->standardGroup($id('bg_bucket_group'))->withTitle("Some Notifications");

        for ($x = 1; $x < 10; $x++) {
            $n = $factory->standard($id('bg_bucket_id_' . $x))
                ->withTitle("A Notification " . $x)
                ->withSummary("with a super summary " . $x)
                ->withAction("#");

            $group->addNotification($n);
        }

        return [
            $group,
        ];
    }
}
