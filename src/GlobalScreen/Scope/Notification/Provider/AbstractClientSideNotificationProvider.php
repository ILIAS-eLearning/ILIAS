<?php namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;

/**
 * Interface AbstractClientSideNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractClientSideNotificationProvider extends AbstractProvider implements ClientSideNotificationProvider
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
    abstract public function enrichItem(isItem $notification) : isItem;
}
