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
* Storage of ECS imported objects.
* This class stores the econent id and informations whether an object is imported or not.
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSImport
{
    protected ilDBInterface $db;

    protected int $server_id = 0;
    protected int $obj_id = 0;
    protected string $econtent_id = '';
    protected string $content_id = '';
    protected ?string $sub_id = '';
    protected int $mid = 0;
    protected bool $imported = false;

    public function __construct(int $a_server_id, int $a_obj_id)
    {
        global $DIC;
        $this->db = $DIC->database();

        $this->server_id = $a_server_id;
        $this->obj_id = $a_obj_id;

        $this->read();
    }

    public function setServerId($a_server_id): void
    {
        $this->server_id = $a_server_id;
    }

    public function getServerId(): int
    {
        return $this->server_id;
    }

    /**
     * Set imported
     */
    public function setImported(bool $a_status): void
    {
        $this->imported = $a_status;
    }

    public function setSubId($a_id): void
    {
        $this->sub_id = $a_id;
    }

    public function getSubId(): ?string
    {
        return (isset($this->sub_id) && $this->sub_id !== '') ? $this->sub_id : null;
    }

    /**
     * Set content id.
     */
    public function setContentId($a_content_id): void
    {
        $this->content_id = $a_content_id;
    }

    /**
     * get content id
     */
    public function getContentId(): string
    {
        return $this->content_id;
    }

    /**
     * set mid
     */
    public function setMID($a_mid): void
    {
        $this->mid = $a_mid;
    }

    /**
     * get mid
     */
    public function getMID(): int
    {
        return $this->mid;
    }

    /**
     * set econtent id
     *
     * @param int econtent id
     *
     */
    public function setEContentId($a_id): void
    {
        $this->econtent_id = $a_id;
    }

    /**
     * get econtent id
     */
    public function getEContentId(): string
    {
        return $this->econtent_id;
    }

    /**
     * Save
     */
    public function save(): bool
    {
        $query = "DELETE FROM ecs_import " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $this->db->manipulate($query);

        $query = "INSERT INTO ecs_import (obj_id,mid,econtent_id,sub_id,server_id,content_id) " .
            "VALUES ( " .
            $this->db->quote($this->obj_id, 'integer') . ", " .
            $this->db->quote($this->mid, 'integer') . ", " .
            $this->db->quote($this->econtent_id, 'text') . ", " .
            $this->db->quote($this->getSubId(), 'text') . ', ' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote($this->getContentId(), 'text') . ' ' .
            ")";

        $this->db->manipulate($query);

        return true;
    }

    /**
     * Read
     */
    private function read(): void
    {
        $query = "SELECT * FROM ecs_import WHERE " .
            "obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->econtent_id = $row->econtent_id;
            $this->mid = (int) $row->mid;
            $this->sub_id = $row->sub_id;
            $this->content_id = $row->content_id;
        }
    }
}
