<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Notifications\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;

/**
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationCenterProvider extends AbstractStaticMetaBarProvider
{
    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        $mb = $this->globalScreen()->metaBar();

        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $nc = $this->dic->globalScreen()->collector()->notifications();

        $new = $nc->getAmountOfNewNotifications();
        $old = $nc->getAmountOfOldNotifications();

        return [
            $mb->notificationCenter($id('notification_center'))
                ->withAmountOfOldNotifications($new + $old)
                ->withAmountOfNewNotifications($new)
                ->withNotifications($nc->getNotifications())
                ->withAvailableCallable(function () : bool {
                    return $this->dic->ctrl()->getCmd() !== "showLogout";
                })
                ->withVisibilityCallable(
                    function () : bool {
                        return (
                            !$this->dic->user()->isAnonymous() &&
                            $this->dic->globalScreen()->collector()->notifications()->hasItems()
                        );
                    }
                ),
        ];
    }
}
