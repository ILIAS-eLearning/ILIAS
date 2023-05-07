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

namespace ILIAS\Mail\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class MailNotificationProvider
 */
class MailNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        $factory = $this->globalScreen()->notifications()->factory();
        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

		$new_mails = 2;
		
        //Creating a mail Notification Item
        $mail_icon = $this->dic->ui()->factory()->symbol()->icon()->standard("mail","mail");
        $mail_title = $this->dic->ui()->factory()->link()->standard("Inbox", 'ilias.php?baseClass=ilMailGUI');
        $mail_notification_item = $this->dic->ui()->factory()->item()->notification($mail_title,$mail_icon)
                                                   ->withDescription("You have $new_mails Mails.")
                                                   ->withProperties(["Time" => "3 days ago"]);

        $group = $factory->standardGroup($id('mail_bucket_group'))->withTitle($this->dic->language()->txt('mail'))
            ->addNotification($factory->standard($id('mail_bucket'))->withNotificationItem($mail_notification_item)                                                      
                ->withClosedCallable(
                    function(){
                        //@Todo: Memories, that those notifications have been closed.
                        var_dump("Mail Notifications received closed event.");
                    })
                ->withNewAmount($new_mails)
            )
            ->withOpenedCallable(function(){
                //@Todo: Memories, that those notifications have been seen.
                var_dump("Mail Notifications received opened event.");
            });

        return [
            $group,
        ];
    }
}


```

In this case, the effective notifications are collected in a NotificationGroup. These will rendered as a group in the NotificationCenter.

For more details on the properties of the UI Component Notification Item, see the respective documentation in src/UI/Components/Item/Notification and the respective examples.
