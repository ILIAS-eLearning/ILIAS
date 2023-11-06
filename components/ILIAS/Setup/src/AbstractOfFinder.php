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

namespace ILIAS\Setup;

/**
 * Class ImplementationOfInterfaceFinder
 *
 * @package ILIAS\ArtifactBuilder\Generators
 */
abstract class AbstractOfFinder
{
    protected string $root;

    /**
     * @var string[]
     */
    protected array $ignore = [
        '.*/components/ILIAS/Setup/',
        '.*/components/ILIAS/GlobalScreen/',
        '.*/Customizing/.*/src/',
        '.*/vendor/',
        '.*/test/',
        '.*/setup/',
        // Classes using removed Auth-class from PEAR
        '.*ilSOAPAuth.*',
        // Classes using unknown
        '.*ilPDExternalFeedBlockGUI.*',
    ];

    /**
     * @var string[]|null
     */
    protected ?array $classmap = null;

    public function __construct()
    {
        $this->root = substr(__FILE__, 0, strpos(__FILE__, DIRECTORY_SEPARATOR . "src"));
        $external_classmap = include "./vendor/composer/vendor/composer/autoload_classmap.php";
        $this->classmap = $external_classmap ?: null;
    }

    /**
     * The matcher finds the class names implementing the given interface, while
     * ignoring paths in self::$ignore and and the additional patterns provided.
     *
     * Patterns are regexps (without delimiters) to define complete paths on the
     * filesystem to be ignored or selected.
     *
     * @param callable $is_matching which takes a \ReflectionClass as argument and
     *                              returns a bool if the class is matching your
     *                              criteria.
     * @param   string[] $additional_ignore
     * @param   string|null $matching_path
     */
    protected function genericGetMatchingClassNames(
        callable $is_matching,
        array $additional_ignore = [],
        string $matching_path = null
    ): \Iterator {
        foreach ($this->getAllClassNames($additional_ignore, $matching_path) as $class_name) {
            try {
                $reflection_class = new \ReflectionClass($class_name);
                if ($is_matching($reflection_class)) {
                    yield $class_name;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * @param   string[] $additional_ignore
     */
    protected function getAllClassNames(array $additional_ignore, string $matching_path = null): \Iterator
    {
        $ignore = array_merge($this->ignore, $additional_ignore);

        if (!is_array($this->classmap)) {
            throw new \LogicException("Composer ClassMap not loaded");
        }

        $regexp = implode(
            "|",
            array_map(
                // fix path-separators to respect windows' backspaces.
                fn($v): string => "(" . str_replace('/', '(/|\\\\)', $v) . ")",
                $ignore
            )
        );
        if ($matching_path) {
            $matching_path = str_replace('/', '(/|\\\\)', $matching_path);
        }


        foreach ($this->classmap as $class_name => $file_path) {
            $real_path = realpath($file_path);
            if ($real_path === false) {
                throw new \RuntimeException(
                    "Could not find file for class $class_name (path: $file_path). " .
                    "Please check the composer classmap, maybe it is outdated. " .
                    "You can regenerate it by executing 'composer du' or 'composer install' " .
                    "(which also ensures dependencies are correctly installed) in the ILIAS root directory."
                );
            }

            $path = str_replace($this->root, "", $real_path);
            if ($matching_path && !preg_match("#^" . $matching_path . "$#", $path)) {
                continue;
            }
            if (!preg_match("#^" . $regexp . "$#", $path)) {
                yield $class_name;
            }
        }
    }
}
