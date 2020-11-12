<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\UI\Component\Item\Notification as NotificationItem;

/**
 * Class Notification
 *
 * The default Notification mapping currently to one UI Notification Item component
 *
 * @author Timon Amstutz
 */
class StandardNotification extends AbstractBaseNotification implements isStandardItem, hasAmount
{

    /**
     * UI Component mapping to this item
     *
     * @var NotificationItem
     */
    private $notification_item;
    /**
     * Amount of old notes, the notification contains.
     *
     * @see hasAmount
     *
     * @var int
     */
    private $old_amount = 0;
    /**
     * Amount of old notes, the notification contains.
     *
     * @see hasAmount
     *
     * @var int
     */
    private $new_amount = 1;


    /**
     * @param NotificationItem $notification_item
     *
     * @return StandardNotification
     */
    public function withNotificationItem(NotificationItem $notification_item) : StandardNotification
    {
        $clone = clone $this;
        $clone->notification_item = $notification_item;

        return $clone;
    }


    /**
     * @return NotificationItem
     */
    public function getNotificationItem() : NotificationItem
    {
        return $this->notification_item;
    }


    /**
     * @inheritdoc
     */
    public function withOldAmount(int $amount = 0) : StandardNotification
    {
        $clone = clone $this;
        $clone->old_amount = $amount;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withNewAmount(int $amount = 0) : StandardNotification
    {
        $clone = clone $this;
        $clone->new_amount = $amount;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getOldAmount() : int
    {
        return $this->old_amount;
    }


    /**
     * @inheritdoc
     */
    public function getNewAmount() : int
    {
        return $this->new_amount;
    }
}
