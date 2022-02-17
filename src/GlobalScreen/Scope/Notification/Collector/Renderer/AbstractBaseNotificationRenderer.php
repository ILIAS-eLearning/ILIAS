<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\UI\Factory as UIFactory;

/**
 * Class AbstractBaseNotificationRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseNotificationRenderer implements NotificationRenderer
{
    use Hasher;

    /**
     * @var UIFactory
     */
    protected $ui_factory;

    /**
     * AbstractBaseNotificationRenderer constructor.
     * @param UIFactory $factory
     */
    public function __construct(UIFactory $factory)
    {
        $this->ui_factory = $factory;
    }

    /**
     * @param isItem $item
     * @return string
     */
    protected function buildCloseQuery(isItem $item) : string
    {
        return http_build_query([
            ClientNotifications::MODE => ClientNotifications::MODE_CLOSED,
            ClientNotifications::ITEM_ID => $this->hash($item->getProviderIdentification()->serialize()),
        ]);
    }
}
