<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for news
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesNews
 */
class ilNewsExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Services/News/classes/class.ilNewsDataSet.php");
        $this->ds = new ilNewsDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }


    /**
     * Get tail dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
    {
        include_once("./Services/News/classes/class.ilNewsItem.php");
        $mob_ids = array();

        foreach ($a_ids as $id) {
            $mob_id = ilNewsItem::_lookupMobId($id);
            if ($mob_id > 0) {
                $mob_ids[$mob_id] = $mob_id;
            }
        }

        return array(
            array(
                "component" => "Services/MediaObjects",
                "entity" => "mob",
                "ids" => $mob_ids)
            );
    }

    /**
     * Get xml representation
     *
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
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
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/News/news/4_1",
                "xsd_file" => "ilias_news_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
