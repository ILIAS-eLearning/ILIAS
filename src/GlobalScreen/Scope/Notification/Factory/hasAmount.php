<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

/**
 * Interface hasAmount
 * Items can implicitly contain a various amount of news. E.g.
 * A Mail notification, telling the user, that there are 23 unread mails
 * contains a newAmountOf 23 to be displayed by the novelty counter, even
 * if it this information is listed in only one item.
 *
 * @author Timon Amstutz
 */
interface hasAmount
{

    /**
     * Set the amount of old notes, the notification contains.
     *
     * @param int $amount
     * @return StandardNotification
     */
    public function withOldAmount(int $amount = 0) : StandardNotification;

    /**
     * Set the amount of new notes, the notification contains.
     *
     * @param int $amount
     * @return StandardNotification
     */
    public function withNewAmount(int $amount = 0) : StandardNotification;

    /**
     * Get the amount of new notes, the notification contains.
     *
     * @return int
     */
    public function getOldAmount() : int;

    /**
     * Get the amount of new notes, the notification contains.
     *
     * @return int
     */
    public function getNewAmount() : int;
}
