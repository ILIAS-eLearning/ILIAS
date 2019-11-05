<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;

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
     * @return NotificationRenderer
     */
    public function getRenderer() : NotificationRenderer;
}
