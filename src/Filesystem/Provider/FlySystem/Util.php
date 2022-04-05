<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * @internal
 * @description This is an override of the Class \League\Flysystem\Util. normalizeRelativePath in the originl class
 * replaces all backslashes \ with a slash /. This leads to problems with files which have a backslash in its title
 */
class Util extends \League\Flysystem\Util
{

    /**
     * @param string $path
     */
    public static function normalizeRelativePath($path) : string
    {
        $path = preg_replace("#\\\\(?!['\\\])#m", '/', $path); // this only replaces backslashes
        $path = static::removeFunkyWhiteSpace($path);
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
