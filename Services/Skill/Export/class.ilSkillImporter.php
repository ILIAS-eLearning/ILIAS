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
 * Importer class for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillImporter extends ilXmlImporter
{
    protected ilSkillDataSet $ds;
    protected ilImportConfig $config;

    public function init() : void
    {
        $this->ds = new ilSkillDataSet();
        $this->ds->setDSPrefix("ds");

        $this->config = $this->getImport()->getConfig("Services/Skill");
        $this->ds->setSkillTreeId($this->config->getSkillTreeId());
    }

    /**
     * @inheritdoc
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
