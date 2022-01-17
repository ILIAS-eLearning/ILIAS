<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AdministrativeNotification\GlobalScreen;

use ilADNNotification;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use Closure;
use ILIAS\DI\Container;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ADNProvider
 */
class ADNProvider extends AbstractNotificationProvider implements NotificationProvider
{
    protected \ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures $access;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->access = BasicAccessCheckClosures::getInstance();
    }

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

        $i = fn(string $id): IdentificationInterface => $this->if->identifier($id);
        /**
         * @var $item ilADNNotification
         * @var $adn  AdministrativeNotification
         */
        foreach (ilADNNotification::get() as $item) {
            $adn = $this->notification_factory->administrative($i((string) $item->getId()))->withTitle($item->getTitle())->withSummary($item->getBody());
            $adn = $this->handleDenotation($item, $adn);

            $is_visible = static fn(): bool => true;

            // is limited to roles
            if ($item->isLimitToRoles()) {
                $is_visible = $this->combineClosure($is_visible, fn() => $this->dic->rbac()->review()->isAssignedToAtLeastOneGivenRole($this->dic->user()->getId(),
                    $item->getLimitedToRoleIds()));
            }

            // is dismissale
            if ($item->getDismissable() && $this->access->isUserLoggedIn()()) {
                $adn = $adn->withClosedCallable(function () use ($item) {
                    $item->dismiss($this->dic->user());
                });
                $is_visible = $this->combineClosure($is_visible, fn(): bool => !\ilADNDismiss::hasDimissed($this->dic->user(), $item));
            }

            $is_visible = $this->combineClosure($is_visible, fn(): bool => $item->isVisibleForUser($this->dic->user()));

            $adns[] = $adn->withVisibilityCallable($is_visible);
        }
        return $adns;
    }

    private function handleDenotation(
        ilADNNotification $item,
        AdministrativeNotification $adn
    ) : AdministrativeNotification {
        $settype = static function (int $type, AdministrativeNotification $adn) : AdministrativeNotification {
            switch ($type) {
                case ilADNNotification::TYPE_ERROR:
                    return $adn->withBreakingDenotation();
                case ilADNNotification::TYPE_WARNING:
                    return $adn->withImportantDenotation();
                case ilADNNotification::TYPE_INFO:
                default:
                    return $adn->withNeutralDenotation();
            }
        };

        // denotation during event
        if (!$item->isPermanent() && $item->isDuringEvent()) {
            return $settype($item->getTypeDuringEvent(), $adn);
        }
        return $settype($item->getType(), $adn);
    }

    private function combineClosure(Closure $closure, ?Closure $additional = null) : Closure
    {
        if ($additional instanceof Closure) {
            return static fn(): bool => $additional() && $closure();
        }

        return $closure;
    }

}
