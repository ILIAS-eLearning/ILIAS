<?php
require_once('class.arField.php');

/**
 * Class arFieldList
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arFieldList
{
    const HAS_FIELD = 'has_field';
    const IS_PRIMARY = 'is_primary';
    const IS_NOTNULL = 'is_notnull';
    const FIELDTYPE = 'fieldtype';
    const LENGTH = 'length';
    const SEQUENCE = 'sequence';
    const INDEX = 'index';
    /**
     * @var array
     */
    protected static $prefixes = array( 'db', 'con' );
    /**
     * @var array
     */
    protected static $protected_names = array( 'arConnector', 'arFieldList' );
    /**
     * @var array
     */
    protected static $allowed_description_fields = array(
        'is_unique', // There are many classes which already use this (without any function)
        self::IS_PRIMARY,
        self::IS_NOTNULL,
        self::FIELDTYPE,
        self::LENGTH,
        self::SEQUENCE,
        self::INDEX
    );
    /**
     * @var array
     */
    protected static $allowed_connector_fields = array(
        self::IS_NOTNULL,
        self::FIELDTYPE,
        self::LENGTH,
    );
    /**
     * @var arField|array
     */
    protected $primary_field;
    /**
     * @var array
     */
    protected $primary_fields = array();
    /**
     * @var array
     */
    protected $raw_fields = array();
    /**
     * @var array
     */
    protected $fields = array();
    /**
     * @var ActiveRecord
     */
    protected $ar;
    /**
     * @var array
     */
    protected static $key_maps = array(
        self::FIELDTYPE => 'type',
        self::IS_NOTNULL => 'notnull',
    );


    /**
     * arFieldList constructor.
     *
     * @param ActiveRecord $ar
     */
    public function __construct(ActiveRecord $ar)
    {
        $this->ar = $ar;
    }


    /**
     * @param $key
     *
     * @return mixed
     */
    public static function mapKey($key)
    {
        if (isset(self::$key_maps[$key])) {
            return self::$key_maps[$key];
        }

        return $key;
    }


    /**
     * @return array
     */
    public static function getAllowedConnectorFields()
    {
        return self::$allowed_connector_fields;
    }


    /**
     * @return array
     */
    public static function getAllowedDescriptionFields()
    {
        return self::$allowed_description_fields;
    }



    /**
     * @param ActiveRecord $ar
     *
     * @return \arFieldList
     */
    public static function getInstance(ActiveRecord $ar)
    {
        $arFieldList = new self($ar);
        $arFieldList->initRawFields($ar);
        $arFieldList->initFields();

        return $arFieldList;
    }


    /**
     * @param ActiveRecord $ar
     *
     * @deprecated
     * @return \arFieldList
     */
    public static function getInstanceFromStorage($ar)
    {
        $arFieldList = new self();
        $arFieldList->initRawFields($ar);
        $arFieldList->initFields();

        return $arFieldList;
    }


    /**
     * @return array
     */
    public function getArrayForConnector()
    {
        $return = array();
        foreach ($this->getFields() as $field) {
            $return[$field->getName()] = $field->getAttributesForConnector();
        }

        return $return;
    }


    protected function initFields()
    {
        foreach ($this->getRawFields() as $fieldname => $attributes) {
            if (self::checkAttributes($attributes)) {
                $arField = new arField();
                $arField->getHasField();
                $arField->loadFromArray($fieldname, $attributes);
                $this->fields[] = $arField;
                if ($arField->getPrimary()) {
                    $this->setPrimaryField($arField);
                }
            }
        }
    }


    /**
     * @param $field_name
     *
     * @return arField
     */
    public function getFieldByName($field_name)
    {
        $field = null;
        static $field_map;
        $field_key = $this->ar->getConnectorContainerName() . '.' . $field_name;
        if (is_array($field_map) && array_key_exists($field_key, $field_map)) {
            return $field_map[$field_key];
        }
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $field_name) {
                $field_map[$field_key] = $field;

                return $field;
            }
        }
    }


    /**
     * @param $field_name
     *
     * @return bool
     */
    public function isField($field_name)
    {
        $is_field = false;
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $field_name) {
                $is_field = true;
            }
        }

        return $is_field;
    }


    /**
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return $this->getPrimaryField()->getName();
    }


    /**
     * @return mixed
     */
    public function getPrimaryFieldType()
    {
        return $this->getPrimaryField()->getFieldType();
    }


    /**
     * @param \ActiveRecord|\arStorageInterface $ar
     *
     * @return array
     */
    protected function initRawFields(arStorageInterface $ar)
    {
        $regex = "/[ ]*\\* @(" . implode('|', self::$prefixes) . ")_([a-zA-Z0-9_]*)[ ]*([a-zA-Z0-9_]*)/u";
        $reflectionClass = new ReflectionClass($ar);
        $raw_fields = array();
        foreach ($reflectionClass->getProperties() as $property) {
            if (in_array($property->getName(), self::$protected_names)) {
                continue;
            }
            $properties_array = array();
            $has_property = false;
            foreach (explode("\n", $property->getDocComment()) as $line) {
                if (preg_match($regex, $line, $matches)) {
                    $has_property = true;
                    $properties_array[(string) $matches[2]] = $matches[3];
                }
            }
            if ($has_property) {
                $raw_fields[$property->getName()] = $properties_array;
            }
        }

        $this->setRawFields($raw_fields);
    }


    /**
     * @param $attribute_name
     *
     * @return bool
     */
    protected static function isAllowedAttribute($attribute_name)
    {
        return in_array($attribute_name, array_merge(self::$allowed_description_fields, array( self::HAS_FIELD )));
    }


    /**
     * @param array $attributes
     *
     * @return bool
     */
    protected static function checkAttributes(array $attributes)
    {
        if (isset($attributes[self::HAS_FIELD]) && $attributes[self::HAS_FIELD] === 'true') {
            foreach (array_keys($attributes) as $atr) {
                if (!self::isAllowedAttribute($atr)) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }


    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }


    /**
     * @return arField[]
     */
    public function getFields()
    {
        return $this->fields;
    }


    /**
     * @param \arField $primary_field
     */
    public function setPrimaryField($primary_field)
    {
        $this->primary_field = $primary_field;
    }


    /**
     * @return \arField
     */
    public function getPrimaryField()
    {
        return $this->primary_field;
    }


    /**
     * @param array $raw_fields
     */
    public function setRawFields($raw_fields)
    {
        $this->raw_fields = $raw_fields;
    }


    /**
     * @return array
     */
    public function getRawFields()
    {
        return $this->raw_fields;
    }


    /**
     * @param array $primary_fields
     */
    public function setPrimaryFields($primary_fields)
    {
        $this->primary_fields = $primary_fields;
    }


    /**
     * @return array
     */
    public function getPrimaryFields()
    {
        return $this->primary_fields;
    }
}
