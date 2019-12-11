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
     * Amount of notifications already consulted by the user (will spawn
     * status counters)
     *
     * @var int
     */
    private $amount_of_old_notifications = 0;

    /**
     * Amount of notifications not yet consulted by the user (will spawn
     * novelty counters)
     *
     * @var int
     */
    private $amount_of_new_notifications = 0;

    /**
     * Set of notifications in the center.
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

        $f = $DIC->ui()->factory();
        $new = $this->getAmountOfNewNotifications();
        $old = $this->getAmountOfOldNotifications() - $new;
        $glyph = $f->symbol()->glyph()->notification()->withCounter($f->counter()->novelty($new));
        if ($old > 0) {
            $glyph = $glyph->withCounter($f->counter()->status($old));
        }
        return $glyph;
    }


    /**
     * @inheritDoc
     */
    public function getPosition() : int
    {
        return 1;
    }

    /**
     * Get a Center like this, but with a given amount of old notifications
     *
     * @param int $amount
     * @return NotificationCenter
     */
    public function withAmountOfOldNotifications(int $amount) : NotificationCenter
    {
        $clone = clone($this);
        $clone->amount_of_old_notifications = $amount;

        return $clone;
    }

    /**
     * Get the amount of old notifications
     *
     * @return int
     */
    public function getAmountOfOldNotifications() : int
    {
        return $this->amount_of_old_notifications;
    }

    /**
     * Get a Center like this, but with a given amount of new notifications
     *
     * @param int $amount
     * @return NotificationCenter
     */
    public function withAmountOfNewNotifications(int $amount) : NotificationCenter
    {
        $clone = clone($this);
        $clone->amount_of_new_notifications = $amount;

        return $clone;
    }

    /**
     * Get the amount of new notifications
     *
     * @return int
     */
    public function getAmountOfNewNotifications() : int
    {
        return $this->amount_of_new_notifications;
    }
}
