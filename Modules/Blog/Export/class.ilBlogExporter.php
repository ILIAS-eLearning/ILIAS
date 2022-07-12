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
 * Blog export definition
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogExporter extends ilXmlExporter
{
    protected ilBlogDataSet $ds;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function init() : void
    {
        global $DIC;

        $this->ds = new ilBlogDataSet();
        $this->ds->setDSPrefix("ds");
        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();
    }
    
    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        $res = array();
        
        // postings
        $pg_ids = array();
        foreach ($a_ids as $id) {
            $pages = ilBlogPosting::getAllPostings($id);
            foreach (array_keys($pages) as $p) {
                $pg_ids[] = "blp:" . $p;
            }
        }
        if (count($pg_ids)) {
            $res[] = array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids
            );
        }
        
        // style
        $style_ids = array();
        foreach ($a_ids as $id) {
            $style_id = $this->content_style_domain->styleForObjId((int) $id)->getStyleId();
            if ($style_id > 0) {
                $style_ids[] = $style_id;
            }
        }
        if (count($style_ids)) {
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

        return $res;
    }
    
    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ) : string {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }
    
    public function getValidSchemaVersions(
        string $a_entity
    ) : array {
        return array(
                "4.3.0" => array(
                        "namespace" => "https://www.ilias.de/Modules/Blog/4_3",
                        "xsd_file" => "ilias_blog_4_3.xsd",
                        "uses_dataset" => true,
                        "min" => "4.3.0",
                        "max" => "4.9.9"),
                "5.0.0" => array(
                        "namespace" => "https://www.ilias.de/Modules/Blog/5_0",
                        "xsd_file" => "ilias_blog_5_0.xsd",
                        "uses_dataset" => true,
                        "min" => "5.0.0",
                        "max" => "")
            
        );
    }
}
