<?php namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;

/**
 * Interface AbstractNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractNotificationProvider extends AbstractProvider implements NotificationProvider
{

    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var IdentificationProviderInterface
     */
    protected $if;
    /**
     * @var NotificationFactory
     */
    protected $notification_factory;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->notification_factory = $this->globalScreen()->notifications()->factory();
        $this->if = $this->globalScreen()->identification()->core($this);
    }

    /**
     * @inheritDoc
     */
    public function getAdministrativeNotifications() : array
    {
        return [];
    }
}
