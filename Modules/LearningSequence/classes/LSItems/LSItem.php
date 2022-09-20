<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Data holding class LSItem .
 */
class LSItem
{
    protected string $type;
    protected string $title;
    protected string $description;
    protected string $icon_path;
    protected bool $is_online;
    protected int $order_number;
    protected ilLSPostCondition $post_condition;
    protected int $ref_id;

    public function __construct(
        string $type,
        string $title,
        string $description,
        string $icon_path,
        bool $is_online,
        int $order_number,
        ilLSPostCondition $post_condition,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIconPath(): string
    {
        return $this->icon_path;
    }

    public function isOnline(): bool
    {
        return $this->is_online;
    }

    public function getOrderNumber(): int
    {
        return $this->order_number;
    }

    public function getPostCondition(): ilLSPostCondition
    {
        return $this->post_condition;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function withOnline(bool $online): LSItem
    {
        $clone = clone $this;
        $clone->is_online = $online;
        return $clone;
    }

    public function withOrderNumber(int $order_number): LSItem
    {
        $clone = clone $this;
        $clone->order_number = $order_number;
        return $clone;
    }

    public function withPostCondition(ilLSPostCondition $post_condition): LSItem
    {
        $clone = clone $this;
        $clone->post_condition = $post_condition;
        return $clone;
    }
}
