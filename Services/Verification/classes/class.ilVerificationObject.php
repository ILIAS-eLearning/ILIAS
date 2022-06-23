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

/**
 * Verification object base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilVerificationObject extends ilObject2
{
    protected array $map = array();
    protected array $properties = array();

    public const TYPE_STRING = 1;
    public const TYPE_BOOL = 2;
    public const TYPE_INT = 3;
    public const TYPE_DATE = 4;
    public const TYPE_RAW = 5;
    public const TYPE_ARRAY = 6;

    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->map = $this->getPropertyMap();
        parent::__construct($a_id, $a_reference);
    }

    /**
     * Return property map (name => type)
     */
    abstract protected function getPropertyMap() : array;

    /**
     * Check if given property is valid
     */
    public function hasProperty(string $a_name) : bool
    {
        return array_key_exists($a_name, $this->map);
    }

    /**
     * Get property data type
     */
    public function getPropertyType(string $a_name) : ?int
    {
        if ($this->hasProperty($a_name)) {
            return (int) $this->map[$a_name];
        }
        return null;
    }

    /**
     * Get property value
     * @return mixed
     */
    public function getProperty(string $a_name)
    {
        if ($this->hasProperty($a_name)) {
            return $this->properties[$a_name];
        }
        return null;
    }

    /**
     * Set property value
     * @param mixed $a_value
     */
    public function setProperty(string $a_name, $a_value) : void
    {
        if ($this->hasProperty($a_name)) {
            $this->properties[$a_name] = $a_value;
        }
    }

    /**
     * Import property from database
     *
     * @param string $a_type
     * @param mixed $a_data
     * @param ?string $a_raw_data
     */
    protected function importProperty(
        string $a_type,
        $a_data = null,
        ?string $a_raw_data = null
    ) : void {
        $data_type = $this->getPropertyType($a_type);
        if ($data_type) {
            $value = null;
            
            switch ($data_type) {
                case self::TYPE_STRING:
                    $value = (string) $a_data;
                    break;

                case self::TYPE_BOOL:
                    $value = (bool) $a_data;
                    break;

                case self::TYPE_INT:
                    $value = (int) $a_data;
                    break;

                case self::TYPE_DATE:
                    $value = new ilDate($a_data, IL_CAL_DATE);
                    break;

                case self::TYPE_ARRAY:
                    if ($a_data) {
                        $value = unserialize($a_data, ['allowed_classes' => false]);
                    }
                    break;

                case self::TYPE_RAW:
                    $value = $a_raw_data;
                    break;
            }

            $this->setProperty($a_type, $value);
        }
    }

    /**
     * Export property to database
     *
     * @return array(parameters, raw_data)
     */
    protected function exportProperty(string $a_name) : ?array
    {
        $data_type = $this->getPropertyType($a_name);
        if ($data_type) {
            $value = $this->getProperty($a_name);
            $raw_data = null;

            switch ($data_type) {
                case self::TYPE_DATE:
                    if ($value) {
                        $value = $value->get(IL_CAL_DATE);
                    }
                    break;

                case self::TYPE_ARRAY:
                    if ($value) {
                        $value = serialize($value);
                    }
                    break;

                case self::TYPE_RAW:
                    $raw_data = $value;
                    $value = null;
                    break;
            }

            return array("parameters" => $value,
                "raw_data" => $raw_data);
        }
        return null;
    }

    protected function doRead() : void
    {
        $ilDB = $this->db;

        if ($this->id) {
            $set = $ilDB->query("SELECT * FROM il_verification" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
            if ($ilDB->numRows($set)) {
                while ($row = $ilDB->fetchAssoc($set)) {
                    $this->importProperty(
                        $row["type"],
                        $row["parameters"],
                        $row["raw_data"]
                    );
                }
            }
        }
    }

    protected function doCreate(bool $clone_mode = false) : void
    {
        $this->saveProperties();
    }

    protected function doUpdate() : void
    {
        $this->saveProperties();
    }
    
    /**
     * Save current properties to database
     */
    protected function saveProperties() : bool
    {
        $ilDB = $this->db;
        
        if ($this->id) {
            // remove all existing properties
            $ilDB->manipulate("DELETE FROM il_verification" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
            
            foreach ($this->getPropertyMap() as $name => $type) {
                $property = $this->exportProperty($name);
                
                $fields = array("id" => array("integer", $this->id),
                    "type" => array("text", $name),
                    "parameters" => array("text", $property["parameters"]),
                    "raw_data" => array("text", $property["raw_data"]));

                $ilDB->insert("il_verification", $fields);
            }
            
            $this->handleQuotaUpdate();

            return true;
        }
        return false;
    }

    protected function doDelete() : void
    {
        $ilDB = $this->db;

        if ($this->id) {
            // remove all files
            $storage = new ilVerificationStorageFile($this->id);
            $storage->delete();
            
            $this->handleQuotaUpdate();
            
            $ilDB->manipulate("DELETE FROM il_verification" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
        }
    }
    
    public static function initStorage(int $a_id, string $a_subdir = null) : string
    {
        $storage = new ilVerificationStorageFile($a_id);
        $storage->create();
        
        $path = $storage->getAbsolutePath() . "/";
        
        if ($a_subdir) {
            $path .= $a_subdir . "/";
            
            if (!is_dir($path)) {
                mkdir($path);
            }
        }
        
        return $path;
    }
    
    public function getFilePath() : string
    {
        $file = $this->getProperty("file");
        if ($file) {
            $path = self::initStorage($this->getId(), "certificate");
            return $path . $file;
        }
        return "";
    }
    
    public function getOfflineFilename() : string
    {
        return ilFileUtils::getASCIIFilename($this->getTitle()) . ".pdf";
    }
    
    protected function handleQuotaUpdate() : void
    {
    }
}
