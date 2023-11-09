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

namespace ILIAS\Filesystem\Provider\FlySystem;

/**
 * @internal
 * @description This is an override of the Class \League\Flysystem\Util. normalizeRelativePath in the originl class
 * replaces all backslashes \ with a slash /. This leads to problems with files which have a backslash in its title
 */
class Util // extends \League\Flysystem\Util
{
    public static function normalizeRelativePath(string $path): string
    {
        $path = preg_replace("#\\\\(?!['\\\])#m", '/', $path); // this only replaces backslashes
        $path = preg_replace('#\p{C}+#u', '', $path);
        $parts = [];

        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;

                case '..':
                    if (empty($parts)) {
                        throw new \LogicException(
                            'Path is outside of the defined root, path: [' . $path . ']'
                        );
                    }
                    array_pop($parts);
                    break;

                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }
}
