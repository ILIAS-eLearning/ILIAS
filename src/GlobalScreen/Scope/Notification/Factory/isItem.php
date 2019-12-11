<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\UI\Factory as UIFactory;

/**
 * Interface isItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isItem
{

    /**
     * @return IdentificationInterface
     */
    public function getProviderIdentification() : IdentificationInterface;

    /**
     * @param UIFactory $factory
     * @return NotificationRenderer
     */
    public function getRenderer(UIFactory $factory) : NotificationRenderer;

    /**
     * Set the callable to be executed, when the notification center is opened.
     *
     * @param callable $handle_opened
     * @return isItem
     */
    public function withOpenedCallable(callable $handle_opened) : isItem;

    /**
     * Get the callable to be executed, when the notification center is opened.
     *
     * @return callable
     */
    public function getOpenedCallable() : callable;

    /**
     * Set the callable to be executed, when this specific item is closed.
     *
     * @param callable $handle_closed
     * @return isItem
     */
    public function withClosedCallable(callable $handle_closed) : isItem;

    /**
     * Get the callable to be executed, when this specific item is closed.
     *
     * @return callable|null
     */
    public function getClosedCallable();

    /**
     * Get whether there are any callables to be executed when the notification
     * center is closed.
     *
     * @return bool
     */
    public function hasClosedCallable();
}
