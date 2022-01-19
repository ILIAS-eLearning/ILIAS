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
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilWACSecurePath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACSecurePath extends ActiveRecord
{

    /**
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName() : string
    {
        return 'il_wac_secure_path';
    }

    /**
     * Searches a checking instance for the given wac path. If a checking instance is found, wac will try to create a instance of the found checker.
     * The path concatenation pattern for the inclusion is {ComponentDirectory}/classes/class.{CheckingClass}.php. Furthermore the included
     * class must implement the ilWACCeckingClass interface.
     *
     * @param ilWACPath $ilWACPath The wac path which should be used to search a checking instance.
     *
     * @return ilWACCheckingClass The newly created checking instance.
     *
     * @throws ilWACException Thrown if the the checking instance is not found or if the concatenated path is not valid to the checking instance.
     */
    public static function getCheckingInstance(ilWACPath $ilWACPath) : ilWACCheckingClass
    {
        /**
         * @var $obj ilWACSecurePath
         */
        $obj = self::find($ilWACPath->getModuleType());
        if ($obj === null) {
            throw new ilWACException(
                ilWACException::CODE_NO_PATH,
                'No Checking Instance found for id: ' . $ilWACPath->getSecurePathId()
            );
        }

        $secure_path_checking_class = $obj->getComponentDirectory() . '/classes/class.' . $obj->getCheckingClass() . '.php';
        if (!file_exists($secure_path_checking_class)) {
            throw new ilWACException(
                ilWACException::CODE_NO_PATH,
                'Checking Instance not found in path: ' . $secure_path_checking_class
            );
        }
        $class_name = $obj->getCheckingClass();

        return new $class_name();
    }

    /**
     * Searches a checking instance for the given wac path.
     *
     * @param ilWACPath $ilWACPath The wac path which should be used to search the checking instance.
     *
     * @return bool true if a checking instance is found otherwise false.
     */
    public static function hasCheckingInstanceRegistered(ilWACPath $ilWACPath) : bool
    {
        $obj = self::find($ilWACPath->getModuleType());
        return !is_null($obj);
    }

    public function hasCheckingInstance() : bool
    {
        return $this->has_checking_instance;
    }

    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     64
     */
    protected ?string $path = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected string $component_directory = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected string $checking_class = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $in_sec_folder = false;
    protected bool $has_checking_instance = false;

    public function getPath() : string
    {
        return (string) $this->path;
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function getComponentDirectory() : string
    {
        preg_match("/[\\\|\\/](Services|Modules|Customizing)[\\\|\\/].*/u", $this->component_directory, $matches);

        return '.' . $matches[0];
    }

    public function setComponentDirectory(string $component_directory) : void
    {
        $this->component_directory = $component_directory;
    }

    public function getCheckingClass() : string
    {
        return $this->checking_class;
    }

    public function setCheckingClass(string $checking_class) : void
    {
        $this->checking_class = $checking_class;
    }

    public function setHasCheckingInstance(bool $has_checking_instance) : void
    {
        $this->has_checking_instance = $has_checking_instance;
    }

    public function setInSecFolder(bool $in_sec_folder) : void
    {
        $this->in_sec_folder = $in_sec_folder;
    }
}
