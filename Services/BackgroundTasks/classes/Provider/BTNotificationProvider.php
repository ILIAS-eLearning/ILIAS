<?php
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

namespace ILIAS\BackgroundTasks\Provider;

use ilBTPopOverGUI;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class BTNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BTNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications(): array
    {
        $nr_buckets = count($this->dic->backgroundTasks()->persistence()->getBucketIdsOfUser($this->dic->user()->getId()));
        if ($nr_buckets === 0) {
            return [];
        }

        $this->dic->ui()->mainTemplate()->addJavaScript("./Services/BackgroundTasks/js/background_task_refresh.js");
        $this->dic->language()->loadLanguageModule('background_tasks');

        $id = fn (string $id): IdentificationInterface => $this->if->identifier($id);

        $factory = $this->globalScreen()->notifications()->factory();

        $item_source = new ilBTPopOverGUI($this->dic);
        $group = $factory->standardGroup($id('bg_task_bucket_group'))
            ->withTitle($this->txt('background_tasks'))
            ->addNotification(
                $factory->standard($id('bg_task_bucket'))
                    ->withNotificationItem($item_source->getNotificationItem($nr_buckets))
                    ->withNewAmount(1)
            );

        return [
            $group,
        ];
    }


    private function txt(string $id): string
    {
        return $this->dic->language()->txt($id);
    }
}
