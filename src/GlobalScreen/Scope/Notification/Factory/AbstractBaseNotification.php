<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationRenderer;

/**
 * Class AbstractBaseNotification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseNotification implements isItem
{

    /**
     * @var IdentificationInterface
     */
    protected $provider_identification;


    /**
     * StandardNotification constructor.
     *
     * @param IdentificationInterface $identification
     */
    public function __construct(IdentificationInterface $identification)
    {
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
    public function getRenderer() : NotificationRenderer
    {
        return new StandardNotificationRenderer();
    }
}
