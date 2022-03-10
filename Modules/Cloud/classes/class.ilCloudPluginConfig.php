<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/Cloud/exceptions/class.ilCloudPluginConfigException.php');

/**
 * Class ilCloudPluginConfig
 * Model class for the administration settings. Note the use of the __call Function. The value max_file_size could be
 * for example set by the method setMaxFileSize without the declaring this method. Similarly it could be get by
 * getMaxFileSize
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 */
class ilCloudPluginConfig
{
    protected string $table_name = "";
    protected array $cache = array();

    public function __construct(string $table_name)
    {
        $this->table_name = $table_name;
    }

    public function setTableName(string $table_name) : void
    {
        $this->table_name = $table_name;
    }

    public function getTableName() : string
    {
        return $this->table_name;
    }

    public function __call(string $method, array $params) : ?bool
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
        } else {
            if (substr($method, 0, 3) == 'set') {
                $this->cache[$index] = $params[0];
                $this->setValue(self::_fromCamelCase(substr($method, 3)), $params[0]);

                return true;
            } else {
                throw new ilCloudPluginConfigException(ilCloudPluginConfigException::NO_VALID_GET_OR_SET_FUNCTION,
                    $method);
            }
        }
    }

    public function setValue(string $key, string $value) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$ilDB->tableExists($this->table_name)) {
            throw new ilCloudPluginConfigException(ilCloudPluginConfigException::TABLE_DOES_NOT_EXIST,
                $this->table_name);
        }

        if ($this->getValue($key) === false) {
            $ilDB->insert($this->table_name,
                array("config_key" => array("text", $key), "config_value" => array("text", $value)));
        } else {
            $ilDB->update($this->table_name,
                array("config_key" => array("text", $key), "config_value" => array("text", $value)),
                array("config_key" => array("text", $key)));
        }
    }

    public function getValue(string $key) : bool|string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$this->tableExists($this->table_name)) {
            throw new ilCloudPluginConfigException(ilCloudPluginConfigException::TABLE_DOES_NOT_EXIST,
                $this->table_name);
        }

        $result = $ilDB->query("SELECT config_value FROM " . $this->table_name . " WHERE config_key = " . $ilDB->quote($key,
                "text"));

        if ($result->numRows() == 0) {
            return false;
        }
        $record = $ilDB->fetchAssoc($result);

        return (string) $record['config_value'];
    }

    public function initDB() : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$ilDB->tableExists($this->getTableName())) {
            $fields = array(
                'config_key' => array(
                    'type' => 'text',
                    'length' => 128,
                    'notnull' => true,
                ),
                'config_value' => array(
                    'type' => 'clob',
                    'notnull' => false,
                ),
            );
            $ilDB->createTable($this->getTableName(), $fields);
            $ilDB->addPrimaryKey($this->getTableName(), array("config_key"));
        }

        return true;
    }


    //
    // Helper
    //
    public static function _fromCamelCase(string $str) : string
    {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback('/([A-Z])/', function ($c) {
            return "_" . strtolower($c[1]);
        }, $str);
    }

    public static function _toCamelCase(string $str, bool $capitalise_first_char = false) : string
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback('/-([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, $str);
    }

    public function tableExists() : bool
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
