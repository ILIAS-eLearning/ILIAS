<?php
require_once('./Services/Database/test/Implementations/data/class.ilDatabaseCommonTestMockData.php');

/**
 * Class ilDatabaseCommonTestMockData
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabasePostgresTestMockData extends ilDatabaseCommonTestMockData
{

    /**
     * @return string
     */
    public function getNow()
    {
        return "now()";
    }


    /**
     * @param $table_name
     * @return string
     */
    public function getInsertQuery($table_name)
    {
        return "INSERT INTO " . $table_name . " 
		      (id,is_online,is_default,latitude,longitude,elevation,address,init_mob_id,comment_mob_id,container_id) 
		    VALUES 
		      (58,1,0,47.05983,7.624028,2.56,'Farbweg 9, 3400 Burgdorf',78,69,456);";
    }
}
