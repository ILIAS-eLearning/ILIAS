<?php namespace ILIAS\ArtifactBuilder\Generators;

use Closure;
use Iterator;
use SplFileInfo;
use Throwable;

/**
 * Class GeneratorFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class GeneratorFactory
{

    /**
     * @param string $pattern
     *
     * @return Closure
     */
    public function splFileInfosByRegexPattern(string $pattern) : Closure
    {
        return function (Iterator $iterator) use ($pattern): Iterator {
            /**
             * @var $file SplFileInfo
             */
            foreach ($iterator as $file) {
                if (!$file->isDir() && preg_match($pattern, $file->getFilename())) {
                    yield $file;
                }
            }
        };
    }


    /**
     * @param string $file_ending
     *
     * @return Closure
     */
    public function splFileInfoByFileEnding(string $file_ending) : Closure
    {
        return function (Iterator $iterator) use ($file_ending): Iterator {
            foreach ($iterator as $file) {
                if (strtolower($file->getExtension()) === $file_ending || $file->isDir() && !in_array($file->getFilename(), [".", ".."])) {
                    yield $file;
                }
            }
        };
    }


    /**
     * @param string $interface
     *
     * @return Closure
     */
    public function classNamesWhichImplementInterface(string $interface) : Closure
    {
        return function (Iterator $generator) use ($interface): Iterator {
            foreach ($this->classNamesOfSplFiles()($generator) as $class_name) {
                try {
                    if (in_array($interface, class_implements($class_name))) {
                        yield $class_name;
                    }
                } catch (Throwable $e) {
                    // noting to do here
                }
            }
        };
    }


    /**
     * @return Closure
     */
    public function classNamesOfSplFiles() : Closure
    {
        return function (Iterator $generator) : Iterator {
            foreach ($generator as $file) {
                if (preg_match('/class\.(il.+)\.php$/i', $file->getFileName(), $matches)) {
                    yield $matches[1];
                }
            }
        };
    }
}
