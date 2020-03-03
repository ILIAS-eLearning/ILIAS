<?php

/**
 * Class ilDclFieldFactory
 * This Class handles the creation of all field-classes
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFieldFactory
{

    /**
     * @var string
     */
    public static $field_base_path_patter = "./Modules/DataCollection/classes/Fields/%s/";
    /**
     * @var string
     */
    public static $default_prefix = "ilDcl";
    /**
     * @var string
     */
    public static $record_field_class_patter = "%sRecordFieldModel";
    /**
     * @var string
     */
    public static $field_class_patter = "%sFieldModel";
    /**
     * @var string
     */
    public static $record_class_patter = "%sRecordModel";
    /**
     * @var string
     */
    public static $record_representation_class_pattern = "%sRecordRepresentation";
    /**
     * @var string
     */
    public static $field_representation_class_pattern = "%sFieldRepresentation";
    /**
     * @var array
     */
    protected static $record_field_cache = array();


    /**
     * Creates a RecordField instance and loads the field and record representation
     *
     * @param ilDclBaseFieldModel $field
     * @param ilDclBaseRecordModel $record
     *
     * @return mixed
     * @throws ilDclException
     */
    public static function getRecordFieldInstance(ilDclBaseFieldModel $field, ilDclBaseRecordModel $record)
    {
        if (!empty(self::$record_field_cache[$field->getId()][$record->getId()])) {
            return self::$record_field_cache[$field->getId()][$record->getId()];
        }

        $path = self::getClassPathByInstance($field, self::$record_field_class_patter);
        if (file_exists($path)) {
            require_once($path);
            $class = self::getClassByInstance($field, self::$record_field_class_patter);
            $instance = new $class($record, $field);
            if ($instance instanceof ilDclBaseRecordFieldModel) {
                if (!$instance->getFieldRepresentation()) {
                    $instance->setFieldRepresentation(self::getFieldRepresentationInstance($field));
                }

                if (!$instance->getRecordRepresentation()) {
                    $instance->setRecordRepresentation(self::getRecordRepresentationInstance($instance));
                }
                self::$record_field_cache[$field->getId()][$record->getId()] = $instance;

                return $instance;
            }
        }
    }


    /**
     * @var array
     */
    protected static $field_class_cache = array();


    /**
     * Concatenates Classname from datatype and pattern
     *
     * @param $datatype
     * @param $class_pattern
     *
     * @return string
     */
    public static function getFieldClass($datatype, $class_pattern)
    {
        if (!empty(self::$field_class_cache[$datatype . $class_pattern])) {
            return self::$field_class_cache[$datatype . $class_pattern];
        }

        $fieldtype = $datatype;

        $class = sprintf($class_pattern, $fieldtype);
        self::$field_class_cache[$datatype . $class_pattern] = $class;

        return $class;
    }


    /**
     * Get Filename from datatype and pattern
     *
     * @param $datatype
     * @param $class_pattern
     *
     * @return string
     */
    public static function getFieldClassFile($datatype, $class_pattern)
    {
        return "class." . self::getFieldClass($datatype, $class_pattern) . ".php";
    }


    /**
     * @var array
     */
    protected static $field_representation_cache = array();


    /**
     * Returns FieldRepresentation from BaseFieldModel
     *
     * @param ilDclBaseFieldModel $field
     *
     * @return ilDclBaseFieldRepresentation
     * @throws ilDclException
     */
    public static function getFieldRepresentationInstance(ilDclBaseFieldModel $field)
    {
        // when the datatype overview is generated no field-models are available, so an empty instance is used => no caching there
        if ($field->getId() != null && !empty(self::$field_representation_cache[$field->getId()])) {
            return self::$field_representation_cache[$field->getId()];
        }

        $class_path = self::getClassPathByInstance($field, self::$field_representation_class_pattern);

        $instance = null;
        if (file_exists($class_path)) {
            require_once($class_path);
            $class = self::getClassByInstance($field, self::$field_representation_class_pattern);
            $instance = new $class($field);
        } else {
            throw new ilDclException("Path for FieldRepresentation with file " . $class_path . " does not exists!");
        }

        if ($instance == null) {
            throw new ilDclException("Could not create FieldRepresentation of " . $class . " with file " . $class_path);
        }

        if ($field->getId() != null) {
            self::$field_representation_cache[$field->getId()] = $instance;
        }

        return $instance;
    }


    /**
     * @var array
     */
    protected static $record_representation_cache = array();


    /**
     * Get RecordRepresentation from RecordFieldModel
     *
     * @param ilDclBaseRecordFieldModel $record_field
     *
     * @return ilDclBaseRecordRepresentation
     * @throws ilDclException
     */
    public static function getRecordRepresentationInstance(ilDclBaseRecordFieldModel $record_field)
    {
        // there are some field types which have no recordFieldModel object (e.g rating) => no caching
        if ($record_field->getId() != null && !empty(self::$record_representation_cache[$record_field->getId()])) {
            return self::$record_representation_cache[$record_field->getId()];
        }

        $class_path = self::getClassPathByInstance($record_field->getField(), self::$record_representation_class_pattern);
        $instance = null;

        if (file_exists($class_path)) {
            require_once($class_path);
            $class = self::getClassByInstance($record_field->getField(), self::$record_representation_class_pattern);
        } else {
            $class = self::getFieldClass(self::$default_prefix . "Base", self::$record_representation_class_pattern);
        }

        $instance = new $class($record_field);

        if ($instance == null) {
            throw new ilDclException("Could not create RecordRepresentation of " . $class_path . " " . $record_field->getField()->getDatatype()->getTitle());
        }

        if ($record_field->getId() != null) {
            self::$record_representation_cache[$record_field->getId()] = $instance;
        }

        return $instance;
    }


    /**
     * Get FieldModel from field-id and datatype
     *
     * @param      $field_id
     * @param null $datatype
     *
     * @return mixed
     * @throws ilDclException
     */
    public static function getFieldModelInstance($field_id, $datatype = null)
    {
        $base = new ilDclBaseFieldModel($field_id);
        if ($datatype != null) {
            $base->setDatatypeId($datatype);
        }

        $ilDclBaseFieldModel = self::getFieldModelInstanceByClass($base, $field_id);

        return $ilDclBaseFieldModel;
    }


    /**
     * @var array
     */
    protected static $field_model_cache = array();


    /**
     * Gets the correct instance of a fieldModel class
     * Checks if a field is a plugin a replaces the fieldModel with the necessary class
     *
     * @param ilDclBaseFieldModel $field
     * @param null                $field_id
     *
     * @return ilDclBaseFieldModel
     * @throws ilDclException
     */
    public static function getFieldModelInstanceByClass(ilDclBaseFieldModel $field, $field_id = null)
    {
        if ($field->getId() != null && !empty(self::$field_model_cache[$field->getId()])) {
            return self::$field_model_cache[$field->getId()];
        }

        $path_type = self::getClassPathByInstance($field, self::$field_class_patter);

        if (file_exists($path_type)) {
            require_once($path_type);
            $class = self::getClassByInstance($field, self::$field_class_patter);
        } else {
            $class = self::getFieldClass(self::$default_prefix . "Base", self::$field_class_patter);
        }

        if ($field_id) {
            $instance = new $class($field_id);
        } else {
            $instance = new $class();
        }

        if ($instance == null) {
            throw new ilDclException("Could not create FieldModel of " . $class);
        }

        if ($field->getId() != null) {
            self::$field_model_cache[$field->getId()] = $instance;
        }

        return $instance;
    }


    /**
     * @var array
     */
    protected static $field_type_cache = array();


    /**
     *
     * @param ilDclBaseFieldModel $field
     *
     * @return string
     */
    public static function getFieldTypeByInstance(ilDclBaseFieldModel $field)
    {
        $datatype = $field->getDatatype();

        if (!empty(self::$field_type_cache[$datatype->getId()])) {
            if ($datatype->getId() == ilDclDatatype::INPUTFORMAT_PLUGIN) {
                if (!empty(self::$field_type_cache[$datatype->getId()][$field->getId()])) {
                    return self::$field_type_cache[$datatype->getId()][$field->getId()];
                }
            } else {
                return self::$field_type_cache[$datatype->getId()];
            }
        }

        if ($datatype->getId() == ilDclDatatype::INPUTFORMAT_PLUGIN) {
            if ($field->hasProperty(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME)) {
                $plugin_data
                    = ilPlugin::getPluginObject(IL_COMP_MODULE, ilDclFieldTypePlugin::COMPONENT_NAME, ilDclFieldTypePlugin::SLOT_ID, $field->getProperty(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME));
                $fieldtype = $plugin_data->getPluginClassPrefix() . ucfirst($plugin_data->getPluginName());
            } else {
                $fieldtype = self::$default_prefix . ucfirst(self::parseDatatypeTitle($datatype->getTitle()));
            }
            self::$field_type_cache[$datatype->getId()][$field->getId()] = $fieldtype;
        } else {
            $fieldtype = self::$default_prefix . ucfirst(self::parseDatatypeTitle($datatype->getTitle()));
            self::$field_type_cache[$datatype->getId()] = $fieldtype;
        }

        return $fieldtype;
    }


    /**
     * @param ilDclBaseFieldModel $field
     * @param                     $class_pattern
     *
     * @return string
     */
    public static function getClassByInstance(ilDclBaseFieldModel $field, $class_pattern)
    {
        $fieldtype = self::getFieldTypeByInstance($field);

        return self::getFieldClass($fieldtype, $class_pattern);
    }


    /**
     * @var array
     */
    protected static $class_path_cache = array();


    /**
     * @param ilDclBaseFieldModel $field
     * @param                     $class_pattern
     *
     * @return string
     * @throws ilDclException
     */
    public static function getClassPathByInstance(ilDclBaseFieldModel $field, $class_pattern)
    {
        $datatype = $field->getDatatype();

        if ($field->getId() != null && !empty(self::$class_path_cache[$field->getId()][$class_pattern])) {
            return self::$class_path_cache[$field->getId()][$class_pattern];
        }

        if ($datatype->getId() == ilDclDatatype::INPUTFORMAT_PLUGIN) {
            if ($field->hasProperty(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME)) {
                $plugin_data
                    = ilPlugin::getPluginObject(IL_COMP_MODULE, ilDclFieldTypePlugin::COMPONENT_NAME, ilDclFieldTypePlugin::SLOT_ID, $field->getProperty(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME));
                if ($plugin_data == null) {
                    throw new ilDclException(
                        "Something went wrong by initializing the FieldHook-Plugin '"
                        . $field->getProperty(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME) . "' on Component '"
                        . ilDclFieldTypePlugin::COMPONENT_NAME . "' with slot '" . ilDclFieldTypePlugin::SLOT_ID . "' on field: "
                        . $field->getTitle()
                    );
                }

                $class_path = $plugin_data->getDirectory() . "/classes/";
            } else {
                $class_path = sprintf(self::$field_base_path_patter, ucfirst(self::parseDatatypeTitle($datatype->getTitle())));
            }
        } else {
            $class_path = sprintf(self::$field_base_path_patter, ucfirst(self::parseDatatypeTitle($datatype->getTitle())));
        }

        $return = $class_path . self::getFieldClassFile(self::getFieldTypeByInstance($field), $class_pattern);

        if ($field->getId() != null) {
            self::$class_path_cache[$field->getId()][$class_pattern] = $return;
        }

        return $return;
    }


    /**
     * Parse string to FieldClass format
     * Replaces _ with camelcase-notation
     *
     * @param $title
     *
     * @return string
     */
    public static function parseDatatypeTitle($title)
    {
        $parts = explode("_", $title);
        $func = function ($value) {
            return ucfirst($value);
        };

        $parts = array_map($func, $parts);
        $title = implode("", $parts);

        return $title;
    }


    /**
     * Creates a RecordModel instance
     *
     * @param $record_id
     *
     * @return ilDclBaseRecordModel
     */
    public static function getRecordModelInstance($record_id)
    {
        return new ilDclBaseRecordModel($record_id);
    }


    /**
     * Get plugin-name from FieldModel
     *
     * @param ilDclBaseFieldModel $object
     *
     * @return string
     */
    public static function getPluginNameFromFieldModel(ilDclBaseFieldModel $object)
    {
        $class_name = get_class($object);
        $class_name = substr($class_name, 2, -(strlen(self::$field_class_patter) - 2));

        return $class_name;
    }
}
