<?php
require_once('class.ActiveRecordList.php');
require_once('Connector/class.arConnector.php');
require_once('Connector/class.arConnectorDB.php');
require_once('Cache/class.arObjectCache.php');
require_once('Fields/class.arFieldList.php');
require_once('Cache/class.arFieldCache.php');
require_once('Storage/int.arStorageInterface.php');
require_once('Factory/class.arFactory.php');
require_once('Cache/class.arCalledClassCache.php');
require_once('Connector/class.arConnectorMap.php');

/**
 * Class ActiveRecord
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @experimental
 * @description
 *
 * @version 2.0.7
 *
 */
abstract class ActiveRecord implements arStorageInterface
{
    const ACTIVE_RECORD_VERSION = '2.0.7';
    /**
     * @var bool
     */
    protected $ar_safe_read = true;
    /**
     * @var string
     */
    protected $connector_container_name = '';


    /**
     * @return \arConnectorDB
     */
    public function getArConnector()
    {
        return arConnectorMap::get($this);
    }


    /**
     * @return \arFieldList
     */
    public function getArFieldList()
    {
        return arFieldCache::get($this);
    }


    /**
     * @throws \arException
     * @deprecated
     */
    public static function returnDbTableName()
    {
        throw new arException(arException::UNKNONWN_EXCEPTION, 'Implement getConnectorContainerName in your child-class');
    }


    /**
     * @return string
     * @description Return the Name of your Connector Table
     */
    public function getConnectorContainerName()
    {
        // WILL BE ABSTRACT TO REPLACE returnDbTableName() IN NEXT VERSION
        if ($this->connector_container_name) {
            return $this->connector_container_name;
        } else {
            $ar = self::getCalledClass();

            return $ar::returnDbTableName();
        }
    }


    /**
     * @param string $connector_container_name
     */
    public function setConnectorContainerName($connector_container_name)
    {
        $this->connector_container_name = $connector_container_name;
    }


    /**
     * @return mixed
     */
    public function getPrimaryFieldValue()
    {
        $primary_fieldname = arFieldCache::getPrimaryFieldName($this);

        return $this->{$primary_fieldname};
    }


    /**
     * @param $value
     */
    public function setPrimaryFieldValue($value)
    {
        $primary_fieldname = arFieldCache::getPrimaryFieldName($this);

        $this->{$primary_fieldname} = $value;
    }


    /**
     * @param int $primary_key
     * @param arConnector $connector
     */
    public function __construct($primary_key = 0, arConnector $connector = null)
    {
        //		if ($connector == null) {
        //			$connector = new arConnectorDB();
        //		}
        //		arConnectorMap::register($this, $connector);

        $arFieldList = arFieldCache::get($this);

        $key = $arFieldList->getPrimaryFieldName();
        $this->{$key} = $primary_key;
        if ($primary_key !== 0 and $primary_key !== null and $primary_key !== false) {
            $this->read();
        }
    }


    public function storeObjectToCache()
    {
        arObjectCache::store($this);
    }


    /**
     * @param string $format
     *
     * @return array
     */
    public function __getConvertedDateFieldsAsArray($format = null)
    {
        $converted_dates = array();
        foreach ($this->getArFieldList()->getFields() as $field) {
            if ($field->isDateField()) {
                $name = $field->getName();
                $value = $this->{$name};
                $converted_dates[$name] = array(
                    'unformatted' => $value,
                    'unix' => strtotime($value),
                );
                if ($format) {
                    $converted_dates[$name]['formatted'] = date($format, strtotime($value));
                }
            }
        }

        return $converted_dates;
    }


    /**
     * @param string $separator
     * @param bool $header
     *
     * @return string
     */
    public function __asCsv($separator = ';', $header = false)
    {
        $line = '';
        if ($header) {
            $line .= implode($separator, array_keys($this->getArFieldList()->getRawFields()));
            $line .= "\n";
        }
        $array = array();
        foreach ($this->__asArray() as $field_name => $value) {
            $serialized = $this->serializeToCSV($field_name);
            if ($serialized === null) {
                $array[$field_name] = $this->{$field_name};
            } else {
                $array[$field_name] = $serialized;
            }
        }
        $line .= implode($separator, array_values($array));

        return $line;
    }


    /**
     * This method is called for every field of your instance if you use __asCsv.
     * You can use it to customize your export into csv. (e.g. serialize an array).
     *
     * @param $field string
     *
     * @return mixed
     */
    protected function serializeToCSV($field)
    {
        return null;
    }


    /**
     * @return array
     */
    public function __asArray()
    {
        $return = array();
        foreach ($this->getArFieldList()->getFields() as $field) {
            $fieldname = $field->getName();
            $return[$fieldname] = $this->{$fieldname};
        }

        return $return;
    }


    /**
     * @return stdClass
     */
    public function __asStdClass()
    {
        $return = new stdClass();
        foreach ($this->getArFieldList()->getFields() as $field) {
            $fieldname = $field->getName();
            $return->{$fieldname} = $this->{$fieldname};
        }

        return $return;
    }


    /**
     * @return string
     */
    public function __asSerializedObject()
    {
        return serialize($this);
    }


    /**
     * @param array $array
     *
     * @return $this
     */
    public function buildFromArray(array $array)
    {
        $class = get_class($this);
        $primary = $this->getArFieldList()->getPrimaryFieldName();
        $primary_value = $array[$primary];
        if ($primary_value and arObjectCache::isCached($class, $primary_value)) {
            return arObjectCache::get($class, $primary_value);
        }
        foreach ($array as $field_name => $value) {
            $waked = $this->wakeUp($field_name, $value);
            $this->{$field_name} = ($waked === null) ? $value : $waked;
        }
        arObjectCache::store($this);
        $this->afterObjectLoad();

        return $this;
    }


    /**
     * @param $field_name
     * @param $value
     * @return string
     */
    public function fixDateField($field_name, $value)
    {
        if ($this->getArFieldList()->getFieldByName($field_name)->isDateField()) {
            return $this->getArConnector()->fixDate($value);
        }

        return $value;
    }


    /**
     * @param $field_name
     *
     * @return mixed
     */
    public function sleep($field_name)
    {
        return null;
    }


    /**
     * @param $field_name
     * @param $field_value
     *
     * @return mixed
     */
    public function wakeUp($field_name, $field_value)
    {
        return null;
    }


    /**
     * @return array
     * @deprecated
     */
    final public function getArrayForDb()
    {
        return $this->getArrayForConnector();
    }


    /**
     * @return array
     */
    final public function getArrayForConnector()
    {
        $data = array();
        foreach ($this->getArFieldList()->getFields() as $field) {
            $field_name = $field->getName();
            $sleeped = $this->sleep($field_name);
            $var = ($sleeped === null) ? ($this->{$field_name}) : $sleeped;
            $data[$field_name] = array( $field->getFieldType(), $var );
        }

        return $data;
    }




    //
    // Collector Modifications
    //

    /**
     * @return ActiveRecord
     *
     * @description Returns an instance of the instatiated calling active record (needs to be done in static methods)
     * @TODO        : This should be cached somehow
     */
    protected static function getCalledClass()
    {
        $class = get_called_class();

        return arCalledClassCache::get($class);
    }


    /**
     * @return bool
     *
     * @deprecated Do not use in Core DB-update. Please generate the manual installation script by using:
     *
     *             $arBuilder = new arBuilder(new ilYourARBasedClass());
     *             $arBuilder->generateDBUpdateForInstallation();
     */
    final public static function installDB()
    {
        return self::getCalledClass()->installDatabase();
    }


    /**
     * @return bool
     *
     * @deprecated Do not use in Core DB-update.
     */
    public function installConnector()
    {
        return $this->installDatabase();
    }


    /**
     * @param $old_name
     * @param $new_name
     *
     * @return bool
     */
    final public static function renameDBField($old_name, $new_name)
    {
        return self::getCalledClass()->getArConnector()->renameField(self::getCalledClass(), $old_name, $new_name);
    }


    /**
     * @return bool
     */
    final public static function tableExists()
    {
        return self::getCalledClass()->getArConnector()->checkTableExists(self::getCalledClass());
    }


    /**
     * @param $field_name
     *
     * @return bool
     */
    final public static function fieldExists($field_name)
    {
        return self::getCalledClass()->getArConnector()->checkFieldExists(self::getCalledClass(), $field_name);
    }


    /**
     * @param $field_name
     *
     * @return bool
     */
    final public static function removeDBField($field_name)
    {
        return self::getCalledClass()->getArConnector()->removeField(self::getCalledClass(), $field_name);
    }


    /**
     * @return bool
     */
    final protected function installDatabase()
    {
        if (!$this->tableExists()) {
            $fields = array();
            foreach ($this->getArFieldList()->getFields() as $field) {
                $fields[$field->getName()] = $field->getAttributesForConnector();
            }

            return $this->getArConnector()->installDatabase($this, $fields);
        } else {
            return $this->getArConnector()->updateDatabase($this);
        }
    }


    /**
     * @return bool
     */
    final public static function updateDB()
    {
        if (!self::tableExists()) {
            self::getCalledClass()->installDatabase();

            return true;
        }

        return self::getCalledClass()->getArConnector()->updateDatabase(self::getCalledClass());
    }


    /**
     * @return bool
     */
    final public static function resetDB()
    {
        return self::getCalledClass()->getArConnector()->resetDatabase(self::getCalledClass());
    }


    /**
     * @return bool
     */
    final public static function truncateDB()
    {
        return self::getCalledClass()->getArConnector()->truncateDatabase(self::getCalledClass());
    }


    /**
     * @return bool
     */
    final public static function flushDB()
    {
        return self::truncateDB();
    }

    //
    // CRUD
    //
    public function store()
    {
        $primary_fieldname = arFieldCache::getPrimaryFieldName($this);
        $primary_value = $this->getPrimaryFieldValue();

        if (!self::where(array( $primary_fieldname => $primary_value ))->hasSets()) {
            $this->create();
        } else {
            $this->update();
        }
    }


    public function save()
    {
        $this->store();
    }


    public function create()
    {
        if ($this->getArFieldList()->getPrimaryField()->getSequence()) {
            $primary_fieldname = arFieldCache::getPrimaryFieldName($this);
            $this->{$primary_fieldname} = $this->getArConnector()->nextID($this);
        }

        $this->getArConnector()->create($this, $this->getArrayForConnector());
        arObjectCache::store($this);
    }


    /**
     * @param int $new_id
     *
     * @return ActiveRecord
     * @throws arException
     */
    public function copy($new_id = 0)
    {
        if (self::where(array( $this->getArFieldList()->getPrimaryFieldName() => $new_id ))->hasSets()) {
            throw new arException(arException::COPY_DESTINATION_ID_EXISTS);
        }
        $new_obj = clone($this);
        $new_obj->setPrimaryFieldValue($new_id);

        return $new_obj;
    }


    public function afterObjectLoad()
    {
    }


    /**
     * @throws arException
     */
    public function read()
    {
        $records = $this->getArConnector()->read($this);
        if (is_array($records) && count($records) === 0 && $this->ar_safe_read === true) {
            throw new arException(arException::RECORD_NOT_FOUND, $this->getPrimaryFieldValue());
        } elseif (is_array($records) && count($records) === 0 && $this->ar_safe_read === false) {
            $this->is_new = true;
        }
        $records = is_array($records) ? $records : array();
        foreach ($records as $rec) {
            foreach ($this->getArrayForConnector() as $k => $v) {
                $waked = $this->wakeUp($k, $rec->{$k});
                $this->{$k} = ($waked === null) ? $rec->{$k} : $waked;
            }
            arObjectCache::store($this);
            $this->afterObjectLoad();
        }
    }


    public function update()
    {
        $this->getArConnector()->update($this);
        arObjectCache::store($this);
    }


    public function delete()
    {
        $this->getArConnector()->delete($this);
        arObjectCache::purge($this);
    }



    //
    // Collection
    //
    /**
     * @return ActiveRecord[]
     */
    public static function preloadObjects()
    {
        return self::get();
    }


    /**
     * @param array $additional_params
     *
     * @return $this
     */
    public static function additionalParams(array $additional_params)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());
        $srModelObjectList->additionalParams($additional_params);

        return $srModelObjectList;
    }


    /**
     * @param       $primary_key
     * @param array $add_constructor_args
     *
     * @return ActiveRecord
     */
    public static function find($primary_key, array $add_constructor_args = array())
    {
        /**
         * @var $obj ActiveRecord
         */
        try {
            $class_name = get_called_class();
            if (!arObjectCache::isCached($class_name, $primary_key)) {
                $obj = arFactory::getInstance($class_name, $primary_key, $add_constructor_args);
                $obj->storeObjectToCache();

                return $obj;
            }
        } catch (arException $e) {
            return null;
        }

        try {
            $obj = arObjectCache::get($class_name, $primary_key);
        } catch (arException $e) {
            return null;
        }

        return $obj;
    }


    /**
     * Tries to find the object and throws an Exception if object is not found, instead of returning null
     *
     * @param $primary_key
     * @param array $add_constructor_args
     * @throws arException
     * @return ActiveRecord
     */
    public static function findOrFail($primary_key, array $add_constructor_args = array())
    {
        $obj = self::find($primary_key, $add_constructor_args);
        if (is_null($obj)) {
            throw new arException(arException::RECORD_NOT_FOUND);
        }

        return $obj;
    }


    /**
     * @param       $primary_key
     * @param array $add_constructor_args
     *
     * @description Returns an existing Object with given primary-key or a new Instance with given primary-key set but not yet created
     *
     * @return ActiveRecord
     */
    public static function findOrGetInstance($primary_key, array $add_constructor_args = array())
    {
        $obj = self::find($primary_key, $add_constructor_args);
        if ($obj !== null) {
            return $obj;
        } else {
            $class_name = get_called_class();
            $obj = arFactory::getInstance($class_name, 0, $add_constructor_args);
            $obj->setPrimaryFieldValue($primary_key);
            $obj->is_new = true;
            $obj->storeObjectToCache();

            return $obj;
        }
    }


    /**
     * @param      $where
     * @param null $operator
     *
     * @return ActiveRecordList
     */
    public static function where($where, $operator = null)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());
        $srModelObjectList->where($where, $operator);

        return $srModelObjectList;
    }


    /**
     * @param ActiveRecord $ar
     * @param              $on_this
     * @param              $on_external
     * @param array $fields
     * @param string $operator
     *
     * @return $this
     */
    public static function innerjoinAR(ActiveRecord $ar, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        return self::innerjoin($ar->getConnectorContainerName(), $on_this, $on_external, $fields, $operator, $both_external);
    }


    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @param array $fields
     * @param string $operator
     *
     * @return $this
     */
    public static function innerjoin($tablename, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->innerjoin($tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }


    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @param array $fields
     * @param string $operator
     *
     * @return $this
     */
    public static function leftjoin($tablename, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->leftjoin($tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }


    /**
     * @param        $orderBy
     * @param string $orderDirection
     *
     * @return ActiveRecordList
     */
    public static function orderBy($orderBy, $orderDirection = 'ASC')
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());
        $srModelObjectList->orderBy($orderBy, $orderDirection);

        return $srModelObjectList;
    }


    /**
     * @param string $date_format
     *
     * @return ActiveRecordList
     */
    public static function dateFormat($date_format = 'd.m.Y - H:i:s')
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());
        $srModelObjectList->dateFormat($date_format);

        return $srModelObjectList;
    }


    /**
     * @param $start
     * @param $end
     *
     * @return ActiveRecordList
     */
    public static function limit($start, $end)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());
        $srModelObjectList->limit($start, $end);

        return $srModelObjectList;
    }


    /**
     * @return int
     */
    public static function affectedRows()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->affectedRows();
    }


    /**
     * @return int
     */
    public static function count()
    {
        return self::affectedRows();
    }


    /**
     * @return ActiveRecord[]
     */
    public static function get()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->get();
    }


    /**
     * @return ActiveRecordList
     */
    public static function debug()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->debug();
    }


    /**
     * @return ActiveRecord
     */
    public static function first()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->first();
    }


    /**
     * @return ActiveRecordList
     */
    public static function getCollection()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList;
    }


    /**
     * @return ActiveRecord
     */
    public static function last()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->last();
    }


    /**
     * @return ActiveRecordList
     * @deprecated
     */
    public static function getFirstFromLastQuery()
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->getFirstFromLastQuery();
    }


    /**
     * @param arConnector $connector
     *
     * @return ActiveRecordList
     */
    public static function connector(arConnector $connector)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->connector($connector);
    }


    /**
     * @param bool $set_raw
     *
     * @return ActiveRecordList
     */
    public static function raw($set_raw = true)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->raw($set_raw);
    }


    /**
     * @param null $key
     * @param null $values
     *
     * @return array
     */
    public static function getArray($key = null, $values = null)
    {
        $srModelObjectList = new ActiveRecordList(self::getCalledClass());

        return $srModelObjectList->getArray($key, $values);
    }

    //
    // Magic Methods & Helpers
    //
    /**
     * @param $name
     * @param $arguments
     *
     * @return array
     */
    public function __call($name, $arguments)
    {
        // Getter
        if (preg_match("/get([a-zA-Z]*)/u", $name, $matches) and count($arguments) == 0) {
            return $this->{self::fromCamelCase($matches[1])};
        }
        // Setter
        if (preg_match("/set([a-zA-Z]*)/u", $name, $matches) and count($arguments) == 1) {
            $this->{self::fromCamelCase($matches[1])} = $arguments[0];
        }
        if (preg_match("/findBy([a-zA-Z]*)/u", $name, $matches) and count($arguments) == 1) {
            return self::where(array( self::fromCamelCase($matches[1]) => $arguments[0] ))->getFirst();
        }
    }


    /**
     * @param string $str
     * @param bool $capitalise_first_char
     *
     * @return string
     */
    public static function _toCamelCase($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback('/_([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, $str);
    }


    /**
     * @param string $str
     *
     * @return string
     */
    protected static function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback('/([A-Z])/', function ($c) {
            return "_" . strtolower($c[1]);
        }, $str);
    }
}
