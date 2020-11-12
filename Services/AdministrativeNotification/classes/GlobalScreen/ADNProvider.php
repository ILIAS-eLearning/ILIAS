<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AdministrativeNotification\GlobalScreen;

use ilADNNotification;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class ADNProvider
 */
class ADNProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAdministrativeNotifications() : array
    {
        $adns = [];
        $access = BasicAccessCheckClosures::getInstance();

        $i = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };
        /**
         * @var $item ilADNNotification
         * @var $adn  AdministrativeNotification
         */
        foreach (ilADNNotification::get() as $item) {
            $adn = $this->notification_factory->administrative($i((string) $item->getId()))->withTitle($item->getTitle())->withSummary($item->getBody());
            switch ($item->getType()) {
                case ilADNNotification::TYPE_ERROR:
                    $adn = $adn->withBreakingDenotation();
                    break;
                case ilADNNotification::TYPE_WARNING:
                    $adn = $adn->withImportantDenotation();
                    break;
                case ilADNNotification::TYPE_INFO:
                default:
                    $adn = $adn->withNeutralDenotation();
                    break;
            }
            if ((bool) $item->getDismissable() && $access->isUserLoggedIn()()) {
                $adn = $adn->withClosedCallable(function () use ($item) {
                    $item->dismiss($this->dic->user());
                });
                $adn = $adn->withVisibilityCallable(function () use ($item) {
                    return (bool) !\ilADNDismiss::hasDimissed($this->dic->user(), $item);
                });
            }

            $adns[] = $adn;
        }
        return $adns;
    }

}
