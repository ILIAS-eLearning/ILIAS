<?php

namespace CaT\Plugins\ComponentProviderExample\Settings; 

/**
 * Implementation of database for settings.
 */
class ilDB {
    const TABLE_NAME = "xlep_strings";

    /**
     * @var \ilDBInterface
     */
    protected $ilDB;

    public function __construct(\ilDBInterface $ilDB) {
        $this->ilDB = $ilDB;
    }

	/**
     * @inheritdoc
	 */
	public function update(ComponentProviderExample $settings) {
        $obj_id = $settings->objId();
        $this->deleteFor($obj_id);
        foreach ($settings->providedStrings() as $value) {
            $this->ilDB->insert(self::TABLE_NAME,
                [ "obj_id" => ["integer", $obj_id]
                , "value" => ["string", $value]
                ]); 
        } 
    }

	/**
     * @inheritdoc
	 */
	public function getFor($obj_id) {
        assert('is_int($obj_id)');
        $query =
            "SELECT value FROM ".self::TABLE_NAME.
            " WHERE obj_id = ".$this->ilDB->quote($obj_id, "integer");
        $res = $this->ilDB->query($query);

        $values = [];
        while($row = $this->ilDB->fetchAssoc($res)) {
            $values[] = $row["value"];
        }

        return new ComponentProviderExample($obj_id, $values);
    }

	/**
     * @inheritdoc
	 */
	public function deleteFor($obj_id) {
        $statement =
            "DELETE FROM ".self::TABLE_NAME.
            " WHERE obj_id = ".$this->ilDB->quote($obj_id, "integer");
        $this->ilDB->manipulate($statement);
    }

    public function install() {
        if(!$this->ilDB->tableExists(self::TABLE_NAME)) {
            $this->ilDB->createTable(self::TABLE_NAME,
                [ "obj_id" => ["type" => "integer", "length" => 4, "notnull" => true]
                , "value" => ["type" => "text", "length" => 64, "notnull" => true]
                ]);

            $this->ilDB->addPrimaryKey(self::TABLE_NAME, ["obj_id", "value"]);
        }
    }
}
