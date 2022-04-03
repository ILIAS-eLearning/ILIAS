<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitExporter;

/**
 * Class OrgUnitTree
 * @author Martin Studer ms@studer-raimann.ch
 */
class OrgUnitTree extends Base
{
    public const ORGU_REF_ID = 'orgu_ref_id';
    public const ORG_UNIT_TREE = 'OrgUnitTree';

    final protected function run(array $params) : string
    {
        $orgu_ref_id = $params[self::ORGU_REF_ID];

        $orgu_exporter = new ilOrgUnitExporter();

        return $orgu_exporter->simpleExport($orgu_ref_id)->xmlDumpMem(true);
    }

    final public function getName() : string
    {
        return "getOrgUnitsSimpleXML";
    }

    final protected function getAdditionalInputParams() : array
    {
        return array(
            self::ORGU_REF_ID => Base::TYPE_INT,
        );
    }

    final public function getOutputParams() : array
    {
        return array(self::ORG_UNIT_TREE => Base::TYPE_STRING);
    }

    final public function getDocumentation() : string
    {
        return "Returns the ILIAS Organisational Units (SimpleXML)";
    }
}
