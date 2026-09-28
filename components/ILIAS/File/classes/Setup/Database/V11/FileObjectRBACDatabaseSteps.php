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

namespace ILIAS\File\Setup\Database\V11;

use ilDatabaseUpdateSteps;
use ilDBInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FileObjectRBACDatabaseSteps implements ilDatabaseUpdateSteps
{
    private const string OBSOLETE_OPERATION = 'view_content';

    private ?ilDBInterface $database = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }

    /**
     * @description remove the obsolete "view_content" operation of older releases
     */
    public function step_1(): void
    {
        $ops_id = $this->database->fetchAssoc(
            $this->database->queryF(
                "SELECT ops_id FROM rbac_operations WHERE operation = %s",
                ['text'],
                [self::OBSOLETE_OPERATION]
            )
        )['ops_id'] ?? null;

        if ($ops_id === null) {
            return;
        }

        // This operation has been superseded by "file_view_content". The permission
        // assignments are stored for that operation, therefore nothing has to be
        // migrated here and the obsolete operation can simply be dropped.
        $this->database->manipulateF(
            'DELETE FROM rbac_ta WHERE ops_id = %s',
            ['integer'],
            [$ops_id]
        );

        $this->database->manipulateF(
            'DELETE FROM rbac_templates WHERE ops_id = %s',
            ['integer'],
            [$ops_id]
        );

        $this->database->manipulateF(
            'DELETE FROM rbac_operations WHERE ops_id = %s',
            ['integer'],
            [$ops_id]
        );
    }
}
