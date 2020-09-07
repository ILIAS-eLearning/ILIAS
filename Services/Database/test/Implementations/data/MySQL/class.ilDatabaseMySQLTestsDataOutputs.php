<?php
require_once('./Services/Database/test/Implementations/data/class.ilDatabaseCommonTestsDataOutputs.php');

/**
 * Class ilDatabaseCommonTestsDataOutputs
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabaseMySQLTestsDataOutputs extends ilDatabaseCommonTestsDataOutputs
{

    /**
     * @param $table_name
     * @return string
     */
    public function getCreationQueryBuildByILIAS($table_name)
    {
        return "CREATE TABLE $table_name (id INT NOT NULL, is_online TINYINT DEFAULT NULL, is_default TINYINT DEFAULT 1, latitude DOUBLE DEFAULT NULL, longitude DOUBLE DEFAULT NULL, elevation DOUBLE DEFAULT NULL, address VARCHAR(256) DEFAULT NULL NULL, init_mob_id INT DEFAULT NULL, comment_mob_id INT DEFAULT NULL, container_id INT DEFAULT NULL, big_data LONGTEXT)";
    }
}
