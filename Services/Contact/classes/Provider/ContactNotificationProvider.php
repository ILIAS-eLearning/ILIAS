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
        $group = $factory
            ->standardGroup($this->getIdentifier('contact_group'))
            ->withTitle($this->dic->language()->txt('nc_contact_requests_headline'));
        $notification = $factory
            ->standard($this->getIdentifier('contact'))
            ->withTitle(
                sprintf(
                    $this->dic->language()->txt(
                        'nc_contact_requests_number' . (($contactRequestsCount > 1) ? '_p' : '_s')
                    ),
                    $contactRequestsCount
                )
            )
            ->withAction('ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts');

        return [
            $group->addNotification($notification),
        ];
    }
}