<?php

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

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Implementation\Bucket\State;

class BucketContainer extends \ActiveRecord
{
    public static function returnDbTableName(): string
    {
        return "il_bt_bucket";
    }

    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $user_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $root_task_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $current_task_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     2
     */
    protected int $state = State::ERROR;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected int $total_number_of_tasks = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     2
     */
    protected int $percentage = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     255
     */
    protected string $title = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     255
     */
    protected string $description = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected ?int $last_heartbeat = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getRootTaskid(): int
    {
        return $this->root_task_id;
    }

    public function setRootTaskid(int $root_task_id): void
    {
        $this->root_task_id = $root_task_id;
    }

    public function getCurrentTaskid(): int
    {
        return $this->current_task_id;
    }

    public function setCurrentTaskid(int $current_task_id): void
    {
        $this->current_task_id = $current_task_id;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getTotalNumberoftasks(): int
    {
        return $this->total_number_of_tasks;
    }

    public function setTotalNumberoftasks(int $total_number_of_tasks): void
    {
        $this->total_number_of_tasks = $total_number_of_tasks;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): void
    {
        $this->percentage = $percentage;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getLastHeartbeat(): int
    {
        return $this->last_heartbeat;
    }

    public function setLastHeartbeat(int $last_heartbeat): void
    {
        $this->last_heartbeat = $last_heartbeat;
    }
}
