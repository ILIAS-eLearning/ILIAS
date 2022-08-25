<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Storage of ecs remote user
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSRemoteUser
{
    private int $eru_id;
    private int $sid;
    private int $mid;
    private int $usr_id;
    private string $remote_usr_id;


    /**
     * Constructor
     */
    public function __construct(
        int $eru_id,
        int $sid,
        int $mid,
        int $usr_id,
        string $remote_usr_id
    ) {
        $this->eru_id = $eru_id;
        $this->sid = $sid;
        $this->mid = $mid;
        $this->usr_id = $usr_id;
        $this->remote_usr_id = $remote_usr_id;
    }

    public function getId(): int
    {
        return $this->eru_id;
    }

    public function setServerId(int $a_sid): void
    {
        $this->sid = $a_sid;
    }

    public function getServerId(): int
    {
        return $this->sid;
    }

    public function setMid(int $a_mid): void
    {
        $this->mid = $a_mid;
    }

    public function getMid(): int
    {
        return $this->mid;
    }

    public function setUserId(int $a_usr_id): void
    {
        $this->usr_id = $a_usr_id;
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function setRemoteUserId(string $a_remote_id): void
    {
        $this->remote_usr_id = $a_remote_id;
    }

    public function getRemoteUserId(): string
    {
        return $this->remote_usr_id;
    }
}
