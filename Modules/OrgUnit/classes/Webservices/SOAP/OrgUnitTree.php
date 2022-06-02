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

    protected function run(array $params) : string
    {
        $orgu_ref_id = $params[self::ORGU_REF_ID];

        $orgu_exporter = new ilOrgUnitExporter();

        return $orgu_exporter->simpleExport($orgu_ref_id)->xmlDumpMem(true);
    }

    public function getName() : string
    {
        return "getOrgUnitsSimpleXML";
    }

    protected function getAdditionalInputParams() : array
    {
        return array(
            self::ORGU_REF_ID => Base::TYPE_INT,
        );
    }

    public function getOutputParams() : array
    {
        return array(self::ORG_UNIT_TREE => Base::TYPE_STRING);
    }

    public function getDocumentation() : string
    {
        return "Returns the ILIAS Organisational Units (SimpleXML)";
    }
}
