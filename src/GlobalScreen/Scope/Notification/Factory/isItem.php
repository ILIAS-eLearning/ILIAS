<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\UI\Factory as UIFactory;

/**
 * Interface isItem
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
}
