<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\NotificationCenterRenderer;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class NotificationCenter
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationCenter extends AbstractBaseItem implements isItem, hasSymbol
{

    /**
     * @var int
     */
    private $amount_of_notifications = 0;
    /**
     * @var isItem[]
     */
    private $notifications = [];


    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $provider_identification)
    {
        parent::__construct($provider_identification);
        $this->renderer = new NotificationCenterRenderer();
    }


    /**
     * @param isItem[] $notifications
     *
     * @return NotificationCenter
     */
    public function withNotifications(array $notifications) : NotificationCenter
    {
        $clone = clone($this);
        $clone->notifications = $notifications;

        return $clone;
    }


    /**
     * @return isItem[]
     */
    public function getNotifications() : array
    {
        return $this->notifications;
    }


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return true;
    }


    /**
     * @return Symbol
     */
    public function getSymbol() : Symbol
    {
        global $DIC;

        return $DIC->ui()->factory()->symbol()->glyph()->notification()->withCounter($DIC->ui()->factory()->counter()->novelty($this->getAmountOfNotifications()));
    }


    /**
     * @inheritDoc
     */
    public function getPosition() : int
    {
        return 1;
    }


    public function withAmountOfNotifications(int $amount_of_notifications) : NotificationCenter
    {
        $clone = clone($this);
        $clone->amount_of_notifications = $amount_of_notifications;

        return $clone;
    }


    /**
     * @return int
     */
    public function getAmountOfNotifications() : int
    {
        return $this->amount_of_notifications;
    }
}
