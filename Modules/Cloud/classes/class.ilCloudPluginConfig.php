<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Cloud/exceptions/class.ilCloudPluginConfigException.php');

/**
 * Class ilCloudPluginConfig
 *
 * Model class for the administration settings. Note the use of the __call Function. The value max_file_size could be
 * for example set by the method setMaxFileSize without the declaring this method. Similarly it could be get by
 * getMaxFileSize
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 */
class ilCloudPluginConfig
{
    /**
     * @var string
     */
    protected $table_name = "";

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param $table_name
     */
    public function __construct($table_name)
    {
        $this->table_name = $table_name;
    }

    /**
     * @param string $table_name
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param $method
     * @param $params
     * @return bool|null
    */
    public function __call($method, $params)
    {
        $index = substr($method, 3);
        if (substr($method, 0, 3) == 'get') {
            if (!isset($this->cache[$index])) {
                $this->cache[$index] = $this->getValue(self::_fromCamelCase(substr($method, 3)));
            }
            if ($this->cache[$index] == null) {
                $this->cache[$index] = false;
            }

            return $this->cache[$index];
        } elseif (substr($method, 0, 3) == 'set') {
            $this->cache[$index] = $params[0];
            $this->setValue(self::_fromCamelCase(substr($method, 3)), $params[0]);
            return true;
        } else {
            throw new ilCloudPluginConfigException(ilCloudPluginConfigException::NO_VALID_GET_OR_SET_FUNCTION, $method);
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function setValue($key, $value)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$ilDB->tableExists($this->table_name)) {
            throw new ilCloudPluginConfigException(ilCloudPluginConfigException::TABLE_DOES_NOT_EXIST, $this->table_name);
        }

        if (!is_string($this->getValue($key))) {
            $ilDB->insert($this->table_name, array("config_key" => array("text", $key), "config_value" => array("text", $value)));
        } else {
            $ilDB->update($this->table_name, array("config_key" => array("text", $key), "config_value" => array("text", $value)), array("config_key" => array("text", $key)));
        }
    }

    /**
     * @param $key
     * @return bool|string
     */
    public function getValue($key)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];


        if (!$this->tableExists($this->table_name)) {
            throw new ilCloudPluginConfigException(ilCloudPluginConfigException::TABLE_DOES_NOT_EXIST, $this->table_name);
        }

        $result = $ilDB->query("SELECT config_value FROM " . $this->table_name . " WHERE config_key = " . $ilDB->quote($key, "text"));

        if ($result->numRows() == 0) {
            return false;
        }
        $record = $ilDB->fetchAssoc($result);
        return (string) $record['config_value'];
    }



    /**
     * @return bool
     */
    public function initDB()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$ilDB->tableExists($this->getTableName())) {
            $fields = array(
                'config_key'   => array(
                    'type'    => 'text',
                    'length'  => 128,
                    'notnull' => true),
                'config_value' => array(
                    'type'    => 'clob',
                    'notnull' => false),);
            $ilDB->createTable($this->getTableName(), $fields);
            $ilDB->addPrimaryKey($this->getTableName(), array("config_key"));
        }

        return true;
    }


    //
    // Helper
    //


    /**
     * @param string $str
     * @return string
     */
    public static function _fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        return preg_replace_callback('/([A-Z])/', function ($c) {
            return "_" . strtolower($c[1]);
        }, $str);
    }

    /**
     * @param string $str
     * @param bool $capitalise_first_char
     * @return string
     */
    public static function _toCamelCase($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        return preg_replace_callback('/-([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, $str);
    }

    public function tableExists()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query("show tables like '" . $this->getTableName() . "'");

        if ($result->numRows() == 0) {
            return false;
        } else {
            return true;
        }
    }
}
