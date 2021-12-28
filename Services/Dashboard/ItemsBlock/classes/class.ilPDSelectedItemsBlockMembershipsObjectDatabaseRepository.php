<?php declare(strict_types=1);

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
 ********************************************************************
 */

final class ilPDSelectedItemsBlockMembershipsObjectDatabaseRepository implements ilPDSelectedItemsBlockMembershipsObjectRepository
{
    private const VALID_OBJECT_TYPES = [
        'crs',
        'grp',
    ];
    
    /** @var ilDBInterface */
    private $db;
    /** @var int */
    private $recoveryFolderId;

    public function __construct(ilDBInterface $db, int $recoveryFolderId)
    {
        $this->db = $db;
        $this->recoveryFolderId = $recoveryFolderId;
    }

    /**
     * @return string[]
     */
    public function getValidObjectTypes() : array
    {
        return self::VALID_OBJECT_TYPES;
    }

    /**
     * @inheritDoc
     */
    public function getForUser(ilObjUser $user, array $objTypes, string $actorLanguageCode) : Generator
    {
        $objTypes = array_intersect($objTypes, self::VALID_OBJECT_TYPES);
        if ($objTypes === []) {
            return;
        }

        $odObjTypes = ' AND ' . $this->db->in(
            'od.type',
            $objTypes,
            false,
            'text'
        );

        $res = $this->db->queryF(
            "
                SELECT DISTINCT
                    od.obj_id,
                    objr.ref_id,
                    (
                        CASE
                            WHEN (trans.title IS NOT NULL AND trans.title != '')
                            THEN trans.title
                            ELSE od.title
                        END
                    ) title,
                    (
                        CASE
                            WHEN (trans.description IS NOT NULL AND trans.description != '')
                            THEN trans.description
                            ELSE od.description
                        END
                    ) description,
                    od.type,
                    t.parent,
                    tp.lft parent_lft,
                    (
                        CASE
                            WHEN od.type = 'crs' THEN crs_settings.period_start
                            ELSE grp_settings.period_start
                        END
                    ) period_start,
                    (
                        CASE
                            WHEN od.type = 'crs' THEN crs_settings.period_end
                            ELSE grp_settings.period_end
                        END
                    ) period_end,
                    (
                        CASE
                            WHEN od.type = 'crs' THEN crs_settings.period_time_indication
                            ELSE grp_settings.period_time_indication
                        END
                    ) period_has_time            
                FROM rbac_ua ua
                INNER JOIN rbac_fa fa ON fa.rol_id = ua.rol_id AND fa.assign = %s
                INNER JOIN object_reference objr ON objr.ref_id = fa.parent
                INNER JOIN object_data od ON od.obj_id = objr.obj_id $odObjTypes
                INNER JOIN tree t ON t.child = objr.ref_id AND t.tree = %s AND t.parent != %s
                INNER JOIN tree tp ON tp.child = t.parent
                LEFT JOIN grp_settings ON grp_settings.obj_id = od.obj_id
                LEFT JOIN crs_settings ON crs_settings.obj_id = od.obj_id
                LEFT JOIN object_translation trans ON trans.obj_id = od.obj_id AND trans.lang_code = %s
                WHERE ua.usr_id = %s
            ",
            ['text', 'integer', 'integer', 'text', 'integer'],
            ['y', 1, $this->recoveryFolderId, $actorLanguageCode, $user->getId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $periodStart = null;
            if (!is_null($row['period_start'])) {
                $periodStart = new DateTimeImmutable($row['period_start'], new DateTimeZone('UTC'));
            }
            $periodEnd = null;
            if (!is_null($row['period_end'])) {
                $periodEnd = new DateTimeImmutable($row['period_end'], new DateTimeZone('UTC'));
            }

            yield new ilPDSelectedItemBlockMembershipsDTO(
                (int) $row['ref_id'],
                (int) $row['obj_id'],
                (string) $row['type'],
                (string) $row['title'],
                (string) $row['description'],
                (int) $row['parent'],
                (int) $row['parent_lft'],
                (bool) $row['period_has_time'],
                $periodStart,
                $periodEnd
            );
        }
    }
}
