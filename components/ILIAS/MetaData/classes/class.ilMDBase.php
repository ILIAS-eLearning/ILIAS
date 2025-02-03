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
 * Meta Data class
 * always instantiate this class first to set/get single meta data elements
 * @package ilias-core
 * @version $Id$
 * @deprecated will be removed with ILIAS 11, please use the new API (see {@see ../docs/api.md})
 */
abstract class ilMDBase
{
    /**
     * object id (NOT ref_id!) of rbac object (e.g for page objects the obj_id
     * of the content object; for media objects this is set to 0, because their
     * object id are not assigned to ref ids)
     */
    private int $rbac_id;

    /**
     * obj_id (e.g for structure objects the obj_id of the structure object)
     */
    private int $obj_id;

    /**
     * type of the object (e.g st,pg,crs ...)
     */
    private string $obj_type;

    private ?int $meta_id = null;
    private int $parent_id;
    private string $parent_type;

    /**
     * export mode, if true, first Identifier will be
     * set to ILIAS/il_<INSTALL_ID>_<TYPE>_<ID>
     */
    private bool $export_mode = false;

    protected ilLogger $log;
    protected ilDBInterface $db;

    /**
     * constructor
     *
     * @param int    $a_rbac_id       object id (NOT ref_id!) of rbac object (e.g for page objects
     *                                the obj_id of the content object; for media objects this
     *                                is set to 0, because their object id are not assigned to ref ids)
     * @param int    $a_obj_id        object id (e.g for structure objects the obj_id of the structure object)
     * @param string $a_type          type of the object (e.g st,pg,crs ...)
     */
    public function __construct(
        int $a_rbac_id = 0,
        int $a_obj_id = 0,
        string $a_type = ''
    ) {
        global $DIC;

        $this->db = $DIC->database();

        if ($a_obj_id === 0) {
            $a_obj_id = $a_rbac_id;
        }

        $this->log = ilLoggerFactory::getLogger("meta");

        $this->rbac_id = $a_rbac_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_type;
    }

    abstract public function read(): bool;

    // SET/GET
    public function setRBACId(int $a_id): void
    {
        $this->rbac_id = $a_id;
    }

    public function getRBACId(): int
    {
        return $this->rbac_id;
    }

    public function setObjId(int $a_id): void
    {
        $this->obj_id = $a_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjType(string $a_type): void
    {
        $this->obj_type = $a_type;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function setMetaId(int $a_meta_id, bool $a_read_data = true): void
    {
        $this->meta_id = $a_meta_id;

        if ($a_read_data) {
            $this->read();
        }
    }

    public function getMetaId(): ?int
    {
        return $this->meta_id;
    }

    public function setParentType(string $a_parent_type): void
    {
        $this->parent_type = $a_parent_type;
    }

    public function getParentType(): string
    {
        return $this->parent_type;
    }

    public function setParentId(int $a_id): void
    {
        $this->parent_id = $a_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setExportMode(bool $a_export_mode = true): void
    {
        $this->export_mode = $a_export_mode;
    }

    public function getExportMode(): bool
    {
        return $this->export_mode;
    }

    public function validate(): bool
    {
        return false;
    }

    public function update(): bool
    {
        return false;
    }

    public function save(): int
    {
        return 0;
    }

    public function delete(): bool
    {
        return false;
    }

    public function toXML(ilXmlWriter $writer): void
    {
    }
}
