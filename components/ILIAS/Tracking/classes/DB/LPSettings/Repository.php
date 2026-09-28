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

namespace ILIAS\Tracking\DB\LPSettings;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Tracking\DB\LPSettings\Element\FactoryInterface as ElementFactoryInterface;
use ILIAS\Tracking\DB\LPSettings\Element\LPSettingsCollectionInterface;
use ILIAS\Tracking\DB\LPSettings\Element\LPSettingsInterface;

class Repository implements RepositoryInterface
{
    public function __construct(
        protected ilDBInterface $db,
        protected ElementFactoryInterface $element_factory
    ) {
    }

    public function readLPSettings(
        int $object_id
    ): LPSettingsInterface|null {
        $query = "SELECT * FROM ut_lp_settings WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        if ($row = $res->fetchAssoc()) {
            return $this->element_factory->lpSettings()
                ->withObjectId($object_id)
                ->withUMode((int) $row['u_mode'])
                ->withVisits((int) $row['visits'])
                ->withObjType($row['obj_type']);
        }
        return null;
    }

    public function readLPSettingsCollection(
        int ...$object_ids
    ): LPSettingsCollectionInterface|null {
        $query = "SELECT * FROM ut_lp_settings WHERE " . $this->db->in('obj_id', $object_ids, false, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $res->fetchAssoc()) {
            $elements[] = $this->element_factory->lpSettings()
                ->withObjectId((int) $row['obj_id'])
                ->withUMode((int) $row['u_mode'])
                ->withVisits((int) $row['visits'])
                ->withObjType($row['obj_type']);
        }
        return count($elements) === 0
            ? null
            : $this->element_factory->lpSettingsCollection(...$elements);
    }

    public function writeLPSettings(
        LPSettingsInterface $lp_settings
    ): void {
        $query = "INSERT INTO ut_lp_settings (obj_id, obj_type, u_mode, visits) VALUES ("
            . $this->db->quote($lp_settings->getObjectId(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($lp_settings->getObjType(), ilDBConstants::T_TEXT) . ", "
            . $this->db->quote($lp_settings->getUMode(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($lp_settings->getVisits(), ilDBConstants::T_INTEGER) . ")"
            . " ON DUPLICATE KEY UPDATE obj_type=VALUES(obj_type), u_mode=VALUES(u_mode), visits=VALUES(visits)";
        $this->db->manipulate($query);
    }

    public function deleteLPSettings(
        int $object_id
    ): void {
        $query = "DELETE FROM ut_lp_settings WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public function isLPSettingsEntryInDB(
        int $obj_id
    ): bool {
        return !is_null($this->readLPSettings($obj_id));
    }
}
