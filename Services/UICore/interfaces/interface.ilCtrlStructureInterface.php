<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

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
    public const KEY_CLASS_CID = 'cid';
    public const KEY_CLASS_PATH = 'class_path';
    public const KEY_CLASS_NAME = 'class_name';
    public const KEY_CLASS_CID_PATHS = 'cid_paths';
    public const KEY_CLASS_PARENTS = 'parents';
    public const KEY_CLASS_CHILDREN = 'children';
    public const KEY_UNSAFE_COMMANDS = 'unsafe_commands';
    public const KEY_SAFE_COMMANDS = 'safe_commands';

    /**
     * Returns whether the given class is registered as a valid
     * baseclass (module or service class) in the database.
     *
     * @param string $class_name
     * @return bool
     */
    public function isBaseClass(string $class_name) : bool;

    /**
     * Returns the qualified object name of a class for the given CID,
     * which can be used to instantiate the object.
     *
     * @param string $cid
     * @return string|null
     */
    public function getObjNameByCid(string $cid) : ?string;

    /**
     * Returns the qualified object name of a given class,
     * which can be used to instantiate the object.
     *
     * @param string $class_name
     * @return string|null
     */
    public function getObjNameByName(string $class_name) : ?string;

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
    public function getRelativePathByName(string $class_name) : ?string;

    /**
     * Returns the absolute path of a class for the given CID.
     *
     * @param string $cid
     * @return string|null
     */
    public function getRelativePathByCid(string $cid) : ?string;

    /**
     * Returns all classes that can be called by a class for the given CID.
     *
     * @param string $cid
     * @return array
     */
    public function getChildrenByCid(string $cid) : ?array;

    /**
     * Returns all classes that can be called by the given class.
     *
     * @param string $class_name
     * @return array
     */
    public function getChildrenByName(string $class_name) : ?array;

    /**
     * Returns all classes that can call a class for the given CID.
     *
     * @param string $cid
     * @return array
     */
    public function getParentsByCid(string $cid) : ?array;

    /**
     * Returns all classes that can call the given class.
     *
     * @param string $class_name
     * @return array
     */
    public function getParentsByName(string $class_name) : ?array;

    /**
     * Saves a parameter for the given class, that should be fetched with
     * every request including it.
     *
     * @param string $class_name
     * @param string $parameter_name
     * @throws ilCtrlException if an invalid parameter name is provided.
     */
    public function setPermanentParameterByClass(string $class_name, string $parameter_name) : void;

    /**
     * Removes all permanent parameters for the given class.
     *
     * @param string $class_name
     */
    public function removePermanentParametersByClass(string $class_name) : void;

    /**
     * Returns all permanent parameters for the given class.
     *
     * @param string $class_name
     * @return array|null
     */
    public function getPermanentParametersByClass(string $class_name) : ?array;

    /**
     * Sets a parameter => value pair for the given class which will be appended
     * for the next request.
     *
     * @param string $class_name
     * @param string $parameter_name
     * @param mixed  $value
     * @throws ilCtrlException if an invalid parameter name is provided.
     */
    public function setTemporaryParameterByClass(string $class_name, string $parameter_name, $value) : void;

    /**
     * Removes all temporarily set parameter => value pairs for the given class.
     *
     * @param string $class_name
     */
    public function removeTemporaryParametersByClass(string $class_name) : void;

    /**
     * Returns all temporary parameters currently set for a given class.
     *
     * @param string $class_name
     * @return array|null
     */
    public function getTemporaryParametersByClass(string $class_name) : ?array;

    /**
     * Removes a specific permanent or temporary parameter for the given class.
     *
     * @param string $class_name
     * @param string $parameter_name
     */
    public function removeSingleParameterByClass(string $class_name, string $parameter_name) : void;

    /**
     * Sets a target URL for the given class in order to reach it.
     *
     * @param string $class_name
     * @param string $target_url
     */
    public function setReturnTargetByClass(string $class_name, string $target_url) : void;

    /**
     * Returns a target URL for the given class in order to reach it.
     *
     * @param string $class_name
     * @return string|null
     */
    public function getReturnTargetByClass(string $class_name) : ?string;

    /**
     * Returns all unsafe GET commands for a given cid.
     *
     * @param string $cid
     * @return array
     */
    public function getUnsafeCommandsByCid(string $cid) : array;

    /**
     * Returns all unsafe GET commands for a given classname.
     *
     * @param string $class_name
     * @return array
     */
    public function getUnsafeCommandsByName(string $class_name) : array;

    /**
     * Returns all safe POST commands for a given cid.
     *
     * @param string $cid
     * @return array
     */
    public function getSafeCommandsByCid(string $cid) : array;

    /**
     * Returns all safe POST commands for a given classname.
     *
     * @param string $class_name
     * @return array
     */
    public function getSafeCommandsByName(string $class_name) : array;
}
