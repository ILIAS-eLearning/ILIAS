<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class NotificationCenterProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationCenterProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
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
        $item = [];

        $item[] = $mb->notificationCenter($id('notification_center'))
            ->withAvailableCallable(function () {
                // Check if notifications available
                return true;
            })
            ->withVisibilityCallable(
                function () {
                    return !$this->dic->user()->isAnonymous();
                }
            );

        return $item;
    }
}