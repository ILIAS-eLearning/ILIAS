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

namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use ILIAS\Notifications\ilNotificationOSDHandler;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\OSD\ilOSDNotificationObject;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;

/**
 * Interface AbstractNotificationProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractNotificationProvider extends AbstractProvider implements NotificationProvider
{
    protected const NOTIFICATION_TYPE = 'none';
    protected const MUTED_UNTIL_PREFERENCE_KEY = '';

    protected Container $dic;
    protected IdentificationProviderInterface $if;
    protected NotificationFactory $notification_factory;
    protected ilNotificationOSDHandler $osd_handler;

    /**
     * @inheritDoc
     */
    public function __construct(Container $dic = null)
    {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }
        parent::__construct($dic);
        $this->notification_factory = $this->globalScreen()->notifications()->factory();
        $this->if = $this->globalScreen()->identification()->core($this);
        $this->osd_handler = new ilNotificationOSDHandler(new ilNotificationOSDRepository($this->dic->database()));
    }

    final public function getType(): string
    {
        return $this::NOTIFICATION_TYPE;
    }

    final public function removeOSDNotificationsByProviderKey(string $provider_key, int $user_id = 0): void
    {
        $this->osd_handler->removeProviderNotification($this, $provider_key, $user_id);
    }

    /**
     * @return ilOSDNotificationObject[]
     */
    final public function getUserOSDNotifications(): array
    {
        return $this->osd_handler->getOSDNotificationsForUser(
            $this->dic->user()->getId(),
            true,
            time() - (int) ($this->dic->user()->getPref(self::MUTED_UNTIL_PREFERENCE_KEY) ?? 0),
            $this::NOTIFICATION_TYPE
        );
    }

    final public function deleteStaleNotifications(): void
    {
        $this->osd_handler->deleteStaleOSDNotificationsForUserAndType(
            $this->dic->user()->getId(),
            $this::NOTIFICATION_TYPE
        );
    }

    final public function getNotificationConfig(): ilNotificationConfig
    {
        $config = new ilNotificationConfig($this::NOTIFICATION_TYPE);
        $config->setValidForSeconds(ilNotificationConfig::TTL_LONG);
        $config->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getAdministrativeNotifications(): array
    {
        return [];
    }
}
