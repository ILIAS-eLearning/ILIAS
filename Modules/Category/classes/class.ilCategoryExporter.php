<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCategoryExporter extends ilXmlExporter
{
    /**
     * Get head dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
    {
        // always trigger container because of co-page(s)
        return array(
            array(
                'component'		=> 'Services/Container',
                'entity'		=> 'struct',
                'ids'			=> $a_ids
            )
        );
        
        /*
        include_once './Services/Export/classes/class.ilExportOptions.php';
        $eo = ilExportOptions::getInstance();

        $obj_id = end($a_ids);


        $GLOBALS['ilLog']->write(__METHOD__.': '.$obj_id);
        if($eo->getOption(ilExportOptions::KEY_ROOT) != $obj_id)
        {
            return array();
        }

        if(count(ilExportOptions::getInstance()->getSubitemsForExport()) > 1)
        {
            return array(
                array(
                    'component'		=> 'Services/Container',
                    'entity'		=> 'struct',
                    'ids'			=> $a_ids
                )
            );
        }
        return array();
        */
    }
    
    /**
     * Get tail dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        if ($a_entity == "cat") {
            include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
            $tax_ids = array();
            foreach ($a_ids as $id) {
                $t_ids = ilObjTaxonomy::getUsageOfObject($id);
                if (count($t_ids) > 0) {
                    $tax_ids[$t_ids[0]] = $t_ids[0];
                }
            }

            return array(
                array(
                    "component" => "Services/Taxonomy",
                    "entity" => "tax",
                    "ids" => $tax_ids)
                );
        }
        return array();
    }

    /**
     * Get xml
     * @param object $a_entity
     * @param object $a_schema_version
     * @param object $a_id
     * @return
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $cat_ref_id = end(ilObject::_getAllReferences($a_id));
        $category = ilObjectFactory::getInstanceByRefId($cat_ref_id, false);

        if (!$category instanceof ilObjCategory) {
            $GLOBALS['ilLog']->write(__METHOD__ . $a_id . ' is not instance of type category!');
            return '';
        }

        include_once './Modules/Category/classes/class.ilCategoryXmlWriter.php';

        $writer = new ilCategoryXmlWriter($category);
        $writer->setMode(ilCategoryXmlWriter::MODE_EXPORT);
        $writer->export(false);
        return $writer->getXml();
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Category/cat/4_3",
                "xsd_file" => "ilias_cat_4_3.xsd",
                "uses_dataset" => false,
                "min" => "4.3.0",
                "max" => "")
        );
    }

    /**
     * Init method
     */
    public function init()
    {
    }
}
