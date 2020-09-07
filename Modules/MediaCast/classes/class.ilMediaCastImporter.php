<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for media casts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaCast
 */
class ilMediaCastImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Modules/MediaCast/classes/class.ilMediaCastDataSet.php");
        $this->ds = new ilMediaCastDataSet();
        $this->ds->setDSPrefix("ds");
    }


    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
    
    public function finalProcessing($a_mapping)
    {
        // restore manual order
        $order = $this->ds->getOrder();
        if (sizeof($order)) {
            foreach ($order as $obj_id => $items) {
                $map = array();
                foreach ($items as $old_id) {
                    $map[] = $a_mapping->getMapping("Services/News", "news", $old_id);
                }
                
                $mcst = new ilObjMediaCast($obj_id, false);
                $mcst->saveOrder($map);
            }
        }
    }
}
