<?php

declare(strict_types=1);

/**
 * Add learning progress and availability information to the LSItem
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LSLearnerItem extends LSItem
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $learning_progress_status;

    /**
     * @var int
     */
    protected $availability_status;

    /**
     * @var ILIAS\KioskMode\State
     */
    protected $kiosk_state;


    public function __construct(
        int $usr_id,
        \Closure $learning_progress_status,
        int $availability_status,
        ILIAS\KioskMode\State $kiosk_state,
        LSItem $ls_item
    ) {
        $this->usr_id = $usr_id;
        $this->learning_progress_status = $learning_progress_status;
        $this->availability_status = $availability_status;
        $this->kiosk_state = $kiosk_state;

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

    /**
     * Calling a closure here is a breach of the "immutable object" paradigm
     * and no good practice at all! Do NOT copy!
     * However, this fixes #27853 for relase 5.4 - with release 6, the issue (among others)
     * is solved by restructuring dependencies and re-ordering calls and instantiation.
     */
    public function getLearningProgressStatus() : int
    {
        $lp_call = $this->learning_progress_status;
        $lp = $lp_call($this->getRefId(), $this->getUserId());
        return $lp;
    }

    public function getAvailability() : int
    {
        return $this->availability_status;
    }

    public function getState() : ILIAS\KioskMode\State
    {
        return $this->kiosk_state;
    }

    public function withPostCondition(ilLSPostCondition $postcondition) : LSItem
    {
        throw new \LogicException('keep this item receptive only');
    }

    public function withOrderNumber(int $position) : LSItem
    {
        throw new \LogicException('keep this item receptive only');
    }

    public function withOnline(bool $online) : LSItem
    {
        throw new \LogicException('keep this item receptive only');
    }
}
