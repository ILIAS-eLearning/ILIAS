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
        $this->notifications = $notifications;

        return $this;
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

        // TODO implement counter
        return $DIC->ui()->factory()->symbol()->glyph()->notification();
    }


    /**
     * @inheritDoc
     */
    public function getPosition() : int
    {
        return 1;
    }
}
