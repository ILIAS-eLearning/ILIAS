<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * container structure export
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerExporter extends ilXmlExporter
{
    public function __construct()
    {
    }
    
    public function init() : void
    {
    }
    
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        if ($a_entity != 'struct') {
            return [];
        }
        
        
        $res = array();
        
        // pages
        
        $pg_ids = array();
        
        // container pages
        foreach ($a_ids as $id) {
            if (ilContainerPage::_exists("cont", $id)) {
                $pg_ids[] = "cont:" . $id;
            }
        }
        
        // container start objects pages
        foreach ($a_ids as $id) {
            if (ilContainerStartObjectsPage::_exists("cstr", $id)) {
                $pg_ids[] = "cstr:" . $id;
            }
        }
        
        if (sizeof($pg_ids)) {
            $res[] = array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids
            );
        }
        
        // style
        $style_ids = array();
        foreach ($a_ids as $id) {
            $style_id = ilObjStyleSheet::lookupObjectStyle($id);
            // see #24888
            $style_id = ilObjStyleSheet::getEffectiveContentStyleId($style_id);
            if ($style_id > 0) {
                $style_ids[] = $style_id;
            }
        }
        if (sizeof($style_ids)) {
            $res[] = array(
                "component" => "Services/Style",
                "entity" => "sty",
                "ids" => $style_ids
            );
        }

        // service settings
        $res[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids);

        // skill profiles
        $res[] = array(
            "component" => "Services/Skill",
            "entity" => "skl_local_prof",
            "ids" => $a_ids);

        // news settings
        $res[] = [
            "component" => "Services/News",
            "entity" => "news_settings",
            "ids" => $a_ids
        ];

        return $res;
    }
    
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        global $DIC;

        $log = $DIC->logger()->root();
        if ($a_entity == 'struct') {
            $log->debug(__METHOD__ . ': Received id = ' . $a_id);
            $ref_ids = ilObject::_getAllReferences((int) $a_id);
            $writer = new ilContainerXmlWriter(end($ref_ids));
            $writer->write();
            return $writer->xmlDumpMem(false);
        }
        return "";
    }
    
    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array[]
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Folder/fold/4_1",
                "xsd_file" => "ilias_fold_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
