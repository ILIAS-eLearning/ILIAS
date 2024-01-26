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
namespace ILIAS\GlobalScreen;

use ReflectionClass;

/**
 * Class SingletonTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait SingletonTrait
{
    /**
     * @var mixed[]
     */
    private static $services = [];

    private function get(string $class_name) : object
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name();
        }

        return self::$services[$class_name];
    }

    private function getWithArgument(string $class_name, $argument) : object
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name($argument);
        }

        return self::$services[$class_name];
    }

    private function getWithMultipleArguments(string $class_name, array $arguments) : object
    {
        if (!$this->has($class_name)) {
            $i = new ReflectionClass($class_name);

            self::$services[$class_name] = $i->newInstanceArgs($arguments);
        }

        return self::$services[$class_name];
    }

    private function has(string $class_name) : bool
    {
        return isset(self::$services[$class_name]);
    }
}
