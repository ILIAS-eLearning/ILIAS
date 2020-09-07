<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class arConfig
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConfig extends ActiveRecord
{

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return 'ar_demo_config';
    }


    /**
     * @var array
     */
    protected static $cache = array();
    /**
     * @var array
     */
    protected static $cache_loaded = array();


    /**
     * @param $name
     *
     * @return string
     */
    public static function get($name)
    {
        if (!self::$cache_loaded[$name]) {
            $obj = new self($name);
            self::$cache[$name] = $obj->getValue();
            self::$cache_loaded[$name] = true;
        }

        return self::$cache[$name];
    }


    /**
     * @param $name
     * @param $value
     */
    public static function set($name, $value)
    {
        $obj = new self($name);
        $obj->setValue($value);
        if (self::where(array( 'name' => $name ))->hasSets()) {
            $obj->update();
        } else {
            $obj->create();
        }
    }


    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           250
     */
    protected $name;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           1000
     */
    protected $value;


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
