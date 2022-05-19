<?php declare(strict_types=1);

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
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilContainerReferenceExporter extends ilXmlExporter
{
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        global $DIC;

        $log = $DIC->logger()->root();

        $eo = ilExportOptions::getInstance();

        $obj_id = end($a_ids);

        $log->debug(__METHOD__ . ': ' . $obj_id);
        if ($eo->getOption(ilExportOptions::KEY_ROOT) != $obj_id) {
            return [];
        }
        if (count(ilExportOptions::getInstance()->getSubitemsForExport()) > 1) {
            return [
                [
                    'component' => 'Services/Container',
                    'entity' => 'struct',
                    'ids' => $a_ids
                ]
            ];
        }
        return [];
    }
    
    abstract protected function initWriter(ilContainerReference $ref) : ilContainerReferenceXmlWriter;

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        global $DIC;

        $log = $DIC->logger()->root();

        $refs = ilObject::_getAllReferences((int) $a_id);
        $ref_ref_id = end($refs);
        $ref = ilObjectFactory::getInstanceByRefId($ref_ref_id, false);

        if (!$ref instanceof ilContainerReference) {
            $log->debug(__METHOD__ . $a_id . ' is not instance of type category!');
            return '';
        }
        $writer = $this->initWriter($ref);
        $writer->setMode(ilContainerReferenceXmlWriter::MODE_EXPORT);
        $writer->export(false);
        return $writer->getXml();
    }

    /**
     * @return array[]
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return [
            "4.3.0" => [
                "namespace" => "https://www.ilias.de/Modules/CategoryReference/catr/4_3",
                "xsd_file" => "ilias_catr_4_3.xsd",
                "uses_dataset" => false,
                "min" => "4.3.0",
                "max" => ""
            ]
        ];
    }

    public function init() : void
    {
    }
}
