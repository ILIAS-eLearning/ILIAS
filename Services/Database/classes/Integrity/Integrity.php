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

namespace ILIAS\Services\Database\Integrity;

use ilDBInterface;

class Integrity
{
    public function __construct(
        private ilDBInterface $database
    ) {
    }

    /**
     * Example:
     * $violations = $this->check(new Definition([
     *     new Association(new Field('mail', 'folder_id'), new Field('mail', 'folder_id')
     * ])))->violations();
     * Mail example:
     * $mailId = new Field('mail', 'mail_id');
     * $mailObjDataId = new Field('mail_obj_data', 'obj_id');
     * $defintions = [
     *     new Definition([new Association(new Field('mail', 'folder_id'), $mailObjDataId)]),
     *     new Definition([new Association(new Field('mail_attachment', 'mail_id'), $mailId)]),
     *     new Definition([new Association(new Field('mail_cron_orphaned', 'mail_id'), $mailId)]),
     *     new Definition([new Association(new Field('mail_cron_orphaned', 'folder_id'), $mailObjDataId)]),
     *     new Definition([new Association(new Field('mail_tree', 'child'), $mailObjDataId)]),
     *     new Definition([new Association(new Field('mail_tree', 'parent'), new Field('mail_tree', 'child', 'parent'))], new Ignore(null, '0')),
     * ];
     * $results = array_map([$this, 'check'], $defintions);
     */
    public function check(Definition $definition): Result
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
            implode(' AND ', $on),
            implode(' AND ', $where),
        ));

        $result = $this->database->fetchAssoc($result);

        return new Result((int) $result['violations']);
    }
}
