<?php

use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * ilCtrlStructureInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlStructureInterface
{
    /**
     * array key constants that are used for certain information.
     */
    public const KEY_CLASS_PATH      = 'absolute_path';
    public const KEY_CLASS_NAME      = 'class_name';
    public const KEY_CLASS_CID       = 'cid';
    public const KEY_CALLING_CLASSES = 'called_by';
    public const KEY_CALLED_CLASSES  = 'calls';

    /**
     * Returns the qualified object name of a given class,
     * which can be used to instantiate the object.
     *
     * @param string $class_name
     * @return string|null
     */
    public function getQualifiedClassName(string $class_name) : ?string;

    /**
     * Returns the lower-cased name of a class for the given CID.
     *
     * @param string $cid
     * @return string|null
     */
    public function getClassNameByCid(string $cid) : ?string;

    /**
     * Returns the CID of the given classname.
     *
     * @param string $class_name
     * @return string|null
     */
    public function getClassCidByName(string $class_name) : ?string;

    /**
     * Returns all classes that can be called by a class for the given CID.
     *
     * @param string $cid
     * @return array
     */
    public function getCalledClassesByCid(string $cid) : array;

    /**
     * Returns all classes that can be called by the given class.
     *
     * @param string $class_name
     * @return array
     */
    public function getCalledClassesByName(string $class_name) : array;

    /**
     * Returns all classes that can call a class for the given CID.
     *
     * @param string $cid
     * @return array
     */
    public function getCallingClassesByCid(string $cid) : array;

    /**
     * Returns all classes that can call the given class.
     *
     * @param string $class_name
     * @return array
     */
    public function getCallingClassesByName(string $class_name) : array;

    /**
     * Saves a parameter for the given class, that should be fetched with
     * every request including it.
     *
     * @param string $class_name
     * @param string $parameter_name
     * @throws ilCtrlException if an invalid parameter name is provided.
     */
    public function saveParameterForClass(string $class_name, string $parameter_name) : void;

    /**
     * Sets a parameter => value pair for the given class which will be appended
     * for the next request.
     *
     * @param string $class_name
     * @param string $parameter_name
     * @param mixed  $value
     * @throws ilCtrlException if an invalid parameter name is provided.
     */
    public function setParameterForClass(string $class_name, string $parameter_name, mixed $value) : void;

    /**
     * Returns all parameters currently set for a given class.
     *
     * @param string $class_name
     * @return array
     */
    public function getParametersByClass(string $class_name) : array;
}