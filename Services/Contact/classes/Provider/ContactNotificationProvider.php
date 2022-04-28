<?php declare(strict_types=1);

namespace ILIAS\Contact\Provider;

use ilContactGUI;
use ilDashboardGUI;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Class ContactNotificationProvider
 * @author Ingmar Szmais <iszmais@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
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
    public function getNotifications() : array
    {
        if (
            0 === (int) $this->dic->user()->getId() ||
            $this->dic->user()->isAnonymous() ||
            !\ilBuddySystem::getInstance()->isEnabled()
        ) {
            return [];
        }

        $leftIntervalTimestamp = $this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY);
        $latestRequestTimestamp = null;
        $openRequests = \ilBuddyList::getInstanceByGlobalUser()
            ->getRequestRelationsForOwner()->filter(
                function (\ilBuddySystemRelation $relation) use ($leftIntervalTimestamp, &$latestRequestTimestamp) : bool {
                    $timeStamp = $relation->getTimestamp();
                    
                    if ($timeStamp > $latestRequestTimestamp) {
                        $latestRequestTimestamp = $timeStamp;
                    }
                    
                    if (!is_numeric($leftIntervalTimestamp)) {
                        return true;
                    }

                    return $timeStamp > $leftIntervalTimestamp;
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
                          ->standard(Standard::CADM, 'contacts')->withIsOutlined(true);

        $title = $this->dic->ui()->factory()
            ->link()
            ->standard(
                $this->dic->language()->txt('nc_contact_requests_headline'),
                $this->dic->ctrl()->getLinkTargetByClass([ilDashboardGUI::class, ilContactGUI::class], 'showContactRequests')
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
            ->withDescription($description)
            ->withProperties([
                $this->dic->language()->txt('nc_contact_requests_prop_time') => \ilDatePresentation::formatDate(
                    new \ilDateTime($latestRequestTimestamp, IL_CAL_UNIX)
                )
            ]);

        $group = $factory
            ->standardGroup($this->getIdentifier('contact_bucket_group'))
            ->withTitle($this->dic->language()->txt('nc_contact_requests_headline'))
            ->addNotification(
                $factory->standard($this->getIdentifier('contact_bucket'))
                    ->withNotificationItem($notificationItem)
                    ->withClosedCallable(
                        function () {
                            $this->dic->user()->writePref(self::MUTED_UNTIL_PREFERENCE_KEY, time());
                        }
                    )->withNewAmount(1)
            );

        return [
            $group
        ];
    }
}
