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
 ********************************************************************
 */


/**
 * Exporter class for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillExporter extends ilXmlExporter
{
    private ilSkillDataSet $ds;
    protected ilExportConfig $config;

    public function init() : void
    {
        $this->ds = new ilSkillDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getExport()->getConfig("Services/Skill");
        $this->ds->setSelectedNodes($this->config->getSelectedNodes());
        $this->ds->setSelectedProfiles($this->config->getSelectedProfiles());
        $this->ds->setMode($this->config->getMode());
        $this->ds->setSkillTreeId($this->config->getSkillTreeId());
    }
    
    /**
     * @inheritdoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * @inheritdoc
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "8.0" => array(
                "namespace" => "http://www.ilias.de/Services/Skill/skll/8_0",
                "xsd_file" => "ilias_skll_8_0.xsd",
                "uses_dataset" => true,
                "min" => "8.0",
                "max" => ""),
            "7.0" => array(
                "namespace" => "http://www.ilias.de/Services/Skill/skll/7_0",
                "xsd_file" => "ilias_skll_7_0.xsd",
                "uses_dataset" => true,
                "min" => "7.0",
                "max" => ""),
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/Skill/skll/5_1",
                "xsd_file" => "ilias_skll_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "")
        );
    }
}
