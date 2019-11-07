<?php declare(strict_types=1);

namespace ILIAS\Mail\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class MailNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MailNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        if (0 === (int) $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return [];
        } 

        $hasInternalMailAccess = $this->dic->rbac()->system()->checkAccess(
            'internal_mail', \ilMailGlobalServices::getMailObjectRefId()
        );
        if (!$hasInternalMailAccess) {
            return [];
        }

        $numberOfNewMessages = \ilMailGlobalServices::getNumberOfNewMailsByUserId($this->dic->user()->getId());
        if (0 === $numberOfNewMessages) {
            return [];
        }

        $factory = $this->globalScreen()->notifications()->factory();


        if (1 === $numberOfNewMessages) {
            $body = $this->dic->language()->txt('nc_mail_unread_messages_number_s');
        } else {
            $body = sprintf($this->dic->language()->txt('nc_mail_unread_messages_number_p'), $numberOfNewMessages);
        }

        //Creating a mail Notification Item
        $mail_icon = $this->dic->ui()->factory()->symbol()->icon()->standard("mail","mail");
        $mail_title = $this->dic->ui()->factory()->link()->standard("Inbox", 'ilias.php?baseClass=ilMailGUI');
        $mail_notification_item = $this->dic->ui()->factory()->item()->notification($mail_title,$mail_icon)
                                                   ->withDescription($body);

        $group = $factory->standardGroup($id('mail_bucket_group'))->withTitle($this->dic->language()->txt('mail'))
            ->addNotification($factory->standard($id('mail_bucket'))->withNotificationItem($mail_notification_item)
                ->withClosedCallable(
                    function(){
                        //@Todo: Memories, that those notifications have been closed.
                        var_dump("Mail Notifications received closed event.");
                    })
                ->withNewAmount($numberOfNewMessages)
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
