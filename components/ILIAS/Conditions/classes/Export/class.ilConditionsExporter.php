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

declare(strict_types=1);

use ILIAS\Conditions\Export\Factory as ConditionExportFactory;

class ilConditionsExporter extends ilXmlExporter
{
    protected const string ENTITY = 'cond';
    protected ConditionExportFactory $factory;
    protected ilTree $tree;

    public function init(): void
    {
        global $DIC;
        $this->factory = new ConditionExportFactory($DIC->database());
        $this->tree = $DIC->repositoryTree();
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        if ($a_entity !== self::ENTITY) {
            return '';
        }
        $ref_id = min(ilObject::_getAllReferences((int) $a_id));
        $obj_ids = [];
        foreach ($this->tree->getSubTree($this->tree->getNodeData($ref_id)) as $node) {
            if (((int) $node['obj_id']) === (int) $a_id) {
                continue;
            }
            $obj_ids[] = (int) $node['obj_id'];
        }
        $infos = $this->factory->repository()->getInfosByObjectIds($obj_ids);
        $writer = $this->factory->xmlWriter();
        $writer->writeAll($infos);
        return $writer->__toString();
    }

    public function getValidSchemaVersions(
        string $a_entity
    ): array {
        return [
            "11.0" => [
                "namespace" => "http://www.ilias.de/Components/Conditions/cond/11_0",
                "xsd_file" => "ilias_cond_11_0.xsd",
                "min" => "11.0",
                "max" => ""
            ]
        ];
    }
}
