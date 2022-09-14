<?php declare(strict_types=1);

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

namespace ILIAS\Services\Notifications;

use Closure;
use ILIAS\UI\Factory as UIFactory;
use ilSetting;
use ILIAS\UI\Component\Toast\Toast;
use ILIAS\Notifications\Model\OSD\ilOSDNotificationObject;

class ToastsOfNotifications
{
    private UIFactory $factory;
    private ilSetting $settings;

    public function __construct(UIFactory $factory, ilSetting $settings)
    {
        $this->factory = $factory;
        $this->settings = $settings;
    }

    public function create(array $notifications): array
    {
        return array_map([$this, 'toastFromNotification'], $notifications);
    }

    private function toastFromNotification(ilOSDNotificationObject $notification): Toast
    {
        $toast = $this->factory
               ->toast()
               ->standard(
                   $notification->getObject()->title,
                   $this->factory->symbol()->icon()->custom($notification->getObject()->iconPath, '')
               )
               ->withAction('ilias.php?' . http_build_query([
                   'baseClass' => 'ilNotificationGUI',
                   'cmd' => 'removeOSDNotifications',
                   'cmdMode' => 'asynch',
                   'notification_id' => $notification->getId()
               ]))
               ->withDescription($notification->getObject()->shortDescription)
               ->withVanishTime($this->secondsToMS((int) $this->settings->get('osd_vanish')))
               ->withDelayTime((int) $this->settings->get('osd_delay'));

        foreach ($notification->getObject()->links as $link) {
            $toast = $toast->withAdditionalLink($this->factory->link()->standard(
                $link->getTitle(),
                $link->getUrl()
            ));
        }

        return $toast;
    }

    private function secondsToMS(int $seconds): int
    {
        return $seconds * 1000;
    }
}
