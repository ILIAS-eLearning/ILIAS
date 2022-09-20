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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSParticipant
{
    private int $cid;
    private int $pid;
    private int $mid;
    private string $email;
    private string $dns;
    private string $description;
    private string $participantname;
    private bool $is_self;

    private ilECSOrganisation $org;

    public function __construct(object $json_obj, int $a_cid)
    {
        $this->cid = $a_cid;
        $this->read($json_obj);
    }

    /**
     * get community id
     */
    public function getCommunityId(): int
    {
        return $this->cid;
    }

    /**
     * get mid
     */
    public function getMID(): int
    {
        return $this->mid;
    }

    /**
     * get email
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    /**
     * get dns
     */
    public function getDNS(): string
    {
        return $this->dns;
    }

    /**
     * get description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * get participant name
     */
    public function getParticipantName(): string
    {
        return $this->participantname;
    }

    /**
     * Get pid
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * is self
     */
    public function isSelf(): bool
    {
        return $this->is_self;
    }

    /**
     * Get organisation
     * @return ilECSOrganisation $org
     */
    public function getOrganisation(): ilECSOrganisation
    {
        return $this->org;
    }

    /**
     * Read
     */
    private function read(object $json_obj): void
    {
        $this->pid = $json_obj->pid;
        $this->mid = $json_obj->mid;
        $this->email = $json_obj->email;
        $this->dns = $json_obj->dns;
        $this->description = $json_obj->description;

        $this->participantname = $json_obj->name;
        $this->is_self = $json_obj->itsyou;

        $this->org = new ilECSOrganisation();
        if (is_object($json_obj->org)) {
            $this->org->loadFromJson($json_obj->org);
        }
    }
}
