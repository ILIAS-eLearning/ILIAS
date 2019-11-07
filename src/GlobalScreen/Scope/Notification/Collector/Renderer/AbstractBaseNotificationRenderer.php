<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\UI\Factory as UIFactory;

/**
 * Class AbstractBaseNotificationRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseNotificationRenderer implements NotificationRenderer
{

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
}