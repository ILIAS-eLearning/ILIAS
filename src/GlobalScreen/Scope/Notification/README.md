Scope Notifications
===================
This scope addresses notifications that are displayed to the user in the NotificationCenter (a dedicated item in the MetaBar). Components can - as in all other scopes - via an implementation of a `NotificationProvider` provide the `MainNotificationCollector` with a list of notifications. These are summarized and displayed in the NotificationCenter.

The following types are currently available via the Factory:

- `StandardNotification`
- `StandardNotificationGroup`

# Provider

An own provider is quickly implemented, e.g:

```php
<?php declare(strict_types=1);

namespace ILIAS\BackgroundTasks\Provider;

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

        $group = $factory->standardGroup($id('bg_bucket_group'));

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

```

In this case, the effective notifications are collected in a NotificationGroup. These can then also be rendered as a group in the NotificationCenter.
