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

namespace ILIAS\Services\Database\Integrity;

use ilDBInterface;

class ilDBIntegrity
{
    public function __construct(
        private ilDBInterface $database
    ) {
    }

    /**
     * Example:
     * $violations = $this->check(new ilDBDefinition([
     *     new ilDBAssociation(new ilDBField('mail', 'folder_id'), new ilDBField('mail', 'folder_id')
     * ])))->violations();
     *
     * Mail example:
     * $mailId = new ilDBField('mail', 'mail_id');
     * $mailObjDataId = new ilDBField('mail_obj_data', 'obj_id');
     *
     * $defintions = [
     *     new ilDBDefinition([new ilDBAssociation(new ilDBField('mail', 'folder_id'), $mailObjDataId)]),
     *     new ilDBDefinition([new ilDBAssociation(new ilDBField('mail_attachment', 'mail_id'), $mailId)]),
     *     new ilDBDefinition([new ilDBAssociation(new ilDBField('mail_cron_orphaned', 'mail_id'), $mailId)]),
     *     new ilDBDefinition([new ilDBAssociation(new ilDBField('mail_cron_orphaned', 'folder_id'), $mailObjDataId)]),
     *     new ilDBDefinition([new ilDBAssociation(new ilDBField('mail_tree', 'child'), $mailObjDataId)]),
     *     new ilDBDefinition([new ilDBAssociation(new ilDBField('mail_tree', 'parent'), new ilDBField('mail_tree', 'child', 'parent'))], new ilDBIgnore(null, '0')),
     * ];
     * $results = array_map([$this, 'check'], $defintions);
     */
    public function check(ilDBDefinition $definition): ilDBResult
    {
        $on = [];
        $where = [];
        // $definition->associations() always returns a non empty array
        foreach ($definition->associations() as $association) {
            $on[] = sprintf('%s = %s', $association->field()->fieldName(), $association->referenceField()->fieldName());
            $where[] = sprintf('%s IS NULL', $association->referenceField()->fieldName());
            foreach ($definition->ignoreValues() as $value_to_ignore) {
                $where[] = sprintf('%s %s', $association->field()->fieldName(), $value_to_ignore);
            }
        }

        $result = $this->database->query(sprintf(
            'SELECT COUNT(1) as violations FROM %s LEFT JOIN %s ON %s WHERE %s',
            $definition->tableName(),
            $definition->referenceTableName(),
            join(' AND ', $on),
            join(' AND ', $where),
        ));

        $result = $this->database->fetchAssoc($result);

        return new ilDBResult((int) $result['violations']);
    }
}