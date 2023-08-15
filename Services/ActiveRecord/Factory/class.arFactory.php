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
 * Class arFactory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arFactory
{
    /**
     * @throws arException
     */
    public static function getInstance(
        string $class_name,
        mixed $primary_key = 0,
        array $additional_arguments = []
    ): \ActiveRecord {
        $reflectionClass = new ReflectionClass($class_name);
        if ($reflectionClass->isInstantiable()) {
            /** @var ActiveRecord $obj */
            $obj = $reflectionClass->newInstanceArgs(array_merge([$primary_key], $additional_arguments));
            if (empty($primary_key)) {
                $obj = clone($obj);
            }
        } else {
            throw new arException(arException::PRIVATE_CONTRUCTOR);
        }

        return $obj;
    }
}
