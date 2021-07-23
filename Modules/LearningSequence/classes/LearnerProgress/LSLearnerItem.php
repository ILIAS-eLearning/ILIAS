<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */
/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Add learning progress and availability information to the LSItem
 */
class LSLearnerItem extends LSItem
{
    protected int $usr_id;
    protected int $learning_progress_status;
    protected int $availability_status;

    public function __construct(
        int $usr_id,
        int $learning_progress_status,
        int $availability_status,
        LSItem $ls_item
    ) {
        $this->usr_id = $usr_id;
        $this->learning_progress_status = $learning_progress_status;
        $this->availability_status = $availability_status;
        parent::__construct(
            $ls_item->getType(),
            $ls_item->getTitle(),
            $ls_item->getDescription(),
            $ls_item->getIconPath(),
            $ls_item->isOnline(),
            $ls_item->getOrderNumber(),
            $ls_item->getPostCondition(),
            $ls_item->getRefId()
        );
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getLearningProgressStatus() : int
    {
        return $this->learning_progress_status;
    }

    public function getAvailability() : int
    {
        return $this->availability_status;
    }

    public function withPostCondition(ilLSPostCondition $post_condition) : LSItem
    {
        throw new \LogicException('keep this item receptive only');
    }

    public function withOrderNumber(int $order_number) : LSItem
    {
        throw new \LogicException('keep this item receptive only');
    }

    public function withOnline(bool $online) : LSItem
    {
        throw new \LogicException('keep this item receptive only');
    }
}
