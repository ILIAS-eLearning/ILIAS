<?php declare(strict_types=1);

namespace ILIAS\Contact\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class ContactNotificationProvider
 * @author Ingmar Szmais <iszmais@databay.de>
 * @since 10.09.19
 */
class ContactNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @param string $id
     * @return IdentificationInterface
     */
    private function getIdentifier(string $id)
    {
        return $this->if->identifier($id);
    }

    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        if (
            0 === (int) $this->dic->user()->getId() ||
            $this->dic->user()->isAnonymous() ||
            !\ilBuddySystem::getInstance()->isEnabled()
        ) {
            return [];
        }

        $contactRequestsCount = count(
            \ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->getKeys()
        );
        if ($contactRequestsCount === 0) {
            return [];
        }

        $factory = $this->globalScreen()->notifications()->factory();

        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        //Creating a mail Notification Item
        $body = sprintf(
            $this->dic->language()->txt(
                'nc_contact_requests_number' . (($contactRequestsCount > 1) ? '_p' : '_s')
            ),
            $contactRequestsCount);
        $contact_icon = $this->dic->ui()->factory()->symbol()->icon()->standard("contact","contact");
        $contact_title = $this->dic->ui()->factory()->link()->standard(
            $this->dic->language()->txt('nc_contact_requests_headline'),
            'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts');
        $contact_notification_item = $this->dic->ui()->factory()->item()->notification($contact_title,$contact_icon)
                                            ->withDescription($body);

        $group = $factory->standardGroup($id('contact_bucket_group'))->withTitle($this->dic->language()->txt('contact'))
                         ->addNotification($factory->standard($id('contact_bucket'))->withNotificationItem($contact_notification_item)
                             ->withClosedCallable(
                                 function(){
                                     //@Todo: Memories, that those notifications have been closed.
                                     var_dump("Contact received closed event.");
                                 })->withNewAmount($contactRequestsCount)
                         )
                         ->withOpenedCallable(function(){
                             //@Todo: Memories, that those notifications have been seen.
                             var_dump("Contact received opened event.");
                         });

        return [
            $group,
        ];

    }
}