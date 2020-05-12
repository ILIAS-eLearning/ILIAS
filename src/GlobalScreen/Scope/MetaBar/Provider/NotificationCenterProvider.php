<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

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

        $id = function ($id) : IdentificationInterface {
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
                ->withAvailableCallable(static function () {
                    //This is a heavily incomplete fix for: #26586
                    //This should be fixed by the auth service
                    global $DIC;
                    if ($DIC->ctrl()->getCmd() == "showLogout") {
                        return false;
                    }
                    return true;
                })
                ->withVisibilityCallable(
                    function () {
                        return !$this->dic->user()->isAnonymous() && $this->dic->globalScreen()->collector()->notifications()->hasItems();
                    }
                ),
        ];
    }
}
