<?php namespace ILIAS\Notifications\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;

/**
 * Class NotificationCenterProvider
 *
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
                    //This is a heavily incomplete fix for: #26586
                    //This should be fixed by the auth service
                    if ($this->dic->ctrl()->getCmd() == "showLogout") {
                        return false;
                    }

                    return true;
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
