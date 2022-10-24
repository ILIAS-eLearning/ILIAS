<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Contact\Provider;

use ilContactGUI;
use ilDashboardGUI;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilBuddyList;
use ilObjUser;
use ilBuddySystem;
use ilBuddySystemRelation;
use ilDatePresentation;
use ilDateTime;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\Notifications\ilNotificationOSDHandler;

/**
 * Class ContactNotificationProvider
 * @author Ingmar Szmais <iszmais@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ContactNotificationProvider extends AbstractNotificationProvider
{
    public const MUTED_UNTIL_PREFERENCE_KEY = 'bs_nc_muted_until';
    public const NOTIFICATION_TYPE = 'buddysystem_request';

    private function getIdentifier(string $id): IdentificationInterface
    {
        return $this->if->identifier($id);
    }

    /**
     * @inheritDoc
     */
    public function getNotifications(): array
    {
        if (
            0 === $this->dic->user()->getId() ||
            $this->dic->user()->isAnonymous() ||
            !ilBuddySystem::getInstance()->isEnabled()
        ) {
            return [];
        }

        $leftIntervalTimestamp = $this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY);
        $latestRequestTimestamp = null;

        $relations = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner();

        $openRequests = $relations->filter(
            function (ilBuddySystemRelation $relation) use ($leftIntervalTimestamp, &$latestRequestTimestamp, $relations): bool {
                $timeStamp = $relation->getTimestamp();

                if ($timeStamp > $latestRequestTimestamp) {
                    $latestRequestTimestamp = $timeStamp;
                }

                $usrId = $relations->getKey($relation);

                if (!ilObjUser::_lookupActive($usrId)) {
                    return false;
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
                          ->standard(Standard::CADM, 'contacts');

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
                $this->dic->language()->txt('nc_contact_requests_prop_time') => ilDatePresentation::formatDate(
                    new ilDateTime($latestRequestTimestamp, IL_CAL_UNIX)
                )
            ]);

        $osd_notification_handler = new ilNotificationOSDHandler(new ilNotificationOSDRepository($this->dic->database()));

        $group = $factory
            ->standardGroup($this->getIdentifier('contact_bucket_group'))
            ->withTitle($this->dic->language()->txt('nc_contact_requests_headline'))
            ->addNotification(
                $factory->standard($this->getIdentifier('contact_bucket'))
                    ->withNotificationItem($notificationItem)
                    ->withClosedCallable(
                        function () use ($osd_notification_handler): void {
                            $this->dic->user()->writePref(self::MUTED_UNTIL_PREFERENCE_KEY, (string) time());

                            $osd_notification_handler->deleteStaleNotificationsForUserAndType(
                                $this->dic->user()->getId(),
                                self::NOTIFICATION_TYPE
                            );
                        }
                    )->withNewAmount(1)
            );

        return [
            $group
        ];
    }
}
