<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

/**
 * @internal
 * @description This is an override of the Class \League\Flysystem\Util. normalizeRelativePath in the originl class
 * replaces all backslashes \ with a slash /. This leads to problems with files which have a backslash in its title
 */
class Util extends \League\Flysystem\Util
{

    /**
     * @param string $path
     * @return string
     */
    public static function normalizeRelativePath($path)
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

        $path = implode('/', $parts);

        return $path;
    }

}
