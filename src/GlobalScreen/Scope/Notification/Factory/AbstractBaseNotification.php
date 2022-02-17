<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationRenderer;
use ILIAS\UI\Factory as UIFactory;

/**
 * Class AbstractBaseNotification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseNotification implements isStandardItem
{

    /**
     * @var IdentificationInterface
     */
    protected $provider_identification;

    /**
     * Callable to be executed, if the notification center has been opened.
     *
     * @var callable
     */
    protected $handle_opened;

    /**
     * Callable to be executed, if this specific item has been closed.
     *
     * @var callable|null
     */
    protected $handle_closed;

    /**
     * StandardNotification constructor.
     *
     * @param IdentificationInterface $identification
     */
    public function __construct(IdentificationInterface $identification)
    {
        $this->handle_opened = function () {
        };

        $this->provider_identification = $identification;
    }


    /**
     * @inheritDoc
     */
    public function getProviderIdentification() : IdentificationInterface
    {
        return $this->provider_identification;
    }


    /**
     * @inheritDoc
     */
    public function getRenderer(UIFactory $factory) : NotificationRenderer
    {
        return new StandardNotificationRenderer($factory);
    }

    /**
     * @inheritDoc
     */
    public function withOpenedCallable(callable $handle_opened) : isItem
    {
        $clone = clone $this;
        $clone->handle_opened = $handle_opened;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getOpenedCallable() : callable
    {
        return $this->handle_opened;
    }

    /**
     * @inheritDoc
     */
    public function withClosedCallable(callable $handle_closed) : isItem
    {
        $clone = clone $this;
        $clone->handle_closed = $handle_closed;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getClosedCallable()
    {
        return $this->handle_closed;
    }

    /**
     * @inheritDoc
     */
    public function hasClosedCallable()
    {
        return is_callable($this->handle_closed);
    }
}
