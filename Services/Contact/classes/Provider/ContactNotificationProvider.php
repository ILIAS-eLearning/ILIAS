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
    const MUTED_UNTIL_PREFERENCE_KEY = 'bs_nc_muted_until';

    /**
     * @param string $id
     * @return IdentificationInterface
     */
    private function getIdentifier(string $id) : IdentificationInterface
    {
        return $this->if->identifier($id);
    }

    /**
     * @inheritDoc
     */
    public function getNotifications(): array
    {
        if (
            0 === (int)$this->dic->user()->getId() ||
            $this->dic->user()->isAnonymous() ||
            !\ilBuddySystem::getInstance()->isEnabled()
        ) {
            return [];
        }

        $leftIntervalTimestamp = $this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY);
        $openRequests = \ilBuddyList::getInstanceByGlobalUser()
            ->getRequestRelationsForOwner()->filter(
                function (\ilBuddySystemRelation $relation) use ($leftIntervalTimestamp) : bool {
                    if (!is_numeric($leftIntervalTimestamp)) {
                        return true;
                    }
                    return $relation->getTimestamp() > $leftIntervalTimestamp;
                }
            );

        $contactRequestsCount = count($openRequests->getKeys());
        if ($contactRequestsCount === 0) {
            return [];
        }

        $factory = $this->globalScreen()->notifications()->factory();

        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->custom(\ilUtil::getImagePath('simpleline/people.svg'), 'contacts');
        $title = $this->dic->ui()->factory()
            ->link()
            ->standard(
                $this->dic->language()->txt('nc_contact_requests_headline'),
                'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToContacts'
            );
        $description = sprintf(
            $this->dic->language()->txt(
                'nc_contact_requests_number' . (($contactRequestsCount > 1) ? '_p' : '_s')
            ),
            $contactRequestsCount
        );
        $notificationItem = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($description);

        $group = $factory
            ->standardGroup($this->getIdentifier('contact_bucket_group'))
            ->withTitle($this->dic->language()->txt('nc_contact_requests_headline'))
            ->addNotification(
                $factory->standard($this->getIdentifier('contact_bucket'))
                    ->withNotificationItem($notificationItem)
                    ->withClosedCallable(
                        function () {
                            $this->dic->user()->writePref(self::MUTED_UNTIL_PREFERENCE_KEY, time());
                        })->withNewAmount(1)
            );

        return [
            $group
        ];
    }
}
