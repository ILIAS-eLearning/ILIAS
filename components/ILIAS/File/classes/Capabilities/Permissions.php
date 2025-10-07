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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */

namespace ILIAS\File\Capabilities;

enum Permissions: string
{
    case NONE = 'none';
    case VISIBLE = 'visible';
    case READ = 'read';
    case VIEW_CONTENT = 'view_content';
    case READ_LP = 'read_learning_progress';
    case EDIT_LP = 'edit_learning_progress';
    case EDIT_PERMISSIONS = 'edit_permission';
    case WRITE = 'write';
    case DELETE = 'delete';
    case COPY = 'copy';
    case EDIT_CONTENT = 'edit_file';

    public static function ANY(): array
    {
        return [
            self::VISIBLE,
            self::READ,
            self::VIEW_CONTENT,
            self::READ_LP,
            self::EDIT_LP,
            self::EDIT_PERMISSIONS,
            self::WRITE,
            self::DELETE,
            self::COPY,
            self::EDIT_CONTENT
        ];
    }

}
