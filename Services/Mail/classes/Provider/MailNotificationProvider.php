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

        $group = $factory->standardGroup($id('mail_group'))->withTitle($this->dic->language()->txt('mail'));

        if (1 === $numberOfNewMessages) {
            $body = $this->dic->language()->txt('nc_mail_unread_messages_number_s');
        } else {
            $body = sprintf($this->dic->language()->txt('nc_mail_unread_messages_number_p'), $numberOfNewMessages);
        }

        $notification = $factory->standard($id('mail'))
            ->withTitle($body)
            ->withAction('ilias.php?baseClass=ilMailGUI');

        $group->addNotification($notification);

        return [
            $group,
        ];
    }
}
