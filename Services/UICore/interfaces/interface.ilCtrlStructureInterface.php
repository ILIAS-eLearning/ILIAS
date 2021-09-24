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
    public const KEY_CLASS_CID       = 'cid';
    public const KEY_CLASS_PATH      = 'class_path';
    public const KEY_CLASS_NAME      = 'class_name';
    public const KEY_CALLING_CLASSES = 'calling_classes';
    public const KEY_CALLED_CLASSES  = 'called_classes';

    /**
     * Returns whether the given class is registered as a valid
     * baseclass (module or service class) in the database.
     *
     * @param string $class_name
     * @return bool
     */
    public function isBaseClass(string $class_name) : bool;

    /**
     * Returns the qualified object name of a given class,
     * which can be used to instantiate the object.
     *
     * @param string $class_name
     * @return string|null
     */
    public function getObjectNameByClass(string $class_name) : ?string;

    /**
     * Returns the qualified object name of a class for the given CID,
     * which can be used to instantiate the object.
     *
     * @param string $cid
     * @return string|null
     */
    public function getObjectNameByCid(string $cid) : ?string;

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
     * Returns the absolute path of a class for the given name.
     *
     * @param string $class_name
     * @return string|null
     */
    public function getClassPathByName(string $class_name) : ?string;

    /**
     * Returns the absolute path of a class for the given CID.
     *
     * @param string $cid
     * @return string|null
     */
    public function getClassPathByCid(string $cid) : ?string;

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
    public function saveParameterByClass(string $class_name, string $parameter_name) : void;

    /**
     * Removes all permanent parameters for the given class.
     *
     * @param string $class_name
     */
    public function removeSavedParametersByClass(string $class_name) : void;

    /**
     * Returns all permanent parameters for the given class.
     *
     * @param string $class_name
     * @return array|null
     */
    public function getSavedParametersByClass(string $class_name) : ?array;

    /**
     * Sets a parameter => value pair for the given class which will be appended
     * for the next request.
     *
     * @param string $class_name
     * @param string $parameter_name
     * @param mixed  $value
     * @throws ilCtrlException if an invalid parameter name is provided.
     */
    public function setParameterByClass(string $class_name, string $parameter_name, mixed $value) : void;

    /**
     * Removes all temporarily set parameter => value pairs for the given class.
     *
     * @param string $class_name
     */
    public function removeParametersByClass(string $class_name) : void;

    /**
     * Returns all parameters currently set for a given class.
     *
     * @param string $class_name
     * @return array|null
     */
    public function getParametersByClass(string $class_name) : ?array;

    /**
     * Removes a specific permanent or temporary parameter for the given class.
     *
     * @param string $class_name
     * @param string $parameter_name
     */
    public function removeSingleParameterByClass(string $class_name, string $parameter_name) : void;
}