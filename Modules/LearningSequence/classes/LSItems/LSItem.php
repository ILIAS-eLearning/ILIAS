<?php

declare(strict_types=1);

/**
 * Data holding class LSItem .
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LSItem
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $icon_path;

    /**
     * @var bool
     */
    protected $is_online;

    /**
     * @var int
     */
    protected $order_number;

    /**
     * @var ilLSPostCondition
     */
    protected $post_condition;

    /**
     * @var int
     */
    protected $ref_id;

    public function __construct(
        string $type,
        string $title,
        string $description,
        string $icon_path,
        bool $is_online,
        int $order_number,
        \ilLSPostCondition $post_condition,
        int $ref_id
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->icon_path = $icon_path;
        $this->is_online = $is_online;
        $this->order_number = $order_number;
        $this->post_condition = $post_condition;
        $this->ref_id = $ref_id;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getIconPath() : string
    {
        return $this->icon_path;
    }

    public function isOnline() : bool
    {
        return $this->is_online;
    }

    public function withOnline(bool $online) : LSItem
    {
        $clone = clone $this;
        $clone->is_online = $online;
        return $clone;
    }

    public function getOrderNumber() : int
    {
        return $this->order_number;
    }

    public function withOrderNumber(int $order_number) : LSItem
    {
        $clone = clone $this;
        $clone->order_number = $order_number;
        return $clone;
    }

    public function getPostCondition() : ilLSPostCondition
    {
        return $this->post_condition;
    }

    public function withPostCondition(ilLSPostCondition $postcondition) : LSItem
    {
        $clone = clone $this;
        $clone->post_condition = $postcondition;
        return $clone;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }
}
