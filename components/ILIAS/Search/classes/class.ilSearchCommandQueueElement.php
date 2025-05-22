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

declare(strict_types=1);
/**
* Represents an entry for the search command queue
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroupServicesSearch
*/
class ilSearchCommandQueueElement
{
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const CREATE = 'create';
    public const RESET = 'reset';

    private int $obj_id;
    private string $obj_type;
    private string $command;
    private ilDateTime $last_update;
    private bool $finished;

    /**
     * set obj_id
     */
    public function setObjId(int $a_id): void
    {
        $this->obj_id = $a_id;
    }

    /**
     * get obj_id
     */
    public function getObjId(): int
    {
        return $this->obj_id;
    }

    /**
     * set obj_type
     */
    public function setObjType(string $a_type): void
    {
        $this->obj_type = $a_type;
    }

    /**
     * get obj_type
     */
    public function getObjType(): string
    {
        return $this->obj_type;
    }

    /**
     * set command
     */
    public function setCommand(string $a_command): void
    {
        $this->command = $a_command;
    }

    /**
     * get command
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * set last_update
     */
    public function setLastUpdate(ilDateTime $date_time): void
    {
        $this->last_update = $date_time;
    }

    /**
     * get last update
     */
    public function getLastUpdate(): ?ilDateTime
    {
        return is_object($this->last_update) ? $this->last_update : null;
    }

    /**
     * set finsihed
     */
    public function setFinished(bool $a_finished): void
    {
        $this->finished = $a_finished;
    }

    /**
     * get finished
     */
    public function getFinished(): bool
    {
        return $this->finished;
    }
}
