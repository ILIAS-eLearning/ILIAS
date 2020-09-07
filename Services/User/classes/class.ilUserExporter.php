<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for user data
 * Note: this is currently NOT used for the classic user export/import
 * It is mainly used for export personsl user data from the personal desktop
 * (settings, profile, bookmarks, calendar entries)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesUser
 */
class ilUserExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Services/User/classes/class.ilUserDataSet.php");
        $this->ds = new ilUserDataSet();
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
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        if ($a_entity == "personal_data") {
            include_once("./Services/Calendar/classes/class.ilCalendarCategories.php");
            $cal_ids = array();
            foreach ($a_ids as $user_id) {
                foreach (ilCalendarCategories::lookupPrivateCategories($user_id) as $ct) {
                    $cal_ids[] = $ct["cat_id"];
                }
            }
            
            return array(
                array(
                    "component" => "Services/User",
                    "entity" => "usr_profile",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/User",
                    "entity" => "usr_multi",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/User",
                    "entity" => "usr_setting",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/Bookmarks",
                    "entity" => "bookmarks",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/Notes",
                    "entity" => "user_notes",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/Calendar",
                    "entity" => "calendar",
                    "ids" => $cal_ids)
                );
        }
        
        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
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
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
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
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/User/usr/4_3",
                "xsd_file" => "ilias_usr_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "4.4.99"),
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/User/usr/5_1",
                "xsd_file" => "ilias_usr_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.1.99"),
            "5.2.0" => array(
                "namespace" => "http://www.ilias.de/Services/User/usr/5_2",
                "xsd_file" => "ilias_usr_5_2.xsd",
                "uses_dataset" => true,
                "min" => "5.2.0",
                "max" => "5.2.99"),
            "5.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/User/usr/5_3",
                "xsd_file" => "ilias_usr_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => "")
        );
    }
}
