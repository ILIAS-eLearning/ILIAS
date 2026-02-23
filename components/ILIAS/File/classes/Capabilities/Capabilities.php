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

enum Capabilities: string
{
    case EDIT_EXTERNAL = 'editExternal';
    case INFO_PAGE = 'showSummary';
    case FORCED_INFO_PAGE = 'showSummaryForced';
    case MANAGE_VERSIONS = 'versions';
    case EDIT_SETTINGS = 'edit';
    case UNZIP = 'unzipCurrentRevision';
    case DOWNLOAD = 'sendfile';
    case VIEW_EXTERNAL = 'viewExternal';
    case NONE = 'none';

    public static function fromName(string $name): Capabilities
    {
        foreach (self::cases() as $case) {
            if ($name === $case->name) {
                return $case;
            }
        }
        return self::NONE;
    }

    public static function fromCommand(string $cmd): Capabilities
    {
        foreach (self::cases() as $case) {
            if ($cmd === $case->value) {
                return $case;
            }
        }
        return self::NONE;
    }

}
