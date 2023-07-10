<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT based-object base class
 * Currently "mixed" with ActiveRecord-pattern, could be splitted
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADTBasedObject
{
    protected ilADT $properties;
    protected array $db_errors = [];

    protected ilDBInterface $db;
    protected ilLanguage $lng;

    /**
     * Constructor
     * Tries to read record from DB, in accordance to current ILIAS behaviour
     */
    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->properties = $this->initProperties();

        // :TODO: to keep constructor "open" we COULD use func_get_args()
        $this->parsePrimary(func_get_args());
        $this->read();
    }


    //
    // properties
    //

    /**
     * Init properties (aka set ADT definition)
     * @return ilADT
     */
    abstract protected function initProperties(): ilADT;

    /**
     * Get all properties
     */
    public function getProperties(): ilADT
    {
        return $this->properties;
    }

    /**
     * Validate
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->properties->isValid();
    }

    /**
     * Get property magic method ("get<PropertyName>()")
     * Setters are type-specific and cannot be magic
     * @param string $a_method
     * @param mixed  $a_value
     * @return ilADT
     * @throws Exception
     */
    public function __call($a_method, $a_value)
    {
        $type = substr($a_method, 0, 3);
        switch ($type) {
            case "get":
                $parsed = strtolower(preg_replace("/([A-Z])/", " $1", substr($a_method, 3)));
                $parsed = str_replace(" ", "_", trim($parsed));
                if (!$this->properties->hasElement($parsed)) {
                    throw new Exception("ilADTObject unknown property " . $parsed);
                }
                return $this->properties->getElement($parsed);

            default:
                throw new Exception("ilADTObject unknown type: " . $type);
        }
    }


    //
    // CRUD / active record
    //

    /**
     * Parse incoming primary key
     * @param array $a_args
     * @see __construct()
     */
    abstract protected function parsePrimary(array $a_args): void;

    /**
     * Check if currently has primary
     * @return bool
     */
    abstract protected function hasPrimary(): bool;

    /**
     * Create new primary key, e.g. sequence
     * @return bool
     */
    abstract protected function createPrimaryKeyb(): bool;

    /**
     * Init (properties) DB bridge
     * @param ilADTDBBridge $a_adt_db
     */
    abstract protected function initDBBridge(ilADTDBBridge $a_adt_db): void;

    /**
     * Init active record helper for current table, primary and properties
     * @return ilADTActiveRecord
     */
    protected function initActiveRecordInstance(): ilADTActiveRecord
    {
        if (!$this->hasPrimary()) {
            throw new Exception("ilADTBasedObject no primary");
        }

        $factory = ilADTFactory::getInstance();
        $adt_db = $factory->getDBBridgeForInstance($this->properties);
        $this->initDBBridge($adt_db);

        // use custom error handling
        //FIXME
        //$this->db->exception = "ilADTDBException";

        /** @noinspection PhpParamsInspection */
        return $factory->getActiveRecordInstance($adt_db);
    }

    /**
     * Read record
     * @return bool
     */
    public function read(): bool
    {
        if ($this->hasPrimary()) {
            $rec = $this->initActiveRecordInstance();
            return $rec->read();
        }
        return false;
    }

    /**
     * Create record (only if valid)
     * @return bool
     */
    public function create(): bool
    {
        if ($this->hasPrimary()) {
            return $this->update();
        }

        if ($this->isValid()) {
            if ($this->createPrimaryKeyb()) {
                try {
                    $rec = $this->initActiveRecordInstance();
                    $rec->create();
                } catch (ilADTDBException $e) {
                    $this->db_errors[$e->getColumn()][] = $e->getCode();
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Update record (only if valid)
     * @return bool
     */
    public function update(): bool
    {
        if (!$this->hasPrimary()) {
            return $this->create();
        }

        if ($this->isValid()) {
            try {
                $rec = $this->initActiveRecordInstance();
                $rec->update();
            } catch (ilADTDBException $e) {
                $this->db_errors[$e->getColumn()][] = $e->getCode();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Delete record
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->hasPrimary()) {
            $rec = $this->initActiveRecordInstance();
            $rec->delete();
            return true;
        }
        return false;
    }

    /**
     * Get DB errors
     * @return array
     */
    public function getDBErrors(): array
    {
        return $this->db_errors;
    }

    /**
     * Translate DB error codes
     * @param array $a_codes
     * @return array
     */
    public function translateDBErrorCodes(array $a_codes): array
    {
        $res = array();

        foreach ($a_codes as $code) {
            switch ($code) {
                default:
                    $res[] = "Unknown ADT error code " . $code;
                    break;
            }
        }
        return $res;
    }

    /**
     * Get translated error codes (DB, Validation)
     * @param string $delimiter
     * @return string
     */
    public function getAllTranslatedErrors(string $delimiter = "\n"): string
    {
        $tmp = array();

        foreach ($this->getProperties()->getValidationErrorsByElements() as $error_code => $element_id) {
            $tmp[] = $element_id . " [validation]: " . $this->getProperties()->translateErrorCode($error_code);
        }

        foreach ($this->getDBErrors() as $element_id => $codes) {
            $tmp[] = $element_id . " [db]: " . implode($delimiter, $this->translateDBErrorCodes($codes));
        }

        if (count($tmp)) {
            return get_class($this) . $delimiter . implode($delimiter, $tmp);
        }
        return '';
    }
}
