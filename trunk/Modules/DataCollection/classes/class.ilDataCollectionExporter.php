<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oskar
 * Date: 11/5/12
 * Time: 1:11 PM
 * To change this template use File | Settings | File Templates.
 */

include_once ("./Services/Export/classes/class.ilExport.php");

class ilDataCollectionExporter extends ilXmlExporter {

    public function init(){
    }

    public function getValidSchemaVersions($entity){
        return array("0.0.0");
    }

    public function getXmlRepresentation($entity, $schema_version, $id){
    }
}

?>
