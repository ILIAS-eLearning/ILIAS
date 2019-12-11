<?php

namespace ILIAS\BackgroundTasks\Types;

/**
 * Class VoidType
 *
 * @package ILIAS\Types
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * Void Type and Singleton for the void type.
 *
 */
class VoidType implements Type
{
    protected static $instance = null;


    /**
     * Just to make it protected.
     * VoidValue constructor.
     */
    protected function __construct()
    {
    }


    /**
     * @return VoidType
     */
    public static function instance()
    {
        if (!self::instance()) {
            self::$instance = new VoidType();
        }

        return self::$instance;
    }


    /**
     * @return string A string representation of the Type.
     */
    public function __toString()
    {
        return "Void";
    }


    /**
     * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
     *
     * @param $type Type
     *
     * @return bool
     */
    public function isExtensionOf(Type $type)
    {
        return $type instanceof VoidType;
    }


    /**
     * returns true if the two types are equal.
     *
     * @param Type $otherType
     *
     * @return bool
     */
    public function equals(Type $otherType)
    {
        return $otherType instanceof VoidType;
    }
}
