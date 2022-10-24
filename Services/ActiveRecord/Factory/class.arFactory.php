<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arFactory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arFactory
{
    /**
     * @param   mixed    $primary_key
     * @throws arException
     */
    public static function getInstance(string $class_name, $primary_key = 0, array $additional_arguments = array()): \ActiveRecord
    {
        $ref = new ReflectionClass($class_name);
        if ($ref->isInstantiable()) {
            /** @var ActiveRecord $obj */
            $obj = $ref->newInstanceArgs(array_merge(array($primary_key), $additional_arguments));
            if (empty($primary_key)) {
                $obj = clone($obj);
            }
        } else {
            throw new arException(arException::PRIVATE_CONTRUCTOR);
        }

        return $obj;
    }
}
