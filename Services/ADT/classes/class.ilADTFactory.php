<?php declare(strict_types=1);

/**
 * Class ilADTFactory
 */
class ilADTFactory
{
    public const TYPE_LOCALIZED_TEXT = 'LocalizedText';

    protected static ?ilADTFactory $instance = null;

    public static function getInstance() : ilADTFactory
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all ADT types
     * @return string[]
     */
    public function getValidTypes() : array
    {
        return array(
            "Float",
            "Integer",
            "Location",
            "Text",
            "Boolean",
            "MultiText",
            "Date",
            "DateTime",
            "Enum",
            "MultiEnum",
            "Group",
            'ExternalLink',
            'InternalLink',
            self::TYPE_LOCALIZED_TEXT
        );
    }

    public function isValidType(string $a_type) : bool
    {
        return in_array($a_type, $this->getValidTypes());
    }

    public function initTypeClass(string $a_type, string $a_class = null) : string
    {
        $class = '';
        if ($this->isValidType($a_type)) {
            $class = "ilADT" . $a_type . $a_class;
            return $class;
        }
        throw new InvalidArgumentException("ilADTFactory unknown type: " . $a_type);
    }

    /**
     * Get instance of ADT definition
     * @param string $a_type
     * @return ilADTDefinition
     * @throws InvalidArgumentException
     */
    public function getDefinitionInstanceByType(string $a_type) : ilADTDefinition
    {
        $class = $this->initTypeClass($a_type, "Definition");
        return new $class();
    }

    /**
     * Get instance of ADT
     * @param ilADTDefinition $a_def
     * @return ilADT
     * @throws Exception
     */
    public function getInstanceByDefinition(ilADTDefinition $a_def) : ilADT
    {
        if (!method_exists($a_def, "getADTInstance")) {
            $class = $this->initTypeClass($a_def->getType());
            return new $class($a_def);
        } else {
            return $a_def->getADTInstance();
        }
    }

    /**
     * Get form bridge instance for ADT
     * @param ilADT $a_adt
     * @return ilADTFormBridge
     * @throws InvalidArgumentException
     */
    public function getFormBridgeForInstance(ilADT $a_adt) : ilADTFormBridge
    {
        $class = $this->initTypeClass($a_adt->getType(), "FormBridge");
        return new $class($a_adt);
    }

    /**
     * Get DB bridge instance for ADT
     * @param ilADT $a_adt
     * @return ilADTDBBridge
     * @throws InvalidArgumentException
     */
    public function getDBBridgeForInstance(ilADT $a_adt) : ilADTDBBridge
    {
        $class = $this->initTypeClass($a_adt->getType(), "DBBridge");
        return new $class($a_adt);
    }

    /**
     * Get presentation bridge instance for ADT
     * @param ilADT $a_adt
     * @return ilADTPresentationBridge
     * @throws InvalidArgumentException
     */
    public function getPresentationBridgeForInstance(ilADT $a_adt) : ilADTPresentationBridge
    {
        $class = $this->initTypeClass($a_adt->getType(), "PresentationBridge");
        return new $class($a_adt);
    }

    /**
     * Get search bridge instance for ADT definition
     * @param ilADTDefinition $a_adt_def
     * @param bool            $a_range
     * @param bool            $a_multi
     * @return ilADTSearchBridge
     * @throws InvalidArgumentException
     */
    public function getSearchBridgeForDefinitionInstance(
        ilADTDefinition $a_adt_def,
        bool $a_range = true,
        bool $a_multi = true
    ) : ilADTSearchBridge {
        if ($a_range) {
            try {
                $class = $this->initTypeClass($a_adt_def->getType(), "SearchBridgeRange");
                if (class_exists($class)) {
                    return new $class($a_adt_def);
                }
            } catch (Exception $e) {
            }
        }

        // multi enum search (single) == enum search (multi)
        if (!$a_multi &&
            $a_adt_def->getType() == "MultiEnum") {
            $class = $this->initTypeClass("Enum", "SearchBridgeMulti");
            return new $class($a_adt_def);
        }

        if ($a_multi) {
            try {
                if ($a_adt_def->getType() == 'MultiEnum') {
                    $class = $this->initTypeClass('Enum', 'SearchBridgeMulti');
                    return new $class($a_adt_def);
                }
                $class = $this->initTypeClass($a_adt_def->getType(), "SearchBridgeMulti");
                return new $class($a_adt_def);
            } catch (Exception $e) {
            }
        }
        $class = $this->initTypeClass($a_adt_def->getType(), "SearchBridgeSingle");
        return new $class($a_adt_def);
    }

    /**
     * Get active record instance for ADT
     * @param ilADT $a_adt
     * @return ilADTActiveRecordBridge
     * @throws InvalidArgumentException
     */
    public function getActiveRecordBridgeForInstance(ilADT $a_adt) : ilADTActiveRecordBridge
    {
        $class = $this->initTypeClass($a_adt->getType(), "ActiveRecordBridge");
        return new $class($a_adt);
    }


    //
    // active records
    //

    /**
     * Get active record instance
     * @param ilADTGroupDBBridge $a_properties
     * @return ilADTActiveRecord
     */
    public static function getActiveRecordInstance(ilADTGroupDBBridge $a_properties) : ilADTActiveRecord
    {
        return new ilADTActiveRecord($a_properties);
    }

    /**
     * Init active record by type
     */
    public static function initActiveRecordByType() : void
    {
    }

    /**
     * Get active record by type instance
     */
    public static function getActiveRecordByTypeInstance(ilADTDBBridge $a_properties) : ilADTActiveRecordByType
    {
        self::initActiveRecordByType();
        return new ilADTActiveRecordByType($a_properties);
    }
}
