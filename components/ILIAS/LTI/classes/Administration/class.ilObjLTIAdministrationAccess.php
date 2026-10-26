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
 * Access class of the LTI administration node.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIAdministrationAccess extends ilObjectAccess
{
    /**
     * True when the user may define tools of their own, which is what allows creating an LTI object
     * for a tool that is not released for everybody.
     */
    public static function hasOwnToolCreationAccess(): bool
    {
        global $DIC;

        $ref_id = self::lookupRefId();

        return $ref_id !== null && $DIC->rbac()->system()->checkAccess('add_consume_provider', $ref_id);
    }

    /**
     * The reference of the administration node, which is the only one of its type.
     */
    public static function lookupRefId(): ?int
    {
        global $DIC;

        $db = $DIC->database();
        $result = $db->queryF(
            'SELECT r.ref_id FROM object_reference r'
            . ' JOIN tree t ON t.child = r.ref_id'
            . ' JOIN object_data d ON d.obj_id = r.obj_id'
            . ' WHERE t.parent = %s AND d.type = %s',
            ['integer', 'text'],
            [SYSTEM_FOLDER_ID, 'ltis']
        );
        $row = $db->fetchAssoc($result);

        return $row === null ? null : (int) $row['ref_id'];
    }
}
