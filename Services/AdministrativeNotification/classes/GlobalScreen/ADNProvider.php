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

/**
 * Class ADNProvider
 */
class ADNProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @var BasicAccessCheckClosures
     */
    protected $access;

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

        $i = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };
        /**
         * @var $item ilADNNotification
         * @var $adn  AdministrativeNotification
         */
        foreach (ilADNNotification::get() as $item) {
            $adn = $this->notification_factory->administrative($i((string) $item->getId()))->withTitle($item->getTitle())->withSummary($item->getBody());
            $adn = $this->handleDenotation($item, $adn);

            $is_visible = static function () : bool {
                return true;
            };

            // is limited to roles
            if ($item->isLimitToRoles()) {
                $is_visible = $this->combineClosure($is_visible, function () use ($item) {
                    return $this->dic->rbac()->review()->isAssignedToAtLeastOneGivenRole($this->dic->user()->getId(),
                        $item->getLimitedToRoleIds());
                });
            }

            // is dismissale
            if ($item->getDismissable() && $this->access->isUserLoggedIn()()) {
                $adn = $adn->withClosedCallable(function () use ($item) {
                    $item->dismiss($this->dic->user());
                });
                $is_visible = $this->combineClosure($is_visible, function () use ($item) : bool {
                    return !\ilADNDismiss::hasDimissed($this->dic->user(), $item);
                });
            }

            $is_visible = $this->combineClosure($is_visible, function () use ($item) : bool {
                return $item->isVisibleForUser($this->dic->user());
            });

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
            return static function () use ($closure, $additional) : bool {
                return $additional() && $closure();
            };
        }

        return $closure;
    }

}
