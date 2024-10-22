<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class ilDclFieldFactory
{
    public static string $field_base_path_patter = "./Modules/DataCollection/classes/Fields/%s/";
    public static string $default_prefix = "ilDcl";
    public static string $record_field_class_patter = "%sRecordFieldModel";
    public static string $field_class_patter = "%sFieldModel";
    public static string $record_representation_class_pattern = "%sRecordRepresentation";
    public static string $field_representation_class_pattern = "%sFieldRepresentation";
    protected static array $record_field_cache = [];

    /**
     * Creates a RecordField instance and loads the field and record representation
     * @param object|ilDclBaseFieldModel  $field
     * @param object|ilDclBaseRecordModel $record
     * @throws ilDclException
     * @throws Exception
     */
    public static function getRecordFieldInstance(object $field, object $record): ilDclBaseRecordFieldModel
    {
        if (!empty(self::$record_field_cache[$field->getId()][$record->getId()])) {
            return self::$record_field_cache[$field->getId()][$record->getId()];
        }

        $path = self::getClassPathByInstance($field, self::$record_field_class_patter);
        if (file_exists($path)) {
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

        throw new RuntimeException("file not found " . $path);
    }

    protected static array $field_class_cache = [];

    /**
     * Concatenates Classname from datatype and pattern
     */
    public static function getFieldClass(string $datatype, string $class_pattern): string
    {
        if (!empty(self::$field_class_cache[$datatype . $class_pattern])) {
            return self::$field_class_cache[$datatype . $class_pattern];
        }

        $fieldtype = $datatype;

        $class = sprintf($class_pattern, $fieldtype);
        self::$field_class_cache[$datatype . $class_pattern] = $class;

        return $class;
    }

    public static function getFieldClassFile(string $datatype, string $class_pattern): string
    {
        return "class." . self::getFieldClass($datatype, $class_pattern) . ".php";
    }

    protected static array $field_representation_cache = [];

    public static function getFieldRepresentationInstance(ilDclBaseFieldModel $field): ilDclBaseFieldRepresentation
    {
        // when the datatype overview is generated no field-models are available, so an empty instance is used => no caching there
        if ($field->getId() != null && !empty(self::$field_representation_cache[$field->getId()])) {
            return self::$field_representation_cache[$field->getId()];
        }

        $class_path = self::getClassPathByInstance($field, self::$field_representation_class_pattern);

        if (file_exists($class_path)) {
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

    protected static array $record_representation_cache = [];

    /**
     * Get RecordRepresentation from RecordFieldModel
     * @throws ilDclException
     */
    public static function getRecordRepresentationInstance(
        ilDclBaseRecordFieldModel $record_field
    ): ilDclBaseRecordRepresentation {
        // there are some field types which have no recordFieldModel object (e.g rating) => no caching
        if ($record_field->getId() != null && !empty(self::$record_representation_cache[$record_field->getId()])) {
            return self::$record_representation_cache[$record_field->getId()];
        }

        $class_path = self::getClassPathByInstance(
            $record_field->getField(),
            self::$record_representation_class_pattern
        );

        if (file_exists($class_path)) {
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
     * @throws ilDclException
     */
    public static function getFieldModelInstance(int $field_id, ?int $datatype = null): ilDclBaseFieldModel
    {
        $base = new ilDclBaseFieldModel($field_id);
        if ($datatype != null) {
            $base->setDatatypeId($datatype);
        }

        return self::getFieldModelInstanceByClass($base, $field_id);
    }

    protected static array $field_model_cache = [];

    /**
     * Gets the correct instance of a fieldModel class
     * Checks if a field is a plugin a replaces the fieldModel with the necessary class
     * @throws ilDclException
     */
    public static function getFieldModelInstanceByClass(
        ilDclBaseFieldModel $field,
        ?int $field_id = null
    ): ilDclBaseFieldModel {
        if ($field->getId() != null && !empty(self::$field_model_cache[$field->getId()])) {
            return self::$field_model_cache[$field->getId()];
        }

        $path_type = self::getClassPathByInstance($field, self::$field_class_patter);

        if (file_exists($path_type)) {
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
        $instance->setDatatypeId($field->getDatatypeId());

        if ($field->getId() != null) {
            self::$field_model_cache[$field->getId()] = $instance;
        }

        return $instance;
    }

    protected static array $field_type_cache = [];

    public static function getFieldTypeByInstance(ilDclBaseFieldModel $field): string
    {
        global $DIC;
        $datatype = $field->getDatatype();

        if (!empty(self::$field_type_cache[$datatype->getId()])) {
            return self::$field_type_cache[$datatype->getId()];
        }

        if (ilDclFieldTypePlugin::isPluginDatatype($datatype->getTitle())) {
            $plugin_id = ilDclFieldTypePlugin::getPluginId($datatype->getTitle());
            if ($DIC["component.repository"]->hasActivatedPlugin($plugin_id)) {
                $fieldtype = 'il' . $DIC["component.repository"]->getPluginById($plugin_id)->getName();
            } else {
                $fieldtype = '';
            }
        } elseif ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_FILEUPLOAD) {
            // This is for legacy reasons. The fileupload field was replaced with ilDclDatatype::INPUTFORMAT_FILE in
            // ILIAS 9, but must be available for one more release, since there might be records with this field type
            // which have not et been migrated.
            $fieldtype = self::$default_prefix . ucfirst('Fileupload');
        } else {
            $fieldtype = self::$default_prefix . ucfirst(self::parseDatatypeTitle($datatype->getTitle()));
        }
        self::$field_type_cache[$datatype->getId()] = $fieldtype;
        return $fieldtype;
    }

    public static function getClassByInstance(ilDclBaseFieldModel $field, string $class_pattern): string
    {
        $fieldtype = self::getFieldTypeByInstance($field);

        return self::getFieldClass($fieldtype, $class_pattern);
    }

    protected static array $class_path_cache = [];

    /**
     * @throws ilDclException
     */
    public static function getClassPathByInstance(ilDclBaseFieldModel $field, string $class_pattern): string
    {
        global $DIC;
        $datatype = $field->getDatatype();

        if ($field->getId() != null && !empty(self::$class_path_cache[$field->getId()][$class_pattern])) {
            return self::$class_path_cache[$field->getId()][$class_pattern];
        }

        if (ilDclFieldTypePlugin::isPluginDatatype($datatype->getTitle())) {
            $plugin_id = ilDclFieldTypePlugin::getPluginId($datatype->getTitle());
            if ($DIC["component.repository"]->hasActivatedPlugin($plugin_id)) {
                $class_path = $DIC["component.repository"]->getPluginById($plugin_id)->getPath() . '/classes/';
            } else {
                return '';
            }
        } elseif ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_FILEUPLOAD) {
            // This is for legacy reasons. The fileupload field was replaced with ilDclDatatype::INPUTFORMAT_FILE in
            // ILIAS 9, but must be available for one more release, since there might be records with this field type
            // which have not et been migrated.
            $class_path = sprintf(
                self::$field_base_path_patter,
                ucfirst(self::parseDatatypeTitle('Fileupload'))
            );

            $class_name = sprintf(
                'class.' . self::$default_prefix . '%s.php',
                sprintf(
                    $class_pattern,
                    ucfirst('Fileupload'),
                )
            );

            $return = $class_path . $class_name;
            if ($field->getId() != null) {
                self::$class_path_cache[$field->getId()][$class_pattern] = $return;
            }

            return $return;
        } else {
            $class_path = sprintf(
                self::$field_base_path_patter,
                ucfirst(self::parseDatatypeTitle($datatype->getTitle()))
            );
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
     */
    public static function parseDatatypeTitle(string $title): string
    {
        $parts = explode("_", $title);
        $func = function ($value) {
            return ucfirst($value);
        };

        $parts = array_map($func, $parts);
        return implode("", $parts);
    }

    public static function getRecordModelInstance(?int $record_id): ilDclBaseRecordModel
    {
        return new ilDclBaseRecordModel($record_id);
    }

    public static function getPluginNameFromFieldModel(ilDclBaseFieldModel $object): string
    {
        $class_name = get_class($object);
        return substr($class_name, 2, -(strlen(self::$field_class_patter) - 2));
    }
}
